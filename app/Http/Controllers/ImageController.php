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
