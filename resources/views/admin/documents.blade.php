@extends('layouts.admin')

@section('title', 'Verifikasi Berkas — Admin PPDB')

@push('head')
<style>
    .verify-page { max-width: 1100px; margin: 0 auto; }

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

    .page-sub { font-size: .85rem; color: var(--tx-muted); margin: 0; max-width: 560px; line-height: 1.5; }

    .alert-success {
        display: flex; align-items: center; gap: .6rem;
        background: var(--nu-50); border: 1px solid var(--nu-200); color: var(--nu-700);
        border-radius: 12px; padding: .8rem 1rem; font-size: .85rem; font-weight: 600;
        margin-bottom: 1.25rem;
    }

    .panel {
        background: var(--sf-card);
        border: 1px solid var(--sf-border);
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .data-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .85rem; }

    .data-table thead th {
        font-family: var(--admin-font-mono);
        font-size: .62rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: .1em; color: var(--tx-muted); text-align: left;
        padding: .8rem 1.25rem; border-bottom: 1px solid var(--sf-border); background: var(--sf-page);
    }

    .data-table tbody td {
        padding: .9rem 1.25rem; border-bottom: 1px solid var(--sf-border);
        color: var(--tx-secondary); vertical-align: middle;
    }

    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover { background: var(--sf-hover); }

    .td-mono { font-family: var(--admin-font-mono); font-weight: 600; color: var(--nu-500); font-size: .78rem; }
    .td-name { font-weight: 600; color: var(--tx-primary); }

    .badge {
        display: inline-flex; padding: .2rem .55rem; border-radius: 6px;
        font-family: var(--admin-font-mono); font-size: .63rem; font-weight: 600;
    }

    .badge.approved { background: var(--nu-50); color: var(--nu-500); }
    .badge.pending  { background: var(--gold-100); color: var(--gold-500); }
    .badge.revision,
    .badge.rejected { background: var(--st-error-bg); color: var(--st-error); }

    .doc-chips { display: flex; gap: .3rem; flex-wrap: wrap; }

    .doc-chip {
        font-family: var(--admin-font-mono); font-size: .6rem; font-weight: 600;
        padding: .15rem .4rem; border-radius: 5px; border: 1px solid var(--sf-border);
    }

    .doc-chip.ok { background: var(--nu-50); color: var(--nu-500); border-color: var(--nu-200); }
    .doc-chip.no { background: var(--sf-page); color: var(--tx-muted); }

    .row-actions { display: flex; gap: .35rem; justify-content: flex-end; flex-wrap: wrap; }

    .btn {
        font-family: var(--admin-font-sans); font-size: .76rem; font-weight: 600;
        padding: .45rem .8rem; border-radius: 9px; border: 1px solid transparent;
        cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: .35rem;
        transition: all var(--transition);
    }

    .btn-approve { background: var(--nu-500); color: #fff; }
    .btn-approve:hover { background: var(--nu-600); }
    .btn-reject { background: var(--sf-card); border-color: var(--sf-border); color: var(--st-error); }
    .btn-reject:hover { background: var(--st-error-bg); border-color: #FCA5A5; }
    .btn-ghost { background: var(--sf-card); border-color: var(--sf-border); color: var(--tx-secondary); }
    .btn-ghost:hover { border-color: var(--nu-500); color: var(--nu-500); }

    .empty { text-align: center; padding: 3rem 1rem; color: var(--tx-muted); font-size: .88rem; }

    /* Dialog penolakan sederhana berbasis <details> agar tanpa JS tambahan */
    .reject-form { display: flex; gap: .3rem; align-items: center; }
    .reject-input {
        width: 190px; font-family: var(--admin-font-sans); font-size: .74rem;
        padding: .4rem .6rem; border: 1px solid var(--sf-border); border-radius: 8px;
        outline: none; background: var(--sf-page);
    }
    .reject-input:focus { border-color: var(--st-error); background: #fff; }

    /* ── Panel berkas per pendaftar ── */
    .file-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: .75rem; flex-wrap: wrap;
        padding: .7rem 0; border-bottom: 1px dashed var(--sf-border);
    }
    .file-row:last-child { border-bottom: none; }

    .file-info { display: flex; align-items: center; gap: .6rem; min-width: 0; }
    .file-name { font-weight: 600; color: var(--tx-primary); font-size: .82rem; }
    .file-meta { font-family: var(--admin-font-mono); font-size: .68rem; color: var(--tx-muted); margin-top: .1rem; }

    .file-actions { display: flex; gap: .3rem; flex-wrap: wrap; }

    /* Tombol kecil khusus daftar berkas */
    .btn-xs {
        font-family: var(--admin-font-mono); font-size: .65rem; font-weight: 600;
        padding: .3rem .55rem; border-radius: 6px; border: 1px solid var(--sf-border);
        background: var(--sf-card); color: var(--tx-secondary); text-decoration: none;
        display: inline-flex; align-items: center; gap: .25rem; cursor: pointer;
        transition: all var(--transition);
    }
    .btn-xs:hover { border-color: var(--nu-500); color: var(--nu-500); }
    .btn-xs.drive { background: var(--nu-50); color: var(--nu-500); border-color: var(--nu-200); }
    .btn-xs.drive:hover { background: var(--nu-100, var(--nu-50)); }
    .btn-xs.off { opacity: .45; cursor: not-allowed; }

    .badge-drive { background: var(--nu-50); color: var(--nu-500); }
    .badge-local { background: var(--sf-page); color: var(--tx-muted); }
</style>
@endpush

@section('content')
<div class="verify-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">Verifikasi Berkas</h1>
            <p class="page-sub">Tinjau kelengkapan dokumen calon siswa, lalu setujui atau tolak dengan catatan perbaikan.</p>
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

    <div class="panel">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Pendaftaran</th>
                        <th>Nama Lengkap</th>
                        <th>Jurusan</th>
                        <th>Kelengkapan Berkas</th>
                        <th>Status</th>
                        <th>Penyimpanan</th>
                        <th style="text-align:right;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendaftar as $p)
                    @php
                        $st = $p->status_verifikasi;
                        $badgeClass = match($st) {
                            'approved' => 'approved',
                            'revision' => 'revision',
                            'rejected' => 'rejected',
                            default    => 'pending',
                        };
                        $badgeLabel = match($st) {
                            'approved' => 'Diterima',
                            'revision' => 'Revisi',
                            'rejected' => 'Ditolak',
                            default    => 'Menunggu',
                        };
                    @endphp
                    <tr>
                        <td class="td-mono">{{ $p->nomor_pendaftaran }}</td>
                        <td>
                            <a href="{{ route('admin.pendaftar.show', $p->id) }}" class="td-name" style="color:inherit; text-decoration:none; display:block;">{{ $p->nama_lengkap }}</a>
                            <div style="font-family:var(--admin-font-mono); font-size:.72rem; color:var(--tx-muted); margin-top:.2rem;">{{ $p->asal_sekolah }}</div>
                            @if ($p->email)
                            <div style="font-family:var(--admin-font-mono); font-size:.68rem; color:var(--tx-muted); margin-top:.15rem;">{{ $p->email }}</div>
                            @endif
                        </td>
                        <td>
                            @if ($p->jurusan1)
                                <span style="font-family:var(--admin-font-mono); font-size:.72rem; font-weight:600; padding:.15rem .45rem; border-radius:6px; background:var(--sf-page); border:1px solid var(--sf-border); color:var(--nu-700);">{{ $p->jurusan1->kode }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @php $dokumen = $p->dokumenList(); @endphp
                            <div class="doc-chips">
                                @foreach ($dokumen as $dok)
                                    <span class="doc-chip {{ $dok['ada'] ? 'ok' : 'no' }}">{{ $dok['label'] }} {{ $dok['ada'] ? '✓' : '—' }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                        <td>
                            @if ($p->drive_folder_id)
                                <span class="badge badge-drive" title="Dokumen tersalin ke Google Drive">Drive ✓</span>
                            @else
                                <span class="badge badge-local" title="Hanya tersimpan di server lokal">Lokal</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                {{-- Panel berkas: lihat / buka / unduh (lokal + Drive) --}}
                                <button type="button" class="btn btn-ghost"
                                        onclick="toggleBerkas('berkas-{{ $p->id }}')"
                                        aria-expanded="false"
                                        aria-controls="berkas-{{ $p->id }}">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                    Berkas
                                </button>

                                {{-- Setujui --}}
                                <form method="POST" action="{{ route('admin.dokumen.approve', $p->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-approve">
                                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        Setujui
                                    </button>
                                </form>

                                {{-- Tolak dengan alasan --}}
                                <form method="POST" action="{{ route('admin.dokumen.reject', $p->id) }}" class="reject-form">
                                    @csrf
                                    <input type="text" name="alasan" class="reject-input" placeholder="Alasan penolakan..." maxlength="1000">
                                    <button type="submit" class="btn btn-reject">Tolak</button>
                                </form>

                                <a href="{{ route('admin.pendaftar.edit', $p->id) }}" class="btn btn-ghost">Edit Data</a>
                            </div>
                        </td>
                    </tr>

                    {{-- Baris detail berkas (tersembunyi sampai tombol "Berkas" diklik) --}}
                    <tr id="berkas-{{ $p->id }}" hidden>
                        <td colspan="7" style="background:var(--sf-page); padding:0;">
                            <div style="padding:1rem 1.25rem;">
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-bottom:.6rem;">
                                    <div style="font-family:var(--admin-font-mono); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.1em; color:var(--tx-muted);">
                                        Dokumen Unggahan — {{ $p->nama_lengkap }}
                                    </div>
                                    @if ($p->drive_folder_id)
                                    <div style="display:flex; gap:.3rem; flex-wrap:wrap;">
                                        <a href="{{ \App\Support\GoogleDrive::folderUrl() }}" target="_blank" rel="noopener noreferrer" class="btn-xs drive">
                                            Buka Folder Drive Utama
                                        </a>
                                        @php $folderDokUrl = $p->drive_folder_id ? 'https://drive.google.com/drive/folders/' . $p->drive_folder_id : null; @endphp
                                        @if ($folderDokUrl)
                                        <a href="{{ $folderDokUrl }}" target="_blank" rel="noopener noreferrer" class="btn-xs drive">
                                            Folder Drive Siswa Ini
                                        </a>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                @foreach ($dokumen as $dok)
                                <div class="file-row">
                                    <div class="file-info">
                                        <span class="doc-chip {{ $dok['ada'] ? 'ok' : 'no' }}">{{ $dok['label'] }}</span>
                                        <div>
                                            <div class="file-name">{{ $dok['nama_berkas'] ?? 'Belum diunggah' }}</div>
                                            <div class="file-meta">
                                                @if ($dok['ada'])
                                                    {{ $dok['ukuran'] ?? '—' }}
                                                    @if ($dok['drive_id']) · <span style="color:var(--nu-500);">tersalin ke Google Drive</span>@else · <span>lokal saja</span>@endif
                                                @else
                                                    Tidak ada berkas
                                                @endif
                                            </div>
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
                                            <a href="{{ $dok['drive_download'] }}" target="_blank" rel="noopener noreferrer" class="btn-xs drive">Unduh Drive</a>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="empty">Belum ada berkas pendaftaran yang masuk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/**
 * Tampilkan/sembunyikan baris detail berkas milik satu pendaftar.
 */
function toggleBerkas(id) {
    const row = document.getElementById(id);
    const tombol = document.querySelector('[aria-controls="' + id + '"]');

    if (!row) return;

    const terbuka = !row.hidden;
    row.hidden = terbuka;

    if (tombol) tombol.setAttribute('aria-expanded', terbuka ? 'false' : 'true');
}
</script>
@endpush
