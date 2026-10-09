<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $list = Faq::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('pertanyaan', 'like', '%' . $search . '%')
                      ->orWhere('jawaban', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->input('status') === 'active');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.content.faq', compact('list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.content.faq-form', [
            'item' => new Faq(),
            'mode' => 'create',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $validated['keywords']  = $this->parseKeywords($request->input('keywords'));
        $validated['is_active'] = $request->boolean('is_active');

        Faq::create($validated);

        return redirect()
            ->route('admin.faq.index')
            ->with('success', 'FAQ berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Faq $faq)
    {
        return view('admin.content.faq-show', ['item' => $faq]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Faq $faq)
    {
        return view('admin.content.faq-form', [
            'item' => $faq,
            'mode' => 'edit',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Faq $faq)
    {
        $validated = $this->validated($request);

        $validated['keywords']  = $this->parseKeywords($request->input('keywords'));
        $validated['is_active'] = $request->boolean('is_active');

        $faq->update($validated);

        return redirect()
            ->route('admin.faq.index')
            ->with('success', 'FAQ berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()
            ->route('admin.faq.index')
            ->with('success', 'FAQ berhasil dihapus.');
    }

    /**
     * Aturan validasi yang dipakai untuk store & update.
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'pertanyaan' => ['required', 'string'],
            'jawaban'    => ['required', 'string'],
            'kategori'   => ['nullable', 'string', 'max:50'],
            'keywords'   => ['nullable', 'string'],
            'is_active'  => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Ubah input keywords (dipisah koma) menjadi array bersih.
     */
    protected function parseKeywords(?string $raw): array
    {
        if (!$raw) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($k) => strtolower(trim($k)),
            explode(',', $raw)
        )));
    }
}
