<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pendaftaran', 20)->unique();
            $table->string('periode', 10)->default('2026/2027');
            $table->string('nisn', 20)->nullable();
            $table->string('nik', 16);
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('agama', 20);
            $table->text('alamat');
            $table->string('asal_sekolah');
            $table->string('no_hp', 20);
            $table->string('nama_ayah');
            $table->string('pekerjaan_ayah');
            $table->string('nama_ibu');
            $table->string('pekerjaan_ibu');
            $table->string('no_hp_ortu', 20);
            $table->text('alamat_ortu')->nullable();
            $table->foreignId('jurusan_1_id')->nullable()->constrained('jurusan')->nullOnDelete();
            $table->foreignId('jurusan_2_id')->nullable()->constrained('jurusan')->nullOnDelete();
            $table->foreignId('rekomendasi_jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
            $table->json('rekomendasi_skor')->nullable();
            $table->enum('status_verifikasi', ['pending', 'approved', 'rejected', 'revision'])->default('pending');
            $table->text('catatan_verifikasi')->nullable();
            $table->string('kk_path')->nullable();
            $table->string('akta_path')->nullable();
            $table->string('rapor_path')->nullable();
            $table->json('nilai_rapor')->nullable();
            $table->string('minat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftar');
    }
};
