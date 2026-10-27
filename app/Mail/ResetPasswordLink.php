<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Single-use password-reset link.
 *
 * Carries the plaintext token (which exists nowhere else — the database only
 * stores its sha256) and nothing about the account itself, so a forwarded or
 * leaked email grants only the short-lived reset it describes.
 */
final class ResetPasswordLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $token,
        public readonly int $ttlMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your KP Wear password',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.reset-password',
            with: [
                'name' => $this->name,
                'url' => url('/reset-password?token='.urlencode($this->token)),
                'ttlMinutes' => $this->ttlMinutes,
            ],
        );
    }
}
