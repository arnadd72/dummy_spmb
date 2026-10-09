<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    protected $table = 'jurusan';

    protected $fillable = [
        'kode', 'nama', 'deskripsi', 'prospek_karir', 'kuota', 'kriteria', 'is_active',
    ];

    protected $casts = [
        'kuota' => 'integer',
        'kriteria' => 'array',
        'is_active' => 'boolean',
    ];

    public function pendaftarPilihan1()
    {
        return $this->hasMany(Pendaftar::class, 'jurusan_1_id');
    }

    public function pendaftarPilihan2()
    {
        return $this->hasMany(Pendaftar::class, 'jurusan_2_id');
    }

    /**
     * Jumlah pendaftar yang menjadikan jurusan ini sebagai pilihan pertama.
     */
    public function getPendaftarCountAttribute(): int
    {
        return $this->pendaftarPilihan1()->count();
    }

    /**
     * Sisa kursi yang masih tersedia.
     */
    public function getSisaKuotaAttribute(): int
    {
        return max(0, (int) $this->kuota - $this->pendaftar_count);
    }

    /**
     * Persentase keterisian kuota (0-100).
     */
    public function getPersentaseTerisiAttribute(): float
    {
        if ((int) $this->kuota <= 0) {
            return 0.0;
        }

        return round(min(100, ($this->pendaftar_count / (int) $this->kuota) * 100), 1);
    }

    /**
     * Hitung skor rekomendasi berdasarkan nilai rapor dan minat
     */
    public function hitungSkor(array $nilaiRapor = [], string $minat = ''): float
    {
        if (!$this->kriteria) return 0;

        $kriteria = $this->kriteria;
        $bobotMapel = $kriteria['bobot_mapel'] ?? [];
        $minatTerkait = $kriteria['minat_terkait'] ?? [];

        // Komponen 1: Nilai rapor (60%)
        $skorNilai = 0;
        $totalBobot = 0;
        foreach ($bobotMapel as $mapel => $bobot) {
            $nilaiKey = strtolower(str_replace([' ', '/'], '_', $mapel));
            $nilai = $nilaiRapor[$nilaiKey] ?? $nilaiRapor[$mapel] ?? 0;
            $skorNilai += $bobot * $nilai;
            $totalBobot += $bobot;
        }
        if ($totalBobot > 0) {
            $skorNilai = ($skorNilai / ($totalBobot * 100)) * 100 * 0.6;
        }

        // Komponen 2: Kecocokan minat (30%)
        $skorMinat = 0;
        if (!empty($minat) && !empty($minatTerkait)) {
            $minatLower = strtolower($minat);
            foreach ($minatTerkait as $m) {
                if (str_contains(strtolower($m), $minatLower) || str_contains($minatLower, strtolower($m))) {
                    $skorMinat = 0.3 * 100;
                    break;
                }
            }
        }

        return round($skorNilai + $skorMinat, 2);
    }

    public function getLabelRekomendasi(float $skor): string
    {
        if ($skor >= 60) return 'Sangat Cocok';
        if ($skor >= 35) return 'Cocok';
        return 'Kurang Cocok';
    }

    public function getColorRekomendasi(float $skor): string
    {
        if ($skor >= 60) return 'green';
        if ($skor >= 35) return 'yellow';
        return 'gray';
    }
}
