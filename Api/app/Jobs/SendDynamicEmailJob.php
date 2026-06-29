<?php

namespace App\Jobs;

use App\Mail\DynamicEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDynamicEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipient;
    protected $subject;
    protected $full_message_data;

    public function __construct($email, $subject, $full_message_data)
    {
        $this->recipient = $email;
        $this->subject = $subject;
        $this->full_message_data = $full_message_data;
    }

    public function handle(): void
    {
        Mail::to($this->recipient)->send(new DynamicEmail($this->subject, $this->full_message_data));
    }
}
