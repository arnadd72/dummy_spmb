@extends('layouts.app')
@section('title', 'Langkah 8: Review & Submit — Pendaftaran PPDB')

@section('content')
<div class="form-page">
    <div class="container">
        <x-step-indicator :currentStep="8" />

        <div class="form-card" style="max-width:900px;">
            <div style="text-align:center; margin-bottom:2rem;">
                <div style="width:4rem; height:4rem; background:#eff6ff; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="var(--color-primary)" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                    </svg>
                </div>
                <h1 class="form-page-title">Periksa Data Anda</h1>
                <p class="form-page-subtitle">Pastikan semua data sudah benar sebelum mengirim pendaftaran. Data yang telah disubmit tidak dapat diubah.</p>
            </div>

            @php
            $penghasilanLabel = \App\Models\Pendaftar::opsiPenghasilan()[$step5['penghasilan'] ?? ''] ?? '—';
            $nilaiUjianMap    = \App\Models\Pendaftar::opsiNilaiUjian();
            @endphp

            {{-- Jurusan --}}
            <div style="margin-bottom:1.25rem; padding:1.25rem; background:linear-gradient(135deg, #eff6ff, #f0fdf4); border:1px solid var(--color-border); border-radius:var(--radius-lg);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--color-primary-dark);">17. Jurusan yang Dipilih</h2>
                    <a href="{{ route('ppdb.step4') }}" class="btn btn-ghost btn-sm">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
                        Ubah
                    </a>
                </div>
                <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                    <div>
                        <div style="font-size:0.75rem; color:var(--color-text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.06em;">Pilihan 1</div>
                        <div style="font-weight:700; font-size:1.0625rem; color:var(--color-primary-dark);">{{ $jurusan1?->kode ?? '—' }} — {{ $jurusan1?->nama ?? 'Belum dipilih' }}</div>
                    </div>
                    @if ($jurusan2)
                    <div style="padding-left:1rem; border-left:2px solid var(--color-border);">
                        <div style="font-size:0.75rem; color:var(--color-text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.06em;">Pilihan 2</div>
                        <div style="font-weight:700; font-size:1.0625rem; color:var(--color-primary-dark);">{{ $jurusan2->kode }} — {{ $jurusan2->nama }}</div>
                    </div>
                    @endif
                </div>
                @if (!empty($step4['minat']))
                <div style="margin-top:0.75rem; font-size:0.875rem; color:var(--color-text-secondary);">
                    Minat: <strong>{{ ucfirst($step4['minat']) }}</strong>
                </div>
                @endif
            </div>

            {{-- A. Keterangan Diri --}}
            <div style="margin-bottom:1.25rem; padding:1.25rem; background:var(--color-background); border:1px solid var(--color-border); border-radius:var(--radius-lg);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--color-primary-dark);">A. Keterangan Diri Murid Baru</h2>
                    <a href="{{ route('ppdb.step5') }}" class="btn btn-ghost btn-sm">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
                        Ubah
                    </a>
                </div>
                <dl style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem 1.5rem;">
                    @php
                    $siswaFields = [
                        'Nama Lengkap'   => $step5['nama_lengkap'] ?? '—',
                        'Tempat/Tanggal Lahir' => isset($step5['tanggal_lahir']) ? (($step5['tempat_lahir'] ?? '') . ', ' . date('d F Y', strtotime($step5['tanggal_lahir']))) : ($step5['tempat_lahir'] ?? '—'),
                        'NISN'           => $step5['nisn'] ?? '—',
                        'NIK'            => isset($step5['nik']) ? substr($step5['nik'], 0, 6) . '••••••' . substr($step5['nik'], -4) : '—',
                        'No. Kartu Keluarga' => $step5['no_kk'] ?? '—',
                        'Jenis Kelamin'  => ($step5['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : (($step5['jenis_kelamin'] ?? '') === 'P' ? 'Perempuan' : '—'),
                        'Agama'          => $step5['agama'] ?? '—',
                        'Anak Ke'        => $step5['anak_ke'] ?? '—',
                        'Jumlah Saudara' => $step5['jumlah_saudara'] ?? '—',
                        'Asal Sekolah'   => $step5['asal_sekolah'] ?? '—',
                        'Tamat Tahun'    => $step5['tamat_tahun'] ?? '—',
                        'No. Ijazah'     => $step5['no_ijazah'] ?? '—',
                        'No. Telepon'    => $step5['no_hp'] ?? '—',
                        'No. WhatsApp'   => $step5['no_wa'] ?? '—',
                        'Email'          => $step5['email'] ?? '—',
                        'Penghasilan'    => $penghasilanLabel,
                        'Referensi'      => $step5['referensi_pendaftar'] ?? '—',
                    ];
                    @endphp
                    @foreach ($siswaFields as $label => $value)
                    <div style="{{ in_array($label, ['Nama Lengkap']) ? 'grid-column:1/-1;' : '' }}">
                        <dt style="font-size:0.75rem; font-weight:700; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.125rem;">{{ $label }}</dt>
                        <dd style="font-weight:600; color:var(--color-text); margin:0;">{{ $value ?: '—' }}</dd>
                    </div>
                    @endforeach
                    <div style="grid-column:1/-1;">
                        <dt style="font-size:0.75rem; font-weight:700; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.125rem;">Alamat</dt>
                        <dd style="font-weight:600; color:var(--color-text); margin:0;">{{ $step5['alamat'] ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Orang Tua --}}
            <div style="margin-bottom:1.25rem; padding:1.25rem; background:var(--color-background); border:1px solid var(--color-border); border-radius:var(--radius-lg);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--color-primary-dark);">Data Orang Tua / Wali</h2>
                    <a href="{{ route('ppdb.step6') }}" class="btn btn-ghost btn-sm">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
                        Ubah
                    </a>
                </div>
                <dl style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem 1.5rem;">
                    @foreach ([
                        'Nama Ayah' => $step6['nama_ayah'] ?? '—',
                        'TTL Ayah' => $step6['ayah_ttl'] ?? '—',
                        'Pendidikan Ayah' => $step6['ayah_pendidikan'] ?? '—',
                        'Pekerjaan Ayah' => $step6['pekerjaan_ayah'] ?? '—',
                        'Nama Ibu' => $step6['nama_ibu'] ?? '—',
                        'TTL Ibu' => $step6['ibu_ttl'] ?? '—',
                        'Pendidikan Ibu' => $step6['ibu_pendidikan'] ?? '—',
                        'Pekerjaan Ibu' => $step6['pekerjaan_ibu'] ?? '—',
                        'No. Telepon Wali' => $step6['no_hp_ortu'] ?? '—',
                    ] as $label => $value)
                    <div>
                        <dt style="font-size:0.75rem; font-weight:700; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.125rem;">{{ $label }}</dt>
                        <dd style="font-weight:600; color:var(--color-text); margin:0;">{{ $value ?: '—' }}</dd>
                    </div>
                    @endforeach
                    <div style="grid-column:1/-1;">
                        <dt style="font-size:0.75rem; font-weight:700; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.125rem;">Alamat Orang Tua</dt>
                        <dd style="font-weight:600; color:var(--color-text); margin:0;">{{ $step6['alamat_ortu'] ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- B, C, D --}}
            <div style="margin-bottom:1.25rem; padding:1.25rem; background:var(--color-background); border:1px solid var(--color-border); border-radius:var(--radius-lg);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--color-primary-dark);">B–D. Pendidikan, Kesehatan &amp; Lain-lain</h2>
                    <a href="{{ route('ppdb.step7') }}" class="btn btn-ghost btn-sm">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
                        Ubah
                    </a>
                </div>
                <dl style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem 1.5rem;">
                    @foreach ([
                        'SMP/MTs Kecamatan' => $step7['smp_kecamatan'] ?? '—',
                        'SMP/MTs Kabupaten' => $step7['smp_kabupaten'] ?? '—',
                        'SMP/MTs Propinsi' => $step7['smp_propinsi'] ?? '—',
                        'Nomor & Tahun Ijazah' => $step7['ijazah_nomor_tahun'] ?? '—',
                        'Lama Belajar' => isset($step7['lama_belajar']) ? $step7['lama_belajar'] . ' Tahun' : '—',
                        'Golongan Darah' => $step7['golongan_darah'] ?? '—',
                        'Tinggi Badan' => isset($step7['tinggi_badan']) ? $step7['tinggi_badan'] . ' cm' : '—',
                        'Berat Badan' => isset($step7['berat_badan']) ? $step7['berat_badan'] . ' kg' : '—',
                        'Penyakit Pernah Diderita' => $step7['penyakit_pernah_diderita'] ?? '—',
                        'Hobi Olah Raga' => $step7['hobi_olahraga'] ?? '—',
                        'Hobi Kesenian' => $step7['hobi_kesenian'] ?? '—',
                        'Keorganisasian' => $step7['hobi_organisasi'] ?? '—',
                        'Prestasi' => $step7['hobi_prestasi'] ?? '—',
                        'Lain-lain' => $step7['hobi_lain'] ?? '—',
                        'Ukuran Seragam' => $step7['ukuran_seragam'] ?? '—',
                    ] as $label => $value)
                    <div>
                        <dt style="font-size:0.75rem; font-weight:700; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.125rem;">{{ $label }}</dt>
                        <dd style="font-weight:600; color:var(--color-text); margin:0;">{{ $value ?: '—' }}</dd>
                    </div>
                    @endforeach
                    <div style="grid-column:1/-1;">
                        <dt style="font-size:0.75rem; font-weight:700; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.125rem;">Nilai Ujian</dt>
                        <dd style="font-weight:600; color:var(--color-text); margin:0;">
                            @foreach ($nilaiUjianMap as $key => $label)
                                {{ $label }}: {{ $step7['nilai_ujian'][$key] ?? '—' }}@if (!$loop->last) &middot; @endif
                            @endforeach
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Dokumen Status --}}
            <div style="margin-bottom:1.5rem; padding:1rem 1.25rem; background:var(--color-background); border:1px solid var(--color-border); border-radius:var(--radius-lg);">
                <h2 style="font-size:0.9375rem; font-weight:700; color:var(--color-primary-dark); margin-bottom:0.75rem;">Dokumen Terupload</h2>
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    @foreach (['KK' => ($step2['kk_path'] ?? null), 'Akta Kelahiran' => ($step2['akta_path'] ?? null), 'Rapor' => ($step3['rapor_path'] ?? null)] as $doc => $path)
                    <span class="rekomendasi-badge {{ $path ? 'badge-green' : 'badge-gray' }}">
                        @if ($path)
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @else
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                        {{ $doc }}
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Submit Form --}}
            <form method="POST" action="{{ route('ppdb.submit') }}" novalidate id="form-submit">
                @csrf

                @error('konfirmasi_final')
                <div class="alert alert-error" role="alert">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    {{ $message }}
                </div>
                @enderror

                @php $cfgKonf = ($konfig ?? [])['konfirmasi_final'] ?? null; $showKonf = ! $cfgKonf || $cfgKonf->tampil; $wajibKonf = $cfgKonf ? ($cfgKonf->tampil && $cfgKonf->wajib) : true; @endphp
                @if($showKonf)
                <label class="checkbox-group" id="konfirmasi-label" style="padding:1rem; background:var(--color-background); border:2px solid var(--color-border); border-radius:var(--radius-md); cursor:pointer; transition:all 150ms;">
                    <input type="checkbox" name="konfirmasi_final" id="konfirmasi_final" value="1" @if($wajibKonf) aria-required="true" @endif>
                    <div>
                        <div style="font-weight:700; font-size:0.9375rem;">{{ $cfgKonf->label ?? 'Saya menyatakan seluruh data di atas benar dan dapat dipertanggungjawabkan' }} @if($wajibKonf)<span style="color:var(--color-error);">*</span>@endif</div>
                        <div style="font-size:0.8125rem; color:var(--color-text-secondary); margin-top:0.25rem;">{{ $cfgKonf->teks_bantuan ?? 'Pemalsuan data mengakibatkan pembatalan pendaftaran sesuai ketentuan PPDB.' }}</div>
                    </div>
                </label>
                @endif

                {{-- Field kustom Step 8 dari panel admin --}}
                @if(!empty($kustomStep8))
                    <div style="display:grid; gap:1rem; margin:1.25rem 0;">
                        @foreach($kustomStep8 as $field)
                            @include('components.form-field', ['field' => $field, 'data' => []])
                        @endforeach
                    </div>
                @endif

                <div class="form-actions">
                    <a href="{{ route('ppdb.step7') }}" class="btn btn-ghost">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-accent btn-lg" id="btn-submit" disabled>
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.269 20.876L5.999 12zm0 0h7.5"/></svg>
                        Submit Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const cb = document.getElementById('konfirmasi_final');
