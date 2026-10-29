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

class CoaReleasedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Certificate of Analysis {$this->enquiry->coa->report_number} | Pacific Lab Services");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.coa-released');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => Documents::coa($this->enquiry)->output(), Documents::fileName("PacLab_COA", $this->enquiry->coa->report_number))
                ->withMime("application/pdf"),
        ];
    }
}
