<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Pendaftar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji fitur "Aksi & Ekspor" pada dashboard admin:
 * ekspor XLS, kelola kuota, dan input manual pendaftar luring.
 * (Fitur cetak kartu sudah dihapus.)
 */
class AdminAksiEksporTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** Data pendaftar lengkap untuk keperluan uji. */
    private function dataPendaftar(array $override = []): array
    {
        $jurusan = Jurusan::orderBy('kode')->get();

        return array_merge([
            'nama_lengkap'   => 'Siswa Uji Luring',
            'nik'            => '3302123456789012',
            'nisn'           => '0011223344',
            'jenis_kelamin'  => 'L',
            'tempat_lahir'   => 'Banyumas',
            'tanggal_lahir'  => '2010-05-14',
            'agama'          => 'Islam',
            'alamat'         => 'Jl. Uji No. 1, Wangon',
            'asal_sekolah'   => 'SMP Negeri 1 Wangon',
            'no_hp'          => '081234567890',
            'nama_ayah'      => 'Ayah Uji',
            'pekerjaan_ayah' => 'Petani',
            'nama_ibu'       => 'Ibu Uji',
            'pekerjaan_ibu'  => 'Guru',
            'no_hp_ortu'     => '081298765432',
            'alamat_ortu'    => 'Jl. Uji No. 1, Wangon',
            'jurusan_1_id'   => $jurusan[0]->id,
            'jurusan_2_id'   => $jurusan[1]->id,
            'minat'          => 'teknik',
        ], $override);
    }

    /* ─────────────── Ekspor XLS ─────────────── */

    public function test_ekspor_xls_mengunduh_berkas_spreadsheet(): void
    {
        Pendaftar::factory()->create();

        $response = $this->get(route('admin.export.xls'));

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'spreadsheet',
            (string) $response->headers->get('Content-Type')
        );
    }

    /* ─────────────── Kelola Kuota ─────────────── */

    public function test_halaman_kelola_kuota_dapat_dibuka(): void
    {
        $this->get(route('admin.kuota.index'))
            ->assertStatus(200)
            ->assertSee('Kelola Kuota')
            ->assertSee('Simpan Perubahan');
    }

    public function test_kuota_dapat_diperbarui_massal(): void
    {
        $jurusan = Jurusan::orderBy('kode')->get();
        $payload = ['kuota' => []];

        foreach ($jurusan as $j) {
            $payload['kuota'][$j->id] = 100;
        }

        $this->put(route('admin.kuota.update'), $payload)
            ->assertRedirect(route('admin.kuota.index'));

        foreach ($jurusan as $j) {
            $this->assertSame(100, $j->fresh()->kuota);
        }
    }

    public function test_kuota_negatif_ditolak(): void
    {
        $jurusan = Jurusan::first();

        $this->put(route('admin.kuota.update'), [
            'kuota' => [$jurusan->id => -1],
        ])->assertSessionHasErrors('kuota.' . $jurusan->id);
    }

    /* ─────────────── Input Manual ─────────────── */

    public function test_form_input_manual_dapat_dibuka(): void
    {
        // Input Manual langsung melempar ke form formulir (wizard daring).
        $this->get(route('admin.manual.create'))
            ->assertRedirect(route('ppdb.step1'));
    }

    public function test_input_manual_menyimpan_pendaftar_sebagai_luring_dan_terverifikasi(): void
    {
        $response = $this->post(route('admin.manual.store'), $this->dataPendaftar());

        $pendaftar = Pendaftar::where('nama_lengkap', 'Siswa Uji Luring')->firstOrFail();

        $this->assertSame('luring', $pendaftar->sumber);
        $this->assertTrue($pendaftar->is_luring);
        $this->assertSame('approved', $pendaftar->status_verifikasi);
        $this->assertStringStartsWith('PPDB-', $pendaftar->nomor_pendaftaran);

        // Setelah tersimpan, panitia diarahkan ke detail pendaftar.
        $response->assertRedirect(route('admin.pendaftar.show', $pendaftar->id));
    }

    public function test_input_manual_dengan_jurusan_sama_ditolak(): void
    {
        $jurusan = Jurusan::first();

        $this->post(route('admin.manual.store'), $this->dataPendaftar([
            'jurusan_1_id' => $jurusan->id,
            'jurusan_2_id' => $jurusan->id,
        ]))->assertSessionHasErrors('jurusan_2_id');

        $this->assertDatabaseCount('pendaftar', 0);
    }

    public function test_input_manual_dengan_nik_tidak_valid_ditolak(): void
    {
        $this->post(route('admin.manual.store'), $this->dataPendaftar(['nik' => '123']))
            ->assertSessionHasErrors('nik');

        $this->assertDatabaseCount('pendaftar', 0);
    }

    public function test_nomor_pendaftaran_berurutan_tanpa_duplikat(): void
    {
        $first = Pendaftar::generateNomor();

        Pendaftar::factory()->create(['nomor_pendaftaran' => $first]);

        $second = Pendaftar::generateNomor();

        $this->assertNotSame($first, $second);
        $this->assertSame('PPDB-' . date('Y') . '-0002', $second);
    }
}
