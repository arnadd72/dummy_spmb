@extends('layouts.admin')

@section('title', 'Kelola Kuota Daya Tampung — Admin PPDB')

@push('head')
<style>
    /* ─── Kuota Manager ─── */
    .kuota-page { max-width: 1100px; margin: 0 auto; }

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .page-title {
        font-family: var(--admin-font-mono);
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--nu-700);
        letter-spacing: -.03em;
        margin: 0 0 .3rem;
    }

    .page-sub {
        font-size: .85rem;
        color: var(--tx-muted);
        margin: 0;
        max-width: 560px;
        line-height: 1.5;
    }

    .alert-success {
        display: flex;
        align-items: center;
        gap: .6rem;
        background: var(--nu-50);
        border: 1px solid var(--nu-200);
        color: var(--nu-700);
        border-radius: 12px;
        padding: .8rem 1rem;
        font-size: .85rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
    }

    /* Ringkasan total */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }

    .summary-tile {
        background: var(--sf-card);
        border: 1px solid var(--sf-border);
        border-radius: var(--card-radius);
        padding: 1.25rem 1.4rem;
        box-shadow: var(--card-shadow);
    }

    .summary-label {
        font-family: var(--admin-font-mono);
        font-size: .6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: var(--tx-muted);
        margin-bottom: .5rem;
    }

    .summary-value {
        font-family: var(--admin-font-mono);
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        letter-spacing: -.03em;
        color: var(--nu-700);
    }

    .summary-value.gold { color: var(--gold-500); }
    .summary-value.green { color: var(--nu-500); }

    /* Kartu tabel kuota */
    .kuota-panel {
        background: var(--sf-card);
        border: 1px solid var(--sf-border);
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .panel-head {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--sf-border);
        background: var(--sf-page);
    }

    .panel-title {
        font-family: var(--admin-font-mono);
        font-size: 1rem;
        font-weight: 700;
        color: var(--nu-700);
        margin: 0 0 .2rem;
        letter-spacing: -.02em;
    }

    .panel-sub { font-size: .78rem; color: var(--tx-muted); margin: 0; }

    .kuota-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .85rem; }

    .kuota-table thead th {
        font-family: var(--admin-font-mono);
        font-size: .62rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--tx-muted);
        text-align: left;
        padding: .8rem 1.5rem;
        border-bottom: 1px solid var(--sf-border);
        background: var(--sf-card);
    }

    .kuota-table tbody td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--sf-border);
        color: var(--tx-secondary);
        vertical-align: middle;
    }

    .kuota-table tbody tr:last-child td { border-bottom: none; }
    .kuota-table tbody tr:hover { background: var(--sf-hover); }

    .jr-kode {
        font-family: var(--admin-font-mono);
        font-weight: 700;
        font-size: .72rem;
        color: #fff;
        padding: .2rem .5rem;
        border-radius: 6px;
        display: inline-block;
    }

    .jr-nama { font-weight: 600; color: var(--tx-primary); margin-top: .35rem; font-size: .85rem; }

    .kuota-input {
        width: 96px;
        font-family: var(--admin-font-mono);
        font-size: .95rem;
        font-weight: 700;
        color: var(--nu-700);
        text-align: center;
        padding: .5rem .6rem;
        border: 1px solid var(--sf-border);
        border-radius: 10px;
        background: var(--sf-page);
        outline: none;
        transition: all var(--transition);
    }

    .kuota-input:focus {
        border-color: var(--nu-500);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(5,122,60,.12);
    }

    .kuota-input.err { border-color: var(--st-error); box-shadow: 0 0 0 3px rgba(220,38,38,.1); }

    .fill-track {
        width: 100%;
        min-width: 120px;
        height: 6px;
        background: rgba(0,0,0,.06);
        border-radius: 99px;
        overflow: hidden;
        margin-top: .45rem;
    }

    .fill-bar { height: 100%; border-radius: 99px; transition: width .6s cubic-bezier(.4,0,.2,1); }

    .fill-meta {
        font-family: var(--admin-font-mono);
        font-size: .7rem;
        color: var(--tx-muted);
        margin-top: .35rem;
    }

    .fill-meta strong { color: var(--tx-secondary); }

    .actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1.1rem 1.5rem;
        border-top: 1px solid var(--sf-border);
        background: var(--sf-page);
    }

    .actions-note {
        font-family: var(--admin-font-mono);
        font-size: .7rem;
        color: var(--tx-muted);
    }

    .btn {
        font-family: var(--admin-font-sans);
        font-size: .82rem;
        font-weight: 600;
        padding: .6rem 1.25rem;
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
    .btn-primary:hover { background: var(--nu-600); transform: translateY(-1px); box-shadow: 0 6px 18px -4px rgba(2,75,35,.35); }

    .btn-ghost { background: var(--sf-card); border-color: var(--sf-border); color: var(--tx-secondary); }
    .btn-ghost:hover { border-color: var(--nu-500); color: var(--nu-500); background: var(--nu-50); }

    @media (max-width: 720px) {
        .kuota-table thead { display: none; }
        .kuota-table tbody td { display: block; padding: .6rem 1.25rem; border: none; }
        .kuota-table tbody tr { display: block; border-bottom: 1px solid var(--sf-border); padding: .75rem 0; }
    }
</style>
@endpush

@section('content')
<div class="kuota-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">Kelola Kuota</h1>
            <p class="page-sub">Atur kapasitas daya tampung setiap program keahlian. Jumlah ini menjadi acuan keterisian kursi pada dashboard dan rekap pendaftaran.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Dashboard
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success" role="status">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-success" style="background:var(--st-error-bg); border-color:#FCA5A5; color:var(--st-error);" role="alert">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Ringkasan --}}
    <div class="summary-grid">
        <div class="summary-tile">
            <div class="summary-label">Total Daya Tampung</div>
            <div class="summary-value">{{ number_format($totalKuota) }}</div>
        </div>
        <div class="summary-tile">
            <div class="summary-label">Total Pendaftar</div>
            <div class="summary-value gold">{{ number_format($totalPendaftar) }}</div>
        </div>
        <div class="summary-tile">
            <div class="summary-label">Sisa Kursi</div>
            <div class="summary-value green">{{ number_format(max(0, $totalKuota - $totalPendaftar)) }}</div>
        </div>
    </div>

    {{-- Tabel Kuota --}}
    <form method="POST" action="{{ route('admin.kuota.update') }}">
        @csrf
        @method('PUT')

        <div class="kuota-panel">
            <div class="panel-head">
                <h2 class="panel-title">Kapasitas per Program Keahlian</h2>
                <p class="panel-sub">Ubah angka pada kolom “Kuota”, lalu tekan Simpan Perubahan.</p>
            </div>

            <div style="overflow-x:auto;">
                <table class="kuota-table">
                    <thead>
                        <tr>
                            <th>Program Keahlian</th>
                            <th style="width:140px; text-align:center;">Kuota</th>
                            <th style="width:260px;">Keterisian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jurusan as $j)
                        @php
                            $pct = $j->persentase_terisi;
                            $barColor = $pct >= 100 ? 'var(--st-error)' : ($pct >= 75 ? 'var(--gold-500)' : 'var(--nu-500)');
                        @endphp
                        <tr>
                            <td>
                                <span class="jr-kode" style="background: {{ $pct >= 100 ? 'var(--st-error)' : 'var(--nu-600)' }};">{{ $j->kode }}</span>
                                <div class="jr-nama">{{ $j->nama }}</div>
                            </td>
                            <td style="text-align:center;">
                                <input type="number"
                                       class="kuota-input {{ $errors->has('kuota.'.$j->id) ? 'err' : '' }}"
                                       name="kuota[{{ $j->id }}]"
                                       id="kuota-{{ $j->id }}"
                                       value="{{ old('kuota.'.$j->id, $j->kuota) }}"
                                       min="0"
                                       max="1000"
                                       step="1"
                                       inputmode="numeric"
                                       aria-label="Kuota {{ $j->nama }}">
                                @error('kuota.'.$j->id)
                                    <div style="font-size:.7rem; color:var(--st-error); margin-top:.3rem;">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <div class="fill-track" role="img" aria-label="Keterisian {{ $pct }} persen">
                                    <div class="fill-bar" style="width: {{ min(100, $pct) }}%; background: {{ $barColor }};"></div>
                                </div>
                                <div class="fill-meta">
                                    <strong>{{ number_format($j->pendaftar_count) }}</strong> pendaftar &middot;
                                    sisa <strong>{{ number_format($j->sisa_kuota) }}</strong> kursi &middot; {{ $pct }}%
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align:center; padding:2.5rem; color:var(--tx-muted);">
                                Belum ada program keahlian terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="actions-bar">
                <span class="actions-note">Perubahan berlaku langsung untuk pelaporan &amp; dashboard.</span>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    <a href="{{ route('admin.kuota.index') }}" class="btn btn-ghost">Reset</a>
                    <button type="submit" class="btn btn-primary">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
