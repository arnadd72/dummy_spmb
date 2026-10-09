<?php

namespace Tests\Feature;

use App\Models\Berita;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Galeri;
use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menguji modul Manajemen Konten: Berita, Agenda/Event, Galeri, dan FAQ chatbot.
 *
 * Fokus utama:
 *  - /admin/faq tidak lagi error 500 (view yang hilang).
 *  - Konten yang dikelola admin tampil pada landing page portal siswa.
 *  - Konten nonaktif tidak tampil ke publik.
 *  - Berkas gambar benar-benar tersimpan / terhapus saat diganti.
 */
class AdminContentManagementTest extends TestCase
{
    use RefreshDatabase;

    /** Path berkas uji pada disk `public` yang perlu dibersihkan setelah tes. */
    private array $berkasUji = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
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

    /**
     * Berkas PNG asli (1x1) agar aturan validasi `image` benar-benar teruji.
     */
    private function pngFile(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'ct') . '.png';

        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8AAAwAB/AF+7wAAAABJRU5ErkJggg=='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    /** Simpan berkas gambar tiruan ke disk public dan catat untuk pembersihan. */
    private function simpanBerkas(string $path): string
    {
        Storage::disk('public')->put($path, 'x');
        $this->berkasUji[] = $path;

        return $path;
    }

    /* ─────────────── FAQ (chatbot) ─────────────── */

    public function test_halaman_faq_tidak_lagi_error_500(): void
    {
        $this->get(route('admin.faq.index'))
            ->assertStatus(200)
            ->assertSee('Chatbot FAQ');
    }

    public function test_form_tambah_dan_edit_faq_dapat_dibuka(): void
    {
        $faq = Faq::create([
            'pertanyaan' => 'Kapan pendaftaran dibuka?',
            'jawaban'    => 'Pendaftaran dibuka mulai Januari.',
            'keywords'   => ['jadwal', 'kapan'],
        ]);

        $this->get(route('admin.faq.create'))->assertStatus(200);

        $this->get(route('admin.faq.edit', $faq->id))
            ->assertStatus(200)
            ->assertSee('Kapan pendaftaran dibuka?');
    }

    public function test_faq_dapat_dibuat_dengan_kata_kunci_dipisah_koma(): void
    {
        $this->post(route('admin.faq.store'), [
            'pertanyaan' => 'Apakah pendaftaran berbayar?',
            'jawaban'    => 'Tidak, pendaftaran gratis.',
            'kategori'   => 'Biaya',
            'keywords'   => 'biaya, GRATIS , bayar',
            'is_active'  => '1',
        ])->assertRedirect(route('admin.faq.index'));

        $faq = Faq::where('pertanyaan', 'Apakah pendaftaran berbayar?')->firstOrFail();

        $this->assertIsArray($faq->keywords);
        $this->assertSame(['biaya', 'gratis', 'bayar'], $faq->keywords);
        $this->assertSame('Biaya', $faq->kategori);
        $this->assertTrue($faq->is_active);
    }

    public function test_faq_wajib_memiliki_pertanyaan_dan_jawaban(): void
    {
        $this->post(route('admin.faq.store'), [])
            ->assertSessionHasErrors(['pertanyaan', 'jawaban']);
    }

