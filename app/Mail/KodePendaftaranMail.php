<?php

namespace App\Mail;

use App\Models\Pendaftar;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email berisi kode (nomor) pendaftaran yang dikirim otomatis setelah siswa
 * menyelesaikan seluruh langkah pendaftaran.
 */
class KodePendaftaranMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pendaftar $pendaftar,
        public ?string $grupWaUrl = null
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Pendaftaran PPDB Anda: ' . $this->pendaftar->nomor_pendaftaran,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.kode-pendaftaran',
            with: [
                'pendaftar'  => $this->pendaftar,
                'grupWaUrl'  => $this->grupWaUrl,
                'statusUrl'  => route('ppdb.cekStatus'),
            ],
        );
    }
}
