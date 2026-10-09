<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Pemindahan berkas unggahan siswa dari penyimpanan sementara ke penyimpanan
 * permanen, sekaligus menyalinnya ke Google Drive.
 *
 * Alur dua tahap dipakai karena berkas diunggah lebih dulu pada langkah 2/3
 * (disk `local`, belum ada nomor pendaftaran), baru dipindahkan saat submit
 * ketika nomor pendaftaran sudah diketahui.
 */
class PenyimpananDokumen
{
    /** Disk penyimpanan sementara saat siswa mengisi formulir bertahap. */
    public const DISK_TEMP = 'local';

    /** Disk penyimpanan permanen (dapat diakses publik melalui /storage). */
    public const DISK_PERMANEN = 'public';

    /**
     * Pindahkan satu berkas dari disk sementara ke disk permanen,
     * lalu salin ke Google Drive (bila aktif).
     *
     * @param  string|null $tempPath     Path relatif pada disk `local`
     * @param  string      $folder       Folder tujuan pada disk `public`, contoh: "dokumen/PPDB-2026-0001"
     * @param  string      $namaDasar    Nama dasar berkas, contoh: "kk"
     * @param  string|null $subFolderDrive Sub-folder di Google Drive
     * @return array{path: ?string, drive_id: ?string, nama: ?string} Hasil penyimpanan
     */
    public static function pindahkan(
        ?string $tempPath,
        string $folder,
        string $namaDasar,
        ?string $subFolderDrive = null
    ): array {
        $kosong = ['path' => null, 'drive_id' => null, 'nama' => null];

        if (!$tempPath || !Storage::disk(self::DISK_TEMP)->exists($tempPath)) {
            return $kosong;
        }

        // Pertahankan ekstensi asli agar jenis berkas tetap jelas bagi panitia.
        $ext = strtolower(pathinfo($tempPath, PATHINFO_EXTENSION)) ?: 'bin';
        $nama = $namaDasar . '-' . Str::random(6) . '.' . $ext;
        $tujuan = trim($folder, '/') . '/' . $nama;

        try {
            Storage::disk(self::DISK_PERMANEN)->writeStream(
                $tujuan,
                Storage::disk(self::DISK_TEMP)->readStream($tempPath)
            );
        } catch (\Throwable $e) {
            Log::error('Gagal memindahkan berkas unggahan ke penyimpanan permanen.', [
                'temp'   => $tempPath,
                'tujuan' => $tujuan,
                'error'  => $e->getMessage(),
            ]);

            return $kosong;
        }

        // Salinan ke Google Drive bersifat opsional: kegagalan tidak menghentikan proses.
        $driveId = GoogleDrive::upload(
            $tujuan,
            $nama,
            Storage::disk(self::DISK_PERMANEN)->mimeType($tujuan) ?: null,
            $subFolderDrive
        );

        // Berkas sementara tidak diperlukan lagi setelah berhasil dipindahkan.
        Storage::disk(self::DISK_TEMP)->delete($tempPath);

        return ['path' => $tujuan, 'drive_id' => $driveId, 'nama' => $nama];
    }

    /**
     * Hapus berkas permanen beserta salinan di Google Drive.
     */
    public static function hapus(?string $path, ?string $driveId = null): void
    {
        if ($path && Storage::disk(self::DISK_PERMANEN)->exists($path)) {
            Storage::disk(self::DISK_PERMANEN)->delete($path);
        }

        GoogleDrive::delete($driveId);
    }

    /**
     * Ukuran berkas permanen dalam satuan yang mudah dibaca (mis. "1,2 MB").
     */
    public static function ukuran(?string $path): ?string
    {
        if (!$path || !Storage::disk(self::DISK_PERMANEN)->exists($path)) {
            return null;
        }

        $byte = Storage::disk(self::DISK_PERMANEN)->size($path);

        foreach (['B', 'KB', 'MB', 'GB'] as $i => $satuan) {
            if ($byte < 1024) {
                return number_format($byte, $i === 0 ? 0 : 1, ',', '.') . ' ' . $satuan;
            }
            $byte /= 1024;
        }

        return number_format($byte, 1, ',', '.') . ' TB';
    }
}