    public function test_detail_faq_dapat_dibuka_dan_dihapus(): void
    {
        $faq = Faq::create([
            'pertanyaan' => 'Bagaimana cara upload dokumen?',
            'jawaban'    => 'Lewat langkah 2 formulir.',
            'keywords'   => ['upload', 'dokumen'],
        ]);

        $this->get(route('admin.faq.show', $faq->id))
            ->assertStatus(200)
            ->assertSee('Kata Kunci');

        $this->delete(route('admin.faq.destroy', $faq->id))
            ->assertRedirect(route('admin.faq.index'));

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    /* ─────────────── Berita ─────────────── */

    public function test_berita_disimpan_dengan_thumbnail_dan_slug(): void
    {
        $this->post(route('admin.berita.store'), [
            'judul'     => 'Pengumuman Gelombang I',
            'isi'       => 'Isi pengumuman.',
            'gambar'    => $this->pngFile('thumb.png'),
            'is_active' => '1',
        ])->assertRedirect(route('admin.berita.index'));

        $berita = Berita::where('judul', 'Pengumuman Gelombang I')->firstOrFail();

        $this->berkasUji[] = $berita->gambar;

        $this->assertSame('pengumuman-gelombang-i', $berita->slug);
        $this->assertNotEmpty($berita->gambar);
        $this->assertTrue(Storage::disk('public')->exists($berita->gambar));
        $this->assertTrue($berita->has_gambar);
        $this->assertStringContainsString('/storage/berita/', $berita->gambar_url);
    }

    /**
     * Regresi: tanpa nama parameter rute yang dipatok, `berita` disingular-kan
     * Laravel menjadi `beritum` sehingga route model binding gagal (404/500).
     */
    public function test_rute_berita_menggunakan_route_model_binding_yang_benar(): void
    {
        $berita = Berita::create(['judul' => 'Uji Binding', 'slug' => 'uji-binding']);

        $this->get(route('admin.berita.show', $berita->id))->assertStatus(200);
        $this->get(route('admin.berita.edit', $berita->id))->assertStatus(200);
    }

    public function test_thumbnail_lama_dihapus_saat_diganti(): void
    {
        $berita = Berita::create(['judul' => 'Berita Galeri', 'slug' => 'berita-galeri']);
        $berita->update(['gambar' => $this->simpanBerkas('berita/lama.png')]);
        $lama = $berita->gambar;

        $this->post(route('admin.berita.update', $berita->id), [
            '_method'   => 'PUT',
            'judul'     => 'Berita Galeri',
            'isi'       => 'Diperbarui.',
            'gambar'    => $this->pngFile('baru.png'),
            'is_active' => '1',
        ])->assertRedirect(route('admin.berita.index'));

        $berita->refresh();
        $this->berkasUji[] = $berita->gambar;

        $this->assertNotSame($lama, $berita->gambar);
        $this->assertFalse(Storage::disk('public')->exists($lama));
        $this->assertTrue(Storage::disk('public')->exists($berita->gambar));
    }

    public function test_menghapus_berita_ikut_menghapus_berkas_thumbnail(): void
    {
        $berita = Berita::create(['judul' => 'Berita Hapus', 'slug' => 'berita-hapus']);
        $berita->update(['gambar' => $this->simpanBerkas('berita/hapus.png')]);
        $path = $berita->gambar;

        $this->delete(route('admin.berita.destroy', $berita->id))
            ->assertRedirect(route('admin.berita.index'));

        $this->assertFalse(Storage::disk('public')->exists($path));
        $this->assertDatabaseMissing('beritas', ['id' => $berita->id]);
    }

    /* ─────────────── Event / Agenda ─────────────── */

    public function test_event_disimpan_dengan_poster_tanggal_dan_lokasi(): void
    {
        $this->post(route('admin.event.store'), [
            'judul'     => 'Sosialisasi PPDB',
            'deskripsi' => 'Sosialisasi untuk orang tua.',
            'tanggal'   => '2026-11-20',
            'lokasi'    => 'Aula Sekolah',
            'gambar'    => $this->pngFile('poster.png'),
            'is_active' => '1',
        ])->assertRedirect(route('admin.event.index'));

        $event = Event::where('judul', 'Sosialisasi PPDB')->firstOrFail();
        $this->berkasUji[] = $event->gambar;

        $this->assertSame('2026-11-20', $event->tanggal->format('Y-m-d'));
        $this->assertSame('Aula Sekolah', $event->lokasi);
        $this->assertTrue($event->has_gambar);
    }

    public function test_detail_event_dapat_dibuka(): void
    {
        $event = Event::create(['judul' => 'Uji Event', 'slug' => 'uji-event', 'tanggal' => '2026-12-01']);

        $this->get(route('admin.event.show', $event->id))
            ->assertStatus(200)
            ->assertSee('Uji Event');
    }

    /* ─────────────── Galeri ─────────────── */

    public function test_galeri_menyimpan_berkas_foto(): void
    {
        $this->post(route('admin.galeri.store'), [
            'judul'     => 'Praktik Bengkel TKR',
            'deskripsi' => 'Siswa berlatih mesin.',
            'gambar'    => $this->pngFile('foto.png'),
            'is_active' => '1',
        ])->assertRedirect(route('admin.galeri.index'));

        $galeri = Galeri::where('judul', 'Praktik Bengkel TKR')->firstOrFail();
        $this->berkasUji[] = $galeri->path;

        $this->assertNotEmpty($galeri->path);
        $this->assertTrue(Storage::disk('public')->exists($galeri->path));
        $this->assertTrue($galeri->has_image);
        $this->assertStringContainsString('/storage/', $galeri->url);
    }

    public function test_halaman_galeri_dan_form_unggah_dapat_dibuka(): void
    {
        $this->get(route('admin.galeri.index'))
            ->assertStatus(200)
            ->assertSee('Galeri');

        $this->get(route('admin.galeri.create'))->assertStatus(200);
    }

    /* ─────────────── Pengaturan situs (gambar hero) ─────────────── */

    public function test_gambar_hero_dapat_diunggah_dan_dihapus(): void
    {
        $this->get(route('admin.pengaturan.index'))
            ->assertStatus(200)
            ->assertSee('Gambar Hero');

        $this->post(route('admin.pengaturan.update'), [
            '_method'    => 'PUT',
            'hero_image' => $this->pngFile('hero.png'),
        ])->assertRedirect(route('admin.pengaturan.index'));

        $path = Pengaturan::heroPath();
        $this->berkasUji[] = $path;

        $this->assertNotEmpty($path);
        $this->assertTrue(Pengaturan::heroExists());
        $this->assertStringContainsString('/storage/pengaturan/', Pengaturan::heroUrl());

        // Hapus gambar hero.
        $this->post(route('admin.pengaturan.update'), [
            '_method'    => 'PUT',
            'hapus_hero' => '1',
        ])->assertRedirect(route('admin.pengaturan.index'));

        $this->assertNull(Pengaturan::heroPath());
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    /* ─────────────── Landing page publik ─────────────── */

    public function test_landing_page_menampilkan_konten_yang_dikelola_admin(): void
    {
        $galeri = Galeri::create([
            'judul'     => 'Kegiatan Siswa',
            'path'      => $this->simpanBerkas('galeri/kegiatan.png'),
            'is_active' => true,
        ]);

        Berita::create([
            'judul'     => 'Pengumuman Gelombang I Dibuka',
            'isi'       => 'Segera daftarkan diri Anda.',
            'slug'      => 'gelombang-i',
            'is_active' => true,
        ]);

        Event::create([
            'judul'     => 'Sosialisasi PPDB',
            'slug'      => 'sosialisasi-ppdb',
            'deskripsi' => 'Sosialisasi untuk orang tua.',
            'tanggal'   => '2026-11-20',
            'lokasi'    => 'Aula SMK',
            'is_active' => true,
        ]);

        $this->get(route('ppdb.index'))
            ->assertStatus(200)
            ->assertSee('Berita Terbaru')
            ->assertSee('Pengumuman Gelombang I Dibuka')
            ->assertSee('Agenda Mendatang')
            ->assertSee('Sosialisasi PPDB')
            ->assertSee('Aula SMK')
            ->assertSee('Galeri Kegiatan')
            ->assertSee('Kegiatan Siswa');
    }

    public function test_konten_nonaktif_tidak_tampil_di_landing_page(): void
    {
        Berita::create([
            'judul'     => 'Draf Rahasia Belum Terbit',
            'isi'       => 'Konten draf.',
            'slug'      => 'draf-rahasia',
            'is_active' => false,
        ]);

        Event::create([
            'judul'     => 'Event Nonaktif Tersembunyi',
            'slug'      => 'event-nonaktif',
            'is_active' => false,
        ]);

        $this->get(route('ppdb.index'))
            ->assertStatus(200)
            ->assertDontSee('Draf Rahasia Belum Terbit')
            ->assertDontSee('Event Nonaktif Tersembunyi');
    }

    public function test_landing_page_memakai_gambar_hero_yang_diunggah_admin(): void
    {
        $path = $this->simpanBerkas('pengaturan/hero.png');
        Pengaturan::set('hero_image', $path);

        $this->get(route('ppdb.index'))
            ->assertStatus(200)
            ->assertSee('storage/pengaturan/hero.png', false);
    }

    public function test_landing_page_tetap_tampil_saat_konten_kosong(): void
    {
        $this->assertSame(0, Berita::count());
        $this->assertSame(0, Event::count());
        $this->assertSame(0, Galeri::count());

        $this->get(route('ppdb.index'))
            ->assertStatus(200)
            ->assertDontSee('id="berita-heading"', false);
    }
}
