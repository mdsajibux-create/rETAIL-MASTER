<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UnsubscribedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscriber;

    public function __construct(Subscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function build()
    {
        return $this->subject('You’ve Unsubscribed from Our Newsletter')
            ->view('emails.unsubscribed')
            ->with([
                'email' => $this->subscriber->email,
            ]);
    }
}
