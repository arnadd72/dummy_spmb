<?php

namespace App\Support;

use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Jembatan konfigurasi isian formulir (admin) ke wizard publik.
 *
 * - Field bawaan: hormati tampil/wajib + override label, teks bantuan,
 *   placeholder, nilai default, dan label opsi (nilai/value tetap).
 * - Field kustom (kunci kustom_*): validasi & simpan ke data_kustom.
 */
class FormFieldConfig
{
    protected static ?bool $available = null;

    public static function available(): bool
    {
        if (static::$available === null) {
            try {
                static::$available = Schema::hasTable('form_fields');
            } catch (\Throwable) {
                static::$available = false;
            }
        }

        return static::$available;
    }

    /**
     * Peta konfigurasi keyed by kunci.
     *
     * @return array<string, FormField>
     */
    public static function map(): array
    {
        if (! static::available()) {
            return [];
        }

        try {
            return FormField::orderBy('urutan')->get()->keyBy('kunci')->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public static function field(?array $map, string $kunci): ?FormField
    {
        $map ??= static::map();

        return $map[$kunci] ?? null;
    }

    public static function tampil(?array $map, string $kunci, bool $default = true): bool
    {
        $f = static::field($map, $kunci);

        return $f ? (bool) $f->tampil : $default;
    }

    public static function wajib(?array $map, string $kunci, bool $defaultWajib): bool
    {
        $f = static::field($map, $kunci);

        return $f ? (bool) $f->wajib : $defaultWajib;
    }

    public static function label(?array $map, string $kunci, string $default): string
    {
        $f = static::field($map, $kunci);

        return $f && $f->label ? $f->label : $default;
    }

    /**
     * Label opsi untuk field bawaan: bila jumlah opsi admin sama dengan
     * jumlah bawaan, pakai label admin dengan value asli (kode tetap valid).
     * Bila tidak, kembalikan bawaan.
     *
     * @param  array<int,string> $values  value yang disimpan (kode)
     * @param  array<int,string> $labels  label bawaan sejajar values
     * @return array<int, array{value:string,label:string}>
     */
    public static function opsiBerlabel(?array $map, string $kunci, array $values, array $labels): array
    {
        $f = static::field($map, $kunci);
        $admin = $f?->opsi_list ?? [];

        if ($admin && count($admin) === count($values)) {
            return array_map(
                fn ($v, $l) => ['value' => $v, 'label' => $l],
                array_values($values),
                array_values($admin)
            );
        }

        return array_map(
            fn ($v, $l) => ['value' => $v, 'label' => $l],
            array_values($values),
            array_values($labels)
        );
    }

    /**
     * Opsi murni (value = label) untuk field tanpa kode: agama, dsb.
     *
     * @param  array<int,string> $default
     * @return array<int,string>
     */
    public static function opsiMurni(?array $map, string $kunci, array $default): array
    {
        $f = static::field($map, $kunci);
        $admin = $f?->opsi_list ?? [];

        return $admin ?: $default;
    }

    /**
     * Field kustom yang tampil untuk satu langkah, urut tampilan.
     *
     * @return array<int, FormField>
     */
    public static function kustomUntuk(?array $map, string $langkah): array
    {
        $map ??= static::map();

        return array_values(array_filter($map, fn ($f) => $f->langkah === $langkah && $f->is_kustom && $f->tampil));
    }

    /**
     * Aturan validasi untuk field kustom satu langkah.
     */
    public static function aturanKustom(?array $map, string $langkah): array
    {
        $rules = [];

        foreach (static::kustomUntuk($map, $langkah) as $f) {
            // Tipe file: validasi berkas terpisah (tidak masuk string/max:500/in:).
            if ($f->tipe === 'file') {
                $rules[$f->kunci] = ($f->wajib ? 'required' : 'nullable') . '|file|mimes:jpg,jpeg,png,pdf|max:5120';
                continue;
            }

            // Checkbox: wajib = harus dicentang (accepted), opsional = boolean longgar.
            if ($f->tipe === 'checkbox') {
                $rules[$f->kunci] = $f->wajib ? 'required|accepted' : 'nullable|boolean';
                continue;
            }

            $rule = $f->wajib ? 'required' : 'nullable';

            $rule .= match ($f->tipe) {
                'number' => '|numeric',
                'email' => '|email',
                'date' => '|date',
                default => '|string',
            };

            $rule .= '|max:500';

            if (in_array($f->tipe, ['select', 'radio'], true) && $f->opsi_list) {
                $rule .= '|in:' . implode(',', array_map(fn ($o) => str_replace(',', ' ', $o), $f->opsi_list));
            }

            $rules[$f->kunci] = $rule;
        }

        return $rules;
    }

    /**
     * Sesuaikan aturan bawaan dengan toggle tampil/wajib admin.
     * Field disembunyikan => nullable (nilai lama dipertahankan).
     */
    public static function sesuaikanAturan(?array $map, array $base, array $defaultWajib): array
    {
        if (! static::available()) {
            return $base;
        }

        $map ??= static::map();

        foreach ($base as $kunci => $rule) {
            // Lewati aturan array (nilai_ujian.*) & nested.
            if (! is_string($rule) || str_contains($kunci, '*')) {
                continue;
            }

            $f = $map[$kunci] ?? null;
            if (! $f || $f->is_kustom) {
                continue;
            }

            $wajib = $f->tampil && $f->wajib;

            if ($wajib) {
                continue;
            }

            // required -> nullable, pertahankan sisa aturan tipe.
            $parts = array_filter(explode('|', $rule), fn ($p) => $p !== '' && $p !== 'required');
            array_unshift($parts, 'nullable');
            $base[$kunci] = implode('|', $parts);
        }

        return $base;
    }

    /**
     * Ambil nilai field kustom dari request untuk satu langkah.
     * Berkas (tipe file) otomatis disimpan ke temp/kustom.
     */
    public static function ambilKustom(Request $request, ?array $map, string $langkah): array
    {
        $data = [];

        foreach (static::kustomUntuk($map, $langkah) as $f) {
            if ($f->tipe === 'file' && $request->hasFile($f->kunci)) {
                try {
                    $data[$f->kunci] = $request->file($f->kunci)->store('temp/kustom', 'local');
                } catch (\Throwable) {
                    // Gagal simpan berkas kustom tidak boleh menggagalkan wizard.
                }
                continue;
            }

            // Checkbox tak-dicentang: simpan null agar array_filter submit membuangnya.
            $data[$f->kunci] = $request->input($f->kunci);
        }

        return $data;
    }

    /**
     * Label langkah manusiawi untuk panel admin & pesan error.
     */
    public static function namaLangkah(string $langkah): string
    {
        return match ($langkah) {
            'step1' => 'Step 1 — Identitas & Persetujuan',
            'step2' => 'Step 2 — Upload KK & Akta',
            'step3' => 'Step 3 — Rapor',
            'step4' => 'Step 4 — Jurusan & Minat',
            'step5' => 'Step 5 — Data Siswa',
            'step6' => 'Step 6 — Data Orang Tua',
            'step7' => 'Step 7 — Bagian B/C/D',
            'step8' => 'Step 8 — Konfirmasi Akhir',
            default => $langkah,
        };
    }
}
