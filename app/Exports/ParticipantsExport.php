<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Rekapitulasi data pendaftar untuk keperluan pelaporan panitia PPDB.
 *
 * Tidak lagi menyimpan query di dalam objek agar instance-nya aman dibuat
 * sekali lalu dipakai untuk beberapa kondisi (mis. filter status dari dashboard).
 */
class ParticipantsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
     * @param  Collection  $participants  Koleksi model Pendaftar beserta relasi jurusan.
     */
    public function __construct(protected Collection $participants)
    {
    }

    public function collection(): Collection
    {
        return $this->participants;
    }

    /**
     * Satu baris data per pendaftar, sudah dalam urutan kolom yang siap dibaca.
     */
    public function map($p): array
    {
        return [
            $p->nomor_pendaftaran,
            $p->nama_lengkap,
            $p->nik,
            $p->nisn,
            $p->jenis_kelamin === 'L' ? 'Laki-laki' : ($p->jenis_kelamin === 'P' ? 'Perempuan' : '—'),
            $p->tempat_lahir,
            optional($p->tanggal_lahir)->format('d/m/Y') ?? '—',
            $p->agama,
            $p->asal_sekolah,
            $p->no_hp,
            $p->no_wa,
            $p->email,
            $p->alamat,
            optional($p->jurusan1)->kode,
            optional($p->jurusan2)->kode,
            $p->nama_ayah,
            $p->pekerjaan_ayah,
            $p->nama_ibu,
            $p->pekerjaan_ibu,
            $p->no_hp_ortu,
            $this->labelStatus($p->status_verifikasi),
            $p->sumber === 'luring' ? 'Luring' : 'Daring',
            optional($p->created_at)->format('d/m/Y H:i') ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'No. Pendaftaran',
            'Nama Lengkap',
            'NIK',
            'NISN',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'Asal Sekolah',
            'No. HP',
            'No. WhatsApp',
            'Email',
            'Alamat',
            'Jurusan Pilihan 1',
            'Jurusan Pilihan 2',
            'Nama Ayah',
            'Pekerjaan Ayah',
            'Nama Ibu',
            'Pekerjaan Ibu',
            'No. HP Orang Tua',
            'Status Verifikasi',
            'Sumber',
            'Tanggal Daftar',
        ];
    }

    public function title(): string
    {
        return 'Data Pendaftar';
    }

    /**
     * Label status yang ramah dibaca, bukan nilai enum mentah.
     */
    protected function labelStatus(?string $status): string
    {
        return match ($status) {
            'approved' => 'Diterima',
            'rejected' => 'Ditolak',
            'revision' => 'Perlu Revisi',
            default    => 'Menunggu Verifikasi',
        };
    }

    /**
     * Gaya kop tabel agar rekapitulasi langsung enak dibaca saat dibuka tim panitia.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            // Baris pertama: header tebal dengan latar emerald.
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '057A3C']],
                'alignment' => ['vertical' => 'center'],
            ],
        ];
    }
}
