<?php

namespace App\Models;

use App\Support\GoogleDrive;
use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    protected $table = 'testimonis';

    protected $fillable = [
        'nama',
        'angkatan',
        'jurusan_kode',
        'pekerjaan',
        'testimoni',
        'foto',
        'foto_drive_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/' . ltrim($this->foto, '/')) : null;
    }

    public function getDriveViewAttribute(): ?string
    {
        return GoogleDrive::viewUrl($this->foto_drive_id);
    }
}
