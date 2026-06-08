<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EsgBewijsReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public array $data,
        public string $pdfBinary,
        public string $filename,
    ) {}

    public function envelope(): Envelope
    {
        $brand = config('esg.brand', 'Greidefûgels');

        return new Envelope(
            subject: sprintf(
                '%s — biodiversiteits-bewijs %s (seizoen %d)',
                $brand,
                $this->data['report']['nr'],
                $this->data['report']['season']
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.esg-bewijs-report',
            with: [
                'data' => $this->data,
                'brand' => config('esg.brand', 'Greidefûgels'),
            ],
        );
    }

    /**
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfBinary, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
