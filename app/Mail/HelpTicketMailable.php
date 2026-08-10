<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HelpTicketMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $ticketData;

    /**
     * Create a new message instance.
     */
    public function __construct($ticketData)
    {
        $this->ticketData = $ticketData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->sanitizeString($this->ticketData['subject'] ?? 'No Subject');
        return new Envelope(
            subject: 'New Help Ticket: ' . $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.help-ticket',
            with: [
                'name' => $this->sanitizeString($this->ticketData['name'] ?? ''),
                'email' => $this->sanitizeString($this->ticketData['email'] ?? ''),
                'subject' => $this->sanitizeString($this->ticketData['subject'] ?? ''),
                'messageText' => $this->sanitizeString($this->ticketData['message'] ?? ''),
                'priority' => $this->sanitizeString($this->ticketData['priority'] ?? ''),
            ]
        );
    }

    /**
     * Sanitize string to ensure it's safe for HTML output.
     */
    private function sanitizeString($value): string
    {
        if (is_null($value)) {
            return '';
        }

        if (!is_string($value)) {
            return (string) $value;
        }

        return $value;
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