const btn = document.getElementById('btn-submit');
const lbl = document.getElementById('konfirmasi-label');

if (cb && btn) {
    // Jika checkbox disembunyikan admin (tampil=false): tombol tetap aktif.
    // Jika wajib: kunci tombol sampai dicentang. Jika opsional: biarkan aktif.
    const isWajib = cb.hasAttribute('aria-required') || cb.required;
    if (!isWajib) { btn.disabled = false; }
    cb.addEventListener('change', function() {
        if (!isWajib) { btn.disabled = false; return; }
        btn.disabled = !this.checked;
        if (lbl) {
            lbl.style.borderColor = this.checked ? 'var(--color-accent)' : 'var(--color-border)';
            lbl.style.background  = this.checked ? '#f0fdf4' : 'var(--color-background)';
        }
    });
    if (isWajib) { btn.disabled = !cb.checked; } else { btn.disabled = false; }
} else if (btn) {
    // Tanpa checkbox konfirmasi (dinonaktifkan admin): izinkan submit langsung.
    btn.disabled = false;
}

// Prevent double submit
document.getElementById('form-submit').addEventListener('submit', function() {
    btn.disabled = true;
    btn.innerHTML = '<svg class="ocr-spinner" style="width:1.125rem;height:1.125rem;margin-right:0.5rem;" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"></circle></svg> Mengirim...';
});
</script>
@endpush
