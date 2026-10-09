# PRD: Digitalisasi Pendaftaran Peserta Didik Baru (PPDB) Terpusat Berbasis Smart System dan Rekomendasi Terintegrasi

## SMK Ma'arif NU 1 Wangon — Tahun Ajaran 2026/2027

| | |
|---|---|
| **Dokumen** | Product Requirements Document (PRD) v1.0 |
| **Status** | Draft — Menunggu Validasi Pihak Sekolah |
| **Estimasi Pengerjaan** | ± 1 bulan (4 minggu, 1–2 developer) |
| **Teknologi Utama** | Laravel (PHP), Blade + Bootstrap/Tailwind, JavaScript, Tesseract.js, Supabase PostgreSQL, Supabase Storage |

---

## 1. Latar Belakang

Proses PPDB di SMK Ma'arif NU 1 Wangon selama ini berjalan konvensional, menyebabkan antrean panjang, input data manual berulang, risiko kesalahan transkripsi, dan sulitnya merekapitulasi data pendaftar. SMK Ma'arif NU 1 Wangon memiliki 4 program keahlian unggulan — Akuntansi dan Keuangan Lembaga (AKL), Manajemen Perkantoran dan Layanan Bisnis (MPLB), Farmasi Klinis dan Komunitas (FKK), dan Teknik Kendaraan Ringan (TKR) — sehingga kebutuhan rekomendasi jurusan yang objektif semakin penting [1][4].

Sistem ini dirancang untuk mendigitalisasi seluruh alur PPDB secara terpusat, memanfaatkan OCR client-side (tanpa mengirim dokumen sensitif ke server pihak ketiga), serta menyediakan smart recommendation jurusan berbasis nilai dan minat — semuanya dibangun di atas stack open-source dan gratis.

---

## 2. Tujuan

1. **Efisiensi** — Mengurangi waktu pendaftaran dari ± 30 menit manual menjadi ± 10 menit digital.
2. **Akurasi Data** — Auto-fill dari OCR KK & Akta mengurangi kesalahan input manual hingga 80% (target internal).
3. **Rekomendasi Objektif** — Memberikan saran jurusan berbasis skor terukur (nilai rapor + minat + kriteria sekolah), bukan sekadar ikut-ikutan teman.
4. **Informasi Terpusat** — Admin dapat memfilter, memverifikasi, mencetak, dan merekapitulasi data dari satu dashboard.
5. **Keamanan Data Pribadi** — Dokumen KK, Akta, dan rapor disimpan di Supabase Storage private bucket; hanya admin terverifikasi yang dapat mengakses.

---

## 3. Ruang Lingkup Sistem

### 3.1. Include (Dalam Scope)

- Multi-step form pendaftaran (7 tahap)
- OCR Tesseract.js client-side untuk auto-fill dari KK & Akta Kelahiran
- Upload rapor opsional
- Smart recommendation jurusan (weighted scoring / content-based)
- Chatbot PPDB berbasis FAQ/knowledge base (tanpa paid AI API)
- Dashboard admin: verifikasi, pencarian, filter, statistik
- Ekspor CSV & cetak PDF (seluruh data / hasil filter)
- Manajemen jurusan & FAQ oleh admin
- Role-based access (guest, pendaftar, admin)

### 3.2. Exclude (Out of Scope)

