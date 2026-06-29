<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryEarningsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $template;
    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($template, $data)
    {
        $this->template = $template;
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->data['subject'] ?? 'Delivery Earnings Mail';
        return new Envelope(subject: $subject);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if ($this->template) {
            // If a raw template is provided, replace placeholders dynamically
            $processedTemplate = $this->template;
            foreach ($this->data as $key => $value) {
                $processedTemplate = str_replace("{{ $key }}", e($value), $processedTemplate);
            }

            return new Content(
                html: $processedTemplate // Send raw HTML if template provided
            );
        } else {
            // If no raw template, use Blade view
            return new Content(
                view: 'emails.delivery_earnings', // Path to Blade view
                with: $this->data // Pass dynamic data to the Blade view
            );
        }
    }

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
