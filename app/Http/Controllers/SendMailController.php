<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use App\Mail\vendorRegistrationMail;

class SendMailController extends Controller
{
    public function vendorRegistrationEmail($data)
    {
        $content = [
            'subject' => 'This is the mail subject',
            'body' => 'This is the email body of how to send email from laravel 10 with mailtrap.'
        ];

        Mail::to('gnana2315@gmail.com')->send(new vendorRegistrationMail($content));

        return "Email has been sent.";
    }
}
