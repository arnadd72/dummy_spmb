@extends('layouts.app')
@section('title', 'Langkah 1: Identitas & Persetujuan — Pendaftaran PPDB')
@section('meta_description')Masukkan nama lengkap dan NISN calon peserta didik serta setujui persyaratan PPDB SMK Ma'arif NU 1 Wangon {{ $tahunAjaran ?? '2027/2028' }}.@endsection

@section('content')
<div class="form-page" style="padding: 2.5rem 0 4rem;">
    <div class="container" style="max-width: 820px; margin: 0 auto; padding: 0 1rem;">
        <x-step-indicator :currentStep="1" />

        <div class="form-card" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl); box-shadow: var(--shadow-elevated); padding: 2.5rem; margin-top: 2rem;">
            
            {{-- Header Form (Clean Typography, No Decorative AI-Slop Icons) --}}
            <div style="margin-bottom: 2.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 1.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.8125rem; font-weight: 800; text-transform: uppercase; color: var(--color-primary); letter-spacing: 0.08em;">
                        Tahap 1 dari 8 : Verifikasi Awal
                    </span>
                    <span style="font-size: 0.8125rem; color: var(--color-text-muted); font-weight: 600;">
                        Tahun Ajaran {{ $tahunAjaran ?? '2027/2028' }}
                    </span>
                </div>
                <h1 class="form-page-title" style="font-size: 1.85rem; font-weight: 800; color: var(--color-primary-dark); margin: 0 0 0.5rem; letter-spacing: -0.02em;">
                    Identitas Calon Siswa & Persetujuan
                </h1>
                <p class="form-page-subtitle" style="font-size: 0.95rem; color: var(--color-text-secondary); margin: 0; line-height: 1.6;">
                    Lengkapi nama lengkap dan NISN resmi Anda. Data ini akan digunakan untuk mencocokkan dokumen pada tahap unggah berkas.
                </p>
            </div>

            @if($errors->any())
            <div class="alert alert-error" role="alert" style="margin-bottom: 2rem; background: #FEF2F2; border: 1px solid #FCA5A5; border-left: 4px solid #DC2626; border-radius: var(--radius-md); padding: 1rem 1.25rem; color: #991B1B;">
                <div style="font-weight: 700; font-size: 0.9rem; margin-bottom: 0.35rem;">Mohon periksa kembali input formulir:</div>
                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.85rem; line-height: 1.5;">
                    @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('ppdb.step1.post') }}" novalidate id="form-step1">
                @csrf

                {{-- Bagian 1: Identitas Dasar --}}
                <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 1.75rem; margin-bottom: 1.75rem; box-shadow: var(--shadow-card);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--color-border);">
                        <div>
                            <h2 style="font-size: 1.125rem; font-weight: 800; color: var(--color-primary-dark); margin: 0;">
                                <span style="color: var(--color-primary); margin-right: 0.35rem;">01.</span> Identitas Dasar Siswa
                            </h2>
                            <span style="font-size: 0.8125rem; color: var(--color-text-muted);">Data identitas pokok untuk pencocokan berkas</span>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-primary); background: var(--color-muted); padding: 0.25rem 0.65rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                            Wajib Diisi
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
                        @php
                            $cfgNama = ($konfig ?? [])['nama_lengkap'] ?? null;
                            $cfgNisn = ($konfig ?? [])['nisn'] ?? null;
                            $showNama = ! $cfgNama || $cfgNama->tampil;
                            $showNisn = ! $cfgNisn || $cfgNisn->tampil;
                            $wajibNama = $cfgNama ? ($cfgNama->tampil && $cfgNama->wajib) : true;
                            $wajibNisn = $cfgNisn ? ($cfgNisn->tampil && $cfgNisn->wajib) : true;
                        @endphp
                        {{-- Nama Lengkap --}}
                        @if($showNama)
                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem;">
                                <label class="form-label" for="nama_lengkap" style="font-weight: 700; color: var(--color-text); margin: 0;">
                                    {{ $cfgNama->label ?? 'Nama Lengkap Calon Siswa' }}
                                    @if($wajibNama)<span style="color: var(--color-error); font-weight: bold;" aria-label="wajib">*</span>@endif
                                </label>
                                <span style="font-size: 0.75rem; color: var(--color-text-muted);">Sesuai Ijazah SMP / Akta</span>
                            </div>
                            <input type="text"
                                   name="nama_lengkap"
                                   id="nama_lengkap"
                                   class="form-control {{ $errors->has('nama_lengkap') ? 'error' : '' }}"
                                   value="{{ old('nama_lengkap', $step1['nama_lengkap'] ?? '') }}"
                                   placeholder="{{ $cfgNama->placeholder ?? 'Contoh: Muhammad Rizky Pratama' }}"
                                   @if($wajibNama) required aria-required="true" @endif
                                   autocomplete="name"
                                   style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid {{ $errors->has('nama_lengkap') ? 'var(--color-error)' : 'var(--color-border)' }}; border-radius: var(--radius-md); font-size: 1rem; font-weight: 600; color: var(--color-text); background: var(--color-surface); transition: all 0.2s;">
                            @if(!empty($cfgNama->teks_bantuan))
                            <div class="form-hint" style="font-size: 0.8125rem; color: var(--color-text-muted); margin-top: 0.4rem; line-height: 1.5;">
                                {{ $cfgNama->teks_bantuan }}
                            </div>
                            @else
                            <div class="form-hint" style="font-size: 0.8125rem; color: var(--color-text-muted); margin-top: 0.4rem; line-height: 1.5;">
                                Nama ini dipakai sistem untuk mencocokkan NIK secara tepat pada Kartu Keluarga (KK) dan Akta Kelahiran.
                            </div>
                            @endif
                            @error('nama_lengkap')<div class="form-error" style="color: var(--color-error); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
                        </div>
                        @endif

                        {{-- NISN --}}
                        @if($showNisn)
                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem;">
                                <label class="form-label" for="nisn" style="font-weight: 700; color: var(--color-text); margin: 0;">
                                    {{ $cfgNisn->label ?? 'NISN (Nomor Induk Siswa Nasional)' }}
                                    @if($wajibNisn)<span style="color: var(--color-error); font-weight: bold;" aria-label="wajib">*</span>@endif
                                </label>
                                <span id="nisn-counter" style="font-size: 0.75rem; font-weight: 700; font-family: monospace; color: var(--color-text-muted); background: var(--color-muted); padding: 0.2rem 0.6rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                                    0 / 10 Digit
                                </span>
                            </div>
                            <input type="text"
                                   name="nisn"
                                   id="nisn"
                                   class="form-control {{ $errors->has('nisn') ? 'error' : '' }}"
                                   value="{{ old('nisn', $step1['nisn'] ?? '') }}"
                                   placeholder="{{ $cfgNisn->placeholder ?? 'Contoh: 0081234567' }}"
                                   maxlength="10"
                                   inputmode="numeric"
                                   @if($wajibNisn) required aria-required="true" @endif
                                   autocomplete="off"
                                   style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid {{ $errors->has('nisn') ? 'var(--color-error)' : 'var(--color-border)' }}; border-radius: var(--radius-md); font-family: monospace; letter-spacing: 0.12em; font-size: 1.05rem; font-weight: 700; color: var(--color-primary-dark); background: var(--color-surface); transition: all 0.2s;">
                            <div class="form-hint" style="font-size: 0.8125rem; color: var(--color-text-muted); margin-top: 0.4rem;">
                                {{ $cfgNisn->teks_bantuan ?? 'Tepat 10 digit angka resmi dari Kemendikbud atau rapor sekolah asal (SMP/MTs).' }}
                            </div>
                            @error('nisn')<div class="form-error" style="color: var(--color-error); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        {{-- Field kustom Step 1 dari panel admin --}}
                        @if(!empty($kustomStep1))
                            @foreach($kustomStep1 as $field)
                                @include('components.form-field', ['field' => $field, 'data' => array_merge($step1, $step1['data_kustom_step1'] ?? [])])
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- Penjelasan Alur Dokumen (Clean Editorial Box, No Sparkles/Slop) --}}
                <div style="background: var(--color-background); border: 1px solid var(--color-border); border-left: 4px solid var(--color-primary); border-radius: var(--radius-md); padding: 1.25rem 1.5rem; margin-bottom: 1.75rem;">
                    <div style="font-weight: 800; font-size: 0.92rem; color: var(--color-primary-dark); margin-bottom: 0.35rem;">
                        Alur Verifikasi Dokumen Otomatis
                    </div>
                    <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin: 0 0 0.85rem; line-height: 1.55;">
                        Di tahap berikutnya Anda cukup mengunggah foto dokumen asli. Sistem akan membaca data secara otomatis:
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem;">
                        <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; font-size: 0.8125rem; color: var(--color-text);">
                            <span style="font-weight: 800; color: var(--color-primary);">Akta Kelahiran:</span> Validasi nama lengkap resmi serta tempat & tanggal lahir.
                        </div>
                        <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: 0.65rem 0.85rem; font-size: 0.8125rem; color: var(--color-text);">
                            <span style="font-weight: 800; color: var(--color-accent);">Kartu Keluarga:</span> Pencocokan NIK siswa dan identitas orang tua / wali.
                        </div>
                    </div>
                </div>

                {{-- Bagian 2: Syarat & Ketentuan --}}
                <div style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 1.75rem; margin-bottom: 1.75rem; box-shadow: var(--shadow-card);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--color-border);">
                        <div>
                            <h3 style="font-size: 1.125rem; font-weight: 800; color: var(--color-primary-dark); margin: 0;">
                                <span style="color: var(--color-primary); margin-right: 0.35rem;">02.</span> Syarat & Ketentuan PPDB
                            </h3>
                            <span style="font-size: 0.8125rem; color: var(--color-text-muted);">Ketentuan penerimaan calon siswa SMK Ma'arif NU 1 Wangon</span>
                        </div>
                    </div>

                    <div style="background: var(--color-background); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 1.25rem 1.5rem; max-height: 240px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: var(--color-border) transparent;" tabindex="0" aria-label="Syarat dan ketentuan pendaftaran">
                        <div style="margin-bottom: 1rem;">
                            <div style="font-size: 0.875rem; font-weight: 800; color: var(--color-primary-dark); margin-bottom: 0.4rem;">
                                A. Persyaratan Calon Peserta Didik
                            </div>
                            <ol style="margin: 0; padding-left: 1.25rem; font-size: 0.84rem; color: var(--color-text-secondary); line-height: 1.75;">
                                <li>Lulusan atau calon lulusan SMP/MTs/sederajat tahun pelajaran 2026/2027.</li>
                                <li>Memiliki Surat Keterangan Lulus (SKL) atau nilai rapor semester akhir.</li>
                                <li>Berusia maksimal 21 tahun pada tanggal 1 Juli tahun pelajaran {{ $tahunAjaran ?? '2027/2028' }}.</li>
                                <li>Sehat jasmani dan rohani, serta tidak buta warna (khusus program keahlian FKK & TKR).</li>
                                <li>Mempersiapkan foto/scan dokumen asli: <strong>Akta Kelahiran</strong> dan <strong>Kartu Keluarga</strong>.</li>
                            </ol>
                        </div>

                        <div>
                            <div style="font-size: 0.875rem; font-weight: 800; color: var(--color-primary-dark); margin-bottom: 0.4rem;">
                                B. Keabsahan Data & Privasi
                            </div>
                            <ol style="margin: 0; padding-left: 1.25rem; font-size: 0.84rem; color: var(--color-text-secondary); line-height: 1.75;">
                                <li>Semua data yang diisikan harus benar dan dapat dipertanggungjawabkan sesuai dokumen resmi kependudukan.</li>
                                <li>Pemalsuan dokumen dapat mengakibatkan diskualifikasi atau pembatalan status pendaftaran.</li>
                                <li>Seluruh proses pencocokan dokumen (OCR) berjalan aman untuk melindungi privasi data calon siswa.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                {{-- Checkbox Persetujuan --}}
                @php $cfgSetuju = ($konfig ?? [])['setuju'] ?? null; $showSetuju = ! $cfgSetuju || $cfgSetuju->tampil; $wajibSetuju = $cfgSetuju ? ($cfgSetuju->tampil && $cfgSetuju->wajib) : true; @endphp
                @if($showSetuju)
                <label id="checkbox-card" class="checkbox-group" style="padding: 1.25rem 1.5rem; background: var(--color-background); border: 2px solid {{ $errors->has('setuju') ? 'var(--color-error)' : 'var(--color-border)' }}; border-radius: var(--radius-lg); cursor: pointer; transition: all 0.2s ease; display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 2rem;">
                    <input type="checkbox"
                           name="setuju"
                           id="setuju"
                           value="1"
                           {{ old('setuju', $step1['setuju'] ?? false) ? 'checked' : '' }}
                           @if($wajibSetuju) aria-required="true" @endif
                           aria-describedby="setuju-desc"
                           style="margin-top: 0.25rem; width: 1.25rem; height: 1.25rem; accent-color: var(--color-primary); cursor: pointer; flex-shrink: 0;">
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--color-primary-dark);" id="setuju-label">
                            {{ $cfgSetuju->label ?? 'Saya menyatakan data nama dan NISN di atas benar serta dokumen yang akan diunggah adalah asli' }}
                            @if($wajibSetuju)<span style="color: var(--color-error);">*</span>@endif
                        </div>
                        <div style="font-size: 0.8125rem; color: var(--color-text-secondary); margin-top: 0.25rem; line-height: 1.5;" id="setuju-desc">
                            {{ $cfgSetuju->teks_bantuan ?? "Dengan mencentang ini, saya menyetujui seluruh ketentuan PPDB SMK Ma'arif NU 1 Wangon TA " . ($tahunAjaran ?? '2027/2028') . '.' }}
                        </div>
                        @error('setuju')<div class="form-error" style="color: var(--color-error); font-size: 0.8125rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
                    </div>
                </label>
                @endif

                {{-- Tombol Aksi --}}
                <div class="form-actions" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="{{ route('ppdb.index') }}" class="btn btn-ghost" style="padding: 0.75rem 1.25rem; border-radius: var(--radius-md); font-weight: 600; color: var(--color-text-secondary); text-decoration: none; transition: all 0.2s;">
                        &larr; Kembali ke Beranda
                    </a>
                    <button type="submit" class="btn btn-primary" id="btn-mulai" style="padding: 0.85rem 1.75rem; background: var(--color-primary); color: #FFFFFF; border: none; border-radius: var(--radius-md); font-size: 1rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(5, 122, 60, 0.35); transition: all 0.2s;">
                        Lanjut ke Upload Dokumen &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// NISN: digit only limit to 10 & live counter update (null-guarded: field bisa disembunyikan admin)
