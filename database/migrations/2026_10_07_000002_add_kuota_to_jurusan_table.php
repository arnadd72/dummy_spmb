<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurusan', function (Blueprint $table) {
            // Kapasitas daya tampung (jumlah kursi) per program keahlian.
            $table->unsignedInteger('kuota')->default(72)->after('prospek_karir');
        });
    }

    public function down(): void
    {
        Schema::table('jurusan', function (Blueprint $table) {
            $table->dropColumn('kuota');
        });
    }
};
