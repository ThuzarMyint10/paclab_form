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

class InvoiceIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Tax Invoice {$this->invoice->invoice_number} | Pacific Lab Services");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invoice-issued');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => Documents::invoice($this->invoice)->output(), Documents::fileName("PacLab_Tax_Invoice", $this->invoice->invoice_number))
                ->withMime("application/pdf"),
        ];
    }
}
