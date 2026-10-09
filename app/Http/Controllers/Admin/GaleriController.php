<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use App\Support\GoogleDrive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaleriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $list = Galeri::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));
                $query->where('judul', 'like', '%' . $search . '%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->input('status') === 'active');
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.content.galeri', compact('list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.content.galeri-form', [
            'item' => new Galeri(),
            'mode' => 'create',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        // Gambar disimpan ke disk `public` agar bisa diakses via /storage.
        if ($request->hasFile('gambar')) {
            $validated['path'] = $request->file('gambar')->store('galeri', 'public');

            // Salinan ke Google Drive bersifat opsional (gagal = tetap jalan).
            $validated['drive_id'] = GoogleDrive::upload(
                $validated['path'],
                basename($validated['path']),
                null,
                'konten/galeri'
            );
        }

        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['gambar']);

        Galeri::create($validated);

        return redirect()
            ->route('admin.galeri.index')
            ->with('success', 'Galeri berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Galeri $galeri)
    {
        return view('admin.content.galeri-show', ['item' => $galeri]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Galeri $galeri)
    {
        return view('admin.content.galeri-form', [
            'item' => $galeri,
            'mode' => 'edit',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Galeri $galeri)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama agar tidak menumpuk di storage.
            if ($galeri->path && Storage::disk('public')->exists($galeri->path)) {
                Storage::disk('public')->delete($galeri->path);
            }
            GoogleDrive::delete($galeri->drive_id);

            $validated['path'] = $request->file('gambar')->store('galeri', 'public');
            $validated['drive_id'] = GoogleDrive::upload(
                $validated['path'],
                basename($validated['path']),
                null,
                'konten/galeri'
            );
        }

        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['gambar']);

        $galeri->update($validated);

        return redirect()
            ->route('admin.galeri.index')
            ->with('success', 'Galeri berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Galeri $galeri)
    {
        if ($galeri->path && Storage::disk('public')->exists($galeri->path)) {
            Storage::disk('public')->delete($galeri->path);
        }
        GoogleDrive::delete($galeri->drive_id);

        $galeri->delete();

        return redirect()
            ->route('admin.galeri.index')
            ->with('success', 'Galeri berhasil dihapus.');
    }

    /**
     * Aturan validasi yang dipakai untuk store & update.
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'judul'     => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'gambar'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
