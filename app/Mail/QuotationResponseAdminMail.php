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

class QuotationResponseAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry, public bool $approved)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "[Quotation ".($this->approved ? "APPROVED" : "DECLINED")."] {$this->enquiry->quotation_number} – {$this->enquiry->company_name}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-quotation-response');
    }

    public function attachments(): array
    {
        return [];
    }
}
