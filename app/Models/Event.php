<?php

namespace App\Models;

use App\Support\GoogleDrive;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /**
     * Table associated with the model.
     */
    protected $table = 'events';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'judul',      // title of the event
        'slug',       // URL-friendly identifier
        'deskripsi',  // description (may be HTML)
        'tanggal',    // event date
        'lokasi',     // event location
        'gambar',     // poster/flyer image (relative path di disk `public`)
        'gambar_drive_id', // ID salinan poster di Google Drive (opsional)
        'is_active',  // flag to publish/unpublish
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'tanggal'   => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active events.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('tanggal', '>=', now()->toDateString());
    }

    /**
     * Public URL of the event image, null bila belum diunggah.
     */
    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar ? asset('storage/' . ltrim($this->gambar, '/')) : null;
    }

    /**
     * Whether the event image exists on disk.
     */
    public function getHasGambarAttribute(): bool
    {
        return (bool) ($this->gambar && file_exists(public_path('storage/' . ltrim($this->gambar, '/'))));
    }

    /**
     * Tautan lihat salinan poster di Google Drive, null bila tidak ada.
     */
    public function getGambarDriveViewAttribute(): ?string
    {
        return GoogleDrive::viewUrl($this->gambar_drive_id);
    }

    /**
     * Tautan unduh salinan poster di Google Drive, null bila tidak ada.
     */
    public function getGambarDriveDownloadAttribute(): ?string
    {
        return GoogleDrive::downloadUrl($this->gambar_drive_id);
    }
}
