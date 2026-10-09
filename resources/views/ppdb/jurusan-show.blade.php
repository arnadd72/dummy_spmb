@extends('layouts.app')

@section('title', $jurusan->nama . ' — SPMB SMK NU')

@section('content')
@php
    $initials = [
        'AKL' => 'AK', 'MPLB' => 'MP', 'FKK' => 'FK', 'TKR' => 'TK',
    ];
    $kode = strtoupper($jurusan->kode);
    $inisial = $initials[$kode] ?? substr($kode, 0, 2);
    $sisa = max(0, (int) $jurusan->kuota - (int) ($jurusan->pendaftar_count ?? 0));
    $persen = (int) $jurusan->kuota > 0 ? round(min(100, (($jurusan->pendaftar_count ?? 0) / (int) $jurusan->kuota) * 100), 1) : 0;
@endphp

<div style="background:linear-gradient(135deg,#0A3D1F,#057A3C); color:#fff; padding:3.5rem 1rem 2.5rem;">
    <div style="max-width:1080px; margin:0 auto;">
        <a href="{{ route('ppdb.index') }}" style="color:#d1fae5; font-size:.85rem; font-weight:600; text-decoration:none;">← Kembali ke Beranda</a>
        <div style="display:flex; gap:1.25rem; align-items:center; margin-top:1rem; flex-wrap:wrap;">
            <div style="width:72px; height:72px; border-radius:20px; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.5rem;">{{ $inisial }}</div>
            <div>
                <div style="font-size:.8rem; font-weight:700; letter-spacing:.12em; color:#a7f3d0;">PROGRAM KEAHLIAN · SPMB {{ $tahunAjaran }}</div>
                <h1 style="margin:.25rem 0; font-size:2rem; font-weight:800;">{{ $jurusan->nama }}</h1>
                <div style="font-size:.9rem; color:#d1fae5;">Kode Jurusan: <strong>{{ $kode }}</strong></div>
            </div>
        </div>
    </div>
</div>

<div style="max-width:1080px; margin:0 auto; padding:2rem 1rem 3rem;">
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem;" class="jurusan-grid">
        <div>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:1.75rem; margin-bottom:1.25rem;">
                <h2 style="font-size:1.15rem; font-weight:800; color:#0A3D1F; margin:0 0 .75rem;">Tentang Jurusan</h2>
                <p style="color:#374151; line-height:1.8; margin:0;">{{ $jurusan->deskripsi ?: 'Deskripsi jurusan belum diisi. Silakan hubungi panitia SPMB untuk informasi lengkap.' }}</p>
            </div>

            @if ($jurusan->prospek_karir)
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:1.75rem; margin-bottom:1.25rem;">
                <h2 style="font-size:1.15rem; font-weight:800; color:#0A3D1F; margin:0 0 .75rem;">Prospek Karier Lulusan</h2>
                <p style="color:#374151; line-height:1.8; margin:0;">{{ $jurusan->prospek_karir }}</p>
            </div>
            @endif

            @if (!empty($jurusan->kriteria))
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:1.75rem;">
                <h2 style="font-size:1.15rem; font-weight:800; color:#0A3D1F; margin:0 0 .75rem;">Kriteria & Keunggulan</h2>
                <ul style="color:#374151; line-height:1.8; margin:0; padding-left:1.2rem;">
                    @foreach ((array) $jurusan->kriteria as $key => $val)
                        <li><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($val) ? implode(', ', $val) : $val }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <div>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:1.5rem; margin-bottom:1.25rem;">
                <h3 style="font-size:1rem; font-weight:800; color:#0A3D1F; margin:0 0 1rem;">Daya Tampung {{ $tahunAjaran }}</h3>
                <div style="display:flex; justify-content:space-between; font-size:.85rem; color:#6b7280; margin-bottom:.35rem;">
                    <span>Terisi {{ $jurusan->pendaftar_count ?? 0 }} / {{ $jurusan->kuota }}</span>
                    <span>{{ $persen }}%</span>
                </div>
                <div style="height:10px; background:#e5e7eb; border-radius:99px; overflow:hidden; margin-bottom:.75rem;">
                    <div style="height:100%; width:{{ $persen }}%; background:linear-gradient(90deg,#057A3C,#0D9050); border-radius:99px;"></div>
                </div>
                <div style="font-size:.9rem; color:#374151;">Sisa kuota: <strong style="color:#057A3C;">{{ $sisa }} kursi</strong></div>
                <a href="{{ route('ppdb.step1') }}" style="display:block; text-align:center; margin-top:1rem; background:linear-gradient(135deg,#057A3C,#0D9050); color:#fff; font-weight:800; border-radius:12px; padding:.8rem; text-decoration:none;">Daftar Jurusan Ini</a>
            </div>

            @if (count($lainnya) > 0)
            <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:18px; padding:1.5rem;">
                <h3 style="font-size:.95rem; font-weight:800; color:#0A3D1F; margin:0 0 .85rem;">Jurusan Lainnya</h3>
                @foreach ($lainnya as $j)
                    <a href="{{ route('ppdb.jurusan.show', $j->kode) }}" style="display:block; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:.8rem 1rem; margin-bottom:.6rem; text-decoration:none;">
                        <div style="font-weight:700; color:#0A3D1F; font-size:.9rem;">{{ $j->nama }}</div>
                        <div style="font-size:.78rem; color:#6b7280;">{{ $j->kode }} · Kuota {{ $j->kuota }}</div>
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<style>
@media (max-width: 860px) {
    .jurusan-grid { grid-template-columns: 1fr !important; }
}
</style>
@endsection
