<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konfigurasi isian formulir publik yang bisa diubah admin:
     * tampil/sembunyi, wajib/opsional, label, teks bantuan, opsi dropdown,
     * placeholder, nilai default, urutan, plus field kustom baru.
     */
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('kunci', 60)->unique();
            $table->string('langkah', 20)->default('step5');
            $table->string('label', 150);
            $table->string('teks_bantuan', 255)->nullable();
            $table->string('tipe', 20)->default('text');
            $table->json('opsi')->nullable();
            $table->string('placeholder', 150)->nullable();
            $table->string('nilai_default', 150)->nullable();
            $table->boolean('tampil')->default(true);
            $table->boolean('wajib')->default(true);
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_kustom')->default(false);
            $table->timestamps();
        });

        Schema::table('pendaftar', function (Blueprint $table) {
            $table->json('data_kustom')->nullable()->after('referensi_pendaftar');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropColumn('data_kustom');
        });

        Schema::dropIfExists('form_fields');
    }
};
