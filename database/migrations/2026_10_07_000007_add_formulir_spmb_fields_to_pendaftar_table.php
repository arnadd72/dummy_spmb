<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melengkapi kolom pendaftar agar seluruh isian pada "Formulir Pendaftaran
 * SPMB 2026/2027" (bagian A–D) dapat disimpan, sehingga formulir daring bisa
 * dicetak dalam bentuk PDF dengan struktur yang sama seperti dokumen resmi.
 *
 * Pengelompokan kolom mengikuti urutan bagian pada dokumen formulir:
 *   A. Keterangan Diri Murid Baru  → identitas, alamat, orang tua, penghasilan
 *   B. Keterangan Pendidikan       → asal SMP, ijazah, lama belajar, nilai ujian
 *   C. Keterangan Kesehatan        → golongan darah, tinggi/berat, penyakit
 *   D. Keterangan Lain-lain        → hobi/prestasi, ukuran seragam
 *
 * Semua kolom nullable karena data lama (pendaftar yang sudah masuk) tidak
 * memilikinya, dan sebagian isian bersifat opsional pada formulir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            /* ── A. Keterangan Diri Murid Baru ───────────────────────── */

            // 4. NIK & No KK  (nik sudah ada sebelumnya)
            $this->tambahKolom($table, 'no_kk', fn (Blueprint $t) => $t->string('no_kk', 20)->nullable());

            // 7. Anak ke berapa
            $this->tambahKolom($table, 'anak_ke', fn (Blueprint $t) => $t->unsignedTinyInteger('anak_ke')->nullable());

            // 8. Jumlah saudara kandung
            $this->tambahKolom($table, 'jumlah_saudara', fn (Blueprint $t) => $t->unsignedTinyInteger('jumlah_saudara')->nullable());

            // 10. Tamat SMP / MTs Tahun
            $this->tambahKolom($table, 'tamat_tahun', fn (Blueprint $t) => $t->string('tamat_tahun', 4)->nullable());

            // 11. No. Ijazah
            $this->tambahKolom($table, 'no_ijazah', fn (Blueprint $t) => $t->string('no_ijazah', 100)->nullable());

            // Alamat murid terurai (dipakai lembar "Tanda Terima Berkas Persyaratan")
            $this->tambahKolom($table, 'rt', fn (Blueprint $t) => $t->string('rt', 5)->nullable());
            $this->tambahKolom($table, 'rw', fn (Blueprint $t) => $t->string('rw', 5)->nullable());
            $this->tambahKolom($table, 'desa', fn (Blueprint $t) => $t->string('desa', 100)->nullable());
            $this->tambahKolom($table, 'kecamatan', fn (Blueprint $t) => $t->string('kecamatan', 100)->nullable());
            $this->tambahKolom($table, 'kabupaten', fn (Blueprint $t) => $t->string('kabupaten', 100)->nullable());
            $this->tambahKolom($table, 'propinsi', fn (Blueprint $t) => $t->string('propinsi', 100)->nullable());

            // 14. Orang Tua — TTL & Pendidikan (nama & pekerjaan sudah ada)
            $this->tambahKolom($table, 'ayah_ttl', fn (Blueprint $t) => $t->string('ayah_ttl', 150)->nullable());
            $this->tambahKolom($table, 'ayah_pendidikan', fn (Blueprint $t) => $t->string('ayah_pendidikan', 50)->nullable());
            $this->tambahKolom($table, 'ibu_ttl', fn (Blueprint $t) => $t->string('ibu_ttl', 150)->nullable());
            $this->tambahKolom($table, 'ibu_pendidikan', fn (Blueprint $t) => $t->string('ibu_pendidikan', 50)->nullable());

            // 15. Alamat Orangtua / Wali (terurai)
            $this->tambahKolom($table, 'ortu_rt', fn (Blueprint $t) => $t->string('ortu_rt', 5)->nullable());
            $this->tambahKolom($table, 'ortu_rw', fn (Blueprint $t) => $t->string('ortu_rw', 5)->nullable());
            $this->tambahKolom($table, 'ortu_desa', fn (Blueprint $t) => $t->string('ortu_desa', 100)->nullable());
            $this->tambahKolom($table, 'ortu_kecamatan', fn (Blueprint $t) => $t->string('ortu_kecamatan', 100)->nullable());
            $this->tambahKolom($table, 'ortu_kabupaten', fn (Blueprint $t) => $t->string('ortu_kabupaten', 100)->nullable());
            $this->tambahKolom($table, 'ortu_propinsi', fn (Blueprint $t) => $t->string('ortu_propinsi', 100)->nullable());

            // 16. Penghasilan Bulanan (kode 01–05)
            $this->tambahKolom($table, 'penghasilan', fn (Blueprint $t) => $t->string('penghasilan', 10)->nullable());

            /* ── B. Keterangan Pendidikan ────────────────────────────── */

            $this->tambahKolom($table, 'smp_kecamatan', fn (Blueprint $t) => $t->string('smp_kecamatan', 100)->nullable());
            $this->tambahKolom($table, 'smp_kabupaten', fn (Blueprint $t) => $t->string('smp_kabupaten', 100)->nullable());
            $this->tambahKolom($table, 'smp_propinsi', fn (Blueprint $t) => $t->string('smp_propinsi', 100)->nullable());
            $this->tambahKolom($table, 'ijazah_nomor_tahun', fn (Blueprint $t) => $t->string('ijazah_nomor_tahun', 100)->nullable());
            $this->tambahKolom($table, 'lama_belajar', fn (Blueprint $t) => $t->string('lama_belajar', 10)->nullable());

            // Nilai Ujian: Bahasa Indonesia, Bahasa Inggris, Matematika, IPA.
            $this->tambahKolom($table, 'nilai_ujian', fn (Blueprint $t) => $t->json('nilai_ujian')->nullable());

            /* ── C. Keterangan Kesehatan ─────────────────────────────── */

            $this->tambahKolom($table, 'golongan_darah', fn (Blueprint $t) => $t->string('golongan_darah', 5)->nullable());
            $this->tambahKolom($table, 'tinggi_badan', fn (Blueprint $t) => $t->unsignedSmallInteger('tinggi_badan')->nullable());
            $this->tambahKolom($table, 'berat_badan', fn (Blueprint $t) => $t->unsignedSmallInteger('berat_badan')->nullable());
            $this->tambahKolom($table, 'penyakit_pernah_diderita', fn (Blueprint $t) => $t->string('penyakit_pernah_diderita', 255)->nullable());

            /* ── D. Keterangan Lain-lain ─────────────────────────────── */

            $this->tambahKolom($table, 'hobi_olahraga', fn (Blueprint $t) => $t->string('hobi_olahraga', 150)->nullable());
            $this->tambahKolom($table, 'hobi_kesenian', fn (Blueprint $t) => $t->string('hobi_kesenian', 150)->nullable());
            $this->tambahKolom($table, 'hobi_organisasi', fn (Blueprint $t) => $t->string('hobi_organisasi', 150)->nullable());
            $this->tambahKolom($table, 'hobi_prestasi', fn (Blueprint $t) => $t->string('hobi_prestasi', 150)->nullable());
            $this->tambahKolom($table, 'hobi_lain', fn (Blueprint $t) => $t->string('hobi_lain', 150)->nullable());
            $this->tambahKolom($table, 'ukuran_seragam', fn (Blueprint $t) => $t->string('ukuran_seragam', 10)->nullable());

            /* ── Isian tambahan pada lembar formulir ─────────────────── */

            // "Referensi Pendaftar" — dari mana calon murid tahu informasi pendaftaran.
            $this->tambahKolom($table, 'referensi_pendaftar', fn (Blueprint $t) => $t->string('referensi_pendaftar', 150)->nullable());
        });
    }

    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropColumn([
                'no_kk', 'anak_ke', 'jumlah_saudara', 'tamat_tahun', 'no_ijazah',
                'rt', 'rw', 'desa', 'kecamatan', 'kabupaten', 'propinsi',
                'ayah_ttl', 'ayah_pendidikan', 'ibu_ttl', 'ibu_pendidikan',
                'ortu_rt', 'ortu_rw', 'ortu_desa', 'ortu_kecamatan', 'ortu_kabupaten', 'ortu_propinsi',
                'penghasilan',
                'smp_kecamatan', 'smp_kabupaten', 'smp_propinsi',
                'ijazah_nomor_tahun', 'lama_belajar', 'nilai_ujian',
                'golongan_darah', 'tinggi_badan', 'berat_badan', 'penyakit_pernah_diderita',
                'hobi_olahraga', 'hobi_kesenian', 'hobi_organisasi', 'hobi_prestasi', 'hobi_lain',
                'ukuran_seragam',
                'referensi_pendaftar',
            ]);
        });
    }

    /**
     * Tambahkan kolom hanya bila belum ada, supaya migrasi tetap aman
     * dijalankan ulang pada basis data yang sudah pernah dimigrasi.
     */
    private function tambahKolom(Blueprint $table, string $nama, callable $definisi): void
    {
        if (!Schema::hasColumn('pendaftar', $nama)) {
            $definisi($table);
        }
    }
};
