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

Run this command one by one to create all files

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
