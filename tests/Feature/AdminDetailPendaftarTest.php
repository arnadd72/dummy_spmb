<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Pendaftar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji halaman detail pendaftar pada panel admin.
 *
 * Halaman ini menjadi tempat panitia melihat SELURUH isian formulir siswa
 * (identitas, kontak email & WhatsApp, orang tua, pilihan jurusan, nilai
 * rapor, rekomendasi, dan berkas) dalam satu tampilan read-only.
 */
class AdminDetailPendaftarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function buatPendaftar(array $atribut = []): Pendaftar
    {
        $jurusan = Jurusan::orderBy('kode')->get();

        return Pendaftar::create(array_merge([
            'nomor_pendaftaran' => Pendaftar::generateNomor(),
            'periode'           => '2026/2027',
            'sumber'            => 'daring',
            'nisn'              => '0081234567',
            'nik'               => '3302010101090001',
            'nama_lengkap'      => 'Budi Santoso',
            'jenis_kelamin'     => 'L',
            'tempat_lahir'      => 'Banyumas',
            'tanggal_lahir'     => '2009-01-01',
            'agama'             => 'Islam',
            'alamat'            => 'Jl. Contoh No. 1, Wangon',
            'asal_sekolah'      => 'SMP Negeri 1 Wangon',
            'no_hp'             => '081234567890',
            'no_wa'             => '081298765432',
            'email'             => 'budi@example.com',
            'nama_ayah'         => 'Ayah Budi',
            'pekerjaan_ayah'    => 'Petani',
            'nama_ibu'          => 'Ibu Budi',
            'pekerjaan_ibu'     => 'Pedagang',
            'no_hp_ortu'        => '081200000001',
            'alamat_ortu'       => 'Jl. Contoh No. 1, Wangon',
            'jurusan_1_id'      => $jurusan[0]->id,
            'jurusan_2_id'      => $jurusan[1]->id,
            'rekomendasi_jurusan_id' => $jurusan[0]->id,
            'rekomendasi_skor'  => [$jurusan[0]->id => 72.5, $jurusan[1]->id => 40],
            'nilai_rapor'       => ['matematika' => 88, 'b_inggris' => 80],
            'minat'             => 'teknik',
            'status_verifikasi' => 'pending',
        ], $atribut));
    }

    public function test_halaman_detail_menampilkan_seluruh_data_isian_formulir(): void
    {
        $pendaftar = $this->buatPendaftar();

        $response = $this->get(route('admin.pendaftar.show', $pendaftar->id));

        $response->assertStatus(200)
            ->assertSee('Detail Pendaftar')
            // Identitas
            ->assertSee($pendaftar->nama_lengkap)
            ->assertSee($pendaftar->nik)
            ->assertSee($pendaftar->nisn)
            ->assertSee('SMP Negeri 1 Wangon')
            // Kontak email & WhatsApp (permintaan utama)
            ->assertSee('budi@example.com')
            ->assertSee('081298765432')
            ->assertSee('No. WhatsApp Siswa')
            // Orang tua
            ->assertSee('Ayah Budi')
            ->assertSee('Ibu Budi')
            // Jurusan & rekomendasi
            ->assertSee('Jurusan Pilihan 1')
            ->assertSee('Skor Kecocokan per Jurusan')
            // Nilai rapor
            ->assertSee('Nilai Rapor')
            ->assertSee('Matematika')
            // Berkas
            ->assertSee('Berkas Unggahan')
            // Status
            ->assertSee('Status Verifikasi');
    }

    public function test_halaman_detail_membuat_tautan_whatsapp_dari_nomor_lokal(): void
    {
        $pendaftar = $this->buatPendaftar(['no_wa' => '081298765432']);

        $this->get(route('admin.pendaftar.show', $pendaftar->id))
            ->assertStatus(200)
            ->assertSee('https://wa.me/6281298765432');
    }

    public function test_tautan_whatsapp_jatuh_ke_no_hp_bila_no_wa_kosong(): void
    {
        $pendaftar = $this->buatPendaftar(['no_wa' => null]);

        $this->assertSame('081234567890', $pendaftar->nomor_wa);
        $this->assertSame('https://wa.me/6281234567890', $pendaftar->wa_url);
    }

    public function test_detail_pendaftar_yang_tidak_ada_menghasilkan_404(): void
    {
        $this->get(route('admin.pendaftar.show', 99999))->assertStatus(404);
    }

    public function test_detail_pendaftar_tanpa_nilai_rapor_dan_berkas_tetap_tampil(): void
    {
        $pendaftar = $this->buatPendaftar([
            'no_wa'                  => null,
            'email'                  => null,
            'rekomendasi_jurusan_id' => null,
            'rekomendasi_skor'       => null,
            'nilai_rapor'            => null,
            'alamat_ortu'            => null,
        ]);

        $this->get(route('admin.pendaftar.show', $pendaftar->id))
            ->assertStatus(200)
            ->assertSee('Detail Pendaftar')
            ->assertSee('Siswa tidak mengisikan nilai rapor.')
            ->assertSee('Belum diunggah');
    }

    public function test_tabel_peserta_menyediakan_tautan_detail(): void
    {
        $pendaftar = $this->buatPendaftar();

        $this->get(route('admin.participants'))
            ->assertStatus(200)
            ->assertSee(route('admin.pendaftar.show', $pendaftar->id));
    }

    /* ─────────────── Input manual: email & WhatsApp ─────────────── */

    private function dataManual(array $override = []): array
    {
        $jurusan = Jurusan::orderBy('kode')->get();

        return array_merge([
            'nama_lengkap'   => 'Siswa Manual',
            'nik'            => '3302123456789012',
            'nisn'           => '0011223344',
            'jenis_kelamin'  => 'P',
            'tempat_lahir'   => 'Wangon',
            'tanggal_lahir'  => '2010-05-14',
            'agama'          => 'Islam',
            'alamat'         => 'Jl. Uji No. 1, Wangon',
            'asal_sekolah'   => 'SMP Negeri 1 Wangon',
            'no_hp'          => '081234567890',
            'no_wa'          => '081298765432',
            'email'          => 'manual@example.com',
            'nama_ayah'      => 'Ayah Uji',
            'pekerjaan_ayah' => 'Petani',
            'nama_ibu'       => 'Ibu Uji',
            'pekerjaan_ibu'  => 'Guru',
            'no_hp_ortu'     => '081200000002',
            'jurusan_1_id'   => $jurusan[0]->id,
        ], $override);
    }

    public function test_input_manual_menyimpan_email_dan_whatsapp(): void
    {
        $this->post(route('admin.manual.store'), $this->dataManual())->assertRedirect();

        $pendaftar = Pendaftar::where('nama_lengkap', 'Siswa Manual')->firstOrFail();

        $this->assertSame('081298765432', $pendaftar->no_wa);
        $this->assertSame('manual@example.com', $pendaftar->email);
    }

    public function test_input_manual_email_tidak_valid_ditolak(): void
    {
        $this->post(route('admin.manual.store'), $this->dataManual(['email' => 'bukan-email']))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('pendaftar', 0);
    }
}
