<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscribedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscriber;
    public $data;

    public function __construct(Subscriber $subscriber, $data)
    {
        $this->subscriber = $subscriber;
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Welcome to Our Newsletter!')
            ->view('emails.subscribed')
            ->with([
                'email' => $this->subscriber->email,
                'data' => $this->data,
            ]);
    }
}
