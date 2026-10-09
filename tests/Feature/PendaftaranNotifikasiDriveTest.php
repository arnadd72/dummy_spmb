<?php

namespace Tests\Feature;

use App\Mail\KodePendaftaranMail;
use App\Models\Berita;
use App\Models\Galeri;
use App\Models\Jurusan;
use App\Models\Pendaftar;
use App\Models\Pengaturan;
use App\Support\GoogleDrive;
use App\Support\PenyimpananDokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menguji fitur tahap lanjutan PPDB:
 *  - Pemberitahuan & grup WhatsApp setelah siswa menyelesaikan formulir.
 *  - Email otomatis berisi kode pendaftaran (sekali kirim).
 *  - Penyimpanan dokumen siswa ke penyimpanan permanen + salinan Google Drive.
 *  - Panel admin melihat / membuka / mengunduh berkas.
 *  - Gambar konten yang diunggah admin disalin ke Google Drive.
 *
 * Integrasi Google Drive sengaja diuji dalam keadaan NONAKTIF: itulah kondisi
 * produksi yang paling mungkin dan memastikan aplikasi tetap berjalan penuh
 * (fail-safe) walau Drive belum dikonfigurasi.
 */
class PendaftaranNotifikasiDriveTest extends TestCase
{
    use RefreshDatabase;

