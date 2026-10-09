<?php

use App\Http\Controllers\PendaftaranController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', [PendaftaranController::class, 'index'])->name('ppdb.index');

// Detail Jurusan — klik kartu jurusan di landing langsung ke halaman lengkap
Route::get('/jurusan/{kode}', [PendaftaranController::class, 'jurusanShow'])->name('ppdb.jurusan.show');

// Login Admin (tanpa database — kredensial di Pengaturan, bisa diganti di panel admin)
Route::get('/admin-login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin-login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin-logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('admin.logout');

// Cek Status
Route::get('/cek-status', [PendaftaranController::class, 'cekStatus'])->name('ppdb.cekStatus');
Route::post('/cek-status', [PendaftaranController::class, 'cekStatusPost'])->name('ppdb.cekStatusPost');

// Chatbot API
Route::post('/chatbot', [PendaftaranController::class, 'chatbot'])->name('ppdb.chatbot')->middleware('throttle:20,1');

// Multi-Step Form
Route::prefix('daftar')->name('ppdb.')->group(function () {
    Route::get('/step-1', [PendaftaranController::class, 'step1'])->name('step1');
    Route::post('/step-1', [PendaftaranController::class, 'step1Post'])->name('step1.post');

    Route::get('/step-2', [PendaftaranController::class, 'step2'])->name('step2');
    Route::post('/step-2', [PendaftaranController::class, 'step2Post'])->name('step2.post');

    Route::get('/step-3', [PendaftaranController::class, 'step3'])->name('step3');
    Route::post('/step-3', [PendaftaranController::class, 'step3Post'])->name('step3.post');
    Route::get('/step-3/lewati', [PendaftaranController::class, 'step3Lewati'])->name('step3.lewati');

    Route::get('/step-4', [PendaftaranController::class, 'step4'])->name('step4');
    Route::post('/step-4', [PendaftaranController::class, 'step4Post'])->name('step4.post');

    Route::get('/step-5', [PendaftaranController::class, 'step5'])->name('step5');
    Route::post('/step-5', [PendaftaranController::class, 'step5Post'])->name('step5.post');

    Route::get('/step-6', [PendaftaranController::class, 'step6'])->name('step6');
    Route::post('/step-6', [PendaftaranController::class, 'step6Post'])->name('step6.post');

    Route::get('/step-7', [PendaftaranController::class, 'step7'])->name('step7');
    Route::post('/step-7', [PendaftaranController::class, 'step7Post'])->name('step7.post');

    Route::get('/step-8', [PendaftaranController::class, 'step8'])->name('step8');
    Route::post('/submit', [PendaftaranController::class, 'submit'])->name('submit');

    Route::get('/sukses/{nomor}', [PendaftaranController::class, 'sukses'])->name('sukses');

    // Cetak/unduh formulir pendaftaran resmi (PDF) berdasarkan nomor pendaftaran.
    Route::get('/formulir/{nomor}', [PendaftaranController::class, 'formulir'])->name('formulir');

    // OCR Service routes
    Route::post('/ocr',        [App\Http\Controllers\OcrController::class, 'process'])->name('ocr');          // general raw OCR
    Route::post('/ocr/akta',   [App\Http\Controllers\OcrController::class, 'processAkta'])->name('ocr.akta'); // structured akta
    Route::post('/ocr/kk',     [App\Http\Controllers\OcrController::class, 'processKk'])->name('ocr.kk');     // structured kk
    Route::get('/ocr/health',  [App\Http\Controllers\OcrController::class, 'health'])->name('ocr.health');    // service health check
});

// Admin Control Panel
// Catatan: halaman admin tetap terbuka (sesuai permintaan), login hanya via tombol di footer.
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('index');
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/peserta', [\App\Http\Controllers\AdminController::class, 'participants'])->name('participants');
    Route::get('/dokumen', [\App\Http\Controllers\AdminController::class, 'documents'])->name('documents');

    // Verifikasi dokumen
    Route::post('/dokumen/{id}/approve', [\App\Http\Controllers\AdminController::class, 'approveDocument'])->name('dokumen.approve');
    Route::post('/dokumen/{id}/reject', [\App\Http\Controllers\AdminController::class, 'rejectDocument'])->name('dokumen.reject');

    // Content Management (CRUD)
    // Catatan: `berita` di-singular-kan Laravel menjadi `beritum`, sehingga nama
    // parameter rute dipatok eksplisit agar route model binding tetap bekerja.
    Route::resource('berita', \App\Http\Controllers\Admin\BeritaController::class)
        ->parameters(['berita' => 'berita']);
    Route::resource('event', \App\Http\Controllers\Admin\EventController::class);
    Route::resource('faq', \App\Http\Controllers\Admin\FaqController::class);
    Route::resource('galeri', \App\Http\Controllers\Admin\GaleriController::class);
    Route::resource('testimoni', \App\Http\Controllers\Admin\TestimoniController::class);

    // Isian Formulir — atur tampil/sembunyi, wajib/opsional, label, opsi, urutan + field kustom
    Route::resource('form-field', \App\Http\Controllers\Admin\FormFieldController::class)
        ->parameters(['form-field' => 'formField'])
        ->except(['show']);
    Route::post('/form-field/urutan', [\App\Http\Controllers\Admin\FormFieldController::class, 'simpanUrutan'])
        ->name('form-field.simpan-urutan');
    Route::post('/form-field/{formField}/toggle', [\App\Http\Controllers\Admin\FormFieldController::class, 'toggle'])
        ->name('form-field.toggle');

    // Pengaturan Situs — aset global landing page (gambar hero terpisah, tahun ajaran, kredensial admin)
    Route::get('/pengaturan', [\App\Http\Controllers\Admin\PengaturanController::class, 'index'])
        ->name('pengaturan.index');
    Route::put('/pengaturan', [\App\Http\Controllers\Admin\PengaturanController::class, 'update'])
        ->name('pengaturan.update');

    // Uji koneksi ke Google Drive (folder + izin tulis)
    Route::post('/pengaturan/uji-drive', [\App\Http\Controllers\Admin\PengaturanController::class, 'ujiDrive'])
        ->name('pengaturan.ujiDrive');

    /*
    |--------------------------------------------------------------------------
    | Aksi & Ekspor (operasional administrasi dan pelaporan)
    |--------------------------------------------------------------------------
    */

    // Ekspor XLS — unduh rekapitulasi data pendaftar
    Route::get('/ekspor/xls', [\App\Http\Controllers\Admin\PendaftarController::class, 'exportXls'])
        ->name('export.xls');

    // Kelola Kuota — atur kapasitas daya tampung
    Route::get('/kuota', [\App\Http\Controllers\Admin\PendaftarController::class, 'kuotaIndex'])
        ->name('kuota.index');
    Route::put('/kuota', [\App\Http\Controllers\Admin\PendaftarController::class, 'kuotaUpdate'])
        ->name('kuota.update');

    // Input Manual — langsung lempar ke form formulir pendaftaran (wizard daring)
    Route::get('/pendaftar/manual', [\App\Http\Controllers\Admin\PendaftarController::class, 'manualCreate'])
        ->name('manual.create');
    Route::post('/pendaftar/manual', [\App\Http\Controllers\Admin\PendaftarController::class, 'manualStore'])
        ->name('manual.store');

    // Detail & Edit Pendaftar — admin bisa ubah field teks (berkas read-only)
    Route::get('/pendaftar/{pendaftar}', [\App\Http\Controllers\Admin\PendaftarController::class, 'show'])
        ->name('pendaftar.show');
    Route::get('/pendaftar/{pendaftar}/edit', [\App\Http\Controllers\Admin\PendaftarController::class, 'edit'])
        ->name('pendaftar.edit');
    Route::put('/pendaftar/{pendaftar}', [\App\Http\Controllers\Admin\PendaftarController::class, 'update'])
        ->name('pendaftar.update');
});

