<?php

namespace App\Models;

use App\Support\GoogleDrive;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Galeri extends Model
{
    /**
     * Table associated with the model.
     */
    protected $table = 'galeris';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'judul',      // title / caption of the image
        'path',       // relative storage path of the image
        'drive_id',   // ID salinan gambar di Google Drive (opsional)
        'deskripsi',  // optional description
        'is_active',  // flag to publish/unpublish
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active gallery items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Public URL of the stored image.
     */
    public function getUrlAttribute(): ?string
    {
        return $this->path ? asset('storage/' . ltrim($this->path, '/')) : null;
    }

    /**
     * Whether the underlying image file exists on disk.
     */
    public function getHasImageAttribute(): bool
    {
        return $this->path && file_exists(public_path('storage/' . ltrim($this->path, '/')));
    }

    /**
     * Tautan lihat salinan gambar di Google Drive, null bila tidak ada.
     */
    public function getDriveViewAttribute(): ?string
    {
        return GoogleDrive::viewUrl($this->drive_id);
    }

    /**
     * Tautan unduh salinan gambar di Google Drive, null bila tidak ada.
     */
    public function getDriveDownloadAttribute(): ?string
    {
        return GoogleDrive::downloadUrl($this->drive_id);
    }
}
