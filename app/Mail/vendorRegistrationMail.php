<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class vendorRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    /**
     * Create a new message instance.
     */
    public function __construct($content)
    {
        $this->content = $content;
        //dd($this->content);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vendor Registration of '.$this->content['businessname'].'',
        );
    }

    public function build()
    {
        return $this->view('email.vendorRegistration')
            ->with(['businessname' => $this->content['businessname'], 'name' => $this->content['name']])
            ->bcc(['support@parloursbooking.com']);
    }
    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     // return new Content(
    //     //     view: 'email.vendorRegistration',
    //     // );
    //     return $this->view('emails.vendorRegistration')
    //                 ->with(['parameter' => $this->content])
    //                 ->bcc(['support@parloursbooking.com']);
    // }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
