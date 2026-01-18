<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendPhotoAndVideo extends Mailable
{
    use Queueable, SerializesModels;
    public string $redeemCode;
    /** @var array<int, Attachment> */
    public array $attachmentsList;

    /**
     * Create a new message instance.
     */
    /**
     * @param array<int, Attachment> $attachments
     */
    public function __construct(string $redeemCode, array $attachments)
    {
        $this->redeemCode = $redeemCode;
        $this->attachmentsList = $attachments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kenangan Foto dan Video dari Photo Studio Chika',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.send-photo-and-video',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return $this->attachmentsList;
    }
}
