<?php

namespace Database\Factories;

use App\Models\Jurusan;
use App\Models\Pendaftar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pendaftar>
 */
class PendaftarFactory extends Factory
{
    protected $model = Pendaftar::class;

    public function definition(): array
    {
        $jurusanIds = Jurusan::query()->pluck('id');

        return [
            'nomor_pendaftaran' => Pendaftar::generateNomor(),
            'periode'           => '2026/2027',
            'sumber'            => 'daring',
            'nisn'              => $this->faker->unique()->numerify('##########'),
            'nik'               => $this->faker->unique()->numerify('################'),
            'no_kk'             => $this->faker->unique()->numerify('################'),
            'nama_lengkap'      => $this->faker->name(),
            'jenis_kelamin'     => $this->faker->randomElement(['L', 'P']),
            'tempat_lahir'      => $this->faker->city(),
            'tanggal_lahir'     => $this->faker->dateTimeBetween('-16 years', '-14 years')->format('Y-m-d'),
            'agama'             => 'Islam',
            'anak_ke'           => $this->faker->numberBetween(1, 4),
            'jumlah_saudara'    => $this->faker->numberBetween(0, 4),
            'alamat'            => $this->faker->address(),
            'rt'                => $this->faker->numerify('0#'),
            'rw'                => $this->faker->numerify('0#'),
            'desa'              => $this->faker->city(),
            'kecamatan'         => $this->faker->city(),
            'kabupaten'         => 'Banyumas',
            'propinsi'          => 'Jawa Tengah',
            'asal_sekolah'      => 'SMP ' . $this->faker->city(),
            'tamat_tahun'       => (string) date('Y'),
            'no_ijazah'         => 'DN-02 D/' . $this->faker->numerify('#####') . '/' . date('Y'),
            'no_hp'             => $this->faker->numerify('08##########'),
            'no_wa'             => $this->faker->numerify('08##########'),
            'email'             => $this->faker->unique()->safeEmail(),
            'penghasilan'       => $this->faker->randomElement(['01', '02', '03', '04', '05']),
            'referensi_pendaftar' => $this->faker->randomElement(['Guru SMP', 'Teman', 'Sosial media', 'Spanduk']),
            // Bagian B — pendidikan
            'smp_kecamatan'     => $this->faker->city(),
            'smp_kabupaten'     => 'Banyumas',
            'smp_propinsi'      => 'Jawa Tengah',
            'ijazah_nomor_tahun' => 'DN-02 D/' . $this->faker->numerify('#####') . '/' . date('Y'),
            'lama_belajar'      => '3',
            'nilai_ujian'       => ['b_indonesia' => 85, 'b_inggris' => 80, 'matematika' => 82, 'ipa' => 78],
            // Bagian C — kesehatan
            'golongan_darah'    => $this->faker->randomElement(['A', 'B', 'AB', 'O']),
            'tinggi_badan'      => $this->faker->numberBetween(150, 180),
            'berat_badan'       => $this->faker->numberBetween(40, 75),
            'penyakit_pernah_diderita' => null,
            // Bagian D — lain-lain
            'hobi_olahraga'     => $this->faker->randomElement(['Sepak bola', 'Voli', 'Bulu tangkis']),
            'hobi_kesenian'     => $this->faker->randomElement(['Musik', 'Tari', 'Menggambar']),
            'hobi_organisasi'   => $this->faker->randomElement(['OSIS', 'Pramuka', 'PMR']),
            'hobi_prestasi'     => null,
            'hobi_lain'         => null,
            'ukuran_seragam'    => $this->faker->randomElement(['M', 'L', 'XL', 'XXL']),
            'nama_ayah'         => $this->faker->name('male'),
            'ayah_ttl'          => $this->faker->city() . ', ' . $this->faker->numberBetween(1, 28) . ' Januari 1975',
            'ayah_pendidikan'   => $this->faker->randomElement(['SMP/MTs', 'SMA/SMK/MA', 'S1']),
            'pekerjaan_ayah'    => $this->faker->jobTitle(),
            'nama_ibu'          => $this->faker->name('female'),
            'ibu_ttl'           => $this->faker->city() . ', ' . $this->faker->numberBetween(1, 28) . ' Maret 1978',
            'ibu_pendidikan'    => $this->faker->randomElement(['SMP/MTs', 'SMA/SMK/MA', 'S1']),
            'pekerjaan_ibu'     => $this->faker->jobTitle(),
            'no_hp_ortu'        => $this->faker->numerify('08##########'),
            'alamat_ortu'       => $this->faker->address(),
            'ortu_rt'           => $this->faker->numerify('0#'),
            'ortu_rw'           => $this->faker->numerify('0#'),
            'ortu_desa'         => $this->faker->city(),
            'ortu_kecamatan'    => $this->faker->city(),
            'ortu_kabupaten'    => 'Banyumas',
            'ortu_propinsi'     => 'Jawa Tengah',
            'jurusan_1_id'      => $jurusanIds->isNotEmpty() ? $jurusanIds->random() : null,
            'jurusan_2_id'      => null,
            'status_verifikasi' => 'pending',
            'minat'             => $this->faker->word(),
        ];
    }
}