    /** Path berkas uji pada disk `public` yang perlu dibersihkan setelah tes. */
    private array $berkasUji = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // Pastikan Drive nonaktif pada lingkungan tes.
        config()->set('services.google_drive.enabled', false);
        config()->set('services.google_drive.folder_id', null);
    }

    protected function tearDown(): void
    {
        foreach ($this->berkasUji as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $this->berkasUji = [];

        parent::tearDown();
    }

    /** Berkas PDF tiruan agar aturan validasi berkas benar-benar teruji. */
    private function pdfFile(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'ppdb') . '.pdf';

        file_put_contents($path, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

        return new UploadedFile($path, $name, 'application/pdf', null, true);
    }

    /**
     * Isi data Bagian A–D formulir SPMB (nama kolom => nilai) sesuai struktur
     * session yang dihasilkan oleh langkah 5, 6, dan 7.
     *
     * @return array<string, array<string, mixed>>
     */
    private function bagian(): array
    {
        return [
            'step5' => [
                'nisn'          => '0081234567',
                'nik'           => '3302010101090001',
                'no_kk'         => '3302010101090002',
                'nama_lengkap'  => 'Budi Santoso',
                'jenis_kelamin' => 'L',
                'tempat_lahir'  => 'Banyumas',
                'tanggal_lahir' => '2009-01-01',
                'agama'         => 'Islam',
                'anak_ke'       => 2,
                'jumlah_saudara'=> 3,
                'alamat'        => 'RT 02/RW 04, Desa Wangon, Kec. Wangon, Banyumas, Jawa Tengah',
                'rt'            => '02',
                'rw'            => '04',
                'desa'          => 'Wangon',
                'kecamatan'     => 'Wangon',
                'kabupaten'     => 'Banyumas',
                'propinsi'      => 'Jawa Tengah',
                'asal_sekolah'  => 'SMP Negeri 1 Wangon',
                'tamat_tahun'   => '2026',
                'no_ijazah'     => 'DN-02 D/12345/2026',
                'no_hp'         => '081234567890',
                'email'         => 'budi@example.com',
                'penghasilan'   => '03',
                'referensi_pendaftar' => 'Guru SMP',
            ],
            'step6' => [
                'nama_ayah'      => 'Ayah Budi',
                'ayah_ttl'       => 'Banyumas, 1 Januari 1975',
                'ayah_pendidikan'=> 'SMA/SMK/MA',
                'pekerjaan_ayah' => 'Petani',
                'nama_ibu'       => 'Ibu Budi',
                'ibu_ttl'        => 'Banyumas, 1 Maret 1978',
                'ibu_pendidikan' => 'SMP/MTs',
                'pekerjaan_ibu'  => 'Pedagang',
                'no_hp_ortu'     => '081298765432',
                'alamat_ortu'    => 'RT 02/RW 04, Desa Wangon, Kec. Wangon, Banyumas, Jawa Tengah',
                'ortu_rt'        => '02',
                'ortu_rw'        => '04',
                'ortu_desa'      => 'Wangon',
                'ortu_kecamatan' => 'Wangon',
                'ortu_kabupaten' => 'Banyumas',
                'ortu_propinsi'  => 'Jawa Tengah',
            ],
            'step7' => [
                'smp_kecamatan'     => 'Wangon',
                'smp_kabupaten'     => 'Banyumas',
                'smp_propinsi'      => 'Jawa Tengah',
                'ijazah_nomor_tahun'=> 'DN-02 D/12345/2026',
                'lama_belajar'      => '3',
                'nilai_ujian'       => ['b_indonesia' => 85, 'b_inggris' => 80, 'matematika' => 82, 'ipa' => 78],
                'golongan_darah'    => 'O',
                'tinggi_badan'      => 165,
                'berat_badan'       => 55,
                'penyakit_pernah_diderita' => null,
                'hobi_olahraga'     => 'Sepak bola',
                'hobi_kesenian'     => 'Musik',
                'hobi_organisasi'   => 'OSIS',
                'hobi_prestasi'     => null,
                'hobi_lain'         => null,
                'ukuran_seragam'    => 'L',
            ],
        ];
    }

    /**
     * Isi session formulir multi-langkah sampai data siap submit.
     *
     * @param  array<string, string>  $berkas  path berkas sementara per jenis dokumen
     */
    private function sessionSiapSubmit(array $berkas = []): array
    {
        $jurusan = Jurusan::orderBy('kode')->firstOrFail();

        return [
            'ppdb' => [
                'step1' => ['nama_lengkap' => 'Budi Santoso', 'nisn' => '0081234567', 'setuju' => true],
                'step2' => $berkas,
                'step3' => [],
                'step4' => [
                    'jurusan_1_id' => $jurusan->id,
                    'jurusan_2_id' => null,
                    'minat'        => 'teknik',
                ],
                'nilai_rapor' => ['matematika' => 85, 'ipa_fisika' => 80],
            ] + $this->bagian(),
        ];
    }

    /* ─────────────── Submit: penyimpanan dokumen ─────────────── */

    public function test_submit_memindahkan_dokumen_dari_sementara_ke_penyimpanan_permanen(): void
    {
        // Berkas sementara seperti hasil upload langkah 2/3.
        Storage::disk('local')->put('temp/kk/kk.pdf', 'isi-kk');
        Storage::disk('local')->put('temp/akta/akta.pdf', 'isi-akta');

        $this->withSession($this->sessionSiapSubmit([
            'kk_path'   => 'temp/kk/kk.pdf',
            'akta_path' => 'temp/akta/akta.pdf',
        ]))
            ->post(route('ppdb.submit'), ['konfirmasi_final' => '1'])
            ->assertRedirect();

        $pendaftar = Pendaftar::firstOrFail();

        foreach (['kk', 'akta'] as $jenis) {
            $path = $pendaftar->{"{$jenis}_path"};

            $this->assertNotNull($path, "Path {$jenis} harus tersimpan.");
            $this->berkasUji[] = $path;

            $this->assertStringStartsWith('dokumen/' . $pendaftar->nomor_pendaftaran, $path);
            $this->assertTrue(Storage::disk('public')->exists($path));
        }

        // Berkas sementara sudah dibersihkan dan tidak ada sisa di disk lokal.
        $this->assertFalse(Storage::disk('local')->exists('temp/kk/kk.pdf'));
        $this->assertFalse(Storage::disk('local')->exists('temp/akta/akta.pdf'));

        $this->assertNull($pendaftar->kk_drive_id, 'Drive nonaktif => tidak ada id Drive.');
    }

    public function test_rapor_opsional_dan_boleh_kosong(): void
    {
        $this->withSession($this->sessionSiapSubmit())
            ->post(route('ppdb.submit'), ['konfirmasi_final' => '1'])
            ->assertRedirect();

        $pendaftar = Pendaftar::firstOrFail();

        $this->assertNull($pendaftar->kk_path);
        $this->assertNull($pendaftar->rapor_path);
        $this->assertSame('pending', $pendaftar->status_verifikasi);
    }

    /* ─────────────── Email kode pendaftaran ─────────────── */

    public function test_email_kode_pendaftaran_terkirim_otomatis_setelah_submit(): void
    {
        Mail::fake();

        $this->withSession($this->sessionSiapSubmit())
            ->post(route('ppdb.submit'), ['konfirmasi_final' => '1'])
            ->assertRedirect();

        $pendaftar = Pendaftar::firstOrFail();

        Mail::assertSent(KodePendaftaranMail::class, function (KodePendaftaranMail $mail) use ($pendaftar) {
            return $mail->pendaftar->nomor_pendaftaran === $pendaftar->nomor_pendaftaran
                && $mail->hasTo('budi@example.com');
        });

        $this->assertNotNull(
            $pendaftar->fresh()->notifikasi_email_at,
            'Waktu notifikasi harus tercatat setelah email terkirim.'
        );
    }

    public function test_email_tidak_dikirim_dua_kali(): void
    {
        Mail::fake();

        $this->withSession($this->sessionSiapSubmit())
            ->post(route('ppdb.submit'), ['konfirmasi_final' => '1']);

        Mail::assertSentCount(1);

        // Panggil pengiriman ulang lewat helper publik controller: harus dilewati.
        $pendaftar = Pendaftar::firstOrFail();
        app(\App\Http\Controllers\PendaftaranController::class)->kirimKodePendaftaran($pendaftar);

        Mail::assertSentCount(1);
    }

    public function test_email_tidak_gagal_walau_tanpa_alamat_email(): void
    {
        Mail::fake();

        $session = $this->sessionSiapSubmit();
        unset($session['ppdb']['step5']['email']);

        $this->withSession($session)
            ->post(route('ppdb.submit'), ['konfirmasi_final' => '1'])
            ->assertRedirect();

        $pendaftar = Pendaftar::firstOrFail();

        Mail::assertNothingSent();
        $this->assertNull($pendaftar->notifikasi_email_at);
        $this->assertNull($pendaftar->email);
    }

    /* ─────────────── Halaman sukses + grup WhatsApp ─────────────── */

    public function test_halaman_sukses_menampilkan_tautan_grup_whatsapp(): void
    {
        Pengaturan::set('wa_group_url', 'https://chat.whatsapp.com/ujiGrup123');

        $pendaftar = $this->buatPendaftar();

        $this->get(route('ppdb.sukses', $pendaftar->nomor_pendaftaran))
            ->assertStatus(200)
            ->assertSee('https://chat.whatsapp.com/ujiGrup123')
            ->assertSee('Gabung Grup WhatsApp', false)
            ->assertSee('btn-wa-group', false)
            ->assertSee($pendaftar->nomor_pendaftaran);
    }

    public function test_halaman_sukses_tanpa_tautan_grup_tetap_tampil(): void
    {
        Pengaturan::forget('wa_group_url');

        $pendaftar = $this->buatPendaftar();

        $this->get(route('ppdb.sukses', $pendaftar->nomor_pendaftaran))
            ->assertStatus(200)
            ->assertDontSee('btn-wa-group', false)
            ->assertSee($pendaftar->nomor_pendaftaran);
    }

    public function test_email_memuat_tautan_grup_whatsapp_bila_diatur(): void
    {
        Pengaturan::set('wa_group_url', 'https://chat.whatsapp.com/ujiGrup123');

        $mail = new KodePendaftaranMail($this->buatPendaftar(), 'https://chat.whatsapp.com/ujiGrup123');

        $html = $mail->render();

        $this->assertStringContainsString('https://chat.whatsapp.com/ujiGrup123', $html);
        $this->assertStringContainsString('Gabung Grup WhatsApp', $html);
    }

    /* ─────────────── Panel admin: berkas ─────────────── */

    public function test_admin_dapat_melihat_membuka_dan_mengunduh_berkas_siswa(): void
    {
        Storage::disk('public')->put('dokumen/PPDB-2026-0001/kk-abc123.pdf', 'isi-kk');
        $this->berkasUji[] = 'dokumen/PPDB-2026-0001/kk-abc123.pdf';

        $pendaftar = $this->buatPendaftar([
            'kk_path' => 'dokumen/PPDB-2026-0001/kk-abc123.pdf',
        ]);

        $this->get(route('admin.documents'))
            ->assertStatus(200)
            ->assertSee('kk-abc123.pdf')
            ->assertSee('Lihat')
            ->assertSee('Unduh')
            ->assertSee('Berkas');

        // Deskripsi berkas di level model memuat URL lokal yang benar.
        $dok = collect($pendaftar->dokumenList())->firstWhere('kunci', 'kk');

        $this->assertTrue($dok['ada']);
        $this->assertStringContainsString('/storage/dokumen/PPDB-2026-0001/kk-abc123.pdf', $dok['url']);
        $this->assertNotNull($dok['ukuran']);
        $this->assertNull($dok['drive_view'], 'Drive nonaktif => tidak ada tautan Drive.');
    }

    public function test_dokumen_yang_hilang_ditandai_tidak_tersedia(): void
    {
        $pendaftar = $this->buatPendaftar(['kk_path' => 'dokumen/tidak-ada.pdf']);

        $dok = collect($pendaftar->dokumenList())->firstWhere('kunci', 'kk');

        $this->assertFalse($dok['ada']);
        $this->assertNull($dok['url']);

        $this->get(route('admin.documents'))
            ->assertStatus(200)
            ->assertSee('Belum diunggah');
    }

    /* ─────────────── Pengaturan: WhatsApp & Drive ─────────────── */

    public function test_admin_dapat_menyimpan_tautan_grup_whatsapp(): void
    {
        $this->put(route('admin.pengaturan.update'), [
            'wa_group_url' => 'https://chat.whatsapp.com/grupResmi',
        ])->assertRedirect(route('admin.pengaturan.index'));

        $this->assertSame(
            'https://chat.whatsapp.com/grupResmi',
            Pengaturan::get('wa_group_url')
        );
    }

    public function test_tautan_grup_whatsapp_harus_url_valid(): void
    {
        $this->put(route('admin.pengaturan.update'), [
            'wa_group_url' => 'bukan-url',
        ])->assertSessionHasErrors('wa_group_url');
    }

    public function test_halaman_pengaturan_menampilkan_status_google_drive(): void
    {
        $this->get(route('admin.pengaturan.index'))
            ->assertStatus(200)
            ->assertSee('Penyimpanan Google Drive')
            ->assertSee('Tautan Grup WhatsApp')
            ->assertSee('GOOGLE_DRIVE_FOLDER_ID')
            ->assertSee('GOOGLE_DRIVE_ENABLED');
    }

    public function test_uji_koneksi_drive_memberi_pesan_saat_nonaktif(): void
    {
        $this->post(route('admin.pengaturan.ujiDrive'))
            ->assertRedirect(route('admin.pengaturan.index'))
            ->assertSessionHas('hasil_uji_drive');

        $hasil = session('hasil_uji_drive');
        $this->assertFalse($hasil['ok']);
    }

    /* ─────────────── Gambar konten + Drive ─────────────── */

    public function test_gambar_berita_tetap_tersimpan_lokal_walau_drive_nonaktif(): void
    {
        $this->post(route('admin.berita.store'), [
            'judul'     => 'Berita Drive',
            'isi'       => 'Isi berita.',
            'gambar'    => $this->pngFile('drive.png'),
            'is_active' => '1',
        ])->assertRedirect(route('admin.berita.index'));

        $berita = Berita::where('judul', 'Berita Drive')->firstOrFail();
        $this->berkasUji[] = $berita->gambar;

        $this->assertTrue(Storage::disk('public')->exists($berita->gambar));
        $this->assertNull($berita->gambar_drive_id);
        $this->assertNull($berita->gambar_drive_view);
    }

    public function test_gambar_galeri_tetap_tersimpan_lokal_walau_drive_nonaktif(): void
    {
        $this->post(route('admin.galeri.store'), [
            'judul'     => 'Foto Kegiatan',
            'gambar'    => $this->pngFile('galeri.png'),
            'is_active' => '1',
        ])->assertRedirect(route('admin.galeri.index'));

        $galeri = Galeri::where('judul', 'Foto Kegiatan')->firstOrFail();
        $this->berkasUji[] = $galeri->path;

        $this->assertTrue(Storage::disk('public')->exists($galeri->path));
        $this->assertNull($galeri->drive_id);
    }

    /* ─────────────── Helper GoogleDrive ─────────────── */

    public function test_helper_drive_mengembalikan_null_dengan_aman_saat_nonaktif(): void
    {
        $this->assertFalse(GoogleDrive::enabled());
        $this->assertNull(GoogleDrive::service());
        $this->assertNull(GoogleDrive::upload('tidak-ada.png', 'tidak-ada.png'));
        $this->assertNull(GoogleDrive::subFolderId('PPDB-2026-0001'));
        $this->assertFalse(GoogleDrive::delete('abc'));
    }

    public function test_tautan_drive_dibentuk_dari_id_berkas(): void
    {
        $this->assertSame(
            'https://drive.google.com/file/d/XYZ/view',
            GoogleDrive::viewUrl('XYZ')
        );
        $this->assertSame(
            'https://drive.google.com/uc?export=download&id=XYZ',
            GoogleDrive::downloadUrl('XYZ')
        );
        $this->assertNull(GoogleDrive::viewUrl(null));
    }

    public function test_penyimpanan_dokumen_mengembalikan_hasil_kosong_untuk_berkas_hilang(): void
    {
        $hasil = PenyimpananDokumen::pindahkan('temp/tidak-ada.pdf', 'dokumen/uji', 'kk');

        $this->assertNull($hasil['path']);
        $this->assertNull($hasil['drive_id']);
    }

    /* ─────────────── Helper ─────────────── */

    /**
     * @param  array<string, mixed>  $atribut
     */
    private function buatPendaftar(array $atribut = []): Pendaftar
    {
        $jurusan = Jurusan::orderBy('kode')->firstOrFail();

        return Pendaftar::create(array_merge([
            'nomor_pendaftaran' => Pendaftar::generateNomor(),
            'periode'           => '2026/2027',
            'nik'               => '3302010101090001',
            'nama_lengkap'      => 'Budi Santoso',
            'jenis_kelamin'     => 'L',
            'tempat_lahir'      => 'Banyumas',
            'tanggal_lahir'     => '2009-01-01',
            'agama'             => 'Islam',
            'alamat'            => 'Jl. Contoh No. 1',
            'asal_sekolah'      => 'SMP Negeri 1 Wangon',
            'no_hp'             => '081234567890',
            'email'             => 'budi@example.com',
            'nama_ayah'         => 'Ayah Budi',
            'pekerjaan_ayah'    => 'Petani',
            'nama_ibu'          => 'Ibu Budi',
            'pekerjaan_ibu'     => 'Pedagang',
            'no_hp_ortu'        => '081298765432',
            'jurusan_1_id'      => $jurusan->id,
            'status_verifikasi' => 'pending',
        ], $atribut));
    }

    private function pngFile(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'ct') . '.png';

        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8AAAwAB/AF+7wAAAABJRU5ErkJggg=='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
