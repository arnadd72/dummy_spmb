@extends('layouts.admin')

@section('title', 'Chatbot FAQ — Admin PPDB')

@push('head')
@include('admin.content.partials.styles')
@endpush

@section('content')
<div class="content-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">Chatbot FAQ</h1>
            <p class="page-sub">
                Kelola basis pengetahuan asisten chatbot pada portal siswa. Setiap FAQ memiliki
                kata kunci pemicu yang dipakai chatbot untuk mencocokkan pertanyaan pengunjung.
            </p>
        </div>
        <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
            <a href="{{ route('ppdb.index') }}" target="_blank" class="btn btn-ghost">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                Lihat Chatbot
            </a>
            <a href="{{ route('admin.faq.create') }}" class="btn btn-primary">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah FAQ
            </a>
        </div>
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
            <div class="stat-label">Total FAQ</div>
            <div class="stat-value">{{ number_format($total) }}</div>
        </div>
        <div class="stat-tile">
            <div class="stat-label">Aktif (Dipakai Chatbot)</div>
            <div class="stat-value green">{{ $active }}</div>
        </div>
        <div class="stat-tile">
            <div class="stat-label">Nonaktif</div>
            <div class="stat-value red">{{ $inactive }}</div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.faq.index') }}" class="toolbar">
        <div class="search-wrap">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <label for="faq-search" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Cari FAQ</label>
            <input type="text" id="faq-search" name="search" value="{{ request('search') }}"
                   placeholder="Cari pertanyaan atau jawaban..." class="search-input">
        </div>
        <div>
            <label for="faq-status" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Filter status</label>
            <select id="faq-status" name="status" class="filter-select">
                <option value="">Semua Status</option>
                <option value="active"   {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <button type="submit" class="btn btn-ghost">Terapkan</button>
        @if (request('search') || request('status'))
            <a href="{{ route('admin.faq.index') }}" class="btn btn-ghost">Reset</a>
        @endif
    </form>

    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Basis Pengetahuan Chatbot</h2>
            <p class="panel-sub">Hanya FAQ berstatus aktif yang akan dijawab oleh asisten.</p>
        </div>

        <div class="panel-body">
            @if (count($list) > 0)
                <div class="data-list">
                    @foreach ($list as $item)
                    <article class="data-item">
                        <div class="data-item-body">
                            <div style="display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; margin-bottom:.4rem;">
                                <span class="badge {{ $item->is_active ? 'badge-on' : 'badge-off' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                @if ($item->kategori)
                                    <span class="badge badge-cat">{{ $item->kategori }}</span>
                                @endif
                            </div>

                            <h3 class="data-item-title">{{ $item->pertanyaan }}</h3>
                            <p class="data-item-text">{{ Str::limit($item->jawaban, 180) }}</p>

                            @if (!empty($item->keywords))
                                <div class="data-item-meta">
                                    <span>Kata kunci: {{ implode(', ', array_slice($item->keywords, 0, 8)) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="data-item-actions">
                            <a href="{{ route('admin.faq.show', $item->id) }}" class="btn btn-ghost btn-sm">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Lihat
                            </a>
                            <a href="{{ route('admin.faq.edit', $item->id) }}" class="btn btn-ghost btn-sm">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                Edit
                            </a>
                        </div>
                    </article>
                    @endforeach
                </div>
            @else
                <div class="empty-box">
                    <svg width="42" height="42" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                    <h3>Belum Ada FAQ</h3>
                    <p>Tambahkan pertanyaan pertama agar chatbot dapat menjawab pengunjung.</p>
                    <a href="{{ route('admin.faq.create') }}" class="btn btn-primary">Tambah FAQ</a>
                </div>
            @endif
        </div>
    </div>

    @if ($list->hasPages())
        <div class="pagination-wrap">{{ $list->links() }}</div>
    @endif
</div>
@endsection
