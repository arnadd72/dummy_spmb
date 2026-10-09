<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan situs sederhana berbasis key/value.
 *
 * Dipakai untuk menyimpan aset global landing page yang tidak terikat pada
 * satu record konten, misalnya gambar hero utama portal PPDB.
 */
class Pengaturan extends Model
{
    /**
     * Table associated with the model.
     */
    protected $table = 'pengaturans';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'key',    // identifier unik, contoh: hero_image
        'value',  // isi: path file relatif (disk public) atau teks bebas
    ];

    /**
     * Ambil nilai pengaturan berdasarkan key.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $row = static::query()->where('key', $key)->first();

        return $row?->value ?? $default;
    }

    /**
     * Simpan (buat/perbarui) nilai pengaturan.
     */
    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Hapus baris pengaturan beserta nilainya.
     */
    public static function forget(string $key): void
    {
        static::query()->where('key', $key)->delete();
    }

    /**
     * Path relatif gambar hero (disk public).
     */
    public static function heroPath(): ?string
    {
        return static::get('hero_image');
    }

    /**
     * URL publik gambar hero, null bila belum diatur.
     */
    public static function heroUrl(): ?string
    {
        $path = static::heroPath();

        return $path ? asset('storage/' . ltrim($path, '/')) : null;
    }

    /**
     * Apakah file gambar hero benar-benar ada di disk.
     */
    public static function heroExists(): bool
    {
        $path = static::heroPath();

        return (bool) ($path && file_exists(public_path('storage/' . ltrim($path, '/'))));
    }
}
