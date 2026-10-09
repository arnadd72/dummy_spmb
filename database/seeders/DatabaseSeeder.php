<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Faq;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Jurusan ──────────────────────────────────────────────────
        $jurusanData = [
            [
                'kode'         => 'AKL',
                'nama'         => 'Akuntansi dan Keuangan Lembaga',
                'deskripsi'    => 'Program keahlian yang mempelajari akuntansi, pembukuan, perpajakan, perbankan, dan pengelolaan keuangan lembaga secara profesional.',
                'prospek_karir'=> 'Staf Akuntansi, Kasir Bank, Akuntan Publik, Wirausaha di Bidang Keuangan',
                'kuota'        => 72,
                'kriteria'     => [
                    'bobot_mapel'   => ['matematika' => 0.5, 'ips_ekonomi' => 0.3, 'b_indonesia' => 0.2],
                    'minat_terkait' => ['angka', 'hitungan', 'administrasi', 'finansial', 'keuangan'],
                ],
            ],
            [
                'kode'         => 'MPLB',
                'nama'         => 'Manajemen Perkantoran dan Layanan Bisnis',
                'deskripsi'    => 'Program keahlian yang mempelajari manajemen perkantoran modern, layanan bisnis, komunikasi profesional, dan administrasi bisnis.',
                'prospek_karir'=> 'Sekretaris, Resepsionis, Admin Perkantoran, Customer Service, Wirausaha',
                'kuota'        => 72,
                'kriteria'     => [
                    'bobot_mapel'   => ['b_indonesia' => 0.4, 'ips_ekonomi' => 0.3, 'matematika' => 0.2, 'b_inggris' => 0.1],
                    'minat_terkait' => ['administrasi', 'komunikasi', 'layanan', 'bisnis', 'perkantoran'],
                ],
            ],
            [
                'kode'         => 'FKK',
                'nama'         => 'Farmasi Klinis dan Komunitas',
                'deskripsi'    => 'Program keahlian yang mempelajari ilmu farmasi, pengelolaan obat, pelayanan kesehatan di apotek, klinik, dan rumah sakit.',
                'prospek_karir'=> 'Asisten Apoteker, Tenaga Teknis Kefarmasian, Staf Apotek/Klinik, Wirausaha Apotek',
                'kuota'        => 72,
                'kriteria'     => [
                    'bobot_mapel'   => ['ipa_biologi' => 0.4, 'ipa_kimia' => 0.3, 'matematika' => 0.2, 'b_indonesia' => 0.1],
                    'minat_terkait' => ['kesehatan', 'sains', 'biologi', 'kimia', 'pelayanan kesehatan', 'farmasi'],
                ],
            ],
            [
                'kode'         => 'TKR',
                'nama'         => 'Teknik Kendaraan Ringan',
                'deskripsi'    => 'Program keahlian yang mempelajari teknik otomotif, perawatan dan perbaikan kendaraan ringan, sistem mesin, kelistrikan kendaraan.',
                'prospek_karir'=> 'Mekanik Otomotif, Teknisi Kendaraan, Staf Bengkel Resmi, Wirausaha Bengkel',
                'kuota'        => 108,
                'kriteria'     => [
                    'bobot_mapel'   => ['matematika' => 0.3, 'ipa_fisika' => 0.4, 'prakarya' => 0.2, 'b_indonesia' => 0.1],
                    'minat_terkait' => ['otomotif', 'teknik', 'mesin', 'perbaikan', 'kendaraan'],
                ],
            ],
        ];

        foreach ($jurusanData as $j) {
            Jurusan::updateOrCreate(['kode' => $j['kode']], $j);
        }

        // ── FAQ ───────────────────────────────────────────────────────
        $faqData = [
            [
                'pertanyaan' => 'Kapan jadwal pendaftaran PPDB dibuka?',
                'jawaban'    => 'PPDB SMK Ma\'arif NU 1 Wangon Tahun Ajaran 2026/2027 dibuka dalam 2 gelombang: Gelombang I (1 Januari – 31 Maret 2026) dan Gelombang II (1 April – 30 Juni 2026). Pendaftaran dilakukan secara online melalui website ini.',
                'keywords'   => ['jadwal', 'kapan', 'tanggal', 'gelombang', 'buka', 'waktu', 'pendaftaran'],
                'kategori'   => 'Jadwal',
            ],
            [
                'pertanyaan' => 'Dokumen apa saja yang diperlukan untuk mendaftar?',
                'jawaban'    => 'Dokumen yang diperlukan: (1) Kartu Keluarga (KK) — wajib, (2) Akta Kelahiran — wajib, (3) Rapor Semester Terakhir — opsional (untuk rekomendasi jurusan lebih akurat). Semua dokumen dalam format JPG, PNG, atau PDF dengan ukuran maksimal 5 MB.',
                'keywords'   => ['dokumen', 'persyaratan', 'syarat', 'berkas', 'upload', 'kk', 'kartu keluarga', 'akta', 'rapor'],
                'kategori'   => 'Persyaratan',
            ],
            [
                'pertanyaan' => 'Bagaimana cara upload dokumen KK dan Akta Kelahiran?',
                'jawaban'    => 'Pada Langkah 2 pendaftaran, klik area upload atau seret file ke zona upload. Foto/scan dokumen dengan pencahayaan cukup dan resolusi minimal 1024px. Sistem OCR akan otomatis membaca data dari dokumen Anda. Periksa dan perbaiki jika ada kesalahan pembacaan.',
                'keywords'   => ['cara', 'upload', 'unggah', 'dokumen', 'kk', 'akta', 'foto', 'scan'],
                'kategori'   => 'Teknis',
            ],
            [
                'pertanyaan' => 'Jurusan apa saja yang tersedia di SMK Ma\'arif NU 1 Wangon?',
                'jawaban'    => 'SMK Ma\'arif NU 1 Wangon memiliki 4 jurusan: (1) AKL — Akuntansi dan Keuangan Lembaga, (2) MPLB — Manajemen Perkantoran dan Layanan Bisnis, (3) FKK — Farmasi Klinis dan Komunitas, (4) TKR — Teknik Kendaraan Ringan.',
                'keywords'   => ['jurusan', 'program', 'keahlian', 'akl', 'mplb', 'fkk', 'tkr', 'tersedia', 'pilihan'],
                'kategori'   => 'Jurusan',
            ],
            [
                'pertanyaan' => 'Bagaimana sistem rekomendasi jurusan bekerja?',
                'jawaban'    => 'Sistem rekomendasi menghitung skor kesesuaian berdasarkan: nilai rapor (60%), minat yang dipilih (30%), dan kriteria sekolah (10%). Hasilnya ditampilkan sebagai "Sangat Cocok", "Cocok", atau "Kurang Cocok". Rekomendasi bersifat informatif, Anda tetap bebas memilih jurusan apapun.',
                'keywords'   => ['rekomendasi', 'sistem', 'skor', 'cocok', 'jurusan', 'cara kerja', 'minat', 'nilai'],
                'kategori'   => 'Jurusan',
            ],
            [
                'pertanyaan' => 'Bagaimana cara mengecek status pendaftaran saya?',
                'jawaban'    => 'Klik menu "Cek Status Pendaftaran" di navbar atau halaman utama, lalu masukkan nomor pendaftaran Anda (format: PPDB-2026-XXXX). Status akan ditampilkan: Menunggu Verifikasi, Diterima, Ditolak, atau Perlu Revisi.',
                'keywords'   => ['cek', 'status', 'pendaftaran', 'nomor', 'verifikasi', 'lihat', 'pantau'],
                'kategori'   => 'Status',
            ],
            [
                'pertanyaan' => 'Berapa biaya pendaftaran PPDB?',
                'jawaban'    => 'Pendaftaran melalui sistem online ini gratis. Untuk informasi biaya daftar ulang dan SPP, silakan hubungi panitia PPDB di nomor 081234567890 atau kunjungi langsung ke sekolah.',
                'keywords'   => ['biaya', 'bayar', 'gratis', 'spp', 'uang', 'pendaftaran', 'daftar ulang'],
                'kategori'   => 'Biaya',
            ],
        ];

        foreach ($faqData as $f) {
            Faq::updateOrCreate(['pertanyaan' => $f['pertanyaan']], $f);
        }
    }
}