- Integrasi Dapodik / NISN otomatis
- Pembayaran online real-time / payment gateway
- Multi-sekolah (hanya 1 sekolah: SMK Ma'arif NU 1 Wangon)
- Aplikasi mobile native (cukup responsive web)
- Verifikasi dokumen otomatis berbasis AI / deep learning
- Fitur pelacakan status pendaftar oleh orang tua via SMS/WhatsApp (bisa jadi fase 2)

---

## 4. Arsitektur & Teknologi

### 4.1. Arsitektur Umum

```
┌─────────────────────────────────────────────────────────┐
│  Browser (Client)                                       │
│  - Multi-step form (Blade + JS)                         │
│  - Tesseract.js OCR (WebAssembly, 100% client-side)     │
│  - Chatbot widget (rule-based / keyword matching)       │
└──────────────────────┬──────────────────────────────────┘
                       │ HTTPS (JSON)
┌──────────────────────▼──────────────────────────────────┐
│  Laravel API + Blade Views                              │
│  - Auth (Sanctum / Session)                             │
│  - Form validation, storage orchestration               │
│  - Recommendation engine (Weighted Scoring)             │
│  - Report generator (CSV / PDF)                         │
│  - Chatbot handler (FAQ lookup)                         │
└───┬──────────────────────────────┬──────────────────────┘
    │                              │
┌───▼───────────────┐   ┌──────────▼──────────┐
│  Supabase         │   │  Supabase Storage    │
│  PostgreSQL       │   │  - bucket: kk        │
│  (data pendaftar, │   │  - bucket: akta      │
│   jurusan, FAQ,   │   │  - bucket: rapor     │
│   bobot, dll.)    │   │  (semua PRIVATE)     │
└───────────────────┘   └─────────────────────┘
```

### 4.2. Stack Teknologi

| Komponen | Teknologi | Alasan |
|---|---|---|
| Backend | Laravel 11.x | MVC framework PHP, routing, queue, validation, Blade templating |
| Frontend | Blade + Bootstrap 5 atau Tailwind CSS | Responsive, komponen siap pakai, cepat |
| OCR | Tesseract.js v5 (bahasa: `ind`) | Gratis, 100% client-side, tidak ada upload dokumen ke server OCR eksternal |
| Database | Supabase PostgreSQL (via `laravel-supabase` / raw PDO) | Gratis tier, relational, row-level security |
| Storage | Supabase Storage (private buckets) | Gratis, terintegrasi dengan PostgreSQL, signed URL untuk akses aman |
| Chatbot | Rule-based / keyword matching + knowledge base (FAQ di DB) | Tanpa API berbayar, fully self-hosted |
| PDF | DomPDF / Laravel-snappy + wkhtmltopdf | Gratis, open-source |
| CSV | Built-in PHP `fputcsv` / League CSV | Ringan |

> **Catatan:** Supabase digunakan sebagai database PostgreSQL. Alternatif: jika sekolah lebih nyaman dengan MySQL/MariaDB lokal, dapat diganti tanpa mengubah logika bisnis. **Perlu Validasi dengan Pihak Sekolah.**

---

## 5. Aktor & Hak Akses

| Aktor | Deskripsi | Hak Akses |
|---|---|---|
| **Guest (Calon Siswa/Orang Tua)** | Membuka halaman PPDB | Melihat info jurusan, FAQ, chatbot, mengisi form pendaftaran |
| **Pendaftar (Terdaftar)** | Sudah submit form | Melihat nomor pendaftaran, status verifikasi, mencetak bukti |
| **Admin** | Panitia PPDB sekolah | Dashboard, verifikasi, kelola jurusan/FAQ/bobot, ekspor CSV/PDF |
| **Super Admin** *(opsional)* | Kepala Panitia | Semua hak Admin + kelola user admin lain |

---

## 6. Alur Pendaftaran — Multi-Step Form (7 Tahap)

### Alur Utama

```
Step 1 → Step 2 → Step 3 → Step 4 → Step 5 → Step 6 → Step 7
Persetujuan → Upload KK+Akta (OCR) → Upload Rapor (Opsional) → Pilih Jurusan
+ Rekomendasi → Data Siswa → Data Ortu → Review → Submit → Nomor Daftar
```

### 6.1. Step 1 — Persetujuan & Syarat

- Menampilkan persyaratan pendaftaran, jadwal, dan ketentuan.
- Checkbox persetujuan: *"Saya menyatakan data yang diisi benar dan dokumen yang diunggah adalah asli."*
- Tombol "Mulai Pendaftaran" — tidak dapat melanjutkan tanpa mencentang.

### 6.2. Step 2 — Upload KK & Akta Kelahiran + OCR Auto-Fill

- Calon siswa mengunggah **scan/foto Kartu Keluarga (KK)** dan **Akta Kelahiran** (format: JPG/PNG/PDF — untuk PDF, render halaman pertama sebagai gambar via `pdf.js` client-side).
- **OCR Tesseract.js** berjalan di browser (WebAssembly, bahasa `ind`), mengekstrak:
  - Dari **KK**: NIK, Nama lengkap, Tempat/Tanggal Lahir, Jenis Kelamin, Alamat, RT/RW, Desa/Kelurahan, Kecamatan, Kabupaten, Provinsi, nama orang tua (ayah/ibu), pekerjaan orang tua.
  - Dari **Akta**: Nama lengkap, Tempat/Tanggal Lahir, NIK (jika ada), nama orang tua.
- Hasil OCR ditampilkan dalam form **editable** — pengguna wajib memeriksa dan mengoreksi kesalahan OCR.
- Field yang tidak terdeteksi OCR dapat diisi manual.
- Validasi minimum: NIK 16 digit, nama wajib terisi.
- File asli KK dan Akta diupload ke Supabase Storage bucket `kk` dan `akta` (private).

> **Catatan teknis:** Tesseract.js dengan bahasa Indonesia (`ind.traineddata`) diunduh sekali (~11 MB) dan di-cache di browser. Proses OCR untuk 1 gambar resolusi normal berlangsung ± 3–10 detik tergantung perangkat. Target: wajib ada indikator loading dan fallback pengisian manual.

### 6.3. Step 3 — Upload Rapor (Opsional)

- Banner info: *"Rapor bersifat opsional. Jika belum tersedia, Anda dapat melewati tahap ini."*
- Jika diunggah: file foto/scan rapor semester terakhir (JPG/PNG/PDF), maks 5 MB.
- Jika diunggah, OCR opsional untuk tabel nilai (atau input manual nilai per mata pelajaran).
- Tombol **"Lewati"** tersedia jelas.
- Jika dilewati → rekomendasi jurusan hanya berdasarkan minat & kriteria umum (bukan nilai).

### 6.4. Step 4 — Pemilihan Jurusan & Rekomendasi

- Menampilkan 4 jurusan SMK Ma'arif NU 1 Wangon: **AKL, MPLB, FKK, TKR** [1][4].
- Setiap jurusan memiliki deskripsi singkat, prospek karier, dan kriteria.
- Pengguna dapat memilih **maksimal 2 jurusan** (pilihan ke-1 dan ke-2).
- **Panel Rekomendasi:** Sistem menghitung skor rekomendasi untuk setiap jurusan berdasarkan:
  - Nilai rapor (jika diunggah)
  - Minat yang dipilih pengguna (dropdown minat: angka/hitungan, kesehatan, administrasi, teknik)
  - Kriteria bobot jurusan yang ditentukan sekolah
- Hasil rekomendasi ditampilkan sebagai **badge/peringkat** (Sangat Cocok / Cocok / Kurang Cocok) di samping setiap jurusan.
- Pengguna tetap bebas memilih jurusan apa pun — rekomendasi bersifat informatif.

### 6.5. Step 5 — Data Siswa

- Auto-filled dari OCR KK/Akta jika tersedia.
- Field wajib:
  - NISN *(opsional, Perlu Validasi dengan Pihak Sekolah)*
  - NIK (16 digit)
  - Nama lengkap
  - Jenis kelamin
  - Tempat, tanggal lahir
  - Agama
  - Alamat lengkap
  - Asal sekolah (SMP/MTs)
  - Nomor HP aktif (untuk notifikasi status)

### 6.6. Step 6 — Data Orang Tua / Wali

- Auto-filled dari OCR KK jika tersedia.
- Field wajib:
  - Nama Ayah
  - Pekerjaan Ayah
  - Nama Ibu
  - Pekerjaan Ibu
  - Nomor HP Orang Tua/Wali
  - Alamat (jika berbeda dengan alamat siswa)

### 6.7. Step 7 — Review & Submit

- Menampilkan ringkasan seluruh data dalam format read-only.
- Link "Ubah" pada setiap section untuk kembali ke step terkait.
- Checkbox final: *"Saya menyatakan seluruh data di atas benar dan dapat dipertanggungjawabkan."*
- Tombol **"Submit Pendaftaran"**.
- Setelah submit:
  - Sistem menyimpan data ke Supabase PostgreSQL.
  - File dokumen dipindahkan dari temporary ke bucket final (atau langsung diupload saat submit).
  - **Nomor Pendaftaran** di-generate otomatis dengan format: `PPDB-2026-XXXX` (4 digit increment, per periode). **Perlu Validasi dengan Pihak Sekolah.**
  - Halaman sukses menampilkan nomor pendaftaran + tombol cetak bukti (PDF).

---

## 7. Smart Recommendation Engine

### 7.1. Pendekatan: Weighted Scoring / Content-Based Recommendation

Setiap jurusan memiliki **profil kebutuhan** yang terdiri dari:
- Bobot nilai mata pelajaran pendukung
- Kata kunci minat
- Kriteria tambahan (jenis kelamin prioritas, kebutuhan khusus, dll.)

Skor rekomendasi dihitung:

```
Skor_Jurusan_J = ( Σ (bobot_mapel_i × nilai_mapel_i) × 0.6 )
               + ( kecocokan_minat × 0.3 )
               + ( kriteria_sekolah × 0.1 )
```

### 7.2. Bobot Default (Perlu Validasi dengan Pihak Sekolah)

| Jurusan | Mapel Pendukung (bobot) | Minat Terkait |
|---|---|---|
| **AKL** (Akuntansi & Keuangan Lembaga) | Matematika (0.5), IPS/Ekonomi (0.3), B. Indonesia (0.2) | Angka, hitungan, administrasi, finansial |
| **MPLB** (Manajemen Perkantoran & Layanan Bisnis) | B. Indonesia (0.4), IPS (0.3), Matematika (0.2), B. Inggris (0.1) | Administrasi, komunikasi, layanan, bisnis |
| **FKK** (Farmasi Klinis & Komunitas) | IPA/Biologi (0.4), Kimia (0.3), Matematika (0.2), B. Indonesia (0.1) | Kesehatan, sains, biologi, pelayanan kesehatan |
| **TKR** (Teknik Kendaraan Ringan) | Matematika (0.3), IPA/Fisika (0.4), Prakarya (0.2), PJOK (0.1) | Otomotif, teknik, mesin, perbaikan |

> **Semua bobot di atas adalah default pabrik. Admin dapat mengubah bobot melalui Dashboard → Manajemen Jurusan.** **Perlu Validasi dengan Pihak Sekolah.**

### 7.3. Contoh Perhitungan

Seorang siswa dengan nilai: Matematika = 85, IPA = 78, B. Indonesia = 80, IPS = 82, minat = "otomotif".

- **TKR:** (85×0.3 + 78×0.4 + 80×0.0 + 82×0.0) × 0.6 + (1.0 × 0.3) + (0 × 0.1) = (25.5 + 31.2) × 0.6 + 0.3 = 34.02 + 0.3 = **34.32%** → **Sangat Cocok**
- **FKK:** (85×0.2 + 78×0.0 + 80×0.1 + 82×0.0) × 0.6 + (0.0 × 0.3) = (17 + 8) × 0.6 = **15%** → **Kurang Cocok**

### 7.4. Interpretasi Skor

| Skor | Label | Warna |
|---|---|---|
| ≥ 60% | Sangat Cocok | Hijau |
| 35–59% | Cocok | Kuning |
| < 35% | Kurang Cocok | Abu-abu/merah muda |

---

## 8. Chatbot PPDB (Knowledge Base / FAQ)

### 8.1. Mekanisme

- **Tanpa API AI berbayar.** Menggunakan rule-based matching + keyword scoring.
- Knowledge base disimpan di tabel `faqs` di Supabase.
- Setiap FAQ memiliki: pertanyaan, jawaban, keywords (array), kategori.
- Saat user mengetik pertanyaan, sistem:
  1. Normalisasi teks (lowercase, hapus stopword dasar)
  2. Hitung skor kemiripan (Jaccard / Cosine similarity sederhana)
  3. Ambil 3 FAQ teratas dengan skor tertinggi
  4. Jika skor melewati threshold (misal 0.35), jawab dengan FAQ terbaik
  5. Jika tidak, tampilkan pesan fallback: *"Maaf, saya belum menemukan jawaban. Silakan hubungi panitia PPDB melalui nomor berikut..."*

### 8.2. Konten FAQ Awal (Perlu Validasi dengan Pihak Sekolah)

- Jadwal PPDB
- Persyaratan dokumen
- Biaya pendaftaran *(jika ada)*
- Cara upload dokumen
- Cara menggunakan rekomendasi jurusan
- Cara cek status pendaftaran
- Kontak panitia

> Admin dapat menambah/mengubah/menghapus FAQ melalui dashboard.

---

## 9. Dashboard Admin

### 9.1. Fitur Utama

| Fitur | Deskripsi |
|---|---|
| **Overview Statistik** | Total pendaftar, per jurusan, per status, per periode, per tanggal. Grafik batang/garis (Chart.js, gratis). |
| **Daftar Pendaftar** | Tabel dengan pagination, sortable columns. |
| **Pencarian** | Berdasarkan nama, NIK, nomor pendaftaran, asal sekolah, nomor HP. |
| **Filter** | Jurusan, status verifikasi, periode, tanggal pendaftaran, kelengkapan dokumen. |
| **Detail Pendaftar** | Lihat seluruh data, preview dokumen (KK/Akta/Rapor) via signed URL Supabase. |
| **Verifikasi** | Tombol "Setujui" / "Tolak" / "Perlu Revisi" + catatan. Status berubah otomatis. |
| **Manajemen Jurusan** | CRUD jurusan, edit bobot rekomendasi, deskripsi, dan kriteria. |
| **Manajemen FAQ** | CRUD FAQ + keywords chatbot. |
| **Ekspor** | CSV (seluruh data / hasil filter). PDF laporan (seluruh data / hasil filter). |
| **Manajemen Admin** *(opsional)* | Tambah/hapus admin lain. |

### 9.2. Format Ekspor

**CSV Columns:**
```
Nomor Pendaftaran, NISN, NIK, Nama Lengkap, Jenis Kelamin, Tempat Lahir, Tanggal Lahir,
Agama, Alamat, Asal Sekolah, Nomor HP, Nama Ayah, Pekerjaan Ayah, Nama Ibu, Pekerjaan Ibu,
Nomor HP Ortu, Jurusan Pilihan 1, Jurusan Pilihan 2, Rekomendasi Jurusan, Status Verifikasi,
Tanggal Pendaftaran, Periode, Kelengkapan Dokumen (KK/Akta/Rapor)
```

**PDF Laporan:** Layout A4 landscape, header berisi logo sekolah + nama sekolah + judul laporan + periode. Tabel data pendaftar (kolom disesuaikan dengan filter). Footer dengan tanda tangan panitia *(opsional)*.

> **Perlu Validasi dengan Pihak Sekolah:** format header PDF, logo, dan template surat resmi.

---

## 10. Keamanan & Privasi

| Aspek | Implementasi |
|---|---|
| **Auth** | Laravel Sanctum (SPA) atau session-based auth. Password di-hash bcrypt. |
| **CSRF** | Middleware bawaan Laravel (VerifyCsrfToken). |
| **XSS** | Blade auto-escaping `{{ }}` — tidak menggunakan `{!! !!}` untuk data user. |
| **SQL Injection** | Eloquent ORM / Query Builder (parameterized). |
| **File Upload** | Validasi ekstensi (jpg, jpeg, png, pdf), max size (KK/Akta: 5 MB; Rapor: 5 MB), MIME type check via `mime_content_type()`. |
| **Supabase Storage** | Semua bucket private (`kk`, `akta`, `rapor`, `bukti_pendaftaran`). Akses via signed URL yang expire (misal 5 menit) hanya untuk admin terverifikasi. |
| **OCR Client-Side** | Dokumen diproses di browser — tidak ada dokumen mentah yang dikirim ke server untuk OCR. Hanya hasil OCR (teks) yang dikirim via HTTPS. |
| **Sensitive Data** | NIK dan nomor HP di-mask di tampilan admin (misal: `3671********1234`). Akses penuh hanya untuk admin level tertinggi. |
| **Rate Limiting** | Laravel `throttle:10,1` pada endpoint submit form dan OCR retina. |
| **Audit Log** *(opsional)* | Catat aktivitas admin (verifikasi, ekspor, hapus) ke tabel `audit_logs`. |

---

## 11. Struktur Database (Supabase PostgreSQL)

```
┌────────────────────────────────────────────────────────────┐
│ Table: pendaftar                                           │
├────────────────────────────────────────────────────────────┤
│ id (uuid, PK)                                             │
│ nomor_pendaftaran (varchar, unique)                       │
│ periode (varchar, e.g. "2026/2027")                       │
│ nisn (varchar, nullable)                                  │
│ nik (varchar 16)                                          │
│ nama_lengkap (varchar)                                    │
│ jenis_kelamin (enum: L/P)                                 │
│ tempat_lahir (varchar)                                    │
│ tanggal_lahir (date)                                      │
│ agama (varchar)                                           │
│ alamat (text)                                             │
│ asal_sekolah (varchar)                                    │
│ no_hp (varchar)                                           │
│ nama_ayah (varchar)                                       │
│ pekerjaan_ayah (varchar)                                  │
│ nama_ibu (varchar)                                        │
│ pekerjaan_ibu (varchar)                                   │
│ no_hp_ortu (varchar)                                      │
│ alamat_ortu (text, nullable)                              │
│ jurusan_1 (uuid → jurusan.id)                             │
│ jurusan_2 (uuid → jurusan.id, nullable)                   │
│ rekomendasi_jurusan (uuid → jurusan.id, nullable)         │
│ rekomendasi_skor (jsonb, nullable)                        │
│ status_verifikasi (enum: pending/approved/rejected/revision)│
│ catatan_verifikasi (text, nullable)                       │
│ kk_path (varchar, nullable)                               │
│ akta_path (varchar, nullable)                             │
│ rapor_path (varchar, nullable)                            │
│ created_at (timestamptz)                                  │
│ updated_at (timestamptz)                                  │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ Table: jurusan                                             │
├────────────────────────────────────────────────────────────┤
│ id (uuid, PK)                                             │
│ kode (varchar, unique, e.g. "AKL", "MPLB", "FKK", "TKR")  │
│ nama (varchar)                                            │
│ deskripsi (text)                                          │
│ prospek_karir (text)                                      │
│ kriteria (jsonb) → {"bobot_mapel": {"matematika": 0.5, ...},│
│                      "minat_terkait": ["angka", ...]}     │
│ created_at, updated_at                                    │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ Table: faqs                                                │
├────────────────────────────────────────────────────────────┤
│ id (uuid, PK)                                             │
│ pertanyaan (text)                                         │
│ jawaban (text)                                            │
│ keywords (jsonb/text[])                                   │
│ kategori (varchar)                                        │
│ is_active (boolean, default true)                         │
│ created_at, updated_at                                    │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ Table: users (admin)                                       │
├────────────────────────────────────────────────────────────┤
│ id (uuid, PK)                                             │
│ name, email, password, role (enum: admin/superadmin)      │
│ created_at, updated_at                                    │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ Table: audit_logs (opsional)                              │
├────────────────────────────────────────────────────────────┤
│ id, user_id, action, entity_type, entity_id,              │
│ old_data (jsonb), new_data (jsonb), ip_address,           │
│ created_at                                                │
└────────────────────────────────────────────────────────────┘
```

---

## 12. Responsive & Mobile-Friendly

- Menggunakan **Bootstrap 5 grid system** atau **Tailwind CSS responsive utilities**.
- Breakpoint target:
  - Mobile (≤ 576px): form stacked vertikal, tombol full-width, sticky "Lanjut" di bawah.
  - Tablet (577–991px): 2 kolom untuk field pendek (NIK, jenis kelamin).
  - Desktop (≥ 992px): multi-column layout, preview dokumen di samping form.
- File upload menerima input kamera (`accept="image/*"` + `capture="environment"` untuk scan langsung via HP).
- OCR indikator loading yang jelas di mobile.
- Chatbot sebagai floating widget di kanan bawah, dapat di-collapse.

---

## 13. Batasan & Risiko

| Risiko | Mitigasi |
|---|---|
| Kualitas OCR pada foto KK yang buram | Panduan upload: pastikan pencahayaan cukup, foto sejajar, resolusi minimal 1024px. Fallback manual selalu tersedia. |
| Tesseract.js size besar (~11 MB per bahasa) | Lazy loading: model bahasa hanya diunduh saat Step 2 diakses. Cache browser. |
| Koneksi internet tidak stabil di daerah pedesaan | Form disimpan sementara di `localStorage` setiap step (autosave). User dapat melanjutkan dari step terakhir. |
| Supabase free tier limit | File dokumen dikompresi client-side (canvas resize max 1500px) sebelum upload. Target: < 500 KB per dokumen. |
| Waktu pengembangan 1 bulan | Scope dipersempit: tanpa integrasi payment, tanpa Dapodik, tanpa notifikasi SMS/WA. |
| Perubahan kebijakan PPDB dari Dinas | Form dibuat fleksibel: field tambahan dapat ditambah via config. |

---

## 14. Milestone & Estimasi (1 Bulan)

| Minggu | Deliverable |
|---|---|
| **Minggu 1** | Setup project Laravel + Supabase. Auth admin. CRUD jurusan. Struktur database. Landing page PPDB. Step 1 (persetujuan). Integration Supabase Storage (upload file). |
| **Minggu 2** | Step 2 (upload KK/Akta + Tesseract.js OCR auto-fill). Step 3 (rapor opsional). Step 5–6 (data siswa & ortu). LocalStorage autosave. |
| **Minggu 3** | Step 4 (pemilihan jurusan + rekomendasi engine). Step 7 (review & submit + nomor pendaftaran). Chatbot FAQ. |
| **Minggu 4** | Dashboard admin lengkap (statistik, filter, verifikasi, detail, preview dokumen). Ekspor CSV & PDF. Testing end-to-end. Deployment. UAT dengan pihak sekolah. |

---

## 15. Kebutuhan Validasi dengan Pihak Sekolah

Berikut daftar yang **wajib dikonfirmasi** ke SMK Ma'arif NU 1 Wangon:

| # | Item | Keterangan |
|---|---|---|
| 1 | Daftar jurusan final | Apakah masih AKL, MPLB, FKK, TKR? Apakah ada jurusan baru? [1][4] |
| 2 | Format nomor pendaftaran | `PPDB-2026-XXXX` atau format lain? |
| 3 | Bobot rekomendasi per jurusan | Tabel bobot di Section 7.2 perlu direview guru BK/kepala jurusan. |
| 4 | Kriteria penerimaan (passing grade) | Jika ada, untuk laporan PDF. |
| 5 | Konten FAQ chatbot | Daftar pertanyaan & jawaban resmi. |
| 6 | Format header PDF laporan | Logo sekolah, kop surat, tanda tangan panitia. |
| 7 | Kebijakan penyimpanan data | Retensi data berapa lama setelah PPDB selesai? |
| 8 | Biaya pendaftaran *(jika ada)* | Apakah perlu dicantumkan di form atau informasi? |
| 9 | Kolom NISN wajib atau opsional | Terkait integrasi Dapodik. |
| 10 | Bahasa tampilan | Indonesia atau bilingual (Jawa Banyumasan)? |

---

## 16. Definisi Sukses (KPI)

- 100% pendaftaran dapat diselesaikan dalam ≤ 10 menit (median).
- Akurasi auto-fill OCR ≥ 70% field terisi benar (dari sampling 30 dokumen).
- 0% dokumen bocor ke publik (semua di private bucket, verified via penetration testing sederhana).
- Admin dapat memfilter 1000 pendaftar dalam ≤ 2 detik.
- Ekspor CSV/PDF berhasil untuk seluruh data tanpa timeout.
- Chatbot mampu menjawab ≥ 60% pertanyaan umum tanpa intervensi admin.

---

## 17. Lampiran

### 17.1. Mockup Alur (Deskripsi)

```
Step 1: [Logo Sekolah]
        "PPDB SMK Ma'arif NU 1 Wangon — 2026/2027"
        [Daftar Persyaratan]
        [x] Saya telah membaca dan menyetujui persyaratan
        [Mulai Pendaftaran →]

Step 2: "Upload Dokumen"
        [Upload KK] ▸ [Preview thumbnail] [OCR Status: Processing... 68%]
        [Upload Akta Kelahiran] ▸ [Preview thumbnail] [OCR Status: Selesai ✓]

Step 3: "Upload Rapor (Opsional)"
        [Info box: "Belum punya rapor? Lewati langkah ini."]
        [Upload Rapor] [Lewati →]

Step 4: "Pilih Jurusan"
        ┌─ AKL ──────┐  ┌─ MPLB ─────┐  ┌─ FKK ──────┐  ┌─ TKR ──────┐
        │ Rekomendasi│  │ Cocok      │  │ Sangat     │  │ Cocok      │
        [Pilih 1] [Pilih 2]

Step 5-6: "Data Siswa & Orang Tua" (auto-filled, editable)
        [NIK: 3671xxxxxxxx3456] [Nama: ...] [Tanggal Lahir: ...]

Step 7: "Review"
        [Ringkasan data]
        [x] Saya menyatakan data benar
        [Submit Pendaftaran]
        →
        "🎉 Pendaftaran Berhasil! Nomor Anda: PPDB-2026-0042"
        [Cetak Bukti (PDF)]
```

---

## 18. Penutup

Dokumen PRD ini merupakan cetak biru awal dan **terbuka untuk direvisi** setelah diskusi dengan pihak SMK Ma'arif NU 1 Wangon. Scope yang dirancang realistis untuk diselesaikan dalam 1 bulan oleh 1–2 developer berpengalaman Laravel, dengan mengutamakan keandalan sistem daripada fitur yang berlebihan. Seluruh teknologi yang digunakan bersifat gratis dan open-source, sehingga tidak ada biaya lisensi software — hanya biaya hosting (jika sekolah belum memiliki server sendiri) dan biaya cadangan Supabase jika melebihi tier gratis.

---

*Dokumen disusun: [Tanggal] — PRD v1.0 — Status: Draft awaiting school validation.*
