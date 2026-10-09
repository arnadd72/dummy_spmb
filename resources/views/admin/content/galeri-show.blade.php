@extends('layouts.admin')

@section('title', 'Detail Galeri — Admin PPDB')

@push('head')
@include('admin.content.partials.styles')
@endpush

@section('content')
<div class="content-page">

    <div class="page-head">
        <div>
            <span class="badge {{ $item->is_active ? 'badge-on' : 'badge-off' }}">
                {{ $item->is_active ? 'Tampil' : 'Disembunyikan' }}
            </span>
            <h1 class="page-title" style="font-size:1.3rem; margin-top:.6rem;">{{ $item->judul ?: 'Tanpa Judul' }}</h1>
            <p class="page-sub">Ditambahkan {{ $item->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}</p>
        </div>
        <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
            <a href="{{ route('admin.galeri.index') }}" class="btn btn-ghost">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <a href="{{ route('admin.galeri.edit', $item->id) }}" class="btn btn-primary">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                Edit Foto
            </a>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Pratinjau Foto</h2>
            <p class="panel-sub">Tampilan foto seperti yang dilihat pengunjung.</p>
        </div>

        <div class="panel-body">
            @if ($item->has_image)
                <div style="border-radius:14px; overflow:hidden; border:1px solid var(--sf-border); margin-bottom:1.5rem; background:var(--sf-page);">
                    <img src="{{ $item->url }}" alt="{{ $item->judul }}"
                         style="width:100%; max-height:480px; object-fit:contain; display:block;">
                </div>
            @else
                <div class="empty-box" style="margin-bottom:1.5rem;">
                    <svg width="42" height="42" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    <h3>Berkas Tidak Ditemukan</h3>
                    <p>Foto tercatat di basis data namun berkasnya tidak ada di storage. Silakan unggah ulang.</p>
                    <a href="{{ route('admin.galeri.edit', $item->id) }}" class="btn btn-primary">Unggah Ulang</a>
                </div>
            @endif

            <div class="detail-grid">
                <div class="detail-key">Judul</div>
                <div class="detail-val" style="font-weight:600;">{{ $item->judul ?: '—' }}</div>

                <div class="detail-key">Deskripsi</div>
                <div class="detail-val">{!! nl2br(e($item->deskripsi ?: '—')) !!}</div>

                <div class="detail-key">Berkas</div>
                <div class="detail-val">{{ $item->path ?: '—' }}</div>
            </div>
        </div>

        <div class="actions-bar">
            <span class="actions-note">Menghapus foto juga menghapus berkasnya dari storage.</span>
            <form method="POST" action="{{ route('admin.galeri.destroy', $item->id) }}"
                  onsubmit="return confirm('Hapus foto ini secara permanen?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    Hapus Foto
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
