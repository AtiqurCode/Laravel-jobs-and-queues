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
