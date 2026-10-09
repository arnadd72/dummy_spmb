@extends('layouts.admin')
@section('title', 'Dashboard PPDB 2027 — SMK Ma\'arif NU 1 Wangon')

@push('head')
<style>
    /* ─── Dashboard Specific ─── */
    .dash-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .dash-title {
        font-family: var(--admin-font-mono);
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--nu-700);
        letter-spacing: -.03em;
        margin: 0 0 .3rem;
        line-height: 1.2;
    }

    .dash-subtitle {
        font-size: .85rem;
        color: var(--tx-muted);
        margin: 0;
        max-width: 500px;
        line-height: 1.5;
    }

    .dash-actions {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .btn-refresh {
        font-family: var(--admin-font-sans);
        padding: .5rem 1rem;
        background: var(--sf-card);
        border: 1px solid var(--sf-border);
        border-radius: 10px;
        font-weight: 600;
        font-size: .8rem;
        color: var(--tx-secondary);
        cursor: pointer;
        transition: all var(--transition);
    }

    .btn-refresh:hover {
        border-color: var(--nu-500);
        color: var(--nu-500);
        background: var(--nu-50);
    }

    .badge-demo {
        font-family: var(--admin-font-mono);
        padding: .3rem .65rem;
        background: var(--gold-100);
        border: 1px solid rgba(217,119,6,.3);
        color: var(--gold-500);
        border-radius: 8px;
        font-size: .7rem;
        font-weight: 600;
        letter-spacing: .04em;
    }

    /* ─── Hero Card (Welcome) ─── */
    .bento-hero {
        grid-column: span 8;
        background: linear-gradient(135deg, var(--nu-700) 0%, var(--nu-500) 50%, #0D9050 100%);
        color: #fff;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .bento-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse at 20% 80%, rgba(245,158,11,.12) 0%, transparent 50%),
            radial-gradient(ellipse at 80% 20%, rgba(255,255,255,.06) 0%, transparent 40%);
        pointer-events: none;
    }

    .hero-eyebrow {
        font-family: var(--admin-font-mono);
        font-size: .65rem;
        font-weight: 600;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: rgba(255,255,255,.65);
        margin-bottom: .75rem;
    }

    .hero-heading {
        font-family: var(--admin-font-mono);
        font-size: 1.35rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.3;
        letter-spacing: -.02em;
        margin: 0 0 .6rem;
        max-width: 500px;
    }

    .hero-desc {
        font-size: .88rem;
        color: rgba(255,255,255,.8);
        line-height: 1.6;
        max-width: 520px;
        margin: 0 0 1.5rem;
    }

    .hero-cta-group {
        display: flex;
        gap: .6rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .hero-cta {
        font-family: var(--admin-font-sans);
        display: inline-block;
        padding: .6rem 1.25rem;
        border-radius: 10px;
        font-size: .82rem;
        font-weight: 600;
        text-decoration: none;
        transition: all var(--transition);
        cursor: pointer;
    }

    .hero-cta-primary {
        background: #fff;
        color: var(--nu-700);
    }

    .hero-cta-primary:hover {
        background: var(--gold-100);
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(0,0,0,.2);
    }

    .hero-cta-ghost {
        background: rgba(255,255,255,.12);
        color: rgba(255,255,255,.9);
        border: 1px solid rgba(255,255,255,.2);
    }

    .hero-cta-ghost:hover {
        background: rgba(255,255,255,.2);
    }

    /* ─── OCR Status Card ─── */
    .bento-ocr-card {
        grid-column: span 4;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .ocr-label {
        font-family: var(--admin-font-mono);
        font-size: .6rem;
        font-weight: 600;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--tx-muted);
    }

    .ocr-status-badge {
        font-family: var(--admin-font-mono);
        font-size: .65rem;
        font-weight: 600;
        padding: .2rem .55rem;
        border-radius: 6px;
        background: var(--nu-50);
        color: var(--nu-500);
    }

    .ocr-title {
        font-family: var(--admin-font-mono);
        font-size: 1rem;
        font-weight: 700;
        color: var(--nu-700);
        margin: .6rem 0 .35rem;
        letter-spacing: -.02em;
    }

    .ocr-desc {
        font-size: .78rem;
        color: var(--tx-muted);
        line-height: 1.5;
    }

    .ocr-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .5rem;
        margin-top: auto;
        padding-top: 1rem;
    }

    .ocr-stat-box {
        background: var(--sf-page);
        border: 1px solid var(--sf-border);
        border-radius: 12px;
        padding: .75rem;
    }

    .ocr-stat-label {
        font-family: var(--admin-font-mono);
        font-size: .55rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--tx-muted);
        margin-bottom: .3rem;
    }

    .ocr-stat-value {
        font-family: var(--admin-font-mono);
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--nu-500);
        line-height: 1;
    }

    .ocr-stat-value.gold {
        color: var(--gold-500);
    }

    /* ─── Metric Tiles ─── */
    .metric-tile {
        grid-column: span 3;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 140px;
    }

    .metric-label {
        font-family: var(--admin-font-mono);
        font-size: .6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: var(--tx-muted);
        margin-bottom: .75rem;
    }

    .metric-value {
        font-family: var(--admin-font-mono);
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: .5rem;
        letter-spacing: -.03em;
    }

    .metric-value.emerald { color: var(--nu-700); }
    .metric-value.gold    { color: var(--gold-500); }
    .metric-value.green   { color: var(--nu-500); }
    .metric-value.red     { color: var(--st-error); }

    .metric-caption {
        font-size: .78rem;
        color: var(--tx-muted);
        font-weight: 500;
    }

    /* ─── Kuota Section ─── */
    .kuota-card {
        grid-column: span 7;
    }

    .kuota-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.25rem;
    }

    .kuota-title {
        font-family: var(--admin-font-mono);
        font-size: 1rem;
        font-weight: 700;
        color: var(--nu-700);
        letter-spacing: -.02em;
        margin: 0 0 .25rem;
    }

    .kuota-subtitle {
        font-size: .78rem;
        color: var(--tx-muted);
    }

    .kuota-total {
        font-family: var(--admin-font-mono);
        font-size: .68rem;
        font-weight: 600;
        padding: .25rem .6rem;
        border-radius: 8px;
        background: var(--nu-50);
        color: var(--nu-700);
        border: 1px solid var(--sf-border);
        white-space: nowrap;
    }

    .kuota-list {
        display: flex;
        flex-direction: column;
        gap: .6rem;
    }

    .kuota-row {
        background: var(--sf-page);
        border: 1px solid var(--sf-border);
        border-radius: 12px;
        padding: .85rem 1rem;
        transition: all var(--transition);
    }

    .kuota-row:hover {
        border-color: var(--sf-border-h);
        background: var(--sf-hover);
    }

    .kuota-row-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .4rem;
    }

    .kuota-kode {
        font-family: var(--admin-font-mono);
        font-weight: 700;
        font-size: .72rem;
        color: #fff;
        padding: .15rem .5rem;
        border-radius: 6px;
    }

    .kuota-nama {
        font-weight: 600;
        font-size: .85rem;
        color: var(--tx-primary);
        margin-left: .5rem;
    }

    .kuota-count {
        font-family: var(--admin-font-mono);
        font-size: .78rem;
        font-weight: 600;
        color: var(--tx-secondary);
    }

    .kuota-bar-track {
        width: 100%;
        height: 6px;
        background: rgba(0,0,0,.06);
        border-radius: 99px;
        overflow: hidden;
    }

    .kuota-bar-fill {
        height: 100%;
        border-radius: 99px;
        transition: width .8s cubic-bezier(.4,0,.2,1);
    }

    .kuota-sisa {
        font-family: var(--admin-font-mono);
        font-size: .66rem;
        color: var(--tx-muted);
        margin-top: .35rem;
    }

    /* ─── Actions Card ─── */
    .actions-card {
        grid-column: span 5;
        display: flex;
        flex-direction: column;
    }

    .actions-title {
        font-family: var(--admin-font-mono);
        font-size: 1rem;
        font-weight: 700;
        color: var(--nu-700);
        letter-spacing: -.02em;
        margin: 0 0 .25rem;
    }

    .actions-subtitle {
        font-size: .78rem;
        color: var(--tx-muted);
        margin: 0 0 1.25rem;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .5rem;
        flex: 1;
    }

    .action-tile {
        background: var(--sf-page);
        border: 1px solid var(--sf-border);
        border-radius: 12px;
        padding: 1rem;
        text-align: left;
        cursor: pointer;
        transition: all var(--transition);
        font-family: var(--admin-font-sans);
        text-decoration: none;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .action-tile:hover {
        border-color: var(--nu-500);
        background: var(--nu-50);
        transform: translateY(-1px);
    }

    .action-tile-icon {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        background: var(--nu-50);
        color: var(--nu-500);
        border: 1px solid var(--sf-border);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: .6rem;
        transition: all var(--transition);
    }

    .action-tile:hover .action-tile-icon {
        background: var(--nu-500);
        color: #fff;
        border-color: var(--nu-500);
    }

    .action-tile-name {
        font-family: var(--admin-font-mono);
        font-weight: 700;
        font-size: .82rem;
        color: var(--nu-700);
        margin-bottom: .2rem;
    }

    .action-tile-desc {
        font-size: .72rem;
        color: var(--tx-muted);
        line-height: 1.4;
    }

    .actions-footer {
        margin-top: .75rem;
        padding: .65rem .85rem;
        background: var(--sf-page);
        border-radius: 10px;
        border: 1px solid var(--sf-border);
        font-family: var(--admin-font-mono);
        font-size: .68rem;
        color: var(--tx-muted);
    }

    /* ─── Table Card ─── */
    .table-card {
        grid-column: span 12;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .table-title {
        font-family: var(--admin-font-mono);
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--nu-700);
        letter-spacing: -.02em;
        margin: 0 0 .2rem;
    }

    .table-subtitle {
        font-size: .78rem;
        color: var(--tx-muted);
        margin: 0;
    }

    .table-count {
        font-family: var(--admin-font-mono);
        font-size: .72rem;
        font-weight: 600;
        padding: .3rem .65rem;
        border-radius: 8px;
        background: var(--sf-page);
        border: 1px solid var(--sf-border);
        color: var(--nu-700);
        white-space: nowrap;
    }

    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: .82rem;
    }

    .data-table thead th {
        font-family: var(--admin-font-mono);
        font-size: .65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--tx-muted);
        padding: .75rem 1rem;
        border-bottom: 1px solid var(--sf-border);
        text-align: left;
        background: var(--sf-page);
        position: sticky;
        top: 0;
    }

    .data-table tbody tr {
        transition: background var(--transition);
    }

    .data-table tbody tr:hover {
        background: var(--sf-hover);
    }

    .data-table tbody td {
        padding: .85rem 1rem;
        border-bottom: 1px solid var(--sf-border);
        color: var(--tx-secondary);
    }

    .td-mono {
        font-family: var(--admin-font-mono);
        font-weight: 600;
        color: var(--nu-500);
        font-size: .78rem;
    }

    .td-name {
        font-weight: 600;
        color: var(--tx-primary);
    }

    .td-badge {
        display: inline-flex;
        padding: .2rem .55rem;
        border-radius: 6px;
        font-family: var(--admin-font-mono);
        font-size: .65rem;
        font-weight: 600;
    }

    .td-badge.approved {
        background: var(--nu-50);
        color: var(--nu-500);
    }

    .td-badge.pending {
        background: var(--gold-100);
        color: var(--gold-500);
    }

    .td-badge.revision,
    .td-badge.rejected {
        background: var(--st-error-bg);
        color: var(--st-error);
    }

    .td-jurusan {
        font-family: var(--admin-font-mono);
        font-size: .72rem;
        font-weight: 600;
        padding: .15rem .45rem;
        border-radius: 6px;
        background: var(--sf-page);
        border: 1px solid var(--sf-border);
        color: var(--nu-700);
    }

    .td-action-link {
        font-family: var(--admin-font-mono);
        font-size: .72rem;
        font-weight: 600;
        color: var(--nu-500);
        text-decoration: none;
        padding: .3rem .6rem;
        border-radius: 8px;
        border: 1px solid var(--sf-border);
        transition: all var(--transition);
        display: inline-block;
    }

    .td-action-link:hover {
        background: var(--nu-500);
        color: #fff;
        border-color: var(--nu-500);
    }

    .table-empty {
        text-align: center;
        padding: 3rem;
        color: var(--tx-muted);
        font-size: .88rem;
    }
