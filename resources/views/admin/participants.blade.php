@extends('layouts.admin')

@section('title', 'Manajemen Peserta — Admin PPDB')

@push('head')
<style>
    .peserta-page { max-width: 1200px; margin: 0 auto; }

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .page-title {
        font-family: var(--admin-font-mono);
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--nu-700);
        letter-spacing: -.03em;
        margin: 0 0 .3rem;
    }

    .page-sub { font-size: .85rem; color: var(--tx-muted); margin: 0; max-width: 560px; line-height: 1.5; }

    .toolbar {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        align-items: center;
        background: var(--sf-card);
        border: 1px solid var(--sf-border);
        border-radius: var(--card-radius);
        padding: 1rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.25rem;
    }

    .search-input {
        flex: 1;
        min-width: 220px;
        padding: .6rem 1rem;
        border: 1px solid var(--sf-border);
        border-radius: 10px;
        font-family: var(--admin-font-sans);
        font-size: .85rem;
        background: var(--sf-page);
        outline: none;
        transition: all var(--transition);
    }

    .search-input:focus { border-color: var(--nu-500); background: #fff; box-shadow: 0 0 0 3px rgba(5,122,60,.12); }

    .filter-select {
        padding: .6rem 1rem;
        border: 1px solid var(--sf-border);
        border-radius: 10px;
        font-family: var(--admin-font-sans);
        font-size: .85rem;
        background: var(--sf-page);
        cursor: pointer;
        outline: none;
    }

    .btn {
        font-family: var(--admin-font-sans);
        font-size: .82rem;
        font-weight: 600;
        padding: .6rem 1.1rem;
        border-radius: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        transition: all var(--transition);
    }

    .btn-primary { background: var(--nu-500); color: #fff; }
    .btn-primary:hover { background: var(--nu-600); }
    .btn-xls { background: #047733; color: #fff; }
    .btn-xls:hover { background: #024B23; }
    .btn-pdf { background: var(--st-error); color: #fff; }
    .btn-pdf:hover { filter: brightness(.92); }
    .btn-ghost { background: var(--sf-card); border-color: var(--sf-border); color: var(--tx-secondary); }
    .btn-ghost:hover { border-color: var(--nu-500); color: var(--nu-500); background: var(--nu-50); }

    .panel {
        background: var(--sf-card);
        border: 1px solid var(--sf-border);
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid var(--sf-border);
        background: var(--sf-page);
    }

    .panel-title { font-family: var(--admin-font-mono); font-size: .95rem; font-weight: 700; color: var(--nu-700); margin: 0; letter-spacing: -.01em; }

    .count-chip {
        font-family: var(--admin-font-mono);
        font-size: .7rem;
        font-weight: 600;
        padding: .25rem .6rem;
        border-radius: 8px;
        background: var(--sf-card);
        border: 1px solid var(--sf-border);
        color: var(--nu-700);
    }

    .data-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .84rem; }

    .data-table thead th {
        font-family: var(--admin-font-mono);
        font-size: .62rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--tx-muted);
        text-align: left;
        padding: .8rem 1rem;
        border-bottom: 1px solid var(--sf-border);
        background: var(--sf-card);
    }

    .data-table tbody td { padding: .85rem 1rem; border-bottom: 1px solid var(--sf-border); color: var(--tx-secondary); vertical-align: middle; }
    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover { background: var(--sf-hover); }

    .td-mono { font-family: var(--admin-font-mono); font-weight: 600; color: var(--nu-500); font-size: .77rem; }
    .td-name { font-weight: 600; color: var(--tx-primary); }

    .badge {
        display: inline-flex;
        padding: .2rem .55rem;
        border-radius: 6px;
        font-family: var(--admin-font-mono);
        font-size: .63rem;
        font-weight: 600;
    }

    .badge.approved { background: var(--nu-50); color: var(--nu-500); }
    .badge.pending  { background: var(--gold-100); color: var(--gold-500); }
    .badge.revision { background: var(--st-error-bg); color: var(--st-error); }
    .badge.rejected { background: var(--st-error-bg); color: var(--st-error); }

    .jurusan-chip {
        font-family: var(--admin-font-mono);
        font-size: .72rem;
        font-weight: 600;
        padding: .15rem .45rem;
        border-radius: 6px;
        background: var(--sf-page);
        border: 1px solid var(--sf-border);
        color: var(--nu-700);
        display: inline-block;
        margin-right: 3px;
    }

    .pagination-wrap { padding: 1rem 1.25rem; border-top: 1px solid var(--sf-border); background: var(--sf-page); }

    .empty { text-align: center; padding: 3rem 1rem; color: var(--tx-muted); font-size: .88rem; }
</style>
@endpush

@section('content')
<div class="peserta-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">Manajemen Peserta</h1>
            <p class="page-sub">Cari, filter, dan ekspor data pendaftar. Ekspor mengikuti filter yang sedang aktif.</p>
        </div>
    </div>

    {{-- Toolbar: pencarian, filter, ekspor --}}
    <form method="GET" action="{{ route('admin.participants') }}" class="toolbar">
        <input type="text"
               name="search"
               class="search-input"
               value="{{ request('search') }}"
               placeholder="Cari nama, NIK, NISN, atau no. HP…"
               aria-label="Cari pendaftar">

        <select name="status" class="filter-select" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="pending"  @selected(request('status') === 'pending')>Menunggu Verifikasi</option>
            <option value="approved" @selected(request('status') === 'approved')>Diterima</option>
            <option value="revision" @selected(request('status') === 'revision')>Perlu Revisi</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
        </select>

        <button type="submit" class="btn btn-primary">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/></svg>
            Terapkan
        </button>

        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-xls">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Ekspor Excel
        </a>

        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-pdf">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            Ekspor PDF
        </a>

        @if (request()->filled('search') || request()->filled('status'))
            <a href="{{ route('admin.participants') }}" class="btn btn-ghost">Bersihkan</a>
        @endif
    </form>

    {{-- Tabel pendaftar --}}
    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Daftar Pendaftar</h2>
            <span class="count-chip">{{ $participants->total() }} siswa</span>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Reg</th>
                        <th>Nama Lengkap</th>
                        <th>NIK</th>
                        <th>NISN</th>
                        <th>Asal Sekolah</th>
                        <th>Jurusan Pilihan</th>
                        <th>Status</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $p)
                    @php
                        $status = $p->status_verifikasi ?? 'pending';
                        $badgeLabel = match ($status) {
                            'approved' => 'Diterima',
                            'rejected' => 'Ditolak',
                            'revision' => 'Perlu Revisi',
                            default    => 'Menunggu',
                        };
                    @endphp
                    <tr>
                        <td class="td-mono">{{ $p->nomor_pendaftaran }}</td>
                        <td class="td-name">
                            <a href="{{ route('admin.pendaftar.show', $p->id) }}" style="color:inherit; text-decoration:none;">{{ $p->nama_lengkap }}</a>
                        </td>
                        <td class="td-mono">{{ $p->nik ?: '—' }}</td>
                        <td class="td-mono">{{ $p->nisn ?: '—' }}</td>
                        <td>{{ $p->asal_sekolah ?: '—' }}</td>
                        <td>
                            @if($p->jurusan1)
                                <span class="jurusan-chip">{{ $p->jurusan1->kode }}</span>
                            @endif
                            @if($p->jurusan2)
                                <span class="jurusan-chip">{{ $p->jurusan2->kode }}</span>
                            @endif
                            @if(!$p->jurusan1 && !$p->jurusan2) — @endif
                        </td>
                        <td><span class="badge {{ $status }}">{{ $badgeLabel }}</span></td>
                        <td style="text-align:center; white-space:nowrap;">
                            <a href="{{ route('admin.pendaftar.show', $p->id) }}" class="btn btn-primary" style="padding:.4rem .7rem; font-size:.74rem;">Detail</a>
                            <a href="{{ route('admin.pendaftar.edit', $p->id) }}" class="btn btn-ghost" style="padding:.4rem .7rem; font-size:.74rem;">Edit</a>
                            <a href="{{ route('admin.documents') }}?pendaftar={{ $p->id }}" class="btn btn-ghost" style="padding:.4rem .7rem; font-size:.74rem;">Verifikasi</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="empty">Tidak ada data pendaftar{{ request()->filled('search') ? ' yang cocok dengan pencarian' : '' }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($participants->hasPages())
            <div class="pagination-wrap">{{ $participants->links() }}</div>
        @endif
    </div>
</div>
@endsection
