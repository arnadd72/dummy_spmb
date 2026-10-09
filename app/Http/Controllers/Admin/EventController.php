<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\GoogleDrive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $list = Event::query()
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

        return view('admin.content.event', compact('list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.content.event-form', [
            'item' => new Event(),
            'mode' => 'create',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        // Poster disimpan ke disk `public` agar bisa diakses via /storage.
        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('event', 'public');

            // Salinan ke Google Drive bersifat opsional (gagal = tetap jalan).
            $validated['gambar_drive_id'] = GoogleDrive::upload(
                $validated['gambar'],
                basename($validated['gambar']),
                null,
                'konten/event'
            );
        }

        $validated['slug']      = $this->uniqueSlug($validated['judul']);
        $validated['is_active'] = $request->boolean('is_active');

        Event::create($validated);

        return redirect()
            ->route('admin.event.index')
            ->with('success', 'Event berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return view('admin.content.event-show', ['item' => $event]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        return view('admin.content.event-form', [
            'item' => $event,
            'mode' => 'edit',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('gambar')) {
            // Hapus poster lama agar tidak menumpuk di storage.
            if ($event->gambar && Storage::disk('public')->exists($event->gambar)) {
                Storage::disk('public')->delete($event->gambar);
            }
            GoogleDrive::delete($event->gambar_drive_id);

            $validated['gambar'] = $request->file('gambar')->store('event', 'public');
            $validated['gambar_drive_id'] = GoogleDrive::upload(
                $validated['gambar'],
                basename($validated['gambar']),
                null,
                'konten/event'
            );
        }

        $validated['slug']      = $this->uniqueSlug($validated['judul'], $event->id);
        $validated['is_active'] = $request->boolean('is_active');

        $event->update($validated);

        return redirect()
            ->route('admin.event.index')
            ->with('success', 'Event berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        if ($event->gambar && Storage::disk('public')->exists($event->gambar)) {
            Storage::disk('public')->delete($event->gambar);
        }
        GoogleDrive::delete($event->gambar_drive_id);

        $event->delete();

        return redirect()
            ->route('admin.event.index')
            ->with('success', 'Event berhasil dihapus.');
    }

    /**
     * Aturan validasi yang dipakai untuk store & update.
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'judul'     => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tanggal'   => ['nullable', 'date'],
            'lokasi'    => ['nullable', 'string', 'max:255'],
            'gambar'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Buat slug unik dari judul, mengabaikan record tertentu (saat update).
     */
    protected function uniqueSlug(string $judul, ?int $ignoreId = null): string
    {
        $base = Str::slug($judul) ?: 'event';
        $slug = $base;
        $i    = 2;

        while (
            Event::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
