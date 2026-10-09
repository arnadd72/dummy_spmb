<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormFieldController extends Controller
{
    /**
     * Daftar isian formulir publik yang bisa diatur admin.
     */
    public function index(Request $request)
    {
        $list = FormField::query()
            ->when($request->filled('langkah'), fn ($q) => $q->where('langkah', $request->input('langkah')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = trim($request->input('search'));
                $q->where(fn ($w) => $w->where('label', 'like', "%{$s}%")->orWhere('kunci', 'like', "%{$s}%"));
            })
            ->orderBy('langkah')
            ->orderBy('urutan')
            ->paginate(30)
            ->withQueryString();

        return view('admin.content.form-field', compact('list'));
    }

    /**
     * Form tambah field kustom baru.
     */
    public function create()
    {
        return view('admin.content.form-field-form', [
            'item' => new FormField(['tipe' => 'text', 'tampil' => true, 'wajib' => false, 'urutan' => 999]),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $validated['kunci'] = $this->kunciBaru($request->input('label'));
        $validated['is_kustom'] = true;
        $validated['opsi'] = $this->parseOpsi($request->input('opsi_teks'), $request->input('tipe'));

        FormField::create($validated);

        return redirect()->route('admin.form-field.index')
            ->with('success', 'Isian formulir baru berhasil ditambahkan.');
    }

    public function edit(FormField $formField)
    {
        return view('admin.content.form-field-form', [
            'item' => $formField,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, FormField $formField)
    {
        $validated = $this->validated($request, $formField->id);
        $validated['opsi'] = $this->parseOpsi($request->input('opsi_teks'), $request->input('tipe', $formField->tipe));

        // Kunci field bawaan dikunci (terikat kolom DB); field kustom boleh ganti kunci.
        if (! $formField->is_kustom) {
            unset($validated['kunci']);
        } elseif (empty($validated['kunci'])) {
            $validated['kunci'] = $formField->kunci;
        }

        $formField->update($validated);

        return redirect()->route('admin.form-field.index')
            ->with('success', 'Isian formulir berhasil diperbarui.');
    }

    /**
     * Toggle tampil/sembunyi cepat dari daftar.
     */
    public function toggle(FormField $formField)
    {
        $formField->update(['tampil' => ! $formField->tampil]);

        return back()->with('success', "Isian \"{$formField->label}\" sekarang " . ($formField->tampil ? 'ditampilkan' : 'disembunyikan') . '.');
    }

    public function destroy(FormField $formField)
    {
        if (! $formField->is_kustom) {
            return back()->withErrors(['field' => 'Isian bawaan tidak bisa dihapus — cukup sembunyikan bila tidak dipakai.']);
        }

        $formField->delete();

        return redirect()->route('admin.form-field.index')
            ->with('success', 'Isian kustom berhasil dihapus.');
    }

    /**
     * Simpan urutan + tampil/wajib massal dari tabel daftar.
     */
    public function simpanUrutan(Request $request)
    {
        $baris = $request->input('fields', []);

        foreach ($baris as $id => $cfg) {
            $field = FormField::find($id);
            if (! $field) {
                continue;
            }

            $field->update([
                'urutan' => (int) ($cfg['urutan'] ?? $field->urutan),
                'tampil' => isset($cfg['tampil']),
                'wajib'  => isset($cfg['wajib']),
            ]);
        }

        return back()->with('success', 'Urutan & status isian berhasil disimpan.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'langkah' => ['required', Rule::in(['step1', 'step2', 'step3', 'step4', 'step5', 'step6', 'step7', 'step8'])],
            'label' => ['required', 'string', 'max:150'],
            'teks_bantuan' => ['nullable', 'string', 'max:255'],
            'tipe' => ['required', Rule::in(['text', 'number', 'tel', 'email', 'date', 'textarea', 'select', 'radio', 'checkbox', 'file'])],
            'placeholder' => ['nullable', 'string', 'max:150'],
            'nilai_default' => ['nullable', 'string', 'max:150'],
            'tampil' => ['nullable', 'boolean'],
            'wajib' => ['nullable', 'boolean'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];

        // Kunci hanya bisa diatur untuk field kustom.
        if ($ignoreId) {
            $field = FormField::find($ignoreId);
            if ($field && $field->is_kustom) {
                $rules['kunci'] = ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/', Rule::unique('form_fields', 'kunci')->ignore($ignoreId)];
            }
        }

        $data = $request->validate($rules);

        $data['tampil'] = $request->boolean('tampil');
        $data['wajib'] = $request->boolean('wajib');
        $data['urutan'] = (int) ($data['urutan'] ?? 999);

        return $data;
    }

    protected function kunciBaru(string $label): string
    {
        $slug = Str::slug($label, '_');
        $slug = preg_replace('/[^a-z0-9_]/', '', $slug) ?: 'field';
        $slug = substr('kustom_' . $slug, 0, 55);

        $kunci = $slug;
        $i = 1;
        while (FormField::where('kunci', $kunci)->exists()) {
            $kunci = substr($slug, 0, 52) . '_' . $i++;
        }

        return $kunci;
    }

    /**
     * Parse textarea opsi (satu baris = satu pilihan) untuk select/radio.
     */
    protected function parseOpsi(?string $teks, ?string $tipe): ?array
    {
        if (! in_array($tipe, ['select', 'radio'], true)) {
            return null;
        }

        $baris = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $teks))));

        return $baris ?: null;
    }
}
