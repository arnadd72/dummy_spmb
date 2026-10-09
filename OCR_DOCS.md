# Self-Hosted OCR Service — Dokumentasi

## File yang Dibuat / Diubah

| File | Status | Keterangan |
|---|---|---|
| `paddle_ocr_server.py` | **BARU (v2)** | Python FastAPI OCR Service utama |
| `public/js/ocr-engine.js` | **TULIS ULANG (v3)** | Frontend engine, structured API call |
| `app/Http/Controllers/OcrController.php` | **DIPERBARUI** | Proxy 3 endpoint + health check |
| `routes/web.php` | **DIPERBARUI** | Tambah route `/ocr/akta`, `/ocr/kk`, `/ocr/health` |
| `resources/views/ppdb/step2.blade.php` | **DIPERBARUI** | Hapus Tesseract CDN, update info alert |

---

## Arsitektur

```
Browser (upload gambar)
  ↓ preprocessImage() — upscale + JPEG 0.93
Laravel /daftar/ocr/akta  atau  /daftar/ocr/kk
  ↓ OcrController::processAkta() / processKk()
Python OCR Service (port 8001)
  ↓ preprocess() — CLAHE + adaptive threshold + denoise
  ↓ EasyOCR → Word objects dengan bounding box
  ↓ group_into_lines() — layout engine
  ↓ parse_akta() / parse_kk() — parser layout-aware
  ↓ Confidence system (HIGH/MEDIUM/LOW/NOT_FOUND)
JSON Response
  ↓ applyAktaData() / applyKKData()
Form autofill (HIGH/MEDIUM → isi, LOW → kosong)
```

---

## Cara Menjalankan OCR Service

```powershell
# Di terminal project (venv harus aktif)
cd "D:\COMPUTING PROJECT\web_ppdb"
.\venv\Scripts\activate
python paddle_ocr_server.py
```

Service akan berjalan di `http://127.0.0.1:8001`

---

## Endpoint API

| Method | URL | Fungsi |
|---|---|---|
| `GET`  | `/daftar/ocr/health` | Cek apakah service jalan |
| `POST` | `/daftar/ocr/akta`   | OCR Akta Kelahiran (structured) |
| `POST` | `/daftar/ocr/kk`     | OCR Kartu Keluarga (structured) |
| `POST` | `/daftar/ocr`        | OCR raw (fallback, text+words) |

### Contoh Response `/ocr/akta`
```json
{
  "success": true,
  "document_type": "akta",
  "raw_text": "KUTIPAN AKTA KELAHIRAN...",
  "fields": {
    "nama": "Rizki Sulaiman Simanjuntak",
    "tempat_lahir": "Simalungun",
    "tanggal_lahir": "1998-10-15",
    "jenis_kelamin": "L",
    "nama_ayah": "Sumihar Silangit",
    "nama_ibu": "Dongsi Purung"
  },
  "confidence": {
    "nama": "HIGH",
    "tempat_lahir": "HIGH",
    "tanggal_lahir": "MEDIUM",
    "jenis_kelamin": "HIGH",
    "nama_ayah": "HIGH",
    "nama_ibu": "HIGH"
  }
}
```

### Contoh Response `/ocr/kk`
```json
{
  "success": true,
  "document_type": "kk",
  "fields": {
    "no_kk": "3302xxxxxxxx0000",
    "alamat": "RT 02/RW 01, Jl. Merdeka, Desa Sukamaju, Kec. Banyumas, Kab. Banyumas",
    "nama_ayah": "Sumihar Silangit",
    "nama_ibu": "Dongsi Purung"
  },
  "confidence": { ... },
  "members": [
    { "nik": "3302xxxxxxxx0001", "name": "Sumihar Silangit", "role": "Kepala Keluarga", "nik_conf": 0.97, "name_conf": 0.89 },
    { "nik": "3302xxxxxxxx0002", "name": "Rizki Sulaiman",   "role": "Anak",            "nik_conf": 0.95, "name_conf": 0.82 }
  ],
  "matched_nik": { "value": "", "confidence": "NOT_FOUND" }
}
```

---

## Cara Testing

```powershell
# 1. Test health
Invoke-WebRequest -Uri "http://localhost/daftar/ocr/health" -UseBasicParsing

# 2. Test OCR via curl (Python service langsung)
curl -X POST http://127.0.0.1:8001/health

# 3. Di browser: buka /daftar/step-2, upload gambar Akta/KK
# Buka DevTools Console → lihat log [OCR] untuk detail confidence
```

---

## Aturan Confidence

| Level | Aksi |
|---|---|
| `HIGH` | Autofill otomatis, badge hijau |
| `MEDIUM` | Autofill + badge kuning "Perlu cek" |
| `LOW` | **Kosong** — user isi manual |
| `NOT_FOUND` | **Kosong** — user isi manual |

---

## Yang TIDAK Dilakukan Sistem (sesuai permintaan)

- ❌ Tidak mengambil NIK pertama yang ditemukan sebagai NIK siswa
- ❌ Tidak mengambil tanggal pertama sebagai tanggal lahir
- ❌ Tidak mengisi nama dari `targetStudentName` (hanya dipakai untuk highlight/validasi)
- ❌ Tidak membuat tebakan data apapun
- ❌ Tidak mengirim data ke OCR cloud/berbayar
- ✅ NIK siswa vs Nomor KK dibedakan berdasarkan label + posisi header
- ✅ Tanggal lahir dipilih berdasarkan skor konteks ("lahir" keyword, tahun wajar, dll)
- ✅ Data KK diambil per-baris (baris NIK = baris nama yang sama)
