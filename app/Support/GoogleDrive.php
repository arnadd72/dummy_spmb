<?php

namespace App\Support;

use Google\Client as GoogleClient;
use Google\Service\Drive as DriveService;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Integrasi Google Drive (opsional).
 *
 * Semua berkas tetap disimpan di disk lokal; kelas ini hanya menyimpan SALINAN
 * ke Google Drive agar panel admin bisa melihat / membuka / mengunduh berkas
 * langsung dari Drive. Bila Drive tidak dikonfigurasi atau gagal diakses,
 * aplikasi tetap berjalan normal (fail-safe) dan hanya mencatat peringatan.
 *
 * Konfigurasi (.env):
 *   GOOGLE_DRIVE_ENABLED=true
 *   GOOGLE_DRIVE_FOLDER_ID=<id folder yang dibagikan ke service account>
 *   GOOGLE_DRIVE_CREDENTIALS=/path/ke/google-credentials.json
 *   GOOGLE_DRIVE_SHARED_DRIVE_ID=<opsional, untuk Shared Drive>
 */
class GoogleDrive
{
    /** @var DriveService|null */
    protected static ?DriveService $service = null;

    /** Cache status kesiapan agar tidak membaca .env berulang kali. */
    protected static ?bool $siap = null;

    /**
     * Apakah integrasi Drive diaktifkan, kredensial tersedia, dan paket Google
     * benar-benar terpasang.
     *
     * Nilai dibaca dari config (bukan env langsung) agar tetap benar ketika
     * `php artisan config:cache` dijalankan di produksi.
     */
    public static function enabled(): bool
    {
        if (static::$siap !== null) {
            return static::$siap;
        }

        // Tanpa paket google/apiclient seluruh integrasi dinonaktifkan agar
        // aplikasi tetap berjalan dengan penyimpanan lokal saja.
        if (!class_exists(GoogleClient::class) || !class_exists(DriveService::class)) {
            return static::$siap = false;
        }

        $aktif = filter_var(
            config('services.google_drive.enabled', env('GOOGLE_DRIVE_ENABLED', false)),
            FILTER_VALIDATE_BOOL
        );

        static::$siap = $aktif
            && static::folderId() !== null
            && static::credentialsPath() !== null;

        return static::$siap;
    }

    /**
     * ID folder induk di Google Drive.
     */
    public static function folderId(): ?string
    {
        $id = config('services.google_drive.folder_id') ?: env('GOOGLE_DRIVE_FOLDER_ID');

        return $id ? trim($id) : null;
    }

    /**
     * Lokasi berkas kredensial service account.
     *
     * Memakai GOOGLE_DRIVE_CREDENTIALS bila diisi, atau jatuh ke
     * GOOGLE_APPLICATION_CREDENTIALS yang sudah dipakai fitur OCR.
     */
    public static function credentialsPath(): ?string
    {
        $path = config('services.google_drive.credentials')
            ?: env('GOOGLE_DRIVE_CREDENTIALS')
            ?: env('GOOGLE_APPLICATION_CREDENTIALS');

        if (!$path) {
            return null;
        }

        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($path));

