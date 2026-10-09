<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Testimoni alumni yang dikelola admin dan tampil di landing page.
     */
    public function up(): void
    {
        Schema::create('testimonis', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('angkatan', 20)->nullable();
            $table->string('jurusan_kode', 10)->nullable();
            $table->string('pekerjaan', 150)->nullable();
            $table->text('testimoni');
            $table->string('foto')->nullable();
            $table->string('foto_drive_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonis');
    }
};
