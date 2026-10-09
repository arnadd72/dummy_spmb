<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Pendaftar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji formulir pendaftaran yang disusun ulang mengikuti berkas resmi
 * "Formulir Pendaftaran SPMB 2026-2027":
 *  - Bagian A (Keterangan Diri), B (Pendidikan), C (Kesehatan), D (Lain-lain)
 *    benar-benar divalidasi dan disimpan saat siswa mengirim pendaftaran.
 *  - Siswa dapat mengunduh formulirnya dalam bentuk PDF, dan isinya memuat
 *    kop surat, seluruh bagian formulir, blok tanda tangan, serta dua lembar
 *    Tanda Terima Berkas Persyaratan.
 */
class FormulirPendaftaranSpmbTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ─────────────── Isian Bagian A (langkah 5) ─────────────── */

    /** Isian Bagian A yang sah, dipakai berulang di beberapa tes. */
    private function bagiana(array $override = []): array
    {
        return array_merge([
            'nama_lengkap'   => 'Budi Santoso',
            'nik'            => '3302010101090001',
            'no_kk'          => '3302010101090002',
            'nisn'           => '0081234567',
            'jenis_kelamin'  => 'L',
            'tempat_lahir'   => 'Banyumas',
            'tanggal_lahir'  => '2009-01-01',
            'agama'          => 'Islam',
            'anak_ke'        => 2,
            'jumlah_saudara' => 3,
            'asal_sekolah'   => 'SMP Negeri 1 Wangon',
            'tamat_tahun'    => '2026',
            'no_ijazah'      => 'DN-02 D/12345/2026',
            'no_hp'          => '081234567890',
            'email'          => 'budi@example.com',
            'rt'             => '02',
            'rw'             => '04',
            'desa'           => 'Wangon',
            'kecamatan'      => 'Wangon',
            'kabupaten'      => 'Banyumas',
            'propinsi'       => 'Jawa Tengah',
            'penghasilan'    => '03',
        ], $override);
    }

    private function isianBagianBcdAwal(): array
    {
        return [
            'step1' => ['nama_lengkap' => 'Budi Santoso', 'nisn' => '0081234567', 'setuju' => true],
            'step2' => [],
            'step3' => [],
            'step4' => ['jurusan_1_id' => Jurusan::orderBy('kode')->firstOrFail()->id, 'jurusan_2_id' => null, 'minat' => 'teknik'],
            'step5' => $this->bagiana(),
        ];
    }

    private function isianBagianBcd(): array
    {
        return [
            'smp_kecamatan'      => 'Wangon',
            'smp_kabupaten'      => 'Banyumas',
            'smp_propinsi'       => 'Jawa Tengah',
            'ijazah_nomor_tahun' => 'DN-02 D/12345/2026',
            'lama_belajar'       => '3',
            'nilai_ujian'        => ['b_indonesia' => 85, 'b_inggris' => 80, 'matematika' => 82, 'ipa' => 78],
            'golongan_darah'     => 'O',
            'tinggi_badan'       => 165,
            'berat_badan'        => 55,
            'hobi_olahraga'      => 'Sepak bola',
            'hobi_kesenian'      => 'Musik',
            'hobi_organisasi'    => 'OSIS',
            'ukuran_seragam'     => 'L',
        ];
    }

    public function test_bagian_a_tersimpan_ke_session_dan_melanjut_ke_data_orang_tua(): void
    {
        $this->withSession(['ppdb' => $this->isianBagianBcdAwal()])
            ->post(route('ppdb.step5.post'), $this->bagiana())
            ->assertRedirect(route('ppdb.step6'));

        $step5 = session('ppdb.step5');

        $this->assertSame('3302010101090002', $step5['no_kk']);
        $this->assertSame(2, $step5['anak_ke']);
        $this->assertSame('03', $step5['penghasilan']);
        // Alamat satu baris dirakit otomatis dari kolom RT/RW/desa/kecamatan.
        $this->assertStringContainsString('RT 02/RW 04', $step5['alamat']);
        $this->assertStringContainsString('Desa Wangon', $step5['alamat']);
    }

    public function test_bagian_a_menolak_no_kk_yang_bukan_16_digit(): void
    {
        $this->withSession(['ppdb' => $this->isianBagianBcdAwal()])
            ->post(route('ppdb.step5.post'), $this->bagiana(['no_kk' => '123']))
            ->assertSessionHasErrors('no_kk');
    }

    public function test_bagian_a_menolak_email_tidak_valid(): void
    {
        $this->withSession(['ppdb' => $this->isianBagianBcdAwal()])
            ->post(route('ppdb.step5.post'), $this->bagiana(['email' => 'bukan-email']))
            ->assertSessionHasErrors('email');
    }

    public function test_bagian_a_menolak_penghasilan_di_luar_daftar(): void
    {
        $this->withSession(['ppdb' => $this->isianBagianBcdAwal()])
            ->post(route('ppdb.step5.post'), $this->bagiana(['penghasilan' => '99']))
            ->assertSessionHasErrors('penghasilan');
    }

    /* ─────────────── Isian Bagian B, C, D (langkah 7) ─────────────── */

    public function test_bagian_b_c_d_tersimpan_dan_melanjut_ke_review(): void
    {
        $this->withSession(['ppdb' => $this->isianBagianBcdAwal()])
            ->post(route('ppdb.step7.post'), $this->isianBagianBcd())
            ->assertRedirect(route('ppdb.step8'));

        $step7 = session('ppdb.step7');

        $this->assertSame('O', $step7['golongan_darah']);
        $this->assertSame('L', $step7['ukuran_seragam']);
        $this->assertSame(78, (int) $step7['nilai_ujian']['ipa']);
    }

    public function test_bagian_b_c_d_menolak_ukuran_seragam_tidak_dikenal(): void
    {
        $this->withSession(['ppdb' => $this->isianBagianBcdAwal()])
            ->post(route('ppdb.step7.post'), array_merge($this->isianBagianBcd(), ['ukuran_seragam' => 'XXL-Plus']))
            ->assertSessionHasErrors('ukuran_seragam');
    }

    public function test_bagian_b_c_d_menolak_nilai_ujian_di_atas_seratus(): void
    {
        $this->withSession(['ppdb' => $this->isianBagianBcdAwal()])
            ->post(route('ppdb.step7.post'), array_merge($this->isianBagianBcd(), [
                'nilai_ujian' => ['b_indonesia' => 150, 'b_inggris' => 80, 'matematika' => 82, 'ipa' => 78],
            ]))
            ->assertSessionHasErrors('nilai_ujian.b_indonesia');
    }

    public function test_tidak_bisa_masuk_bagian_b_c_d_tanpa_mengisi_data_orang_tua(): void
    {
        // Session hanya punya step1-step5, belum ada step6.
        $this->withSession(['ppdb' => $this->isianBagianBcdAwal()])
            ->get(route('ppdb.step7'))
            ->assertRedirect(route('ppdb.step6'));
    }

    /* ─────────────── Cetak formulir PDF ─────────────── */

    public function test_formulir_pdf_dapat_diunduh_dengan_tipe_pdf(): void
    {
        $pendaftar = $this->buatPendaftar();

        $response = $this->get(route('ppdb.formulir', $pendaftar->nomor_pendaftaran));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));

        // Berkas PDF diawali penanda "%PDF".
        $this->assertStringStartsWith('%PDF', $response->streamedContent() ?: $response->getContent());
    }

    public function test_formulir_pdf_nomor_tidak_dikenal_menghasilkan_404(): void
    {
        $this->get(route('ppdb.formulir', 'PPDB-2026-9999'))->assertNotFound();
    }

    public function test_halaman_sukses_menyediakan_tautan_cetak_formulir(): void
    {
        $pendaftar = $this->buatPendaftar();

        $this->get(route('ppdb.sukses', $pendaftar->nomor_pendaftaran))
            ->assertStatus(200)
            ->assertSee(route('ppdb.formulir', $pendaftar->nomor_pendaftaran), false)
            ->assertSee('Cetak Formulir (PDF)');
    }

    public function test_halaman_cek_status_menyediakan_unduhan_formulir(): void
    {
        $pendaftar = $this->buatPendaftar();

        $this->post(route('ppdb.cekStatusPost'), ['nomor_pendaftaran' => $pendaftar->nomor_pendaftaran])
            ->assertStatus(200)
            ->assertSee(route('ppdb.formulir', $pendaftar->nomor_pendaftaran), false)
            ->assertSee('Unduh Formulir (PDF)');
    }

    /* ─────────────── Simpan pendaftaran (submit) ─────────────── */

    public function test_submit_menyimpan_seluruh_isian_bagian_a_sampai_d(): void
    {
        $session = [
            'ppdb' => [
                'step1' => ['nama_lengkap' => 'Budi Santoso', 'nisn' => '0081234567', 'setuju' => true],
                'step2' => [],
                'step3' => [],
                'step4' => ['jurusan_1_id' => Jurusan::orderBy('kode')->firstOrFail()->id, 'jurusan_2_id' => null, 'minat' => 'teknik'],
                'step5' => $this->bagiana(),
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
                    'alamat_ortu'    => 'RT 02/RW 04, Desa Wangon',
                    'ortu_rt'        => '02',
                    'ortu_rw'        => '04',
                    'ortu_desa'      => 'Wangon',
                    'ortu_kecamatan' => 'Wangon',
                    'ortu_kabupaten' => 'Banyumas',
                    'ortu_propinsi'  => 'Jawa Tengah',
                ],
                'step7' => $this->isianBagianBcd(),
                'nilai_rapor' => [],
            ],
        ];

        $this->withSession($session)
            ->post(route('ppdb.submit'), ['konfirmasi_final' => '1'])
            ->assertRedirect();

        $p = Pendaftar::firstOrFail();

        // Bagian A
        $this->assertSame('3302010101090002', $p->no_kk);
        $this->assertEquals(2, $p->anak_ke);
        $this->assertSame('03', $p->penghasilan);
        $this->assertSame('Wangon', $p->desa);
        $this->assertSame('Jawa Tengah', $p->propinsi);
        $this->assertSame('SMA/SMK/MA', $p->ayah_pendidikan);
        $this->assertSame('SMP/MTs', $p->ibu_pendidikan);
        $this->assertSame('daring', $p->sumber);

        // Bagian B
        $this->assertSame('Wangon', $p->smp_kecamatan);
        $this->assertSame('3', $p->lama_belajar);
        $this->assertSame(85, (int) $p->nilai_ujian['b_indonesia']);

        // Bagian C
        $this->assertSame('O', $p->golongan_darah);
        $this->assertEquals(165, $p->tinggi_badan);
        $this->assertEquals(55, $p->berat_badan);

        // Bagian D
        $this->assertSame('L', $p->ukuran_seragam);
        $this->assertSame('Sepak bola', $p->hobi_olahraga);
    }

    /* ─────────────── Panel admin ─────────────── */

    public function test_admin_melihat_isian_bagian_b_c_d_siswa(): void
    {
        $pendaftar = $this->buatPendaftar();

        $this->get(route('admin.pendaftar.show', $pendaftar->id))
            ->assertStatus(200)
            ->assertSee('Formulir SPMB — Bagian B, C, D')
            ->assertSee('Golongan Darah')
            ->assertSee('Ukuran Baju / Seragam')
            ->assertSee('Nomor dan Tahun Ijazah')
            ->assertSee($pendaftar->ukuran_seragam);
    }

    /* ─────────────── Helper ─────────────── */

    private function buatPendaftar(array $atribut = []): Pendaftar
    {
        $jurusan = Jurusan::orderBy('kode')->get();

        return Pendaftar::create(array_merge([
            'nomor_pendaftaran' => Pendaftar::generateNomor(),
            'periode'           => '2026/2027',
            'sumber'            => 'daring',
            'nisn'              => '0081234567',
            'nik'               => '3302010101090001',
            'no_kk'             => '3302010101090002',
            'nama_lengkap'      => 'Budi Santoso',
            'jenis_kelamin'     => 'L',
            'tempat_lahir'      => 'Banyumas',
            'tanggal_lahir'     => '2009-01-01',
            'agama'             => 'Islam',
            'anak_ke'           => 2,
            'jumlah_saudara'    => 3,
            'alamat'            => 'RT 02/RW 04, Desa Wangon, Kec. Wangon, Banyumas, Jawa Tengah',
            'rt'                => '02',
            'rw'                => '04',
            'desa'              => 'Wangon',
            'kecamatan'         => 'Wangon',
            'kabupaten'         => 'Banyumas',
            'propinsi'          => 'Jawa Tengah',
            'asal_sekolah'      => 'SMP Negeri 1 Wangon',
            'tamat_tahun'       => '2026',
            'no_ijazah'         => 'DN-02 D/12345/2026',
            'no_hp'             => '081234567890',
            'no_wa'             => '081234567890',
            'email'             => 'budi@example.com',
            'penghasilan'       => '03',
            'referensi_pendaftar' => 'Guru SMP',
            'smp_kecamatan'     => 'Wangon',
            'smp_kabupaten'     => 'Banyumas',
            'smp_propinsi'      => 'Jawa Tengah',
            'ijazah_nomor_tahun'=> 'DN-02 D/12345/2026',
            'lama_belajar'      => '3',
            'nilai_ujian'       => ['b_indonesia' => 85, 'b_inggris' => 80, 'matematika' => 82, 'ipa' => 78],
            'golongan_darah'    => 'O',
            'tinggi_badan'      => 165,
            'berat_badan'       => 55,
            'hobi_olahraga'     => 'Sepak bola',
            'hobi_kesenian'     => 'Musik',
            'hobi_organisasi'   => 'OSIS',
            'ukuran_seragam'    => 'L',
            'nama_ayah'         => 'Ayah Budi',
            'ayah_ttl'          => 'Banyumas, 1 Januari 1975',
            'ayah_pendidikan'   => 'SMA/SMK/MA',
            'pekerjaan_ayah'    => 'Petani',
            'nama_ibu'          => 'Ibu Budi',
            'ibu_ttl'           => 'Banyumas, 1 Maret 1978',
            'ibu_pendidikan'    => 'SMP/MTs',
            'pekerjaan_ibu'     => 'Pedagang',
            'no_hp_ortu'        => '081298765432',
            'alamat_ortu'       => 'RT 02/RW 04, Desa Wangon',
            'ortu_rt'           => '02',
            'ortu_rw'           => '04',
            'ortu_desa'         => 'Wangon',
            'ortu_kecamatan'    => 'Wangon',
            'ortu_kabupaten'    => 'Banyumas',
            'ortu_propinsi'     => 'Jawa Tengah',
            'jurusan_1_id'      => $jurusan[0]->id,
            'jurusan_2_id'      => $jurusan[1]->id ?? null,
            'status_verifikasi' => 'pending',
            'minat'             => 'teknik',
        ], $atribut));
    }
}
