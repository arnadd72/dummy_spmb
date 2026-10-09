<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimoni;
use App\Support\GoogleDrive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimoniController extends Controller
{
    /**
     * Daftar testimoni alumni untuk dikelola admin.
     */
    public function index(Request $request)
    {
        $list = Testimoni::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));
                $query->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('testimoni', 'like', '%' . $search . '%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->input('status') === 'active');
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.content.testimoni', compact('list'));
    }

    /**
     * Form tambah testimoni baru.
     */
    public function create()
    {
        return view('admin.content.testimoni-form', [
            'item' => new Testimoni(),
            'mode' => 'create',
        ]);
    }

    /**
     * Simpan testimoni baru.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('testimoni', 'public');

            $validated['foto_drive_id'] = GoogleDrive::upload(
                $validated['foto'],
                basename($validated['foto']),
                null,
                'konten/testimoni'
            );
        }

        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['foto_upload']);
        if (isset($validated['foto']) && $request->hasFile('foto')) {
            // keep foto path
        } else {
            unset($validated['foto']);
        }

        Testimoni::create($validated);

        return redirect()
            ->route('admin.testimoni.index')
            ->with('success', 'Testimoni alumni berhasil ditambahkan.');
    }

    /**
     * Detail satu testimoni.
     */
    public function show(Testimoni $testimoni)
    {
        return view('admin.content.testimoni-show', ['item' => $testimoni]);
    }

    /**
     * Form edit testimoni.
     */
    public function edit(Testimoni $testimoni)
    {
        return view('admin.content.testimoni-form', [
            'item' => $testimoni,
            'mode' => 'edit',
        ]);
    }

    /**
     * Update testimoni.
     */
    public function update(Request $request, Testimoni $testimoni)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('foto')) {
            if ($testimoni->foto && Storage::disk('public')->exists($testimoni->foto)) {
                Storage::disk('public')->delete($testimoni->foto);
            }
            GoogleDrive::delete($testimoni->foto_drive_id);

            $validated['foto'] = $request->file('foto')->store('testimoni', 'public');
            $validated['foto_drive_id'] = GoogleDrive::upload(
                $validated['foto'],
                basename($validated['foto']),
                null,
                'konten/testimoni'
            );
        } else {
            unset($validated['foto']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $testimoni->update($validated);

        return redirect()
            ->route('admin.testimoni.index')
            ->with('success', 'Testimoni alumni berhasil diperbarui.');
    }

    /**
     * Hapus testimoni.
     */
    public function destroy(Testimoni $testimoni)
    {
        if ($testimoni->foto && Storage::disk('public')->exists($testimoni->foto)) {
            Storage::disk('public')->delete($testimoni->foto);
        }
        GoogleDrive::delete($testimoni->foto_drive_id);

        $testimoni->delete();

        return redirect()
            ->route('admin.testimoni.index')
            ->with('success', 'Testimoni alumni berhasil dihapus.');
    }

    /**
     * Aturan validasi untuk store & update.
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'nama'         => ['required', 'string', 'max:150'],
            'angkatan'     => ['nullable', 'string', 'max:20'],
            'jurusan_kode' => ['nullable', 'string', 'max:10'],
            'pekerjaan'    => ['nullable', 'string', 'max:150'],
            'testimoni'    => ['required', 'string', 'max:2000'],
            'foto'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active'    => ['nullable', 'boolean'],
        ]);
    }
}
