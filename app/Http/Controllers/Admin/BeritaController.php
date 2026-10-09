<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Support\GoogleDrive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BeritaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $list = Berita::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));
                $query->where('judul', 'like', '%' . $search . '%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->input('status') === 'active');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.content.berita', compact('list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.content.berita-form', [
            'item' => new Berita(),
            'mode' => 'create',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        // Thumbnail disimpan ke disk `public` agar bisa diakses via /storage.
        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('berita', 'public');

            // Salinan ke Google Drive bersifat opsional (gagal = tetap jalan).
            $validated['gambar_drive_id'] = GoogleDrive::upload(
                $validated['gambar'],
                basename($validated['gambar']),
                null,
                'konten/berita'
            );
        }

        $validated['slug']      = $this->uniqueSlug($validated['judul']);
        $validated['is_active'] = $request->boolean('is_active');

        Berita::create($validated);

        return redirect()
            ->route('admin.berita.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Berita $berita)
    {
        return view('admin.content.berita-show', ['item' => $berita]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Berita $berita)
    {
        return view('admin.content.berita-form', [
            'item' => $berita,
            'mode' => 'edit',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Berita $berita)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('gambar')) {
            // Hapus thumbnail lama agar tidak menumpuk di storage.
            if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
                Storage::disk('public')->delete($berita->gambar);
            }
            GoogleDrive::delete($berita->gambar_drive_id);

            $validated['gambar'] = $request->file('gambar')->store('berita', 'public');
            $validated['gambar_drive_id'] = GoogleDrive::upload(
                $validated['gambar'],
                basename($validated['gambar']),
                null,
                'konten/berita'
            );
        }

        $validated['slug']      = $this->uniqueSlug($validated['judul'], $berita->id);
        $validated['is_active'] = $request->boolean('is_active');

        $berita->update($validated);

        return redirect()
            ->route('admin.berita.index')
            ->with('success', 'Berita berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Berita $berita)
    {
        if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
            Storage::disk('public')->delete($berita->gambar);
        }
        GoogleDrive::delete($berita->gambar_drive_id);

        $berita->delete();

        return redirect()
            ->route('admin.berita.index')
            ->with('success', 'Berita berhasil dihapus.');
    }

    /**
     * Aturan validasi yang dipakai untuk store & update.
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'judul'     => ['required', 'string', 'max:255'],
            'isi'       => ['nullable', 'string'],
            'gambar'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Buat slug unik dari judul, mengabaikan record tertentu (saat update).
     */
    protected function uniqueSlug(string $judul, ?int $ignoreId = null): string
    {
        $base = Str::slug($judul) ?: 'berita';
        $slug = $base;
        $i    = 2;

        while (
            Berita::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
