<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            // Asal pendaftaran: daring (formulir publik) atau luring (input manual panitia).
            $table->string('sumber', 20)->default('daring')->after('periode');
            // Alasan penolakan berkas yang ditulis oleh AdminController@rejectDocument.
            $table->string('catatan_penolakan')->nullable()->after('status_verifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropColumn(['sumber', 'catatan_penolakan']);
        });
    }
};
