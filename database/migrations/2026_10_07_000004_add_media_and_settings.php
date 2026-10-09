<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Media untuk konten landing page (thumbnail berita & event) serta tabel
 * pengaturan situs yang menyimpan aset global seperti gambar hero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beritas', function (Blueprint $table) {
            if (!Schema::hasColumn('beritas', 'gambar')) {
                $table->string('gambar')->nullable()->after('isi');
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'gambar')) {
                $table->string('gambar')->nullable()->after('lokasi');
            }
        });

        if (!Schema::hasTable('pengaturans')) {
            Schema::create('pengaturans', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();   // contoh: hero_image
                $table->text('value')->nullable(); // path file / teks
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('beritas', function (Blueprint $table) {
            $table->dropColumn('gambar');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('gambar');
        });

        Schema::dropIfExists('pengaturans');
    }
};
