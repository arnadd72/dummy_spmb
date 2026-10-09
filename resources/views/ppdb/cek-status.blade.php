@extends('layouts.app')
@section('title', 'Cek Status Pendaftaran — PPDB')

@section('content')
<div class="form-page">
    <div class="container">
        <div class="form-card" style="max-width:560px; margin:0 auto;">
            <div style="text-align:center; margin-bottom:2rem;">
                <div style="width:4rem; height:4rem; background:#eff6ff; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="var(--color-primary)" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="form-page-title">Cek Status Pendaftaran</h1>
                <p class="form-page-subtitle">Masukkan nomor pendaftaran Anda untuk melihat status verifikasi.</p>
            </div>

            <form method="POST" action="{{ route('ppdb.cekStatusPost') }}" novalidate>
                @csrf
                <div class="form-group">
                    <label class="form-label" for="nomor_pendaftaran">Nomor Pendaftaran</label>
                    <input type="text" name="nomor_pendaftaran" id="nomor_pendaftaran"
                           class="form-control {{ $errors->has('nomor_pendaftaran') ? 'error' : '' }}"
                           value="{{ old('nomor_pendaftaran') }}"
                           placeholder="PPDB-2026-0001"
                           style="font-family:monospace; font-size:1.0625rem; letter-spacing:0.05em; text-transform:uppercase;"
                           required aria-required="true" autocomplete="off">
                    @error('nomor_pendaftaran')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/></svg>
                    Cek Status
                </button>
            </form>

            {{-- Result --}}
            @if (isset($pendaftar))
                @if ($pendaftar)
                <div style="margin-top:2rem; padding-top:2rem; border-top:1px solid var(--color-border);">
                    @php
                    $statusConfig = [
                        'pending'  => ['label' => 'Menunggu Verifikasi', 'class' => 'badge-blue',   'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'approved' => ['label' => 'Diterima',            'class' => 'badge-green',  'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'rejected' => ['label' => 'Ditolak',             'class' => 'badge-red',    'icon' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'revision' => ['label' => 'Perlu Revisi',        'class' => 'badge-yellow', 'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z'],
                    ];
                    $sc = $statusConfig[$pendaftar->status_verifikasi] ?? $statusConfig['pending'];
                    @endphp

                    <div style="text-align:center; margin-bottom:1.5rem;">
                        <div class="rekomendasi-badge {{ $sc['class'] }}" style="font-size:1rem; padding:0.5rem 1.25rem; margin-bottom:0.75rem;">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sc['icon'] }}"/></svg>
                            {{ $sc['label'] }}
                        </div>
                        <div style="font-size:1.25rem; font-weight:800; color:var(--color-primary-dark);">{{ $pendaftar->nomor_pendaftaran }}</div>
                    </div>

                    <dl style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; font-size:0.9rem;">
                        <div>
                            <dt style="font-size:0.75rem; font-weight:700; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.06em;">Nama</dt>
                            <dd style="font-weight:700; color:var(--color-text); margin:0;">{{ $pendaftar->nama_lengkap }}</dd>
                        </div>
                        <div>
                            <dt style="font-size:0.75rem; font-weight:700; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.06em;">Terdaftar</dt>
                            <dd style="font-weight:700; color:var(--color-text); margin:0;">{{ $pendaftar->created_at->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt style="font-size:0.75rem; font-weight:700; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.06em;">Jurusan Pilihan 1</dt>
                            <dd style="font-weight:700; color:var(--color-text); margin:0;">{{ $pendaftar->jurusan1?->kode }} — {{ $pendaftar->jurusan1?->nama ?? '—' }}</dd>
                        </div>
                        @if ($pendaftar->jurusan2)
                        <div>
                            <dt style="font-size:0.75rem; font-weight:700; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.06em;">Jurusan Pilihan 2</dt>
                            <dd style="font-weight:700; color:var(--color-text); margin:0;">{{ $pendaftar->jurusan2->kode }} — {{ $pendaftar->jurusan2->nama }}</dd>
                        </div>
                        @endif
                    </dl>

                    {{-- Unduh ulang formulir pendaftaran resmi (PDF) --}}
                    <div style="margin-top:1.25rem; padding:1rem 1.25rem; background:linear-gradient(135deg, #eff6ff, #f0fdf4); border:1px solid var(--color-border); border-radius:var(--radius-lg); text-align:center;">
                        <div style="font-weight:700; color:var(--color-primary-dark); margin-bottom:0.5rem;">Formulir Pendaftaran Anda</div>
                        <a href="{{ route('ppdb.formulir', $pendaftar->nomor_pendaftaran) }}" class="btn btn-accent" id="btn-formulir" aria-label="Cetak atau unduh formulir pendaftaran dalam bentuk PDF">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm-4.5 9h6m-6 3.75h3.75"/></svg>
                            Unduh Formulir (PDF)
                        </a>
                        <div style="font-size:0.8125rem; color:var(--color-text-muted); margin-top:0.5rem;">Cetak, tanda tangani, dan serahkan kepada panitia bersama berkas persyaratan.</div>
                    </div>

                    @if ($pendaftar->catatan_verifikasi)
                    <div class="alert alert-warning" style="margin-top:1.25rem;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                        <div><strong>Catatan Panitia:</strong> {{ $pendaftar->catatan_verifikasi }}</div>
                    </div>
                    @endif
                </div>
                @else
                <div class="alert alert-error" style="margin-top:1.5rem;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Nomor pendaftaran tidak ditemukan. Pastikan nomor yang Anda masukkan sudah benar.
                </div>
                @endif
            @endif

            <div style="text-align:center; margin-top:1.5rem;">
                <a href="{{ route('ppdb.index') }}" class="btn btn-ghost btn-sm">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
