<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable

{
    use Queueable, SerializesModels;

    public $user;
    public $token;

    public function __construct($user)
    {
        $this->user = $user;
        $this->token = $user->email_verify_token;
    }

    public function build()
    {
        return $this->subject('Email Verification') // Email subject
        ->view('mail.email-verification-mail') // View path
        ->with([
            'customer' => $this->user, // Pass the customer object
            'token' => $this->token, // Pass the token
        ]);
    }
}

