@extends('layouts.admin')

@section('title', 'Testimoni Alumni — Admin PPDB')

@push('head')
@include('admin.content.partials.styles')
@endpush

@section('content')
<div class="content-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">Testimoni Alumni</h1>
            <p class="page-sub">
                Testimoni aktif akan tampil pada bagian “Kata Alumni” di landing page.
                Tambah, edit, atau sembunyikan testimoni kapan saja.
            </p>
        </div>
        <a href="{{ route('admin.testimoni.create') }}" class="btn btn-primary">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Testimoni
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('admin.testimoni.index') }}" class="toolbar">
        <div class="search-wrap">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <label for="testimoni-search" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Cari testimoni</label>
            <input type="text" id="testimoni-search" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama atau isi testimoni..." class="search-input">
        </div>
        <div>
            <label for="testimoni-status" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Filter status</label>
            <select id="testimoni-status" name="status" class="filter-select">
                <option value="">Semua Status</option>
                <option value="active"   {{ request('status') === 'active' ? 'selected' : '' }}>Tampil</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Disembunyikan</option>
            </select>
        </div>
        <button type="submit" class="btn btn-ghost">Terapkan</button>
        @if (request('search') || request('status'))
            <a href="{{ route('admin.testimoni.index') }}" class="btn btn-ghost">Reset</a>
        @endif
    </form>

    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Daftar Testimoni</h2>
            <p class="panel-sub">Urut dari yang terbaru. Hanya testimoni aktif yang tampil di landing page.</p>
        </div>

        <div class="panel-body">
            @if (count($list) > 0)
                <div class="data-list">
                    @foreach ($list as $item)
                    <article class="data-item">
                        @if ($item->foto)
                            <img src="{{ $item->url }}" alt="{{ $item->nama }}" class="thumb" style="object-fit:cover;">
                        @else
                            <div class="thumb-empty">{{ mb_strtoupper(mb_substr(trim($item->nama), 0, 1)) }}</div>
                        @endif

                        <div class="data-item-body">
                            <div style="display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; margin-bottom:.4rem;">
                                <span class="badge {{ $item->is_active ? 'badge-on' : 'badge-off' }}">
                                    {{ $item->is_active ? 'Tampil' : 'Disembunyikan' }}
                                </span>
                                @if ($item->angkatan)
                                    <span class="badge badge-off">Angkatan {{ $item->angkatan }}</span>
                                @endif
                                @if ($item->jurusan_kode)
                                    <span class="badge badge-off">{{ $item->jurusan_kode }}</span>
                                @endif
                            </div>
                            <h3 class="data-item-title">{{ $item->nama }}{{ $item->pekerjaan ? ' — ' . $item->pekerjaan : '' }}</h3>
                            <p class="data-item-text">“{{ Str::limit(strip_tags($item->testimoni), 180) }}”</p>
                            <div class="data-item-meta">
                                <span>{{ $item->created_at?->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>

                        <div class="data-item-actions">
                            <a href="{{ route('admin.testimoni.show', $item->id) }}" class="btn btn-ghost btn-sm">
                                Lihat
                            </a>
                            <a href="{{ route('admin.testimoni.edit', $item->id) }}" class="btn btn-ghost btn-sm">
                                Edit
                            </a>
                        </div>
                    </article>
                    @endforeach
                </div>
            @else
                <div class="empty-box">
                    <h3>Belum Ada Testimoni</h3>
                    <p>Tambahkan testimoni alumni untuk ditampilkan pada landing page.</p>
                    <a href="{{ route('admin.testimoni.create') }}" class="btn btn-primary">Tambah Testimoni</a>
                </div>
            @endif
        </div>
    </div>

    @if ($list->hasPages())
        <div class="pagination-wrap">{{ $list->links() }}</div>
    @endif
</div>
@endsection
