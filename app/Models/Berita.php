<?php

namespace App\Models;

use App\Support\GoogleDrive;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    /**
     * Table associated with the model.
     */
    protected $table = 'beritas';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'judul',        // title of the news article
        'isi',          // body/content (may be HTML)
        'slug',         // URL-friendly identifier
        'gambar',       // thumbnail image (relative path di disk `public`)
        'gambar_drive_id', // ID salinan gambar di Google Drive (opsional)
        'is_active',   // flag to publish/unpublish
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active news items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Public URL of the thumbnail, null bila belum diunggah.
     */
    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar ? asset('storage/' . ltrim($this->gambar, '/')) : null;
    }

    /**
     * Whether the thumbnail file exists on disk.
     */
    public function getHasGambarAttribute(): bool
    {
        return (bool) ($this->gambar && file_exists(public_path('storage/' . ltrim($this->gambar, '/'))));
    }

    /**
     * Tautan lihat salinan gambar di Google Drive, null bila tidak ada.
     */
    public function getGambarDriveViewAttribute(): ?string
    {
        return GoogleDrive::viewUrl($this->gambar_drive_id);
    }

    /**
     * Tautan unduh salinan gambar di Google Drive, null bila tidak ada.
     */
    public function getGambarDriveDownloadAttribute(): ?string
    {
        return GoogleDrive::downloadUrl($this->gambar_drive_id);
    }
}
