<?php

namespace App\Mail;

use App\Support\ReminderTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * One SIBSTR reminder, rendered from a BPS template (the standard text when
 * none is given). The layout around the template — button, extra-message
 * panel, footer — is fixed here.
 */
class SibstrTriwulanReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $tahun,
        public int $triwulan,
        public ?string $namaPerusahaan,
        public ?string $penghubung,
        public bool $inProgress,
        public ?string $extraMessage = null,
        public ?string $fromName = null,
        public ?string $replyToAddress = null,
        public ?string $subjectTemplate = null,
        public ?string $bodyTemplate = null,
        public bool $isTest = false,
    ) {
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('sibstr.reminder.from_address') ?: config('mail.from.address');
        $subject     = ReminderTemplate::subject($this->subjectTemplate ?: ReminderTemplate::DEFAULT_SUBJECT, $this->vars());

        return new Envelope(
            from: new Address($fromAddress, $this->fromName ?: config('mail.from.name')),
            replyTo: $this->replyToAddress ? [new Address($this->replyToAddress, $this->fromName)] : [],
            subject: ($this->isTest ? '[UJI COBA] ' : '') . $subject,
        );
    }

    public function content(): Content
    {
        [$before, $after] = ReminderTemplate::body($this->bodyTemplate ?: ReminderTemplate::DEFAULT_BODY, $this->vars());

        return new Content(
            markdown: 'emails.sibstr.pengingat-triwulan',
            with: [
                'bodyBefore' => $before,
                'bodyAfter'  => $after,
                'url'        => route('survey.sibstr.entry'),
            ],
        );
    }

    /** The rendered email with its subject shown above it, for BPS previews. */
    public function previewHtml(): string
    {
        $banner = '<div style="font-family:Arial,sans-serif;font-size:14px;padding:12px 16px;background:#fffbeb;'
            . 'border-bottom:1px solid #fde68a;color:#78350f"><strong>Subjek:</strong> '
            . e($this->envelope()->subject) . '</div>';

        return preg_replace_callback('/<body[^>]*>/i', fn ($m) => $m[0] . $banner, $this->render(), 1);
    }

    private function vars(): array
    {
        return ReminderTemplate::variables(
            $this->tahun,
            $this->triwulan,
            $this->namaPerusahaan,
            $this->penghubung,
            $this->inProgress,
            route('survey.sibstr.entry'),
        );
    }
}
