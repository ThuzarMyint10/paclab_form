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

class QuotationIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry, public bool $revised = false)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: ($this->revised ? "Revised quotation" : "Quotation")." {$this->enquiry->quotation_number} – please review | Pacific Lab Services");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quotation-issued');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => Documents::quotation($this->enquiry)->output(), Documents::fileName("PacLab_Quotation", $this->enquiry->quotation_number))
                ->withMime("application/pdf"),
        ];
    }
}
