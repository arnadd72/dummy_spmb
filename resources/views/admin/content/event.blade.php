@extends('layouts.admin')

@section('title', 'Manajemen Event — Admin PPDB')

@push('head')
@include('admin.content.partials.styles')
@endpush

@section('content')
<div class="content-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">Agenda &amp; Event</h1>
            <p class="page-sub">
                Event aktif otomatis tampil pada bagian “Agenda Mendatang” di landing page,
                lengkap dengan poster, tanggal, dan lokasi.
            </p>
        </div>
        <a href="{{ route('admin.event.create') }}" class="btn btn-primary">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Event
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @php
        $collection = $list->getCollection();
        $total      = $list->total();
        $active     = $collection->where('is_active', true)->count();
        $inactive   = $collection->where('is_active', false)->count();
    @endphp

    <div class="stat-grid">
        <div class="stat-tile">
            <div class="stat-label">Total Event</div>
            <div class="stat-value">{{ number_format($total) }}</div>
        </div>
        <div class="stat-tile">
            <div class="stat-label">Aktif</div>
            <div class="stat-value green">{{ $active }}</div>
        </div>
        <div class="stat-tile">
            <div class="stat-label">Nonaktif</div>
            <div class="stat-value red">{{ $inactive }}</div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.event.index') }}" class="toolbar">
        <div class="search-wrap">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <label for="event-search" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Cari event</label>
            <input type="text" id="event-search" name="search" value="{{ request('search') }}"
                   placeholder="Cari judul event..." class="search-input">
        </div>
        <div>
            <label for="event-status" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Filter status</label>
            <select id="event-status" name="status" class="filter-select">
                <option value="">Semua Status</option>
                <option value="active"   {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <button type="submit" class="btn btn-ghost">Terapkan</button>
        @if (request('search') || request('status'))
            <a href="{{ route('admin.event.index') }}" class="btn btn-ghost">Reset</a>
        @endif
    </form>

    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Daftar Event</h2>
            <p class="panel-sub">Urut dari yang terbaru. Hanya event aktif yang tampil di landing page.</p>
        </div>

        <div class="panel-body">
            @if (count($list) > 0)
                <div class="data-list">
                    @foreach ($list as $item)
                    <article class="data-item">
                        @if ($item->has_gambar)
                            <img src="{{ $item->gambar_url }}" alt="Poster {{ $item->judul }}" class="thumb">
                        @else
                            <div class="thumb-empty">Tanpa poster</div>
                        @endif

                        <div class="data-item-body">
                            <div style="display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; margin-bottom:.4rem;">
                                <span class="badge {{ $item->is_active ? 'badge-on' : 'badge-off' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <h3 class="data-item-title">{{ $item->judul ?: 'Tanpa Judul' }}</h3>
                            @if ($item->deskripsi)
                                <p class="data-item-text">{{ Str::limit(strip_tags($item->deskripsi), 160) }}</p>
                            @endif
                            <div class="data-item-meta">
                                <span>Tanggal: {{ $item->tanggal?->translatedFormat('d M Y') ?? 'Belum diatur' }}</span>
                                @if ($item->lokasi) <span>Lokasi: {{ $item->lokasi }}</span> @endif
                            </div>
                        </div>

                        <div class="data-item-actions">
                            <a href="{{ route('admin.event.show', $item->id) }}" class="btn btn-ghost btn-sm">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Lihat
                            </a>
                            <a href="{{ route('admin.event.edit', $item->id) }}" class="btn btn-ghost btn-sm">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                Edit
                            </a>
                        </div>
                    </article>
                    @endforeach
                </div>
            @else
                <div class="empty-box">
                    <svg width="42" height="42" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    <h3>Belum Ada Event</h3>
                    <p>Tambahkan agenda kegiatan untuk ditampilkan pada landing page.</p>
                    <a href="{{ route('admin.event.create') }}" class="btn btn-primary">Tambah Event</a>
                </div>
            @endif
        </div>
    </div>

    @if ($list->hasPages())
        <div class="pagination-wrap">{{ $list->links() }}</div>
    @endif
</div>
@endsection
