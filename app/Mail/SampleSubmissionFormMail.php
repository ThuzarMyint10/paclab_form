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

class SampleSubmissionFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Sample Submission Form {$this->enquiry->ssf_number} – {$this->enquiry->reference} | Pacific Lab Services");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.sample-submission-form');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => Documents::ssf($this->enquiry)->output(), Documents::fileName("PacLab_Sample_Submission_Form", $this->enquiry->ssf_number))
                ->withMime("application/pdf"),
        ];
    }
}
