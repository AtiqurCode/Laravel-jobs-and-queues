<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Laravel Jobs & Queues Two examples

- Send mail Behind the users await
- File upload in background

Go throw **laravel documentation** & many more blog to see more about jobs and Queues

## Note: To run these repo must have to be 8.1 php -v

Let's jump to the repo, first you need to clone this and save **.env.example as .env** and setup your environment or just change the database configure

```sh
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-database-user-name
DB_PASSWORD=your-database-password(if have)
```
We need to to add one more thing for sendMail system. Please configure any mail server for send email.

```sh
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=add_username_email
MAIL_PASSWORD=add_password_smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="atiqur@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Last need to add a line in **.env** File

```sh
QUEUE_CONNECTION=database
```
This is for your queue insert record in **database**

### Firs start with mail send

Run this command in profile file Terminal one by one to create all files

```sh
php artisan queue:table                 // to add jobs migration files
php artisan migrate                     // to migrate all migration file
php artisan make:job SendMailJob        // create a Job class
php artisan make:mail SendMail          // create a Mail class

```

#### configure the *App/Mail/SendMail.php* file to send emails

```sh
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.send_mail')
            ->subject($this->details['subject'])
            ->with('details', $this->details);
    }
}

```

#### configure the *App/Jobs/SendMailJob.php* class to send add jobs


```sh
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Mail\SendMail;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $details;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->details['to'])
            ->send(new SendMail($this->details));
    }
}

```

#### Let's make a function to send any mail from any controller file

```sh
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendMailJob;

class SendMailController extends Controller
{
    public function sendMail()
    {
        $details['to'] = 'your-mail@gmail.com';
        $details['name'] = 'Md Atiqur';
        $details['subject'] = 'Hello Laravailer';
        $details['message'] = 'Here goes all message body.';

        SendMailJob::dispatch($details);

        return response('Email sent successfully');
    }
}

```

#### Adding Route in *route/web.php* file

```sh
use App\Http\Controllers\SendMailController;

Route::get('send-mail', [SendMailController::class, 'sendMail']);

```

#### now run two command in separate terminal

```sh
php artisan serve               // To run the project
php artisan queue:work          // To run the queue to complete the jobs
```

### Lets try to upload a file
#### Run this command first to start the work of downloading the file
To create the symbolic link, you may use the ```sh storage:link``` Artisan command:
```sh
php artisan storage:link
```
And Also these command to create Model, Controller & jobs
```sh
php artisan make:model Image -mc                    // create a model, controller & migration file class
php artisan make:job ProcessImageThumbnails        // create a Job class
```
Migration are up to you which columns you want to add in migration files
in my migration I have just add

```sh
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('org_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
};
```

After migration file done - run in terminal
```shell
php artisan migrate
```

In models need to add ```sh $fillable``` properties
```sh
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_path'
    ];
}
```

Now need to add controller code line. I have use a blade template so also need to redirect here after complete request
```sh 
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use App\Jobs\ProcessImageThumbnails;
use Illuminate\Support\Facades\Redirect;
use Validator;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Show Upload Form
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        return view('upload_form');
    }

    /**
     * Upload Image
     *
     * @param  Request  $request
     * @return Response
     */
    public function upload(Request $request)
    {
        // upload image
        $this->validate($request, [
            'demo_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $image = $request->file('demo_image');
        $input['demo_image'] = time() . '.' . $image->getClientOriginalExtension();
        $destinationPath = Storage::path('public/uploads');
        $image->move($destinationPath, $input['demo_image']);

        // make db entry of that image
        $image = new Image;
        $image->org_path = $input['demo_image'];
        $image->save();
        
        ProcessImageThumbnails::dispatch($image);

        return Redirect::to('image/index')->with('message', 'Image uploaded successfully!');
    }
}
```

make a blade file in resource -> view directory for mine i have save it as ```sh upload_form.blade.php```

Here The body part of the blade code 
```sh
<body>
    <div class="flex-center position-ref full-height">

        <div class="content">
            <div class="m-b-md">
                <h1 class="title">Demo Upload Form</h1>

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('message'))
                <div class="success">
                    {{ session('message') }}
                </div>
                @endif

                <form method="post" action="{{ url('/image/upload') }}" enctype="multipart/form-data">
                    <div>
                        <input type="file" name="demo_image" />
                    </div>
                    <br />
                    <div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="submit" value="Upload Image" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
```

Let's add the jobs code in ```sh ProcessImageThumbnails.php``` class

```sh
<?php

namespace App\Jobs;

use App\Models\Image as ImageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Image;

class ProcessImageThumbnails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $image;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ImageModel $image)
    {
        $this->image = $image;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $image = $this->image;
        $full_image_path = Storage::disk('public/uploads/' . $image->org_path);
        $resized_image_path = Storage::disk('public/uploads/thumbs' . DIRECTORY_SEPARATOR .  $image->org_path);

        $img = Image::make($full_image_path)->resize(300, null);
        $img->save($resized_image_path);
    }
}
```

Last think I need to add the route in ```sh routes/web.php```

```sh
use App\Http\Controllers\ImageController;

Route::get('image/index', [ImageController::class, 'index']);
Route::post('image/upload', [ImageController::class, 'upload']);

```

Now just run the two commands One is for run project and on is for jobs and queue work
#### now run two command in separate terminal

```sh
php artisan serve               // To run the project
php artisan queue:work          // To run the queue to complete the jobs
```
