<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Data Pendaftar PPDB {{ $tahunAjaran ?? '2027/2028' }}</title>
    <style>
        /* DomPDF tidak mendukung flexbox/grid modern, jadi layout memakai tabel. */
        * { box-sizing: border-box; }

        @page { margin: 18mm 14mm; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #0F1F15;
            margin: 0;
        }

        .kop {
            border-bottom: 2px solid #057A3C;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .kop-sekolah {
            font-size: 14px;
            font-weight: bold;
            color: #024B23;
            margin: 0;
        }

        .kop-sub {
            font-size: 9px;
            color: #3D5A47;
            margin: 2px 0 0;
        }

        .kop-judul {
            font-size: 11px;
            font-weight: bold;
            color: #057A3C;
            margin: 10px 0 2px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .kop-meta {
            font-size: 8px;
            color: #7A9484;
            margin: 0;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table.data thead th {
            background: #057A3C;
            color: #FFFFFF;
            font-size: 7.5px;
            font-weight: bold;
            text-align: left;
            padding: 5px 4px;
            border: 1px solid #024B23;
            text-transform: uppercase;
        }

        table.data tbody td {
            padding: 4px;
            border: 1px solid #DCE8E0;
            font-size: 7.5px;
            vertical-align: top;
            color: #3D5A47;
        }

        table.data tbody tr:nth-child(even) td { background: #F7FAF8; }

        .mono { font-family: DejaVu Sans Mono, monospace; font-weight: bold; color: #057A3C; }
        .nama { font-weight: bold; color: #0F1F15; }

        .footer {
            margin-top: 12px;
            font-size: 7.5px;
            color: #7A9484;
            text-align: center;
            border-top: 1px solid #DCE8E0;
            padding-top: 6px;
        }

        .ringkas {
            margin-top: 8px;
            font-size: 8px;
            color: #3D5A47;
        }
    </style>
</head>
<body>

    {{-- Kop laporan --}}
    <div class="kop">
        <p class="kop-sekolah">SMK Ma'arif NU 1 Wangon</p>
        <p class="kop-sub">SMK Pusat Keunggulan &middot; Akreditasi A &middot; NPSN 20330450<br>
            Jl. Karangjengkol, Wangon, Kab. Banyumas, Jawa Tengah 53176</p>
        <p class="kop-judul">Rekapitulasi Data Pendaftar PPDB</p>
        <p class="kop-meta">
            Tahun Ajaran {{ $tahunAjaran ?? '2027/2028' }} &middot; Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
            &middot; Total: {{ $participants->count() }} pendaftar
        </p>
    </div>

    {{-- Tabel rekapitulasi --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:11%;">No. Pendaftaran</th>
                <th style="width:16%;">Nama Lengkap</th>
                <th style="width:7%;">NISN</th>
                <th style="width:7%;">NIK</th>
                <th style="width:4%;">JK</th>
                <th style="width:14%;">Asal Sekolah</th>
                <th style="width:8%;">Jurusan</th>
                <th style="width:12%;">No. HP / Ortu</th>
                <th style="width:9%;">Status</th>
                <th style="width:9%;">Tgl Daftar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($participants as $i => $p)
            @php
                $statusLabel = match ($p->status_verifikasi) {
                    'approved' => 'Diterima',
                    'rejected' => 'Ditolak',
                    'revision' => 'Perlu Revisi',
                    default    => 'Menunggu',
                };
                $jurusan = collect([optional($p->jurusan1)->kode, optional($p->jurusan2)->kode])
                    ->filter()
                    ->implode(' / ');
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="mono">{{ $p->nomor_pendaftaran }}</td>
                <td class="nama">{{ $p->nama_lengkap }}</td>
                <td>{{ $p->nisn ?: '—' }}</td>
                <td>{{ $p->nik ?: '—' }}</td>
                <td>{{ $p->jenis_kelamin ?: '—' }}</td>
                <td>{{ $p->asal_sekolah ?: '—' }}</td>
                <td class="mono">{{ $jurusan ?: '—' }}</td>
                <td>{{ $p->no_hp ?: '—' }}<br>{{ $p->no_hp_ortu ?: '' }}</td>
                <td>{{ $statusLabel }}</td>
                <td>{{ optional($p->created_at)->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align:center; padding:14px; color:#7A9484;">
                    Belum ada data pendaftar.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">
        Dokumen ini dihasilkan otomatis oleh Sistem PPDB Digital SMK Ma'arif NU 1 Wangon (MANUSAWA).
        Rahasia &mdash; hanya untuk keperluan panitia PPDB.
    </p>
</body>
</html>
