<?php

namespace App\Models;

use App\Support\GoogleDrive;
use App\Support\PenyimpananDokumen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Pendaftar extends Model
{
    use HasFactory;

    protected $table = 'pendaftar';

    protected $fillable = [
        'nomor_pendaftaran', 'periode', 'sumber', 'nisn', 'nik', 'no_kk', 'nama_lengkap',
        'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'alamat',
        'anak_ke', 'jumlah_saudara', 'rt', 'rw', 'desa', 'kecamatan', 'kabupaten', 'propinsi',
        'asal_sekolah', 'tamat_tahun', 'no_ijazah', 'lama_belajar',
        'smp_kecamatan', 'smp_kabupaten', 'smp_propinsi', 'ijazah_nomor_tahun', 'nilai_ujian',
        'no_hp', 'no_wa', 'email',
        'nama_ayah', 'pekerjaan_ayah', 'ayah_ttl', 'ayah_pendidikan',
        'nama_ibu', 'pekerjaan_ibu', 'ibu_ttl', 'ibu_pendidikan',
        'no_hp_ortu', 'alamat_ortu',
        'ortu_rt', 'ortu_rw', 'ortu_desa', 'ortu_kecamatan', 'ortu_kabupaten', 'ortu_propinsi',
        'penghasilan',
        'golongan_darah', 'tinggi_badan', 'berat_badan', 'penyakit_pernah_diderita',
        'hobi_olahraga', 'hobi_kesenian', 'hobi_organisasi', 'hobi_prestasi', 'hobi_lain',
        'ukuran_seragam', 'referensi_pendaftar',
        'jurusan_1_id', 'jurusan_2_id', 'rekomendasi_jurusan_id', 'rekomendasi_skor',
        'status_verifikasi', 'catatan_verifikasi', 'catatan_penolakan',
        'kk_path', 'akta_path', 'rapor_path', 'nilai_rapor', 'minat',
        'kk_drive_id', 'akta_drive_id', 'rapor_drive_id',
        'drive_folder_id', 'notifikasi_email_at', 'data_kustom',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'rekomendasi_skor' => 'array',
        'nilai_rapor' => 'array',
        'nilai_ujian' => 'array',
        'data_kustom' => 'array',
        'anak_ke' => 'integer',
        'jumlah_saudara' => 'integer',
        'tinggi_badan' => 'integer',
        'berat_badan' => 'integer',
        'notifikasi_email_at' => 'datetime',
    ];

    public function jurusan1()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_1_id');
    }

    public function jurusan2()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_2_id');
    }

    public function rekomendasiJurusan()
    {
        return $this->belongsTo(Jurusan::class, 'rekomendasi_jurusan_id');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status_verifikasi) {
            'approved'  => 'green',
            'rejected'  => 'red',
            'revision'  => 'yellow',
            default     => 'blue',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status_verifikasi) {
            'approved'  => 'Diterima',
            'rejected'  => 'Ditolak',
            'revision'  => 'Perlu Revisi',
            default     => 'Menunggu',
        };
    }

    /**
     * Apakah pendaftaran ini diinput manual oleh panitia (luring).
     */
    public function getIsLuringAttribute(): bool
    {
        return $this->sumber === 'luring';
    }

    /**
     * Nomor WhatsApp efektif: pakai kolom `no_wa` bila ada, jika tidak
     * jatuh ke `no_hp`. Dipakai halaman detail admin agar tautan WhatsApp
     * tetap bisa dibuat untuk data lama yang belum punya `no_wa`.
     */
    public function getNomorWaAttribute(): ?string
    {
        return $this->no_wa ?: $this->no_hp ?: null;
    }

    /**
     * Tautan wa.me dari nomor WhatsApp efektif.
     *
     * Nomor lokal Indonesia (08xx / 8xx) dinormalisasi ke format
     * internasional (62xx) supaya tautan bisa langsung dibuka panitia.
     */
    public function getWaUrlAttribute(): ?string
    {
        $nomor = $this->nomor_wa;

        if (blank($nomor)) {
            return null;
        }

        $digit = preg_replace('/\D/', '', $nomor);

        if (str_starts_with($digit, '0')) {
            $digit = '62' . substr($digit, 1);
        } elseif (str_starts_with($digit, '8')) {
            $digit = '62' . $digit;
        }

        return $digit ? 'https://wa.me/' . $digit : null;
    }

    /**
     * Label rentang penghasilan bulanan sesuai formulir SPMB (kode 01–05).
     */
    public function getPenghasilanLabelAttribute(): ?string
    {
        return match ($this->penghasilan) {
            '01' => '< Rp 500.000',
            '02' => 'Rp 500.000 – Rp 999.999',
            '03' => 'Rp 1.000.000 – Rp 1.999.999',
            '04' => 'Rp 2.000.000 – Rp 4.999.999',
            '05' => '> Rp 5.000.000',
            default => null,
        };
    }

    /**
     * Daftar opsi penghasilan untuk form & cetak (kode => label).
     *
     * @return array<string, string>
     */
    public static function opsiPenghasilan(): array
    {
        return [
            '01' => '< Rp 500.000',
            '02' => 'Rp 500.000 – Rp 999.999',
            '03' => 'Rp 1.000.000 – Rp 1.999.999',
            '04' => 'Rp 2.000.000 – Rp 4.999.999',
            '05' => '> Rp 5.000.000',
        ];
    }

    /**
     * Daftar opsi ukuran seragam sesuai formulir.
     *
     * @return array<int, string>
     */
    public static function opsiUkuranSeragam(): array
    {
        return ['M', 'L', 'XL', 'XXL', 'XXXL', 'Jumbo'];
    }

    /**
     * Daftar mata pelajaran Nilai Ujian pada bagian B.
     *
     * @return array<string, string>
     */
    public static function opsiNilaiUjian(): array
    {
        return [
            'b_indonesia' => 'Bahasa Indonesia',
            'b_inggris'   => 'Bahasa Inggris',
            'matematika'  => 'Matematika',
            'ipa'         => 'IPA',
        ];
    }

    /**
     * Alamat murid lengkap satu baris (dari kolom terurai).
     */
    public function getAlamatLengkapAttribute(): ?string
    {
        $bagian = array_filter([
            $this->rt || $this->rw ? 'RT ' . ($this->rt ?: '-') . '/RW ' . ($this->rw ?: '-') : null,
            $this->desa ? 'Desa ' . $this->desa : null,
            $this->kecamatan ? 'Kec. ' . $this->kecamatan : null,
            $this->kabupaten,
            $this->propinsi,
        ]);

        return $bagian ? implode(', ', $bagian) : ($this->alamat ?: null);
    }

    /**
     * Alamat orang tua lengkap satu baris; jatuh ke alamat murid bila kosong.
     */
    public function getAlamatOrtuLengkapAttribute(): ?string
    {
        $bagian = array_filter([
            $this->ortu_rt || $this->ortu_rw ? 'RT ' . ($this->ortu_rt ?: '-') . '/RW ' . ($this->ortu_rw ?: '-') : null,
            $this->ortu_desa ? 'Desa ' . $this->ortu_desa : null,
            $this->ortu_kecamatan ? 'Kec. ' . $this->ortu_kecamatan : null,
            $this->ortu_kabupaten,
            $this->ortu_propinsi,
        ]);

        return $bagian ? implode(', ', $bagian) : ($this->alamat_ortu ?: $this->alamat_lengkap);
    }

    /**
     * Daftar dokumen yang diunggah calon siswa, lengkap dengan tautan lokal
     * dan tautan Google Drive (bila salinannya berhasil diunggah).
     *
     * Dipakai oleh halaman admin "Verifikasi Berkas" supaya panitia bisa
     * melihat, membuka, dan mengunduh setiap berkas tanpa perlu tahu detail
     * penyimpanan di baliknya.
     *
     * @return array<int, array<string, mixed>>
     */
    public function dokumenList(): array
    {
        $berkas = [
            ['kunci' => 'kk',    'label' => 'Kartu Keluarga', 'path' => $this->kk_path,    'drive_id' => $this->kk_drive_id],
            ['kunci' => 'akta',  'label' => 'Akta Kelahiran', 'path' => $this->akta_path,  'drive_id' => $this->akta_drive_id],
            ['kunci' => 'rapor', 'label' => 'Rapor',          'path' => $this->rapor_path, 'drive_id' => $this->rapor_drive_id],
        ];

        return array_map(function (array $dok) {
            $path     = $dok['path'];
            $driveId  = $dok['drive_id'];
            $adaLokal = $path && Storage::disk(PenyimpananDokumen::DISK_PERMANEN)->exists($path);

            return [
                'kunci'          => $dok['kunci'],
                'label'          => $dok['label'],
                'path'           => $path,
                'ada'            => (bool) $adaLokal,
                'nama_berkas'    => $path ? basename($path) : null,
                'url'            => $adaLokal ? asset('storage/' . ltrim($path, '/')) : null,
                'ukuran'         => $adaLokal ? PenyimpananDokumen::ukuran($path) : null,
                'drive_id'       => $driveId,
                'drive_view'     => GoogleDrive::viewUrl($driveId),
                'drive_download' => GoogleDrive::downloadUrl($driveId),
            ];
        }, $berkas);
    }

    /**
     * Generate nomor pendaftaran berikutnya: PPDB-2026-XXXX.
     *
     * Nomor diambil dari urutan tertinggi yang sudah ada (bukan jumlah baris)
     * supaya tidak pernah menghasilkan nomor duplikat ketika ada data terhapus.
     */
    public static function generateNomor(): string
    {
        $tahun = date('Y');
        $prefix = "PPDB-{$tahun}-";

        $lastNumber = static::where('nomor_pendaftaran', 'like', $prefix . '%')
            ->pluck('nomor_pendaftaran')
            ->map(fn ($nomor) => (int) substr($nomor, strlen($prefix)))
            ->max();

        $next = ($lastNumber ?? 0) + 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
