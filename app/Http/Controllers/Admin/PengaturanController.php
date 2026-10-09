<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Support\GoogleDrive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Pengaturan aset global landing page (mis. gambar hero) dan integrasi layanan
 * eksternal (Google Drive, grup WhatsApp).
 *
 * Halaman ini melengkapi modul Manajemen Konten: Berita/Event/Galeri menangani
 * konten berulang, sedangkan controller ini menangani aset & tautan tunggal situs.
 */
class PengaturanController extends Controller
{
    /**
     * Tampilkan form pengaturan situs beserta status integrasi Google Drive.
     */
    public function index()
    {
        return view('admin.pengaturan.index', [
            'heroPath'  => Pengaturan::heroPath(),
            'heroUrl'   => Pengaturan::heroUrl(),
            'galeriPath' => Pengaturan::get('galeri_kegiatan_image'),
            'galeriUrl'  => Pengaturan::get('galeri_kegiatan_image') ? asset('storage/' . ltrim(Pengaturan::get('galeri_kegiatan_image'), '/')) : null,
            'kepsekPath' => Pengaturan::get('kepala_sekolah_image'),
            'kepsekUrl'  => Pengaturan::get('kepala_sekolah_image') ? asset('storage/' . ltrim(Pengaturan::get('kepala_sekolah_image'), '/')) : null,
            'waGroupUrl'=> Pengaturan::get('wa_group_url'),
            'tahunAjaran' => Pengaturan::get('tahun_ajaran', '2027/2028'),
            'adminUsername' => Pengaturan::get('admin_username', 'admin'),
            'drive'     => GoogleDrive::status(),
            'hasilUji'  => session('hasil_uji_drive'),
        ]);
    }

    /**
     * Simpan / perbarui aset situs, tahun ajaran, kredensial admin, foto terpisah.
     */
    public function update(Request $request)
    {
        $request->validate([
            'hero_image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hapus_hero'   => ['nullable', 'boolean'],
            'galeri_kegiatan_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hapus_galeri' => ['nullable', 'boolean'],
            'kepala_sekolah_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hapus_kepsek' => ['nullable', 'boolean'],
            'wa_group_url' => ['nullable', 'url', 'max:255'],
            'tahun_ajaran' => ['nullable', 'regex:/^\d{4}\/\d{4}$/', 'max:9'],
            'admin_username' => ['nullable', 'string', 'max:100'],
            'admin_password' => ['nullable', 'string', 'min:6', 'max:255'],
        ], [
            'hero_image.image' => 'Berkas gambar hero harus berupa gambar.',
            'hero_image.mimes' => 'Format gambar hero harus JPG, PNG, atau WEBP.',
            'hero_image.max'   => 'Ukuran gambar hero maksimal 4 MB.',
            'wa_group_url.url' => 'Tautan grup WhatsApp harus berupa URL yang valid (contoh: https://chat.whatsapp.com/xxxx).',
            'tahun_ajaran.regex' => 'Format tahun ajaran harus seperti 2027/2028.',
            'admin_password.min' => 'Kata sandi admin minimal 6 karakter.',
        ]);

        // Tahun ajaran SPMB — tampil di seluruh situs.
        if ($request->has('tahun_ajaran') && trim((string) $request->input('tahun_ajaran')) !== '') {
            Pengaturan::set('tahun_ajaran', trim((string) $request->input('tahun_ajaran')));
        }

        // Kredensial admin — tanpa database, bisa diganti di sini.
        if ($request->filled('admin_username')) {
            Pengaturan::set('admin_username', trim((string) $request->input('admin_username')));
        }
        if ($request->filled('admin_password')) {
            Pengaturan::set('admin_password_hash', \Illuminate\Support\Facades\Hash::make((string) $request->input('admin_password')));
        }

        // Tautan grup WhatsApp dapat diperbarui tanpa mengubah gambar hero.
        if ($request->has('wa_group_url')) {
            Pengaturan::set('wa_group_url', trim((string) $request->input('wa_group_url')) ?: null);
        }

        // Hapus gambar hero bila diminta.
        if ($request->boolean('hapus_hero')) {
            $this->deleteHero();
            Pengaturan::set('hero_image', null);
        }

        if ($request->hasFile('hero_image')) {
            $this->deleteHero();

            // Salinan lokal tetap menjadi sumber utama agar tampilan situs tidak
            // bergantung pada ketersediaan Google Drive.
            $path = $request->file('hero_image')->store('pengaturan', 'public');
            Pengaturan::set('hero_image', $path);

            // Salinan ke Drive bersifat opsional (gagal = tetap jalan).
            Pengaturan::set(
                'hero_image_drive_id',
                GoogleDrive::upload($path, basename($path), null, 'konten/hero')
            );
        }

        // Foto galeri kegiatan — panel terpisah.
        if ($request->boolean('hapus_galeri')) {
            $this->deleteFileKey('galeri_kegiatan_image', 'galeri_kegiatan_image_drive_id');
            Pengaturan::set('galeri_kegiatan_image', null);
        }
        if ($request->hasFile('galeri_kegiatan_image')) {
            $this->deleteFileKey('galeri_kegiatan_image', 'galeri_kegiatan_image_drive_id');
            $path = $request->file('galeri_kegiatan_image')->store('pengaturan', 'public');
            Pengaturan::set('galeri_kegiatan_image', $path);
            Pengaturan::set('galeri_kegiatan_image_drive_id', GoogleDrive::upload($path, basename($path), null, 'konten/galeri'));
        }

        // Foto kepala sekolah — panel terpisah.
        if ($request->boolean('hapus_kepsek')) {
            $this->deleteFileKey('kepala_sekolah_image', 'kepala_sekolah_image_drive_id');
            Pengaturan::set('kepala_sekolah_image', null);
        }
        if ($request->hasFile('kepala_sekolah_image')) {
            $this->deleteFileKey('kepala_sekolah_image', 'kepala_sekolah_image_drive_id');
            $path = $request->file('kepala_sekolah_image')->store('pengaturan', 'public');
            Pengaturan::set('kepala_sekolah_image', $path);
            Pengaturan::set('kepala_sekolah_image_drive_id', GoogleDrive::upload($path, basename($path), null, 'konten/kepsek'));
        }

        return redirect()
            ->route('admin.pengaturan.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Uji koneksi ke Google Drive (folder + izin tulis) lalu tampilkan hasilnya.
     */
    public function ujiDrive()
    {
        $hasil = GoogleDrive::ujiKoneksi();

        return redirect()
            ->route('admin.pengaturan.index')
            ->with('hasil_uji_drive', $hasil);
    }

    /**
     * Hapus berkas gambar hero lama dari disk `public` beserta salinan Drive.
     */
    protected function deleteHero(): void
    {
        $path = Pengaturan::heroPath();

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        GoogleDrive::delete(Pengaturan::get('hero_image_drive_id'));
    }

    /**
     * Hapus berkas generik (galeri kegiatan / kepala sekolah) + salinan Drive.
     */
    protected function deleteFileKey(string $key, string $driveKey): void
    {
        $path = Pengaturan::get($key);

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        GoogleDrive::delete(Pengaturan::get($driveKey));
        Pengaturan::set($driveKey, null);
    }
}
