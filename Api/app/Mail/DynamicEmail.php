<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynamicEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectText;
    public $data;


    public function __construct($subjectText, $data)
    {
        $this->subjectText = $subjectText;
        $this->data = $data;
    }

    public function build()
    {
        return $this->from(com_option_get('com_site_global_email'), com_option_get('com_site_title'))
            ->subject($this->subjectText)
            ->markdown('emails.dynamic-template')
            ->with([
                'data' => $this->data,
            ]);
    }
}
