<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan ID berkas Google Drive untuk tiap dokumen yang diunggah.
 *
 * Kolom `*_drive_id` menampung ID berkas di Drive sehingga panel admin bisa
 * membuka / mengunduhnya langsung dari Drive, sementara `*_path` tetap menjadi
 * sumber utama (fail-safe bila Drive mati).
 *
 * `notifikasi_email_at` dipakai untuk mencegah email kode pendaftaran terkirim
 * dua kali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            foreach (['kk', 'akta', 'rapor'] as $dok) {
                if (!Schema::hasColumn('pendaftar', "{$dok}_drive_id")) {
                    $table->string("{$dok}_drive_id")->nullable();
                }
            }

            if (!Schema::hasColumn('pendaftar', 'notifikasi_email_at')) {
                $table->timestamp('notifikasi_email_at')->nullable();
            }

            // Alamat email calon siswa — dipakai untuk mengirim kode pendaftaran.
            if (!Schema::hasColumn('pendaftar', 'email')) {
                $table->string('email')->nullable()->after('no_hp');
            }

            if (!Schema::hasColumn('pendaftar', 'drive_folder_id')) {
                $table->string('drive_folder_id')->nullable();
            }
        });

        Schema::table('beritas', function (Blueprint $table) {
            if (!Schema::hasColumn('beritas', 'gambar_drive_id')) {
                $table->string('gambar_drive_id')->nullable();
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'gambar_drive_id')) {
                $table->string('gambar_drive_id')->nullable();
            }
        });

        Schema::table('galeris', function (Blueprint $table) {
            if (!Schema::hasColumn('galeris', 'drive_id')) {
                $table->string('drive_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropColumn([
                'kk_drive_id', 'akta_drive_id', 'rapor_drive_id',
                'notifikasi_email_at', 'drive_folder_id', 'email',
            ]);
        });

        Schema::table('beritas', function (Blueprint $table) {
            $table->dropColumn('gambar_drive_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('gambar_drive_id');
        });

        Schema::table('galeris', function (Blueprint $table) {
            $table->dropColumn('drive_id');
        });
    }
};
