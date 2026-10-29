<?php

namespace App\Mail;

use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\TrackingUpdate;
use App\Models\User;
use App\Services\Documents;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnquiryReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry, public ?string $setupToken = null)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Enquiry received – {$this->enquiry->reference} | Pacific Lab Services");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.enquiry-received');
    }

    public function attachments(): array
    {
        return [];
    }
}
