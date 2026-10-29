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

class PasswordLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $token, public bool $newAccount = false)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->newAccount ? "Set up your Pacific Lab customer portal password" : "Reset your Pacific Lab portal password");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.password-link');
    }

    public function attachments(): array
    {
        return [];
    }
}
