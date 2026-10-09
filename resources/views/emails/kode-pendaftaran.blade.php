@php
    /** Variabel: $pendaftar, $grupWaUrl, $statusUrl */
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Pendaftaran PPDB</title>
</head>
<body style="margin:0; padding:0; background:#F4FAF5; font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#0E2719;">
    <div style="max-width:600px; margin:0 auto; padding:24px 16px;">

        {{-- Header --}}
        <div style="background:#023619; border-radius:16px 16px 0 0; padding:28px 28px 24px; border-bottom:4px solid #D97706;">
            <div style="font-size:17px; font-weight:700; color:#FFFFFF; letter-spacing:-0.2px;">
                SMK Ma'arif NU 1 Wangon
            </div>
            <div style="font-size:12px; font-weight:700; color:#6EE7B7; text-transform:uppercase; letter-spacing:1.4px; margin-top:4px;">
                PPDB Terpadu {{ $tahunAjaran ?? '2027/2028' }}
            </div>
        </div>

        {{-- Body --}}
        <div style="background:#FFFFFF; padding:28px; border-left:1px solid #C8E6D2; border-right:1px solid #C8E6D2;">
            <p style="font-size:16px; line-height:1.65; margin:0 0 16px;">
                Assalamu'alaikum, <strong>{{ $pendaftar->nama_lengkap }}</strong>
            </p>

            <p style="font-size:15px; line-height:1.7; margin:0 0 22px; color:#304F3D;">
                Terima kasih telah menyelesaikan pendaftaran peserta didik baru.
                Pendaftaran Anda <strong>berhasil kami terima</strong> dan sedang menunggu verifikasi panitia.
            </p>

            {{-- Kode pendaftaran --}}
            <div style="background:#F4FAF5; border:2px solid #057A3C; border-radius:14px; padding:22px; text-align:center; margin-bottom:22px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1.4px; color:#648772; margin-bottom:8px;">
                    Kode Pendaftaran Anda
                </div>
                <div style="font-size:28px; font-weight:800; color:#057A3C; letter-spacing:2px;">
                    {{ $pendaftar->nomor_pendaftaran }}
                </div>
                <div style="font-size:12px; color:#648772; margin-top:8px; line-height:1.5;">
                    Simpan kode ini untuk mengecek status pendaftaran.
                </div>
            </div>

            {{-- Ringkasan --}}
            <table role="presentation" style="width:100%; border-collapse:collapse; font-size:14px; margin-bottom:22px;">
                <tr>
                    <td style="padding:9px 0; color:#648772; width:44%; border-bottom:1px solid #EBF5EE;">Nama Lengkap</td>
                    <td style="padding:9px 0; font-weight:700; border-bottom:1px solid #EBF5EE;">{{ $pendaftar->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td style="padding:9px 0; color:#648772; border-bottom:1px solid #EBF5EE;">NISN</td>
                    <td style="padding:9px 0; font-weight:700; border-bottom:1px solid #EBF5EE;">{{ $pendaftar->nisn ?: '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:9px 0; color:#648772; border-bottom:1px solid #EBF5EE;">Pilihan 1</td>
                    <td style="padding:9px 0; font-weight:700; border-bottom:1px solid #EBF5EE;">
                        {{ $pendaftar->jurusan1?->kode ?? '—' }} @if($pendaftar->jurusan1)&mdash; {{ $pendaftar->jurusan1->nama }}@endif
                    </td>
                </tr>
                @if ($pendaftar->jurusan2)
                <tr>
                    <td style="padding:9px 0; color:#648772; border-bottom:1px solid #EBF5EE;">Pilihan 2</td>
                    <td style="padding:9px 0; font-weight:700; border-bottom:1px solid #EBF5EE;">
                        {{ $pendaftar->jurusan2->kode }} &mdash; {{ $pendaftar->jurusan2->nama }}
                    </td>
                </tr>
                @endif
                <tr>
                    <td style="padding:9px 0; color:#648772;">Tanggal Daftar</td>
                    <td style="padding:9px 0; font-weight:700;">{{ $pendaftar->created_at?->translatedFormat('d F Y, H:i') }}</td>
                </tr>
            </table>

            {{-- Langkah selanjutnya --}}
            <div style="background:#E0F2FE; border-left:4px solid #0284C7; border-radius:8px; padding:14px 16px; margin-bottom:22px;">
                <div style="font-size:13px; font-weight:700; color:#075985; margin-bottom:6px;">Langkah selanjutnya</div>
                <ol style="margin:0; padding-left:18px; font-size:13px; line-height:1.8; color:#0C4A6E;">
                    <li>Panitia memverifikasi dokumen Anda dalam 1&ndash;3 hari kerja.</li>
                    <li>Pantau status melalui menu <strong>Cek Status Pendaftaran</strong>.</li>
                    <li>Jika ada kendala, hubungi panitia PPDB di <strong>0816-2800-3566</strong>.</li>
                </ol>
            </div>

            {{-- Tombol --}}
            <div style="text-align:center; margin-bottom:8px;">
                <a href="{{ $statusUrl }}"
                   style="display:inline-block; background:#057A3C; color:#FFFFFF; text-decoration:none; font-size:14px; font-weight:700; padding:13px 26px; border-radius:10px;">
                    Cek Status Pendaftaran
                </a>

                @if ($grupWaUrl)
                    <a href="{{ $grupWaUrl }}"
                       style="display:inline-block; background:#25D366; color:#FFFFFF; text-decoration:none; font-size:14px; font-weight:700; padding:13px 26px; border-radius:10px; margin-left:8px;">
                        Gabung Grup WhatsApp
                    </a>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div style="background:#F4FAF5; border:1px solid #C8E6D2; border-top:none; border-radius:0 0 16px 16px; padding:18px 28px; text-align:center;">
            <div style="font-size:12px; color:#648772; line-height:1.7;">
                Email ini dikirim otomatis oleh Sistem PPDB Terpadu.<br>
                SMK Ma'arif NU 1 Wangon &middot; Jl. Karangjengkol, Wangon, Banyumas, Jawa Tengah 53176
            </div>
        </div>
    </div>
</body>
</html>
