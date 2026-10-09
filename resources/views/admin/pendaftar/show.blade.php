@extends('layouts.admin')

@section('title', 'Detail Pendaftar ' . $pendaftar->nomor_pendaftaran . ' — Admin PPDB')

@php
    // Label & kelas badge status verifikasi.
    $statusMap = [
        'approved' => ['label' => 'Diterima',     'class' => 'approved', 'icon' => 'M4.5 12.75l6 6 9-13.5'],
        'rejected' => ['label' => 'Ditolak',      'class' => 'rejected', 'icon' => 'M6 18L18 6M6 6l12 12'],
        'revision' => ['label' => 'Perlu Revisi', 'class' => 'revision', 'icon' => 'M12 9v3.75m0 3h.008v.008H12v-.008z'],
        'pending'  => ['label' => 'Menunggu',     'class' => 'pending',  'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    $status = $statusMap[$pendaftar->status_verifikasi] ?? $statusMap['pending'];

    // Label mata pelajaran untuk nilai rapor (kunci mengikuti form langkah 3).
    $labelMapel = [
        'matematika'  => 'Matematika',
        'ipa_biologi' => 'IPA / Biologi',
        'ipa_fisika'  => 'IPA / Fisika',
        'ipa_kimia'   => 'IPA / Kimia',
        'b_indonesia' => 'Bahasa Indonesia',
        'b_inggris'   => 'Bahasa Inggris',
        'ips_ekonomi' => 'IPS / Ekonomi',
        'prakarya'    => 'Prakarya',
    ];

    $nilaiRapor = is_array($pendaftar->nilai_rapor) ? $pendaftar->nilai_rapor : [];
    $nilaiTerisi = collect($nilaiRapor)->filter(fn ($v) => $v !== null && $v !== '');
    $rataRata = $nilaiTerisi->isNotEmpty() ? round($nilaiTerisi->avg(), 2) : null;

    $minatLabel = [
        'angka' => 'Angka & Hitungan', 'finansial' => 'Keuangan & Perbankan',
        'administrasi' => 'Administrasi & Perkantoran', 'komunikasi' => 'Komunikasi & Layanan Bisnis',
        'kesehatan' => 'Kesehatan & Farmasi', 'biologi' => 'Sains & Biologi',
        'otomotif' => 'Otomotif & Mesin', 'teknik' => 'Teknik & Perbaikan',
    ][$pendaftar->minat] ?? ucfirst((string) $pendaftar->minat);

    $dokumen = $pendaftar->dokumenList();
@endphp

@push('head')
<style>
    .detail-page { max-width: 1080px; margin: 0 auto; }

    .page-head {
        display: flex; justify-content: space-between; align-items: flex-end;
        flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;
    }

    .page-title {
        font-family: var(--admin-font-mono); font-size: 1.5rem; font-weight: 700;
        color: var(--nu-700); letter-spacing: -.03em; margin: 0 0 .3rem;
    }

    .page-sub { font-size: .85rem; color: var(--tx-muted); margin: 0; max-width: 620px; line-height: 1.55; }

    .head-actions { display: flex; gap: .5rem; flex-wrap: wrap; }

    .btn {
        font-family: var(--admin-font-sans); font-size: .82rem; font-weight: 600;
        padding: .6rem 1.1rem; border-radius: 10px; border: 1px solid transparent;
        cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: .45rem;
        transition: all var(--transition);
    }

    .btn-primary { background: var(--nu-500); color: #fff; }
    .btn-primary:hover { background: var(--nu-600); }
    .btn-ghost { background: var(--sf-card); border-color: var(--sf-border); color: var(--tx-secondary); }
    .btn-ghost:hover { border-color: var(--nu-500); color: var(--nu-500); background: var(--nu-50); }
    .btn-wa { background: #16A34A; color: #fff; }
    .btn-wa:hover { filter: brightness(.94); }

    /* ── Kartu identitas pendaftar ── */
    .id-card {
        display: flex; align-items: center; gap: 1.1rem; flex-wrap: wrap;
        background: var(--sf-card); border: 1px solid var(--sf-border);
        border-radius: var(--card-radius); box-shadow: var(--card-shadow);
        padding: 1.35rem 1.5rem; margin-bottom: 1.25rem;
    }

    .avatar {
        width: 60px; height: 60px; border-radius: 16px; flex-shrink: 0;
        background: var(--nu-50); border: 1px solid var(--nu-200);
        display: flex; align-items: center; justify-content: center;
        font-family: var(--admin-font-mono); font-size: 1.35rem; font-weight: 700;
        color: var(--nu-600); letter-spacing: -.02em;
    }

    .id-main { flex: 1; min-width: 220px; }
    .id-name { font-family: var(--admin-font-mono); font-size: 1.2rem; font-weight: 700; color: var(--tx-primary); letter-spacing: -.02em; margin: 0 0 .25rem; }
    .id-nomor { font-family: var(--admin-font-mono); font-size: .82rem; font-weight: 600; color: var(--nu-500); }

    .chip-row { display: flex; gap: .4rem; flex-wrap: wrap; margin-top: .55rem; }

    .chip {
        font-family: var(--admin-font-mono); font-size: .66rem; font-weight: 600;
        padding: .22rem .55rem; border-radius: 7px;
        background: var(--sf-page); border: 1px solid var(--sf-border); color: var(--tx-secondary);
    }

    .badge {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .28rem .6rem; border-radius: 7px;
        font-family: var(--admin-font-mono); font-size: .66rem; font-weight: 600;
    }

    .badge.approved { background: var(--nu-50); color: var(--nu-500); }
    .badge.pending  { background: var(--gold-100); color: var(--gold-500); }
    .badge.revision,
    .badge.rejected { background: var(--st-error-bg); color: var(--st-error); }

    /* ── Panel seksi ── */
    .panel {
        background: var(--sf-card); border: 1px solid var(--sf-border);
        border-radius: var(--card-radius); box-shadow: var(--card-shadow);
        margin-bottom: 1.25rem; overflow: hidden;
    }

    .panel-head {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        flex-wrap: wrap; padding: 1rem 1.35rem; border-bottom: 1px solid var(--sf-border);
        background: var(--sf-page);
    }

    .panel-title {
        font-family: var(--admin-font-mono); font-size: .88rem; font-weight: 700;
        color: var(--nu-700); margin: 0; letter-spacing: -.01em;
        display: flex; align-items: center; gap: .5rem;
    }

    .panel-title .num {
        font-size: .66rem; font-weight: 700; color: #fff; background: var(--nu-500);
        width: 1.35rem; height: 1.35rem; border-radius: 7px;
        display: inline-flex; align-items: center; justify-content: center;
    }

    .panel-sub { font-size: .74rem; color: var(--tx-muted); margin: 0; }
    .panel-body { padding: 1.25rem 1.35rem; }

    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .95rem 1.75rem; }
    .detail-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }

    .field-item { min-width: 0; }
    .field-item.span-2 { grid-column: 1 / -1; }

    .field-key {
        font-family: var(--admin-font-mono); font-size: .62rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .1em; color: var(--tx-muted);
        margin-bottom: .2rem;
    }

    .field-val { font-size: .88rem; color: var(--tx-primary); font-weight: 500; line-height: 1.55; overflow-wrap: anywhere; }
    .field-val.mono { font-family: var(--admin-font-mono); font-weight: 600; letter-spacing: .04em; }
    .field-val.empty { color: var(--tx-muted); font-weight: 400; }

    a.inline-link { color: var(--nu-500); text-decoration: none; font-weight: 600; }
    a.inline-link:hover { text-decoration: underline; }

    /* ── Tabel nilai rapor ── */
    .mini-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .mini-table th {
        font-family: var(--admin-font-mono); font-size: .62rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .1em; color: var(--tx-muted);
        text-align: left; padding: .55rem .35rem; border-bottom: 1px solid var(--sf-border);
    }
    .mini-table td { padding: .55rem .35rem; border-bottom: 1px dashed var(--sf-border); color: var(--tx-secondary); }
    .mini-table tr:last-child td { border-bottom: none; }
    .mini-table .nilai { font-family: var(--admin-font-mono); font-weight: 700; color: var(--nu-600); text-align: right; }

    .empty-note { font-size: .84rem; color: var(--tx-muted); font-style: italic; }

    /* ── Berkas ── */
    .file-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: .75rem; flex-wrap: wrap; padding: .7rem 0; border-bottom: 1px dashed var(--sf-border);
    }
    .file-row:last-child { border-bottom: none; }
    .file-name { font-weight: 600; color: var(--tx-primary); font-size: .82rem; }
    .file-meta { font-family: var(--admin-font-mono); font-size: .68rem; color: var(--tx-muted); margin-top: .1rem; }
    .file-actions { display: flex; gap: .3rem; flex-wrap: wrap; }

    .btn-xs {
        font-family: var(--admin-font-mono); font-size: .65rem; font-weight: 600;
        padding: .3rem .55rem; border-radius: 6px; border: 1px solid var(--sf-border);
        background: var(--sf-card); color: var(--tx-secondary); text-decoration: none;
        display: inline-flex; align-items: center; gap: .25rem; cursor: pointer;
        transition: all var(--transition);
    }
    .btn-xs:hover { border-color: var(--nu-500); color: var(--nu-500); }
    .btn-xs.drive { background: var(--nu-50); color: var(--nu-500); border-color: var(--nu-200); }
    .btn-xs.off { opacity: .45; cursor: not-allowed; }

    .doc-chip {
        font-family: var(--admin-font-mono); font-size: .62rem; font-weight: 600;
        padding: .16rem .45rem; border-radius: 5px; border: 1px solid var(--sf-border);
    }
    .doc-chip.ok { background: var(--nu-50); color: var(--nu-500); border-color: var(--nu-200); }
    .doc-chip.no { background: var(--sf-page); color: var(--tx-muted); }

    .catatan {
        background: var(--st-error-bg); border: 1px solid #FCA5A5; color: #991B1B;
        border-radius: 12px; padding: .85rem 1rem; font-size: .84rem; line-height: 1.55;
    }
    .catatan strong { display: block; margin-bottom: .2rem; }

    @media (max-width: 760px) {
        .detail-grid, .detail-grid.cols-3 { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="detail-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">Detail Pendaftar</h1>
            <p class="page-sub">Seluruh data yang diisikan calon siswa pada formulir PPDB, termasuk kontak (email &amp; WhatsApp), orang tua, jurusan, nilai rapor, dan berkas.</p>
        </div>
        <div class="head-actions">
            <a href="{{ route('admin.participants') }}" class="btn btn-ghost">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <a href="{{ route('admin.documents') }}" class="btn btn-ghost">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                Verifikasi Berkas
            </a>
            <a href="{{ route('ppdb.formulir', $pendaftar->nomor_pendaftaran) }}" class="btn btn-primary">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm-4.5 9h6m-6 3.75h3.75"/></svg>
                Cetak Formulir (PDF)
            </a>
            <a href="{{ route('admin.pendaftar.edit', $pendaftar->id) }}" class="btn btn-ghost">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                Edit Data
            </a>
        </div>
    </div>

    {{-- Kartu identitas ringkas --}}
    <div class="id-card">
        @php
            $inisial = collect(explode(' ', trim($pendaftar->nama_lengkap)))
                ->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
        @endphp
        <div class="avatar" aria-hidden="true">{{ $inisial ?: '—' }}</div>

        <div class="id-main">
            <h2 class="id-name">{{ $pendaftar->nama_lengkap }}</h2>
            <div class="id-nomor">{{ $pendaftar->nomor_pendaftaran }}</div>
            <div class="chip-row">
                <span class="badge {{ $status['class'] }}">
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $status['icon'] }}"/></svg>
                    {{ $status['label'] }}
                </span>
                <span class="chip">{{ $pendaftar->is_luring ? 'Luring' : 'Daring' }}</span>
                @if ($pendaftar->jurusan1)
                    <span class="chip">{{ $pendaftar->jurusan1->kode }}</span>
                @endif
                @if ($pendaftar->jurusan2)
                    <span class="chip">{{ $pendaftar->jurusan2->kode }}</span>
                @endif
                <span class="chip">Periode {{ $pendaftar->periode }}</span>
            </div>
        </div>

        @if ($pendaftar->wa_url)
            <a href="{{ $pendaftar->wa_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-wa">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
                Chat WhatsApp
            </a>
        @endif
    </div>

    {{-- 1. Kontak --}}
    <div class="panel">
        <div class="panel-head">
            <h3 class="panel-title"><span class="num">1</span> Kontak &amp; Komunikasi</h3>
            <p class="panel-sub">Email &amp; nomor WhatsApp yang diisikan siswa</p>
        </div>
        <div class="panel-body">
            <div class="detail-grid cols-3">
                <div class="field-item">
                    <div class="field-key">Alamat Email</div>
                    <div class="field-val">
                        @if ($pendaftar->email)
                            <a href="mailto:{{ $pendaftar->email }}" class="inline-link">{{ $pendaftar->email }}</a>
                        @else
                            <span class="empty">—</span>
                        @endif
                    </div>
                    @if ($pendaftar->notifikasi_email_at)
                        <div class="field-key" style="margin-top:.4rem;">Email terkirim</div>
                        <div class="field-val" style="font-size:.8rem;">{{ $pendaftar->notifikasi_email_at->translatedFormat('d F Y, H:i') }} WIB</div>
                    @endif
                </div>

                <div class="field-item">
                    <div class="field-key">No. WhatsApp Siswa</div>
                    <div class="field-val mono">
                        @if ($pendaftar->nomor_wa)
                            <a href="{{ $pendaftar->wa_url }}" target="_blank" rel="noopener noreferrer" class="inline-link">{{ $pendaftar->nomor_wa }}</a>
                        @else
                            <span class="empty">—</span>
                        @endif
                    </div>
                </div>

                <div class="field-item">
                    <div class="field-key">No. HP Siswa</div>
                    <div class="field-val mono">
                        @if ($pendaftar->no_hp)
                            <a href="tel:{{ $pendaftar->no_hp }}" class="inline-link">{{ $pendaftar->no_hp }}</a>
                        @else
                            <span class="empty">—</span>
                        @endif
                    </div>
                </div>

                <div class="field-item">
                    <div class="field-key">No. HP Orang Tua / Wali</div>
                    <div class="field-val mono">
                        @if ($pendaftar->no_hp_ortu)
                            <a href="tel:{{ $pendaftar->no_hp_ortu }}" class="inline-link">{{ $pendaftar->no_hp_ortu }}</a>
                        @else
                            <span class="empty">—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Identitas Siswa --}}
    <div class="panel">
        <div class="panel-head">
            <h3 class="panel-title"><span class="num">2</span> Identitas Calon Siswa</h3>
            <p class="panel-sub">Sesuai Akta Kelahiran &amp; Kartu Keluarga</p>
        </div>
        <div class="panel-body">
            <div class="detail-grid">
                <div class="field-item span-2">
                    <div class="field-key">Nama Lengkap</div>
                    <div class="field-val" style="font-weight:700;">{{ $pendaftar->nama_lengkap }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">NIK</div>
                    <div class="field-val mono">{{ $pendaftar->nik ?: '—' }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">No. Kartu Keluarga (KK)</div>
                    <div class="field-val mono">{{ $pendaftar->no_kk ?: '—' }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">NISN</div>
                    <div class="field-val mono">{{ $pendaftar->nisn ?: '—' }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">Anak Ke</div>
                    <div class="field-val">{{ $pendaftar->anak_ke ?? '—' }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">Jumlah Saudara Kandung</div>
                    <div class="field-val">{{ $pendaftar->jumlah_saudara ?? '—' }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">Jenis Kelamin</div>
                    <div class="field-val">{{ $pendaftar->jenis_kelamin === 'L' ? 'Laki-laki' : ($pendaftar->jenis_kelamin === 'P' ? 'Perempuan' : '—') }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">Agama</div>
                    <div class="field-val">{{ $pendaftar->agama ?: '—' }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">Tempat Lahir</div>
                    <div class="field-val">{{ $pendaftar->tempat_lahir ?: '—' }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">Tanggal Lahir</div>
                    <div class="field-val">{{ $pendaftar->tanggal_lahir?->translatedFormat('d F Y') ?? '—' }}</div>
                </div>

                <div class="field-item span-2">
                    <div class="field-key">Asal Sekolah (SMP/MTs)</div>
                    <div class="field-val">{{ $pendaftar->asal_sekolah ?: '—' }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">Tamat SMP/MTs Tahun</div>
                    <div class="field-val">{{ $pendaftar->tamat_tahun ?: '—' }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">No. Ijazah</div>
                    <div class="field-val mono">{{ $pendaftar->no_ijazah ?: '—' }}</div>
                </div>

                <div class="field-item span-2">
                    <div class="field-key">Alamat Lengkap Tempat Tinggal</div>
                    <div class="field-val">{{ $pendaftar->alamat_lengkap ?: '—' }}</div>
                </div>

                <div class="field-item span-2">
                    <div class="field-key">Penghasilan Bulanan Orang Tua</div>
                    <div class="field-val">{{ $pendaftar->penghasilan_label ?: '—' }}</div>
                </div>

                <div class="field-item span-2">
                    <div class="field-key">Referensi Pendaftar</div>
                    <div class="field-val">{{ $pendaftar->referensi_pendaftar ?: '—' }}</div>
                </div>

                <div class="field-item">
                    <div class="field-key">Tanggal Daftar</div>
                    <div class="field-val">{{ $pendaftar->created_at?->translatedFormat('d F Y, H:i') ?? '—' }} WIB</div>
                </div>

                <div class="field-item">
                    <div class="field-key">Sumber Pendaftaran</div>
                    <div class="field-val">{{ $pendaftar->is_luring ? 'Input Manual (Luring)' : 'Formulir Daring' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Data Orang Tua --}}
    <div class="panel">
        <div class="panel-head">
            <h3 class="panel-title"><span class="num">3</span> Data Orang Tua / Wali</h3>
            <p class="panel-sub">Diambil dari Kartu Keluarga</p>
        </div>
        <div class="panel-body">
            <div class="detail-grid">
                <div class="field-item">
                    <div class="field-key">Nama Ayah</div>
                    <div class="field-val">{{ $pendaftar->nama_ayah ?: '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">Pendidikan Ayah</div>
                    <div class="field-val">{{ $pendaftar->ayah_pendidikan ?: '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">Pekerjaan Ayah</div>
                    <div class="field-val">{{ $pendaftar->pekerjaan_ayah ?: '—' }}</div>
                </div>
                <div class="field-item span-2">
                    <div class="field-key">TTL Ayah</div>
                    <div class="field-val">{{ $pendaftar->ayah_ttl ?: '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">Nama Ibu</div>
                    <div class="field-val">{{ $pendaftar->nama_ibu ?: '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">Pendidikan Ibu</div>
                    <div class="field-val">{{ $pendaftar->ibu_pendidikan ?: '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">Pekerjaan Ibu</div>
                    <div class="field-val">{{ $pendaftar->pekerjaan_ibu ?: '—' }}</div>
                </div>
                <div class="field-item span-2">
                    <div class="field-key">TTL Ibu</div>
                    <div class="field-val">{{ $pendaftar->ibu_ttl ?: '—' }}</div>
                </div>
                <div class="field-item span-2">
                    <div class="field-key">Alamat Orang Tua / Wali</div>
                    <div class="field-val">{{ $pendaftar->alamat_ortu_lengkap ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Bagian B, C, D Formulir SPMB --}}
    <div class="panel">
        <div class="panel-head">
            <h3 class="panel-title"><span class="num">4</span> Formulir SPMB — Bagian B, C, D</h3>
            <p class="panel-sub">Pendidikan, kesehatan, dan keterangan lain-lain</p>
        </div>
        <div class="panel-body">
            <div class="field-key" style="margin-bottom:.55rem;">B. Keterangan Pendidikan</div>
            <div class="detail-grid">
                <div class="field-item">
                    <div class="field-key">SMP/MTs Kecamatan</div>
                    <div class="field-val">{{ $pendaftar->smp_kecamatan ?: '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">SMP/MTs Kabupaten</div>
                    <div class="field-val">{{ $pendaftar->smp_kabupaten ?: '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">SMP/MTs Propinsi</div>
                    <div class="field-val">{{ $pendaftar->smp_propinsi ?: '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">Nomor dan Tahun Ijazah</div>
                    <div class="field-val mono">{{ $pendaftar->ijazah_nomor_tahun ?: '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">Lama Belajar</div>
                    <div class="field-val">{{ $pendaftar->lama_belajar ? $pendaftar->lama_belajar . ' Tahun' : '—' }}</div>
                </div>
            </div>

            @php
                $nilaiUjian = is_array($pendaftar->nilai_ujian) ? $pendaftar->nilai_ujian : [];
            @endphp
            @if (count(array_filter($nilaiUjian, fn ($v) => $v !== null && $v !== '')))
                <table class="mini-table" style="margin-top:.9rem;">
                    <thead>
                        <tr><th>Mata Pelajaran (Nilai Ujian)</th><th style="text-align:right;">Nilai</th></tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Models\Pendaftar::opsiNilaiUjian() as $key => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td class="nilai">{{ $nilaiUjian[$key] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty-note" style="margin-top:.75rem;">Siswa tidak mengisikan nilai ujian.</p>
            @endif

            <div class="field-key" style="margin:1.1rem 0 .55rem;">C. Keterangan Kesehatan</div>
            <div class="detail-grid cols-3">
                <div class="field-item">
                    <div class="field-key">Golongan Darah</div>
                    <div class="field-val">{{ $pendaftar->golongan_darah ?: '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">Tinggi Badan</div>
                    <div class="field-val">{{ $pendaftar->tinggi_badan ? $pendaftar->tinggi_badan . ' cm' : '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">Berat Badan</div>
                    <div class="field-val">{{ $pendaftar->berat_badan ? $pendaftar->berat_badan . ' kg' : '—' }}</div>
                </div>
                <div class="field-item span-2">
                    <div class="field-key">Penyakit yang Pernah Diderita</div>
                    <div class="field-val">{{ $pendaftar->penyakit_pernah_diderita ?: '—' }}</div>
                </div>
            </div>

            <div class="field-key" style="margin:1.1rem 0 .55rem;">D. Keterangan Lain-lain</div>
            <div class="detail-grid">
                <div class="field-item"><div class="field-key">Olah Raga</div><div class="field-val">{{ $pendaftar->hobi_olahraga ?: '—' }}</div></div>
                <div class="field-item"><div class="field-key">Kesenian</div><div class="field-val">{{ $pendaftar->hobi_kesenian ?: '—' }}</div></div>
                <div class="field-item"><div class="field-key">Keorganisasian</div><div class="field-val">{{ $pendaftar->hobi_organisasi ?: '—' }}</div></div>
                <div class="field-item"><div class="field-key">Prestasi yang Pernah Diraih</div><div class="field-val">{{ $pendaftar->hobi_prestasi ?: '—' }}</div></div>
                <div class="field-item"><div class="field-key">Lain – Lain</div><div class="field-val">{{ $pendaftar->hobi_lain ?: '—' }}</div></div>
                <div class="field-item"><div class="field-key">Ukuran Baju / Seragam</div><div class="field-val" style="font-weight:700;">{{ $pendaftar->ukuran_seragam ?: '—' }}</div></div>
            </div>
        </div>
    </div>

    {{-- 5. Pilihan Jurusan & Rekomendasi --}}
    <div class="panel">
        <div class="panel-head">
            <h3 class="panel-title"><span class="num">5</span> Pilihan Program Keahlian</h3>
            <p class="panel-sub">Pilihan siswa &amp; rekomendasi sistem</p>
        </div>
        <div class="panel-body">
            <div class="detail-grid">
                <div class="field-item">
                    <div class="field-key">Jurusan Pilihan 1</div>
                    <div class="field-val" style="font-weight:700;">
                        {{ $pendaftar->jurusan1 ? ($pendaftar->jurusan1->kode . ' — ' . $pendaftar->jurusan1->nama) : '—' }}
                    </div>
                </div>
                <div class="field-item">
                    <div class="field-key">Jurusan Pilihan 2</div>
                    <div class="field-val" style="font-weight:700;">
                        {{ $pendaftar->jurusan2 ? ($pendaftar->jurusan2->kode . ' — ' . $pendaftar->jurusan2->nama) : 'Tidak ada' }}
                    </div>
                </div>
                <div class="field-item">
                    <div class="field-key">Bidang Minat</div>
                    <div class="field-val">{{ $pendaftar->minat ? $minatLabel : '—' }}</div>
                </div>
                <div class="field-item">
                    <div class="field-key">Rekomendasi Sistem</div>
                    <div class="field-val">
                        @if ($pendaftar->rekomendasiJurusan)
                            {{ $pendaftar->rekomendasiJurusan->kode }} — {{ $pendaftar->rekomendasiJurusan->nama }}
                        @else
                            <span class="empty">Belum dihitung</span>
                        @endif
                    </div>
                </div>
            </div>

            @if (is_array($pendaftar->rekomendasi_skor) && count($pendaftar->rekomendasi_skor))
                <div style="margin-top:1.1rem;">
                    <div class="field-key" style="margin-bottom:.5rem;">Skor Kecocokan per Jurusan</div>
                    <table class="mini-table">
                        <thead>
                            <tr><th>Program Keahlian</th><th style="text-align:right;">Skor</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($pendaftar->rekomendasi_skor as $jurusanId => $skor)
                                @php $j = \App\Models\Jurusan::find($jurusanId); @endphp
                                <tr>
                                    <td>{{ $j ? ($j->kode . ' — ' . $j->nama) : 'Jurusan #' . $jurusanId }}</td>
                                    <td class="nilai">{{ number_format((float) $skor, 2) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- 6. Nilai Rapor --}}
    <div class="panel">
        <div class="panel-head">
            <h3 class="panel-title"><span class="num">6</span> Nilai Rapor</h3>
            <p class="panel-sub">Nilai rata-rata mapel yang diisikan siswa (opsional)</p>
        </div>
        <div class="panel-body">
            @if ($nilaiTerisi->isNotEmpty())
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <th style="text-align:right;">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($labelMapel as $key => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td class="nilai">{{ isset($nilaiRapor[$key]) && $nilaiRapor[$key] !== '' ? $nilaiRapor[$key] : '—' }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="font-weight:700; color:var(--nu-700);">Rata-rata</td>
                            <td class="nilai" style="color:var(--nu-700);">{{ $rataRata ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            @else
                <p class="empty-note">Siswa tidak mengisikan nilai rapor.</p>
            @endif
        </div>
    </div>

    {{-- 7. Berkas --}}
    <div class="panel">
        <div class="panel-head">
            <h3 class="panel-title"><span class="num">7</span> Berkas Unggahan</h3>
            <p class="panel-sub">Kartu Keluarga, Akta Kelahiran, dan Rapor</p>
        </div>
        <div class="panel-body">
            <div class="chip-row" style="margin:0 0 .85rem;">
                @foreach ($dokumen as $dok)
                    <span class="doc-chip {{ $dok['ada'] ? 'ok' : 'no' }}">{{ $dok['label'] }} {{ $dok['ada'] ? '✓' : '—' }}</span>
                @endforeach
            </div>

            @foreach ($dokumen as $dok)
                <div class="file-row">
                    <div>
                        <div class="file-name">{{ $dok['nama_berkas'] ?? 'Belum diunggah' }}</div>
                        <div class="file-meta">
                            <strong>{{ $dok['label'] }}</strong>
                            @if ($dok['ada'])
                                · {{ $dok['ukuran'] ?? '—' }}
                                @if ($dok['drive_id']) · <span style="color:var(--nu-500);">tersalin ke Drive</span>@else · lokal saja @endif
                            @else
                                · Tidak ada berkas
                            @endif
                        </div>
                    </div>
                    <div class="file-actions">
                        @if ($dok['ada'])
                            <a href="{{ $dok['url'] }}" target="_blank" rel="noopener" class="btn-xs">Lihat</a>
                            <a href="{{ $dok['url'] }}" download class="btn-xs">Unduh</a>
                        @else
                            <span class="btn-xs off" aria-disabled="true">Lihat</span>
                            <span class="btn-xs off" aria-disabled="true">Unduh</span>
                        @endif
                        @if ($dok['drive_view'])
                            <a href="{{ $dok['drive_view'] }}" target="_blank" rel="noopener noreferrer" class="btn-xs drive">Drive</a>
                        @endif
                    </div>
                </div>
            @endforeach

            @if ($pendaftar->drive_folder_id)
                <div style="margin-top:.85rem;">
                    <a href="https://drive.google.com/drive/folders/{{ $pendaftar->drive_folder_id }}" target="_blank" rel="noopener noreferrer" class="btn-xs drive">Buka Folder Drive Siswa Ini</a>
                </div>
            @endif
        </div>
    </div>

    {{-- 8. Status Verifikasi --}}
    <div class="panel">
        <div class="panel-head">
            <h3 class="panel-title"><span class="num">8</span> Status Verifikasi</h3>
            <p class="panel-sub">Hasil pemeriksaan panitia</p>
        </div>
        <div class="panel-body">
            <div class="detail-grid">
                <div class="field-item">
                    <div class="field-key">Status Saat Ini</div>
                    <div class="field-val">
                        <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                    </div>
                </div>
                <div class="field-item">
                    <div class="field-key">Terakhir Diperbarui</div>
                    <div class="field-val">{{ $pendaftar->updated_at?->translatedFormat('d F Y, H:i') ?? '—' }} WIB</div>
                </div>
            </div>

            @if ($pendaftar->catatan_verifikasi || $pendaftar->catatan_penolakan)
                <div class="catatan" style="margin-top:1.1rem;">
                    <strong>Catatan Panitia</strong>
                    {{ $pendaftar->catatan_verifikasi ?: $pendaftar->catatan_penolakan }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
