<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom konten yang dibutuhkan oleh modul Content Management
 * (Berita, Event, Galeri) yang sebelumnya hanya memiliki id + timestamps.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beritas', function (Blueprint $table) {
            if (!Schema::hasColumn('beritas', 'judul')) {
                $table->string('judul')->nullable()->after('id');
            }
            if (!Schema::hasColumn('beritas', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('judul');
            }
            if (!Schema::hasColumn('beritas', 'isi')) {
                $table->text('isi')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('beritas', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('isi');
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'judul')) {
                $table->string('judul')->nullable()->after('id');
            }
            if (!Schema::hasColumn('events', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('judul');
            }
            if (!Schema::hasColumn('events', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('events', 'tanggal')) {
                $table->date('tanggal')->nullable()->after('deskripsi');
            }
            if (!Schema::hasColumn('events', 'lokasi')) {
                $table->string('lokasi')->nullable()->after('tanggal');
            }
            if (!Schema::hasColumn('events', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('lokasi');
            }
        });

        Schema::table('galeris', function (Blueprint $table) {
            if (!Schema::hasColumn('galeris', 'judul')) {
                $table->string('judul')->nullable()->after('id');
            }
            if (!Schema::hasColumn('galeris', 'path')) {
                $table->string('path')->nullable()->after('judul');
            }
            if (!Schema::hasColumn('galeris', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('path');
            }
            if (!Schema::hasColumn('galeris', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('deskripsi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('beritas', function (Blueprint $table) {
            $table->dropColumn(['judul', 'slug', 'isi', 'is_active']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['judul', 'slug', 'deskripsi', 'tanggal', 'lokasi', 'is_active']);
        });

        Schema::table('galeris', function (Blueprint $table) {
            $table->dropColumn(['judul', 'path', 'deskripsi', 'is_active']);
        });
    }
};