const nisnInput = document.getElementById('nisn');
const nisnCounter = document.getElementById('nisn-counter');

function updateNisnCounter() {
    if (!nisnInput || !nisnCounter) return;
    const len = nisnInput.value.length;
    nisnCounter.textContent = `${len} / 10 Digit`;
    if (len === 10) {
        nisnCounter.style.background = 'var(--color-primary)';
        nisnCounter.style.color = '#FFFFFF';
        nisnCounter.style.borderColor = 'var(--color-primary)';
    } else {
        nisnCounter.style.background = 'var(--color-muted)';
        nisnCounter.style.color = 'var(--color-text-muted)';
        nisnCounter.style.borderColor = 'var(--color-border)';
    }
}

if (nisnInput) {
    nisnInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
        updateNisnCounter();
    });
    updateNisnCounter();
}

// Interactive highlight checkbox container with brand colors (null-guarded)
const checkbox = document.getElementById('setuju');
const checkCard = document.getElementById('checkbox-card');
function updateCheckboxStyle() {
    if (!checkbox || !checkCard) return;
    if (checkbox.checked) {
        checkCard.style.borderColor = 'var(--color-primary)';
        checkCard.style.background  = 'var(--color-muted)';
        checkCard.style.boxShadow   = '0 4px 12px rgba(5, 122, 60, 0.12)';
    } else {
        checkCard.style.borderColor = 'var(--color-border)';
        checkCard.style.background  = 'var(--color-background)';
        checkCard.style.boxShadow   = 'none';
    }
}
if (checkbox) {
    checkbox.addEventListener('change', updateCheckboxStyle);
    updateCheckboxStyle();
}
</script>
@endpush