        return is_file($path) ? $path : null;
    }

    /**
     * Layanan Drive yang sudah terautentikasi (di-cache per request).
     */
    public static function service(): ?DriveService
    {
        if (!static::enabled()) {
            return null;
        }

        if (static::$service instanceof DriveService) {
            return static::$service;
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig(static::credentialsPath());
            $client->addScope(DriveService::DRIVE_FILE);
            $client->setApplicationName(config('app.name', 'PPDB'));

            static::$service = new DriveService($client);
        } catch (\Throwable $e) {
            Log::warning('Google Drive: gagal membuat service client.', ['error' => $e->getMessage()]);

            return null;
        }

        return static::$service;
    }

    /**
     * Unggah berkas lokal (path relatif pada disk `public`) ke Google Drive.
     *
     * @param  string      $lokalPath  Path relatif, contoh: "dokumen/kk/abc.jpg"
     * @param  string      $nama       Nama berkas yang tampil di Drive
     * @param  string|null $mime       MIME type (opsional)
     * @param  string|null $subFolder  Nama sub-folder di dalam folder induk
     * @return string|null ID berkas di Drive, atau null bila gagal
     */
    public static function upload(string $lokalPath, string $nama, ?string $mime = null, ?string $subFolder = null): ?string
    {
        $service = static::service();

        if (!$service) {
            return null;
        }

        // Baca langsung dari disk agar tidak bergantung pada base_path().
        if (!Storage::disk('public')->exists($lokalPath)) {
            Log::warning('Google Drive: berkas lokal tidak ditemukan untuk diunggah.', ['path' => $lokalPath]);

            return null;
        }

        try {
            $isi = Storage::disk('public')->get($lokalPath);

            $parent = static::folderId();
            if ($subFolder) {
                $parent = static::pastikanFolder($subFolder, $parent);
            }

            $meta = new DriveFile([
                'name'    => $nama,
                'parents' => $parent ? [$parent] : [],
            ]);

            $opsi = ['fields' => 'id, name, webViewLink'];
            if ($mime) {
                $opsi['mimeType'] = $mime;
            }

            $berkas = $service->files->create($meta, array_merge($opsi, [
                'data'       => $isi,
                'uploadType' => 'multipart',
            ]));

            return $berkas->getId();
        } catch (\Throwable $e) {
            Log::warning('Google Drive: gagal mengunggah berkas.', [
                'path'  => $lokalPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Hapus berkas di Drive berdasarkan ID (abaikan bila gagal).
     */
    public static function delete(?string $fileId): bool
    {
        if (!$fileId) {
            return false;
        }

        $service = static::service();

        if (!$service) {
            return false;
        }

        try {
            $service->files->delete($fileId);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Google Drive: gagal menghapus berkas.', [
                'file_id' => $fileId,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Cari ID sub-folder berdasarkan nama, buat bila belum ada.
     */
    protected static function pastikanFolder(string $nama, ?string $parentId): ?string
    {
        $service = static::service();

        if (!$service) {
            return $parentId;
        }

        // Nama folder perlu di-escape dari kutip tunggal untuk query Drive.
        $namaAman = str_replace("'", "\\'", $nama);

        $q = "mimeType = 'application/vnd.google-apps.folder'"
            . " and name = '{$namaAman}' and trashed = false";
        if ($parentId) {
            $q .= " and '{$parentId}' in parents";
        }

        try {
            $hasil = $service->files->listFiles(['q' => $q, 'fields' => 'files(id, name)', 'pageSize' => 1]);

            if (!empty($hasil->getFiles())) {
                return $hasil->getFiles()[0]->getId();
            }

            $meta = new DriveFile([
                'name'     => $nama,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents'  => $parentId ? [$parentId] : [],
            ]);

            return $service->files->create($meta, ['fields' => 'id'])->getId();
        } catch (\Throwable $e) {
            Log::warning('Google Drive: gagal menyiapkan folder.', [
                'folder' => $nama,
                'error'  => $e->getMessage(),
            ]);

            return $parentId;
        }
    }

    /**
     * ID sub-folder berdasarkan nama (dibuat bila belum ada).
     *
     * Berguna untuk menyimpan tautan folder agar panitia bisa membuka seluruh
     * berkas satu siswa langsung di Google Drive.
     */
    public static function subFolderId(string $nama): ?string
    {
        if (!static::enabled()) {
            return null;
        }

        return static::pastikanFolder($nama, static::folderId());
    }

    /**
     * URL pratinjau (lihat) berkas di Drive.
     */
    public static function viewUrl(?string $fileId): ?string
    {
        return $fileId ? "https://drive.google.com/file/d/{$fileId}/view" : null;
    }

    /**
     * URL unduh langsung berkas di Drive.
     */
    public static function downloadUrl(?string $fileId): ?string
    {
        return $fileId ? "https://drive.google.com/uc?export=download&id={$fileId}" : null;
    }

    /**
     * Tautan folder induk di Drive.
     */
    public static function folderUrl(): ?string
    {
        $id = static::folderId();

        return $id ? "https://drive.google.com/drive/folders/{$id}" : null;
    }

    /**
     * Ringkasan status untuk ditampilkan di panel admin.
     *
     * @return array{aktif: bool, folder_id: ?string, folder_url: ?string, kredensial: ?string, pesan: ?string}
     */
    public static function status(): array
    {
        $aktif = filter_var(
            config('services.google_drive.enabled', env('GOOGLE_DRIVE_ENABLED', false)),
            FILTER_VALIDATE_BOOL
        );
        $folder = static::folderId();
        $kredensial = static::credentialsPath();
        $pesan = null;

        if (!$aktif) {
            $pesan = 'Integrasi Google Drive nonaktif (GOOGLE_DRIVE_ENABLED=false). Berkas hanya disimpan lokal.';
        } elseif (!$folder) {
            $pesan = 'GOOGLE_DRIVE_FOLDER_ID belum diisi. Berkas hanya disimpan lokal.';
        } elseif (!$kredensial) {
            $pesan = 'Berkas kredensial service account tidak ditemukan. Berkas hanya disimpan lokal.';
        }

        return [
            'aktif'      => static::enabled(),
            'folder_id'  => $folder,
            'folder_url' => static::folderUrl(),
            'kredensial' => $kredensial,
            'pesan'      => $pesan,
        ];
    }

    /**
     * Uji koneksi: memastikan service account benar-benar bisa mengakses folder.
     *
     * @return array{ok: bool, pesan: string}
     */
    public static function ujiKoneksi(): array
    {
        if (!static::enabled()) {
            return ['ok' => false, 'pesan' => static::status()['pesan'] ?? 'Google Drive belum dikonfigurasi.'];
        }

        $service = static::service();

        if (!$service) {
            return ['ok' => false, 'pesan' => 'Gagal membuat koneksi ke Google Drive.'];
        }

        try {
            $folder = $service->files->get(static::folderId(), ['fields' => 'id, name']);
            $nama = $folder->getName();

            // Uji tulis: buat lalu hapus berkas kecil agar tahu izin sebenarnya.
            $uji = $service->files->create(new DriveFile([
                'name'    => '.ppdb-uji-koneksi.txt',
                'parents' => [static::folderId()],
            ]), [
                'data'       => 'uji koneksi PPDB ' . now()->toDateTimeString(),
                'mimeType'   => 'text/plain',
                'uploadType' => 'multipart',
                'fields'     => 'id',
            ]);

            $service->files->delete($uji->getId());

            return ['ok' => true, 'pesan' => "Berhasil terhubung ke folder \"{$nama}\" dan izin tulis tersedia."];
        } catch (\Throwable $e) {
            return ['ok' => false, 'pesan' => 'Gagal mengakses folder Drive: ' . $e->getMessage()];
        }
    }
}
