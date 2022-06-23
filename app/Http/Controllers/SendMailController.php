<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendMailJob;

class SendMailController extends Controller
{
    public function sendMail()
    {
        $details['to'] = 'atiqur@gmail.com';
        $details['name'] = 'Md Atiqur';
        $details['subject'] = 'Hello Laravailer';
        $details['message'] = 'Here goes all message body.';

        SendMailJob::dispatch($details);

        return response('Email sent successfully');
    }
}