</style>
@endpush

@section('content')
<div style="max-width: 1400px; margin: 0 auto;">

    {{-- Header --}}
    <div class="dash-header">
        <div>
            <h1 class="dash-title">Dashboard PPDB</h1>
            <p class="dash-subtitle">Pantau statistik pendaftaran, verifikasi berkas, dan kuota program keahlian secara real-time.</p>
        </div>
        <div class="dash-actions">
            @if($isDemo)
            <span class="badge-demo">PRATINJAU</span>
            @endif
            <button onclick="window.location.reload();" class="btn-refresh">
                Muat Ulang
            </button>
        </div>
    </div>

    {{-- Bento Grid --}}
    <div class="bento-grid">

        {{-- 1. Hero Welcome ─ Span 8 --}}
        <div class="bento-card bento-hero">
            <div style="position: relative; z-index: 1;">
                <div class="hero-eyebrow">Pusat Kendali &middot; TA {{ $tahunAjaran ?? '2027/2028' }}</div>
                <h2 class="hero-heading">Penerimaan Peserta Didik Baru SMK Ma'arif NU 1 Wangon</h2>
                <p class="hero-desc">SMK Pusat Keunggulan terakreditasi A dengan 4 program keahlian unggulan. Sistem aktif melayani pendaftaran daring dan luring.</p>
                <div class="hero-cta-group">
                    <a href="#tabel-pendaftar" class="hero-cta hero-cta-primary">Verifikasi Berkas &darr;</a>
                    <a href="{{ route('ppdb.index') }}" target="_blank" class="hero-cta hero-cta-ghost">Portal Siswa &nearr;</a>
                </div>
            </div>
        </div>

        {{-- 2. OCR Status ─ Span 4 --}}
        <div class="bento-card bento-ocr-card" id="bento-ocr">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: .6rem;">
                    <span class="ocr-label">Status OCR</span>
                    <span class="ocr-status-badge">Online</span>
                </div>
                <div class="ocr-title">PaddleOCR Engine</div>
                <p class="ocr-desc">Pemindaian otomatis teks Akta Kelahiran dan Kartu Keluarga pada perangkat.</p>
            </div>

            <div class="ocr-stats">
                <div class="ocr-stat-box">
                    <div class="ocr-stat-label">Rata-rata</div>
                    <div class="ocr-stat-value">~0.82s</div>
                </div>
                <div class="ocr-stat-box">
                    <div class="ocr-stat-label">Akurasi</div>
                    <div class="ocr-stat-value gold">99.4%</div>
                </div>
            </div>
        </div>

        {{-- 3. Four Metric Tiles ─ Span 3 each --}}
        <div class="bento-card metric-tile">
            <div class="metric-label">Total Pendaftar</div>
            <div class="metric-value emerald">{{ number_format($totalPendaftar) }}</div>
            <div class="metric-caption">Terdaftar di database</div>
        </div>

        <div class="bento-card metric-tile">
            <div class="metric-label">Menunggu Review</div>
            <div class="metric-value gold">{{ number_format($menungguVerifikasi) }}</div>
            <div class="metric-caption">Perlu validasi panitia</div>
        </div>

        <div class="bento-card metric-tile">
            <div class="metric-label">Diterima</div>
            <div class="metric-value green">{{ number_format($diterima) }}</div>
            <div class="metric-caption">Dokumen memenuhi syarat</div>
        </div>

        <div class="bento-card metric-tile">
            <div class="metric-label">Perlu Revisi</div>
            <div class="metric-value red">{{ number_format($revisi) }}</div>
            <div class="metric-caption">Berkas belum valid</div>
        </div>

        {{-- 4. Kuota Jurusan ─ Span 7 --}}
        <div class="bento-card kuota-card">
            <div class="kuota-header">
                <div>
                    <h3 class="kuota-title">Distribusi Kuota Jurusan</h3>
                    <div class="kuota-subtitle">Kapasitas per program keahlian TA {{ $tahunAjaran ?? '2027/2028' }}</div>
                </div>
                <span class="kuota-total">Total: {{ number_format($totalKuota) }} Kursi</span>
            </div>

            <div class="kuota-list">
                @foreach($jurusanList as $jur)
                @php
                    $pct = $jur['persentase'] ?? 0;
                    $barColor = match($jur['kode']) {
                        'TKR' => 'var(--nu-500)',
                        'FKK' => 'var(--gold-500)',
                        'AKL' => 'var(--nu-400)',
                        default => 'var(--st-info)',
                    };
                @endphp
                <div class="kuota-row">
                    <div class="kuota-row-top">
                        <div style="display: flex; align-items: center;">
                            <span class="kuota-kode" style="background: {{ $barColor }};">{{ $jur['kode'] }}</span>
                            <span class="kuota-nama">{{ $jur['nama'] }}</span>
                        </div>
                        <span class="kuota-count">{{ $jur['pendaftar_count'] }} / {{ $jur['kuota'] }}</span>
                    </div>
                    <div class="kuota-bar-track">
                        <div class="kuota-bar-fill" style="width: {{ $pct }}%; background: {{ $barColor }};"></div>
                    </div>
                    <div class="kuota-sisa">Sisa {{ number_format($jur['sisa_kuota'] ?? 0) }} kursi &middot; {{ $pct }}% terisi</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- 5. Actions ─ Span 5 --}}
        <div class="bento-card actions-card">
            <div>
                <h3 class="actions-title">Aksi & Ekspor</h3>
                <p class="actions-subtitle">Operasional administrasi dan pelaporan</p>
            </div>

            <div class="actions-grid">
                <a href="{{ route('admin.export.xls') }}" class="action-tile">
                    <span class="action-tile-icon" aria-hidden="true">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </span>
                    <div class="action-tile-name">Ekspor XLS</div>
                    <div class="action-tile-desc">Unduh rekapitulasi data pendaftar</div>
                </a>

                <a href="{{ route('admin.kuota.index') }}" class="action-tile">
                    <span class="action-tile-icon" aria-hidden="true">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                    </span>
                    <div class="action-tile-name">Kelola Kuota</div>
                    <div class="action-tile-desc">Atur kapasitas daya tampung</div>
                </a>

                <a href="{{ route('admin.manual.create') }}" class="action-tile">
                    <span class="action-tile-icon" aria-hidden="true">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    </span>
                    <div class="action-tile-name">Input Manual</div>
                    <div class="action-tile-desc">Buka formulir pendaftaran</div>
                </a>
            </div>

            {{-- Statistik pendaftar per tahun --}}
            @if (!empty($statistikTahun) && count($statistikTahun) > 0)
            <div style="margin-top:1.25rem; padding-top:1.25rem; border-top:1px dashed var(--sf-border);">
                <div style="font-size:.82rem; font-weight:800; color:var(--tx-primary); margin-bottom:.75rem;">Jumlah Pendaftar per Tahun</div>
                @php $maksTahun = max(1, collect($statistikTahun)->max('jumlah')); @endphp
                @foreach ($statistikTahun as $st)
                <div style="display:flex; align-items:center; gap:.6rem; margin-bottom:.5rem;">
                    <span style="width:52px; font-size:.78rem; font-weight:700; color:var(--tx-secondary);">{{ $st->tahun }}</span>
                    <div style="flex:1; height:10px; background:#eef2f0; border-radius:99px; overflow:hidden;">
                        <div style="height:100%; width:{{ round(($st->jumlah / $maksTahun) * 100) }}%; background:linear-gradient(90deg,#057A3C,#0D9050); border-radius:99px;"></div>
                    </div>
                    <span style="min-width:44px; text-align:right; font-size:.78rem; font-weight:800; color:var(--nu-600);">{{ number_format($st->jumlah) }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <div class="actions-footer">
                Database diamankan dan disinkronkan berkala.
            </div>
        </div>

        {{-- 6. Recent Registrants Table ─ Span 12 --}}
        <div class="bento-card table-card" id="tabel-pendaftar">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Pendaftar Terbaru</h3>
                    <p class="table-subtitle">Registrasi yang memerlukan verifikasi kelengkapan berkas</p>
                </div>
                <span class="table-count">{{ count($recentPendaftar) }} siswa</span>
            </div>

            <div style="overflow-x: auto; border-radius: 12px; border: 1px solid var(--sf-border);">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Reg</th>
                            <th>Nama Lengkap</th>
                            <th>NISN</th>
                            <th>Asal Sekolah</th>
                            <th>Jurusan</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPendaftar as $p)
                        <tr>
                            <td class="td-mono">{{ $p->nomor_pendaftaran }}</td>
                            <td class="td-name">{{ $p->nama_lengkap }}</td>
                            <td style="font-family: var(--admin-font-mono); font-size: .78rem; color: var(--tx-muted);">{{ $p->nisn }}</td>
                            <td>{{ $p->asal_sekolah ?? '—' }}</td>
                            <td>
                                <span class="td-jurusan">{{ $p->jurusan1->kode ?? '—' }}</span>
                            </td>
                            <td style="font-size: .78rem; color: var(--tx-muted);">
                                {{ $p->created_at ? $p->created_at->diffForHumans() : 'Baru saja' }}
                            </td>
                            <td>
                                @php
                                    $st = $p->status_verifikasi;
                                    $badgeClass = match($st) {
                                        'approved' => 'approved',
                                        'revision' => 'revision',
                                        'rejected' => 'rejected',
                                        default => 'pending',
                                    };
                                    $badgeLabel = match($st) {
                                        'approved' => 'Diterima',
                                        'revision' => 'Revisi',
                                        'rejected' => 'Ditolak',
                                        default => 'Menunggu',
                                    };
                                @endphp
                                <span class="td-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                            </td>
                            <td style="text-align: center;">
                                @if (!empty($p->id))
                                    <a href="{{ route('admin.pendaftar.show', $p->id) }}" class="td-action-link">Detail</a>
                                @else
                                    <a href="{{ route('ppdb.cekStatus') }}?nomor={{ $p->nomor_pendaftaran }}" target="_blank" class="td-action-link">Detail</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="table-empty">
                                Belum ada berkas pendaftaran yang masuk.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
