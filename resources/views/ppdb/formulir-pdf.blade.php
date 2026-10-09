@php
    /*
    |--------------------------------------------------------------------------
    | Cetak Formulir Pendaftaran (PDF)
    |--------------------------------------------------------------------------
    | Tampilan berkas ini disusun agar identik dengan berkas resmi
    | "Formulir Pendaftaran SPMB" (berkas resmi panitia):
    |   - Kop surat (logo NU + 3 baris identitas lembaga/sekolah)
    |   - Judul formulir & tahun pelajaran
    |   - Bagian A (Keterangan Diri), B (Pendidikan), C (Kesehatan), D (Lain-lain)
    |   - Blok tanda tangan & petugas pendaftaran
    |   - Dua lembar "Tanda Terima Berkas Persyaratan"
    |
    | Catatan teknis: DomPDF tidak mendukung flexbox/grid, sehingga tata letak
    | memakai tabel dan inline-block. Font "Times New Roman" dipetakan ke font
    | serif bawaan PDF (Times-Roman) agar hasilnya setia pada berkas aslinya.
    */

    // Nama bulan Indonesia (locale aplikasi belum tentu diset ke "id").
    $bulanId = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    // Tanggal lengkap dalam bahasa Indonesia, mis. "1 Januari 2009".
    $tglIndo = function ($tanggal) use ($bulanId) {
        if (blank($tanggal)) {
            return null;
        }
        $d = $tanggal instanceof \Carbon\CarbonInterface ? $tanggal : \Carbon\Carbon::parse($tanggal);

        return $d->day . ' ' . $bulanId[(int) $d->month] . ' ' . $d->year;
    };

    // Cetak isian siswa: teks tebal di atas garis titik-titik. Bila kosong,
    // yang tercetak hanya garis titik-titik (kolom milik panitia).
    $isi = function ($nilai, $lebar = '160px') {
        $nilai = is_string($nilai) ? trim($nilai) : $nilai;

        if ($nilai === null || $nilai === '' || $nilai === false) {
            return '<span class="titik" style="min-width:' . $lebar . '">&nbsp;</span>';
        }

        return '<span class="titik isi" style="min-width:' . $lebar . '">' . e($nilai) . '</span>';
    };

    // Kotak pilihan (untuk jenis kelamin, penghasilan, jurusan, ukuran seragam).
    $pilih = function (bool $terpilih) {
        return '<span class="kotak' . ($terpilih ? ' ya' : '') . '"></span>';
    };

    $ttlSiswa = trim(($pendaftar->tempat_lahir ?? '') . ($pendaftar->tanggal_lahir ? ', ' . $tglIndo($pendaftar->tanggal_lahir) : ''));
    $nilaiUjian = is_array($pendaftar->nilai_ujian) ? $pendaftar->nilai_ujian : [];

    // Daftar jurusan dengan label persis seperti pada berkas asli.
    $daftarJurusan = [
        'AKL'  => 'AKUNTANSI DAN KEUANGAN LEMBAGA (AKL)',
        'MPLB' => 'MANAJEMEN PERKANTORAN &amp; LAYANAN BISNIS (MPLB)',
        'FKK'  => 'LP. KEFARMASIAN KLINIS DAN KOMUNITAS (LPKKK)',
        'TKR'  => 'TEKNIK KENDARAAN RINGAN (TKR)',
    ];

    $checklistBerkas = [
        'AKTE ASLI / IJAZAH SD/MI'      => '1 lembar',
        'FOTOKOPI AKTE KELAHIRAN'       => '1 lembar',
        'FOTOKOPI KARTU KELUARGA'       => '1 lembar',
        'FOTOKOPI KIP/KIS/PKH'          => '1 lembar',
        'FOTOKOPI KTP ORANG TUA'        => '2 lembar',
        'PAS FOTO 3 X 4'                => '2 lembar',
        'SURAT KETERANGAN LULUS (SKL)'  => '1 lembar',
        'FOTOKOPI IJAZAH MTs/SMP'       => '1 lembar',
    ];

    // Logo NU untuk kop surat (disalin dari berkas .docx aslinya).
    $logoSrc = public_path('images/logo-nu-kop.png');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Pendaftaran {{ $pendaftar->nomor_pendaftaran }}</title>
    <style>
        @page { size: 210mm 330mm portrait; margin: 12mm 12mm 14mm 15mm; }

        * { box-sizing: border-box; }

        body {
            font-family: "Times New Roman", Times, "DejaVu Serif", serif;
            font-size: 12pt;
            color: #000;
            margin: 0;
            line-height: 1.35;
        }

        /* ── Kop surat ── */
        .kop { width: 100%; border-collapse: collapse; margin-bottom: 10pt; }
        .kop td { vertical-align: top; border: none; padding: 0; }
        .kop-logo { width: 108pt; }
        .kop-logo img { width: 100pt; }
        .kop-teks { text-align: right; line-height: 1.25; }
        .kop-lembaga { font-weight: bold; font-size: 14pt; letter-spacing: .2pt; }
        .kop-sekolah { font-weight: bold; font-size: 14pt; letter-spacing: .2pt; }
        .kop-alamat {
            font-family: "Arial Narrow", Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            margin-top: 3pt;
        }

        /* ── Judul formulir ── */
        .judul { text-align: center; line-height: 1.3; margin: 6pt 0 12pt; }
        .judul-1 { font-weight: bold; font-size: 12pt; }
        .judul-2 { font-weight: bold; font-size: 16pt; }
        .judul-3 { font-weight: bold; font-size: 13pt; }
        .judul-4 { font-size: 12pt; margin-top: 6pt; }

        .nomor-pendaftaran { font-weight: bold; margin-bottom: 4pt; }

        /* ── Bagian & butir ── */
        .bagian { font-weight: bold; margin: 9pt 0 3pt; }
        .butir { margin: 0 0 2pt; }
        .sub { margin: 0 0 2pt 22pt; }
        .sub2 { margin: 0 0 2pt 44pt; }

        /* Isian titik-titik */
        .titik {
            display: inline-block;
            border-bottom: 1pt dotted #444;
            padding: 0 4pt;
            vertical-align: bottom;
        }
        .titik.isi { font-weight: bold; }

        /* Kotak pilihan */
        .kotak {
            display: inline-block;
            width: 8pt;
            height: 8pt;
            border: 0.8pt solid #000;
            margin-right: 2pt;
            vertical-align: middle;
        }
        .kotak.ya { background-color: #000; }
        .opsi { margin-right: 12pt; white-space: nowrap; }
        .opsi-ya { font-weight: bold; }

        /* ── Blok tanda tangan ── */
        .ttd { width: 100%; border-collapse: collapse; margin-top: 14pt; }
        .ttd td { vertical-align: top; border: none; padding: 0; }
        .ttd-kanan { text-align: right; width: 130pt; }

        .pasfoto {
            display: inline-block;
            width: 84pt;
            height: 112pt;
            border: 1pt solid #000;
            text-align: center;
            font-size: 11pt;
            line-height: 1.4;
            padding-top: 44pt;
        }
        .catatan-kecil { font-style: italic; font-size: 10pt; }

        /* ── Tanda terima ── */
        .halaman-baru { page-break-before: always; }
        .tt-head { text-align: center; }
        .tt-judul { font-weight: bold; font-size: 16pt; letter-spacing: .5pt; }
        .tt-sub { font-weight: bold; font-size: 14pt; }
        .tt-thn { font-weight: bold; font-size: 13pt; }
        .tt-isi { margin: 3pt 0; }
        .tt-list { width: 100%; border-collapse: collapse; margin: 9pt 0 0; }
        .tt-list td { vertical-align: top; padding: 0 0 2.5pt; border: none; }
        .tt-no { width: 16pt; }
        .tt-jml { text-align: right; width: 60pt; }
        .tt-akhir { margin-top: 14pt; }
    </style>
</head>
<body>

    {{-- ══════════════════════ KOP SURAT ══════════════════════ --}}
    <table class="kop">
        <tr>
            <td class="kop-logo">
                @if (file_exists($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo NU">
                @endif
            </td>
            <td class="kop-teks">
                <div class="kop-lembaga">LEMBAGA PENDIDIKAN MA'ARIF NU PCNU KAB. BANYUMAS</div>
                <div class="kop-sekolah">SMK MA'ARIF NU 1 WANGON</div>
                <div class="kop-alamat">Alamat: Jl.Karangjengkol RT.02/RW.04 Wangon-Banyumas 53176 &nbsp; ( 0281 ) 513425</div>
                <div class="kop-alamat">Email : smk_maarifnu1wgn@yahoo.co.id &nbsp; website: www.smkmanu1wangon.sch.id</div>
            </td>
        </tr>
    </table>

    {{-- ══════════════════════ JUDUL ══════════════════════ --}}
    <div class="judul">
        <div class="judul-1">FORMULIR PENDAFTARAN MURID BARU</div>
        <div class="judul-2">SMK MA'ARIF NU 1 WANGON</div>
        <div class="judul-3">TAHUN PELAJARAN {{ $tahunAjaran ?? '2027/2028' }}</div>
        <div class="judul-4">( Harap diisi dengan huruf besar dan jelas )</div>
    </div>

    {{-- Kolom administratif: diisi panitia saat pendaftaran diterima. --}}
    <div class="nomor-pendaftaran">No. Pendaftaran : {!! $isi(null, '150pt') !!}</div>

    {{-- ══════════════════════ A. KETERANGAN DIRI ══════════════════════ --}}
    <div class="bagian">A. KETERANGAN DIRI MURID BARU</div>

    <div class="butir">1. Nama Lengkap Murid Baru : {!! $isi($pendaftar->nama_lengkap, '260pt') !!}</div>
    <div class="butir">2. Tempat / Tanggal Lahir : {!! $isi($ttlSiswa, '245pt') !!}</div>
    <div class="butir">3. NISN : {!! $isi($pendaftar->nisn, '215pt') !!}</div>
    <div class="butir">4. NIK &amp; No KK : {!! $isi($pendaftar->nik, '140pt') !!} &amp; {!! $isi($pendaftar->no_kk, '140pt') !!}</div>
    <div class="butir">5. Jenis Kelamin :
        <span class="opsi {{ $pendaftar->jenis_kelamin === 'L' ? 'opsi-ya' : '' }}">{!! $pilih($pendaftar->jenis_kelamin === 'L') !!} Laki – laki</span>
        <span class="opsi {{ $pendaftar->jenis_kelamin === 'P' ? 'opsi-ya' : '' }}">{!! $pilih($pendaftar->jenis_kelamin === 'P') !!} Perempuan</span>
    </div>
    <div class="butir">6. Agama : {!! $isi($pendaftar->agama, '205pt') !!}</div>
    <div class="butir">7. Anak ke berapa : {!! $isi($pendaftar->anak_ke, '205pt') !!}</div>
    <div class="butir">8. Jumlah saudara kandung : {!! $isi($pendaftar->jumlah_saudara, '205pt') !!}</div>
    <div class="butir">9. Asal Sekolah : {!! $isi($pendaftar->asal_sekolah, '205pt') !!}</div>
    <div class="butir">10. Tamat SMP / MTs Tahun : {!! $isi($pendaftar->tamat_tahun, '205pt') !!}</div>
    <div class="butir">11. No. Ijazah : {!! $isi($pendaftar->no_ijazah, '205pt') !!}</div>
    <div class="butir">12. No Telepon Murid : {!! $isi($pendaftar->no_hp, '230pt') !!}</div>
    <div class="butir">13. Email : {!! $isi($pendaftar->email, '230pt') !!}</div>

    <div class="butir">14. Orang Tua :</div>
    <div class="sub">a. Ayah : Nama : {!! $isi($pendaftar->nama_ayah, '170pt') !!}</div>
    <div class="sub">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TTL : {!! $isi($pendaftar->ayah_ttl, '205pt') !!}</div>
    <div class="sub">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pendidikan : {!! $isi($pendaftar->ayah_pendidikan, '190pt') !!}</div>
    <div class="sub">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pekerjaan : {!! $isi($pendaftar->pekerjaan_ayah, '190pt') !!}</div>
    <div class="sub">b. Ibu : Nama : {!! $isi($pendaftar->nama_ibu, '175pt') !!}</div>
    <div class="sub">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TTL : {!! $isi($pendaftar->ibu_ttl, '205pt') !!}</div>
    <div class="sub">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pendidikan : {!! $isi($pendaftar->ibu_pendidikan, '190pt') !!}</div>
    <div class="sub">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pekerjaan : {!! $isi($pendaftar->pekerjaan_ibu, '190pt') !!}</div>

    <div class="butir">15. Alamat Orangtua / Wali : RT {!! $isi($pendaftar->ortu_rt, '34pt') !!} / RW {!! $isi($pendaftar->ortu_rw, '34pt') !!} Desa {!! $isi($pendaftar->ortu_desa, '150pt') !!}</div>
    <div class="sub">Kecamatan {!! $isi($pendaftar->ortu_kecamatan, '170pt') !!} / Kabupaten {!! $isi($pendaftar->ortu_kabupaten, '150pt') !!}</div>
    <div class="sub">Propinsi {!! $isi($pendaftar->ortu_propinsi, '180pt') !!} / No. Telepon Wali {!! $isi($pendaftar->no_hp_ortu, '110pt') !!}</div>

    <div class="butir">16. Penghasilan Bulanan :
        @foreach (\App\Models\Pendaftar::opsiPenghasilan() as $kode => $label)
            <span class="opsi {{ $pendaftar->penghasilan === $kode ? 'opsi-ya' : '' }}">{!! $pilih($pendaftar->penghasilan === $kode) !!} {{ str_pad($kode, 2, '0', STR_PAD_LEFT) }}). {{ $label }}</span>
        @endforeach
    </div>

    <div class="butir">17. Jurusan yang dipilih :</div>
    @foreach ($daftarJurusan as $kode => $label)
        @php $terpilih = optional($pendaftar->jurusan1)->kode === $kode; @endphp
        <div class="sub2 {{ $terpilih ? 'opsi-ya' : '' }}">{!! $pilih($terpilih) !!} {!! $label !!}</div>
    @endforeach

    {{-- ══════════════════════ B. KETERANGAN PENDIDIKAN ══════════════════════ --}}
    <div class="bagian">B. KETERANGAN PENDIDIKAN</div>

    <div class="butir">1. Asal Sekolah :</div>
    <div class="sub">a. SMP / MTs. : {!! $isi($pendaftar->asal_sekolah, '150pt') !!}</div>
    <div class="sub">Kecamatan : {!! $isi($pendaftar->smp_kecamatan, '150pt') !!} / Kabupaten : {!! $isi($pendaftar->smp_kabupaten, '110pt') !!}</div>
    <div class="sub">Propinsi : {!! $isi($pendaftar->smp_propinsi, '170pt') !!}</div>
    <div class="sub">b. Nomor dan Tahun Ijazah : {!! $isi($pendaftar->ijazah_nomor_tahun, '170pt') !!}</div>
    <div class="sub">c. Lama belajar : {!! $isi($pendaftar->lama_belajar, '40pt') !!} Tahun</div>
    <div class="sub">d Nilai Ujian :</div>
    <div class="sub2">1) Bahasa Indonesia : {!! $isi($nilaiUjian['b_indonesia'] ?? null, '55pt') !!} ( {!! $isi($nilaiUjian['b_indonesia'] ?? null, '90pt') !!} )</div>
    <div class="sub2">2) Bahasa Inggris : {!! $isi($nilaiUjian['b_inggris'] ?? null, '55pt') !!} ( {!! $isi($nilaiUjian['b_inggris'] ?? null, '90pt') !!} )</div>
    <div class="sub2">3) Matematika : {!! $isi($nilaiUjian['matematika'] ?? null, '55pt') !!} ( {!! $isi($nilaiUjian['matematika'] ?? null, '90pt') !!} )</div>
    <div class="sub2">4) IPA : {!! $isi($nilaiUjian['ipa'] ?? null, '55pt') !!} ( {!! $isi($nilaiUjian['ipa'] ?? null, '90pt') !!} )</div>

    {{-- ══════════════════════ C. KETERANGAN KESEHATAN ══════════════════════ --}}
    <div class="bagian">C. KETERANGAN KESEHATAN</div>

    <div class="butir">1. Golongan darah : {!! $isi($pendaftar->golongan_darah, '200pt') !!}</div>
    <div class="butir">2. Tinggi dan berat badan : {!! $isi($pendaftar->tinggi_badan, '70pt') !!} cm, dan {!! $isi($pendaftar->berat_badan, '70pt') !!} kg.</div>
    <div class="butir">3. Penyakit yang pernah diderita : {!! $isi($pendaftar->penyakit_pernah_diderita, '180pt') !!}</div>

    {{-- ══════════════════════ D. KETERANGAN LAIN-LAIN ══════════════════════ --}}
    <div class="bagian">D. KETERANGAN LAIN - LAIN</div>

    <div class="butir">1. Kegemaran / Hobi / Prestasi murid baru</div>
    <div class="sub">a. Olah raga : {!! $isi($pendaftar->hobi_olahraga, '190pt') !!}</div>
    <div class="sub">b. Kesenian : {!! $isi($pendaftar->hobi_kesenian, '195pt') !!}</div>
    <div class="sub">c. Keorganisasian : {!! $isi($pendaftar->hobi_organisasi, '170pt') !!}</div>
    <div class="sub">d. Prestasi yang pernah diraih : {!! $isi($pendaftar->hobi_prestasi, '140pt') !!}</div>
    <div class="sub">e. Lain – Lain : {!! $isi($pendaftar->hobi_lain, '180pt') !!}</div>

    <div class="butir">2. Ukuran Baju / Seragam :
        @foreach (\App\Models\Pendaftar::opsiUkuranSeragam() as $ukuran)
            <span class="opsi {{ $pendaftar->ukuran_seragam === $ukuran ? 'opsi-ya' : '' }}">{!! $pilih($pendaftar->ukuran_seragam === $ukuran) !!} {{ $ukuran }}</span>
        @endforeach
    </div>

    {{-- ══════════════════════ BLOK TANDA TANGAN ══════════════════════ --}}
    <table class="ttd">
        <tr>
            <td>
                <div>Wangon , {!! $isi(null, '150pt') !!}</div>
                <div style="margin-top:6pt;">Murid Baru,</div>
                <div style="margin-top:34pt;">( {!! $isi($pendaftar->nama_lengkap, '120pt') !!} )</div>
                <div style="margin-top:12pt;">Referensi Pendaftar : {!! $isi($pendaftar->referensi_pendaftar, '120pt') !!}</div>
                <div class="catatan-kecil">(Dari siapa / dari mana informasi pendaftaran yang di dapatkan oleh calon murid baru)</div>
                <div style="margin-top:8pt;">Petugas Pendaftaran : {!! $isi(null, '120pt') !!}</div>
                <div>Tanda Tangan : {!! $isi(null, '160pt') !!}</div>
            </td>
            <td class="ttd-kanan">
                <div class="pasfoto">Pas photo<br>3 x 4</div>
            </td>
        </tr>
    </table>

    {{-- ══════════════════════ TANDA TERIMA BERKAS (2 lembar) ══════════════════════ --}}
    @for ($lembar = 1; $lembar <= 2; $lembar++)
    <div class="halaman-baru">
        <div class="tt-head">
            <div class="tt-judul">TANDA TERIMA</div>
            <div class="tt-sub">BERKAS PERSYARATAN MURID BARU</div>
            <div class="tt-thn">SMK MA'ARIF NU 1 WANGON TAHUN PELAJARAN 2027 / 2028</div>
        </div>

        <div style="margin-top:14pt;">
            <div class="tt-isi">No Pendaftaran / Jurusan : {!! $isi($pendaftar->nomor_pendaftaran, '95pt') !!} / {!! $isi(optional($pendaftar->jurusan1)->kode, '150pt') !!}</div>
            <div class="tt-isi">Nama Murid Baru : {!! $isi($pendaftar->nama_lengkap, '215pt') !!}</div>
            <div class="tt-isi">Asal Sekolah : {!! $isi($pendaftar->asal_sekolah, '235pt') !!}</div>
            <div class="tt-isi">Alamat Murid Baru : RT. {!! $isi($pendaftar->rt, '28pt') !!} / RW. {!! $isi($pendaftar->rw, '28pt') !!} Desa {!! $isi($pendaftar->desa, '150pt') !!}</div>
            <div class="tt-isi">Kecamatan : {!! $isi($pendaftar->kecamatan, '215pt') !!}</div>
            <div class="tt-isi">Kabupaten : {!! $isi($pendaftar->kabupaten, '215pt') !!}</div>
        </div>

        {{-- Daftar berkas persyaratan beserta jumlah lembarnya. --}}
        <table class="tt-list">
            @foreach ($checklistBerkas as $nama => $jumlah)
            <tr>
                <td class="tt-no">{{ $loop->iteration }}.</td>
                <td>{{ $nama }}</td>
                <td class="tt-jml">{{ $jumlah }}</td>
            </tr>
            @endforeach
        </table>

        <div class="tt-akhir">
            <div>Harap datang di SMK Ma'arif NU 1 Wangon tanggal : {!! $isi(null, '150pt') !!}</div>
            <div style="margin-top:16pt;">Wangon, {!! $isi(null, '150pt') !!}</div>
            <div style="margin-top:6pt;">PANITIA SPMB</div>
            <div style="margin-top:42pt;">_______________________</div>
        </div>
    </div>
    @endfor

</body>
</html>
