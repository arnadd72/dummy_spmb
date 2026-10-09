<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan nomor WhatsApp calon siswa sebagai kolom tersendiri.
 *
 * Sebelumnya kontak siswa hanya punya satu kolom `no_hp` yang labelnya
 * mencampur "HP / WhatsApp". Kolom `no_wa` memisahkannya agar form
 * pendaftaran bisa meminta nomor WhatsApp secara eksplisit dan panitia
 * dapat melihatnya tersendiri di halaman detail pendaftar.
 *
 * Kolom bersifat nullable karena data lama serta pendaftar luring mungkin
 * belum memilikinya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            if (!Schema::hasColumn('pendaftar', 'no_wa')) {
                $table->string('no_wa', 20)->nullable()->after('no_hp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropColumn('no_wa');
        });
    }
};
