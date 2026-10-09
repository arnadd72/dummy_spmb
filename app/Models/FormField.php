<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $table = 'form_fields';

    protected $fillable = [
        'kunci',
        'langkah',
        'label',
        'teks_bantuan',
        'tipe',
        'opsi',
        'placeholder',
        'nilai_default',
        'tampil',
        'wajib',
        'urutan',
        'is_kustom',
    ];

    protected $casts = [
        'opsi' => 'array',
        'tampil' => 'boolean',
        'wajib' => 'boolean',
        'is_kustom' => 'boolean',
        'urutan' => 'integer',
    ];

    public function scopeTampil($query)
    {
        return $query->where('tampil', true);
    }

    public function scopeLangkah($query, string $langkah)
    {
        return $query->where('langkah', $langkah);
    }

    /**
     * Daftar opsi sebagai array rapi untuk select/radio.
     *
     * @return array<int, string>
     */
    public function getOpsiListAttribute(): array
    {
        $opsi = $this->opsi ?? [];

        if (is_string($opsi)) {
            $decoded = json_decode($opsi, true);
            $opsi = is_array($decoded) ? $decoded : preg_split('/\r?\n/', $opsi);
        }

        return array_values(array_filter(array_map('trim', (array) $opsi)));
    }

    /**
     * Semua field tampil untuk satu langkah, urut tampilan.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function untukLangkah(string $langkah)
    {
        return static::tampil()->langkah($langkah)->orderBy('urutan')->get();
    }

    /**
     * Seed bawaan: cerminkan isian formulir SPMB yang sudah ada.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function bawaan(): array
    {
        return [
            // ── Step 1: Verifikasi awal (nama/nisn berbagi kunci dengan step5 agar sekali edit berlaku dua tempat) ──
            ['kunci' => 'setuju', 'langkah' => 'step1', 'label' => 'Persetujuan Syarat & Ketentuan', 'teks_bantuan' => 'Centang persetujuan sebelum lanjut.', 'tipe' => 'checkbox', 'tampil' => true, 'wajib' => true, 'urutan' => 30],
            // ── Step 2: Upload dokumen ──
            ['kunci' => 'akta', 'langkah' => 'step2', 'label' => 'Akta Kelahiran', 'teks_bantuan' => 'JPG, PNG, atau PDF. Maks 5 MB.', 'tipe' => 'file', 'tampil' => true, 'wajib' => false, 'urutan' => 10],
            ['kunci' => 'kk', 'langkah' => 'step2', 'label' => 'Kartu Keluarga (KK)', 'teks_bantuan' => 'JPG, PNG, atau PDF. Maks 5 MB.', 'tipe' => 'file', 'tampil' => true, 'wajib' => false, 'urutan' => 20],
            // ── Step 3: Rapor ──
            ['kunci' => 'rapor', 'langkah' => 'step3', 'label' => 'Rapor Semester Terakhir', 'teks_bantuan' => 'JPG, PNG, atau PDF. Maks 5 MB.', 'tipe' => 'file', 'tampil' => true, 'wajib' => false, 'urutan' => 10],
            ['kunci' => 'nilai_rapor', 'langkah' => 'step3', 'label' => 'Input Nilai Rata-rata Rapor (Manual)', 'teks_bantuan' => ' Bagian input nilai manual setelah upload rapor.', 'tipe' => 'text', 'tampil' => true, 'wajib' => false, 'urutan' => 20],
            // ── Step 4: Jurusan & minat ──
            ['kunci' => 'minat', 'langkah' => 'step4', 'label' => 'Bidang Minat', 'tipe' => 'select', 'opsi' => ['Angka & Hitungan', 'Keuangan & Perbankan', 'Administrasi & Perkantoran', 'Komunikasi & Layanan Bisnis', 'Kesehatan & Farmasi', 'Sains & Biologi', 'Otomotif & Mesin', 'Teknik & Perbaikan'], 'tampil' => true, 'wajib' => true, 'urutan' => 10],
            ['kunci' => 'jurusan_1', 'langkah' => 'step4', 'label' => 'Pilihan Jurusan Pertama', 'tipe' => 'select', 'tampil' => true, 'wajib' => true, 'urutan' => 20],
            ['kunci' => 'jurusan_2', 'langkah' => 'step4', 'label' => 'Pilihan Jurusan Kedua', 'tipe' => 'select', 'tampil' => true, 'wajib' => false, 'urutan' => 30],
            ['kunci' => 'nama_lengkap', 'langkah' => 'step5', 'label' => 'Nama Lengkap Murid Baru', 'teks_bantuan' => 'Tulis dengan huruf besar dan jelas sesuai Kutipan Akta Kelahiran.', 'tipe' => 'text', 'placeholder' => 'Sesuai akta kelahiran', 'tampil' => true, 'wajib' => true, 'urutan' => 10],
            ['kunci' => 'tempat_lahir', 'langkah' => 'step5', 'label' => 'Tempat Lahir', 'tipe' => 'text', 'placeholder' => 'Contoh: Banyumas', 'tampil' => true, 'wajib' => true, 'urutan' => 20],
            ['kunci' => 'tanggal_lahir', 'langkah' => 'step5', 'label' => 'Tanggal Lahir', 'tipe' => 'date', 'tampil' => true, 'wajib' => true, 'urutan' => 21],
            ['kunci' => 'nisn', 'langkah' => 'step5', 'label' => 'NISN', 'teks_bantuan' => 'Nomor Induk Siswa Nasional (10 digit).', 'tipe' => 'text', 'placeholder' => '10 digit', 'tampil' => true, 'wajib' => false, 'urutan' => 30],
            ['kunci' => 'nik', 'langkah' => 'step5', 'label' => 'NIK Murid', 'tipe' => 'text', 'placeholder' => '16 digit', 'tampil' => true, 'wajib' => true, 'urutan' => 40],
            ['kunci' => 'no_kk', 'langkah' => 'step5', 'label' => 'No. Kartu Keluarga (KK)', 'tipe' => 'text', 'placeholder' => '16 digit', 'tampil' => true, 'wajib' => true, 'urutan' => 41],
            ['kunci' => 'jenis_kelamin', 'langkah' => 'step5', 'label' => 'Jenis Kelamin', 'tipe' => 'select', 'opsi' => ['Laki – laki', 'Perempuan'], 'tampil' => true, 'wajib' => true, 'urutan' => 50],
            ['kunci' => 'agama', 'langkah' => 'step5', 'label' => 'Agama', 'tipe' => 'select', 'opsi' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'], 'tampil' => true, 'wajib' => true, 'urutan' => 60],
            ['kunci' => 'anak_ke', 'langkah' => 'step5', 'label' => 'Anak ke berapa', 'tipe' => 'number', 'placeholder' => '1', 'tampil' => true, 'wajib' => true, 'urutan' => 70],
            ['kunci' => 'jumlah_saudara', 'langkah' => 'step5', 'label' => 'Jumlah saudara kandung', 'tipe' => 'number', 'placeholder' => '2', 'tampil' => true, 'wajib' => true, 'urutan' => 71],
            ['kunci' => 'asal_sekolah', 'langkah' => 'step5', 'label' => 'Asal Sekolah (SMP/MTs)', 'tipe' => 'text', 'placeholder' => 'Nama sekolah asal', 'tampil' => true, 'wajib' => true, 'urutan' => 90],
            ['kunci' => 'tamat_tahun', 'langkah' => 'step5', 'label' => 'Tamat SMP / MTs Tahun', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 100],
            ['kunci' => 'no_ijazah', 'langkah' => 'step5', 'label' => 'No. Ijazah', 'tipe' => 'text', 'placeholder' => 'Nomor ijazah SMP/MTs', 'tampil' => true, 'wajib' => false, 'urutan' => 101],
            ['kunci' => 'no_hp', 'langkah' => 'step5', 'label' => 'No. Telepon Murid', 'teks_bantuan' => 'Nomor telepon aktif murid untuk dihubungi panitia PPDB.', 'tipe' => 'tel', 'placeholder' => '08xxxxxxxxxx', 'tampil' => true, 'wajib' => true, 'urutan' => 110],
            ['kunci' => 'no_wa', 'langkah' => 'step5', 'label' => 'No. WhatsApp Murid', 'teks_bantuan' => 'Nomor WhatsApp aktif untuk info cepat & konfirmasi panitia.', 'tipe' => 'tel', 'placeholder' => '08xxxxxxxxxx', 'tampil' => true, 'wajib' => false, 'urutan' => 111],
            ['kunci' => 'email', 'langkah' => 'step5', 'label' => 'Email', 'teks_bantuan' => 'Kode pendaftaran akan dikirim otomatis ke email ini.', 'tipe' => 'email', 'placeholder' => 'nama@email.com', 'tampil' => true, 'wajib' => true, 'urutan' => 120],
            ['kunci' => 'rt', 'langkah' => 'step5', 'label' => 'RT', 'tipe' => 'text', 'placeholder' => '02', 'tampil' => true, 'wajib' => true, 'urutan' => 130],
            ['kunci' => 'rw', 'langkah' => 'step5', 'label' => 'RW', 'tipe' => 'text', 'placeholder' => '04', 'tampil' => true, 'wajib' => true, 'urutan' => 131],
            ['kunci' => 'desa', 'langkah' => 'step5', 'label' => 'Desa / Kelurahan', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 132],
            ['kunci' => 'kecamatan', 'langkah' => 'step5', 'label' => 'Kecamatan', 'tipe' => 'text', 'placeholder' => 'Wangon', 'tampil' => true, 'wajib' => true, 'urutan' => 133],
            ['kunci' => 'kabupaten', 'langkah' => 'step5', 'label' => 'Kabupaten', 'tipe' => 'text', 'placeholder' => 'Banyumas', 'tampil' => true, 'wajib' => true, 'urutan' => 134],
            ['kunci' => 'propinsi', 'langkah' => 'step5', 'label' => 'Propinsi', 'tipe' => 'text', 'placeholder' => 'Jawa Tengah', 'tampil' => true, 'wajib' => true, 'urutan' => 135],
            ['kunci' => 'penghasilan', 'langkah' => 'step5', 'label' => 'Penghasilan Bulanan Orang Tua', 'tipe' => 'radio', 'opsi' => ['< Rp 500.000', 'Rp 500.000 – Rp 999.999', 'Rp 1.000.000 – Rp 1.999.999', 'Rp 2.000.000 – Rp 4.999.999', '> Rp 5.000.000'], 'tampil' => true, 'wajib' => true, 'urutan' => 140],
            ['kunci' => 'referensi_pendaftar', 'langkah' => 'step5', 'label' => 'Referensi Pendaftar', 'teks_bantuan' => 'Dari siapa / dari mana informasi pendaftaran didapat.', 'tipe' => 'text', 'tampil' => true, 'wajib' => false, 'urutan' => 150],
            ['kunci' => 'nama_ayah', 'langkah' => 'step6', 'label' => 'Nama Ayah', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 10],
            ['kunci' => 'ayah_ttl', 'langkah' => 'step6', 'label' => 'Tempat / Tanggal Lahir Ayah', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 11],
            ['kunci' => 'ayah_pendidikan', 'langkah' => 'step6', 'label' => 'Pendidikan Terakhir Ayah', 'tipe' => 'select', 'opsi' => ['SD', 'SMP', 'SMA/SMK', 'Diploma', 'Sarjana', 'Pascasarjana'], 'tampil' => true, 'wajib' => true, 'urutan' => 12],
            ['kunci' => 'pekerjaan_ayah', 'langkah' => 'step6', 'label' => 'Pekerjaan Ayah', 'tipe' => 'select', 'opsi' => ['Petani', 'Buruh', 'Wiraswasta', 'PNS', 'TNI/Polri', 'Guru', 'Karyawan Swasta', 'Lainnya'], 'tampil' => true, 'wajib' => true, 'urutan' => 13],
            ['kunci' => 'nama_ibu', 'langkah' => 'step6', 'label' => 'Nama Ibu', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 20],
            ['kunci' => 'ibu_ttl', 'langkah' => 'step6', 'label' => 'Tempat / Tanggal Lahir Ibu', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 21],
            ['kunci' => 'ibu_pendidikan', 'langkah' => 'step6', 'label' => 'Pendidikan Terakhir Ibu', 'tipe' => 'select', 'opsi' => ['SD', 'SMP', 'SMA/SMK', 'Diploma', 'Sarjana', 'Pascasarjana'], 'tampil' => true, 'wajib' => true, 'urutan' => 22],
            ['kunci' => 'pekerjaan_ibu', 'langkah' => 'step6', 'label' => 'Pekerjaan Ibu', 'tipe' => 'select', 'opsi' => ['Ibu Rumah Tangga', 'Petani', 'Buruh', 'Wiraswasta', 'PNS', 'Guru', 'Karyawan Swasta', 'Lainnya'], 'tampil' => true, 'wajib' => true, 'urutan' => 23],
            ['kunci' => 'no_hp_ortu', 'langkah' => 'step6', 'label' => 'No. Telepon Orang Tua / Wali', 'tipe' => 'tel', 'placeholder' => '08xxxxxxxxxx', 'tampil' => true, 'wajib' => true, 'urutan' => 30],
            ['kunci' => 'ortu_rt', 'langkah' => 'step6', 'label' => 'RT (Alamat Ortu)', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 31],
            ['kunci' => 'ortu_rw', 'langkah' => 'step6', 'label' => 'RW (Alamat Ortu)', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 32],
            ['kunci' => 'ortu_desa', 'langkah' => 'step6', 'label' => 'Desa / Kelurahan (Alamat Ortu)', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 33],
            ['kunci' => 'ortu_kecamatan', 'langkah' => 'step6', 'label' => 'Kecamatan (Alamat Ortu)', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 34],
            ['kunci' => 'ortu_kabupaten', 'langkah' => 'step6', 'label' => 'Kabupaten (Alamat Ortu)', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 35],
            ['kunci' => 'ortu_propinsi', 'langkah' => 'step6', 'label' => 'Propinsi (Alamat Ortu)', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 36],
            ['kunci' => 'smp_kecamatan', 'langkah' => 'step7', 'label' => 'SMP/MTs Kecamatan', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 1],
            ['kunci' => 'smp_kabupaten', 'langkah' => 'step7', 'label' => 'SMP/MTs Kabupaten', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 2],
            ['kunci' => 'smp_propinsi', 'langkah' => 'step7', 'label' => 'SMP/MTs Propinsi', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 3],
            ['kunci' => 'ijazah_nomor_tahun', 'langkah' => 'step7', 'label' => 'Nomor & Tahun Ijazah', 'tipe' => 'text', 'tampil' => true, 'wajib' => false, 'urutan' => 4],
            ['kunci' => 'lama_belajar', 'langkah' => 'step7', 'label' => 'Lama Belajar', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 5],
            ['kunci' => 'nilai_ujian', 'langkah' => 'step7', 'label' => 'Nilai Ujian', 'teks_bantuan' => ' Bagian nilai ujian (B. Indonesia, B. Inggris, Matematika, IPA).', 'tipe' => 'text', 'tampil' => true, 'wajib' => true, 'urutan' => 6],
            ['kunci' => 'golongan_darah', 'langkah' => 'step7', 'label' => 'Golongan Darah', 'tipe' => 'select', 'opsi' => ['A', 'B', 'AB', 'O', 'Tidak Tahu'], 'tampil' => true, 'wajib' => true, 'urutan' => 10],
            ['kunci' => 'tinggi_badan', 'langkah' => 'step7', 'label' => 'Tinggi Badan (cm)', 'tipe' => 'number', 'tampil' => true, 'wajib' => true, 'urutan' => 11],
            ['kunci' => 'berat_badan', 'langkah' => 'step7', 'label' => 'Berat Badan (kg)', 'tipe' => 'number', 'tampil' => true, 'wajib' => true, 'urutan' => 12],
            ['kunci' => 'penyakit_pernah_diderita', 'langkah' => 'step7', 'label' => 'Penyakit Yang Pernah Diderita', 'tipe' => 'text', 'tampil' => true, 'wajib' => false, 'urutan' => 13],
            ['kunci' => 'hobi_olahraga', 'langkah' => 'step7', 'label' => 'Hobi Olah Raga', 'tipe' => 'text', 'tampil' => true, 'wajib' => false, 'urutan' => 14],
            ['kunci' => 'hobi_kesenian', 'langkah' => 'step7', 'label' => 'Hobi Kesenian', 'tipe' => 'text', 'tampil' => true, 'wajib' => false, 'urutan' => 15],
            ['kunci' => 'hobi_organisasi', 'langkah' => 'step7', 'label' => 'Hobi Keorganisasian', 'tipe' => 'text', 'tampil' => true, 'wajib' => false, 'urutan' => 16],
            ['kunci' => 'hobi_prestasi', 'langkah' => 'step7', 'label' => 'Prestasi', 'tipe' => 'text', 'tampil' => true, 'wajib' => false, 'urutan' => 17],
            ['kunci' => 'hobi_lain', 'langkah' => 'step7', 'label' => 'Lain-lain (Hobi)', 'tipe' => 'text', 'tampil' => true, 'wajib' => false, 'urutan' => 18],
            ['kunci' => 'ukuran_seragam', 'langkah' => 'step7', 'label' => 'Ukuran Seragam', 'tipe' => 'select', 'opsi' => ['M', 'L', 'XL', 'XXL', 'XXXL', 'Jumbo'], 'tampil' => true, 'wajib' => true, 'urutan' => 20],
            // ── Step 8: Konfirmasi akhir ──
            ['kunci' => 'konfirmasi_final', 'langkah' => 'step8', 'label' => 'Saya menyatakan seluruh data di atas benar dan dapat dipertanggungjawabkan', 'teks_bantuan' => 'Pemalsuan data mengakibatkan pembatalan pendaftaran sesuai ketentuan PPDB.', 'tipe' => 'checkbox', 'tampil' => true, 'wajib' => true, 'urutan' => 10],
        ];
    }
}
