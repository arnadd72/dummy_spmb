@extends('layouts.app')
@section('title', 'Langkah 5: Data Diri — Pendaftaran PPDB')

@section('content')
<div class="form-page">
    <div class="container">
        <x-step-indicator :currentStep="5" />

        <div class="form-card">
            <div style="margin-bottom:1.5rem;">
                <h1 class="form-page-title">A. Keterangan Diri Murid Baru</h1>
                <p class="form-page-subtitle">Isi data sesuai Akta Kelahiran, Kartu Keluarga, dan Ijazah. Beberapa data telah disinkronkan otomatis dari hasil pembacaan dokumen Anda.</p>
            </div>

            @if (!empty($data['nama_lengkap']) || !empty($data['nik']))
            <div class="alert alert-success" role="note" style="margin-bottom:1.5rem; display:flex; align-items:flex-start; gap:0.75rem;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0; margin-top:0.125rem;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div style="font-size:0.875rem; line-height:1.5;">
                    <strong>Data Otomatis Terverifikasi:</strong>
                    <strong>Nama Lengkap</strong> dan <strong>TTL</strong> diambil resmi dari <em>Akta Kelahiran</em>. <strong>NIK</strong> dan <strong>alamat</strong> dicocokkan dari <em>Kartu Keluarga</em>. Silakan periksa dan lengkapi sisanya.
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-error" role="alert">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <ul style="margin:0; padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('ppdb.step5.post') }}" novalidate id="form-step5">
                @csrf

                {{-- 1. Nama Lengkap — sudah diisi di Langkah 1, tampil kunci agar tidak isi 2x --}}
                <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.375rem;">
                        <label class="form-label" for="nama_lengkap" style="margin:0;">1. Nama Lengkap Murid Baru <span class="required">*</span></label>
                        <span style="background:#eff6ff; color:#1d4ed8; font-size:0.75rem; font-weight:700; padding:0.15rem 0.5rem; border-radius:999px;">🔒 Dari Langkah 1</span>
                    </div>
                    <input type="text" name="nama_lengkap" id="nama_lengkap"
                           class="form-control {{ $errors->has('nama_lengkap') ? 'error' : '' }}"
                           value="{{ old('nama_lengkap', $data['nama_lengkap'] ?? '') }}"
                           placeholder="Sesuai akta kelahiran" required aria-required="true" autocomplete="name" readonly
                           style="font-weight:600; text-transform:uppercase; background:#f8fafc; cursor:not-allowed;">
                    <div class="form-hint">Sudah diisi pada <a href="{{ route('ppdb.step1') }}">Langkah 1</a> — tidak perlu isi ulang. Ubah di sana bila salah ketik.</div>
                    @error('nama_lengkap')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- 2. Tempat / Tanggal Lahir --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                    <div class="form-group">
                        <label class="form-label" for="tempat_lahir">2. Tempat Lahir <span class="required">*</span></label>
                        <input type="text" name="tempat_lahir" id="tempat_lahir"
                               class="form-control {{ $errors->has('tempat_lahir') ? 'error' : '' }}"
                               value="{{ old('tempat_lahir', $data['tempat_lahir'] ?? '') }}"
                               placeholder="Contoh: Banyumas" required aria-required="true">
                        @error('tempat_lahir')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tanggal_lahir">2. Tanggal Lahir <span class="required">*</span></label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                               class="form-control {{ $errors->has('tanggal_lahir') ? 'error' : '' }}"
                               value="{{ old('tanggal_lahir', $data['tanggal_lahir'] ?? '') }}"
                               max="{{ date('Y-m-d') }}" required aria-required="true">
                        @error('tanggal_lahir')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- 3. NISN — sudah diisi di Langkah 1, tampil kunci agar tidak isi 2x --}}
                <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.375rem;">
                        <label class="form-label" for="nisn" style="margin:0;">3. NISN <span class="required">*</span></label>
                        <span style="background:#eff6ff; color:#1d4ed8; font-size:0.75rem; font-weight:700; padding:0.15rem 0.5rem; border-radius:999px;">🔒 Dari Langkah 1</span>
                    </div>
                    <input type="text" name="nisn" id="nisn"
                           class="form-control {{ $errors->has('nisn') ? 'error' : '' }}"
                           value="{{ old('nisn', $data['nisn'] ?? '') }}"
                           placeholder="10 digit" maxlength="10" inputmode="numeric" autocomplete="off" readonly
                           style="font-family:var(--font-mono, monospace); font-weight:600; letter-spacing:0.06em; background:#f8fafc; cursor:not-allowed;">
                    <div class="form-hint">Sudah diisi pada <a href="{{ route('ppdb.step1') }}">Langkah 1</a> — tidak perlu isi ulang.</div>
                    @error('nisn')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- 4. NIK & No KK --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.375rem;">
                            <label class="form-label" for="nik" style="margin:0;">4. NIK Murid <span class="required">*</span></label>
                            <span style="background:#f0fdf4; color:#15803d; font-size:0.75rem; font-weight:700; padding:0.15rem 0.5rem; border-radius:999px;">📑 dari KK</span>
                        </div>
                        <input type="text" name="nik" id="nik"
                               class="form-control {{ $errors->has('nik') ? 'error' : '' }}"
                               value="{{ old('nik', $data['nik'] ?? '') }}"
                               placeholder="3302xxxxxxxx0001" maxlength="16" inputmode="numeric" pattern="\d{16}"
                               required aria-required="true" autocomplete="off"
                               style="font-family:var(--font-mono, monospace); font-weight:700; letter-spacing:0.06em;">
                        @error('nik')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="no_kk">4. No. Kartu Keluarga (KK) <span class="required">*</span></label>
                        <input type="text" name="no_kk" id="no_kk"
                               class="form-control {{ $errors->has('no_kk') ? 'error' : '' }}"
                               value="{{ old('no_kk', $data['no_kk'] ?? '') }}"
                               placeholder="16 digit" maxlength="16" inputmode="numeric" pattern="\d{16}"
                               required aria-required="true" autocomplete="off"
                               style="font-family:var(--font-mono, monospace); font-weight:700; letter-spacing:0.06em;">
                        @error('no_kk')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- 5. Jenis Kelamin --}}
                <div class="form-group">
                    <label class="form-label" for="jenis_kelamin">5. Jenis Kelamin <span class="required">*</span></label>
                    <select name="jenis_kelamin" id="jenis_kelamin"
                            class="form-control {{ $errors->has('jenis_kelamin') ? 'error' : '' }}" required aria-required="true">
                        <option value="">— Pilih —</option>
                        <option value="L" {{ old('jenis_kelamin', $data['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' }}>Laki – laki</option>
                        <option value="P" {{ old('jenis_kelamin', $data['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- 6. Agama — opsi/label/wajib/tampil dikendalikan panel admin Isian Formulir --}}
                @if (\App\Support\FormFieldConfig::tampil($konfig ?? null, 'agama', true))
                <div class="form-group">
                    @php
                        $agamaCfg = \App\Support\FormFieldConfig::field($konfig ?? null, 'agama');
                        $agamaWajib = \App\Support\FormFieldConfig::wajib($konfig ?? null, 'agama', true);
                        $agamaLabel = \App\Support\FormFieldConfig::label($konfig ?? null, 'agama', '6. Agama');
                        $agamaOpsi = \App\Support\FormFieldConfig::opsiMurni($konfig ?? null, 'agama', ['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu']);
                    @endphp
                    <label class="form-label" for="agama">{{ $agamaLabel }} @if($agamaWajib)<span class="required">*</span>@endif</label>
                    <select name="agama" id="agama"
                            class="form-control {{ $errors->has('agama') ? 'error' : '' }}" @if($agamaWajib) required aria-required="true" @endif>
                        <option value="">— Pilih —</option>
                        @foreach ($agamaOpsi as $ag)
                        <option value="{{ $ag }}" {{ old('agama', $data['agama'] ?? $agamaCfg->nilai_default ?? '') === $ag ? 'selected' : '' }}>{{ $ag }}</option>
                        @endforeach
                    </select>
                    @if($agamaCfg?->teks_bantuan)<div class="form-hint">{{ $agamaCfg->teks_bantuan }}</div>@endif
                    @error('agama')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                @endif

                {{-- 7 & 8. Anak ke / Jumlah saudara --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                    <div class="form-group">
                        <label class="form-label" for="anak_ke">7. Anak ke berapa <span class="required">*</span></label>
                        <input type="number" name="anak_ke" id="anak_ke"
                               class="form-control {{ $errors->has('anak_ke') ? 'error' : '' }}"
                               value="{{ old('anak_ke', $data['anak_ke'] ?? '') }}"
                               placeholder="1" min="1" max="20" inputmode="numeric" required aria-required="true">
                        @error('anak_ke')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="jumlah_saudara">8. Jumlah saudara kandung <span class="required">*</span></label>
                        <input type="number" name="jumlah_saudara" id="jumlah_saudara"
                               class="form-control {{ $errors->has('jumlah_saudara') ? 'error' : '' }}"
                               value="{{ old('jumlah_saudara', $data['jumlah_saudara'] ?? '') }}"
                               placeholder="2" min="0" max="20" inputmode="numeric" required aria-required="true">
                        @error('jumlah_saudara')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- 9. Asal Sekolah --}}
                <div class="form-group">
                    <label class="form-label" for="asal_sekolah">9. Asal Sekolah (SMP/MTs) <span class="required">*</span></label>
                    <input type="text" name="asal_sekolah" id="asal_sekolah"
                           class="form-control {{ $errors->has('asal_sekolah') ? 'error' : '' }}"
                           value="{{ old('asal_sekolah', $data['asal_sekolah'] ?? '') }}"
                           placeholder="Nama sekolah asal, contoh: SMP Negeri 1 Wangon" required aria-required="true">
                    @error('asal_sekolah')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- 10 & 11. Tamat tahun / No Ijazah --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                    <div class="form-group">
                        <label class="form-label" for="tamat_tahun">10. Tamat SMP / MTs Tahun <span class="required">*</span></label>
                        <input type="text" name="tamat_tahun" id="tamat_tahun"
                               class="form-control {{ $errors->has('tamat_tahun') ? 'error' : '' }}"
                               value="{{ old('tamat_tahun', $data['tamat_tahun'] ?? '') }}"
                               placeholder="{{ date('Y') }}" maxlength="10" required aria-required="true">
                        @error('tamat_tahun')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="no_ijazah">11. No. Ijazah</label>
                        <input type="text" name="no_ijazah" id="no_ijazah"
                               class="form-control {{ $errors->has('no_ijazah') ? 'error' : '' }}"
                               value="{{ old('no_ijazah', $data['no_ijazah'] ?? '') }}"
                               placeholder="Nomor ijazah SMP/MTs">
                        @error('no_ijazah')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- 12. No Telepon Murid --}}
                <div class="form-group">
                    <label class="form-label" for="no_hp">12. No. Telepon Murid <span class="required">*</span></label>
                    <input type="tel" name="no_hp" id="no_hp"
                           class="form-control {{ $errors->has('no_hp') ? 'error' : '' }}"
                           value="{{ old('no_hp', $data['no_hp'] ?? '') }}"
                           placeholder="08xxxxxxxxxx" inputmode="tel" autocomplete="tel" required aria-required="true">
                    <div class="form-hint">Nomor telepon aktif murid untuk dihubungi panitia PPDB.</div>
                    @error('no_hp')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- No WhatsApp (kontak tambahan sistem) --}}
                <div class="form-group">
                    <label class="form-label" for="no_wa">
                        No. WhatsApp Murid
                        <span style="font-weight:400; color:var(--color-text-muted); font-size:0.8125rem;">(boleh sama dengan No. Telepon)</span>
                    </label>
                    <input type="tel" name="no_wa" id="no_wa"
                           class="form-control {{ $errors->has('no_wa') ? 'error' : '' }}"
                           value="{{ old('no_wa', $data['no_wa'] ?? '') }}"
                           placeholder="08xxxxxxxxxx" inputmode="tel" autocomplete="tel">
                    <div class="form-hint">Nomor WhatsApp aktif untuk info cepat &amp; konfirmasi panitia.</div>
                    @error('no_wa')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- 13. Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">13. Email <span class="required">*</span></label>
                    <input type="email" name="email" id="email"
                           class="form-control {{ $errors->has('email') ? 'error' : '' }}"
                           value="{{ old('email', $data['email'] ?? '') }}"
                           placeholder="nama@email.com" autocomplete="email" required aria-required="true">
                    <div class="form-hint">Kode pendaftaran akan dikirim otomatis ke email ini. Pastikan penulisannya benar.</div>
                    @error('email')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- 15. Alamat Siswa (cukup isi sekali — di Langkah 6 tinggal centang "sama") --}}
                <div style="padding:1.25rem; background:var(--color-background); border-radius:var(--radius-lg); border:1px solid var(--color-border); margin-bottom:1.25rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                        <h2 style="font-size:0.9375rem; font-weight:700; color:var(--color-primary-dark); margin:0;">15. Alamat Tempat Tinggal Siswa</h2>
                        <span style="background:#f0fdf4; color:#15803d; font-size:0.75rem; font-weight:700; padding:0.15rem 0.5rem; border-radius:999px;">📑 dari KK</span>
                    </div>
                    <div class="form-hint" style="margin-bottom:1rem;">Isi sekali di sini. Nanti di <strong>Langkah 6 (Data Orang Tua)</strong> cukup centang <em>"Alamat sama dengan siswa"</em> bila tinggal serumah — tidak perlu ketik ulang.</div>
                    <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:1rem;">
                        <div class="form-group">
                            <label class="form-label" for="rt">RT <span class="required">*</span></label>
                            <input type="text" name="rt" id="rt" class="form-control {{ $errors->has('rt') ? 'error' : '' }}"
                                   value="{{ old('rt', $data['rt'] ?? '') }}" placeholder="02" maxlength="5" required aria-required="true">
                            @error('rt')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="rw">RW <span class="required">*</span></label>
                            <input type="text" name="rw" id="rw" class="form-control {{ $errors->has('rw') ? 'error' : '' }}"
                                   value="{{ old('rw', $data['rw'] ?? '') }}" placeholder="04" maxlength="5" required aria-required="true">
                            @error('rw')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="desa">Desa / Kelurahan <span class="required">*</span></label>
                        <input type="text" name="desa" id="desa" class="form-control {{ $errors->has('desa') ? 'error' : '' }}"
                               value="{{ old('desa', $data['desa'] ?? '') }}" placeholder="Nama desa/kelurahan" required aria-required="true">
                        @error('desa')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem;">
                        <div class="form-group">
                            <label class="form-label" for="kecamatan">Kecamatan <span class="required">*</span></label>
                            <input type="text" name="kecamatan" id="kecamatan" class="form-control {{ $errors->has('kecamatan') ? 'error' : '' }}"
                                   value="{{ old('kecamatan', $data['kecamatan'] ?? '') }}" placeholder="Wangon" required aria-required="true">
                            @error('kecamatan')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="kabupaten">Kabupaten <span class="required">*</span></label>
                            <input type="text" name="kabupaten" id="kabupaten" class="form-control {{ $errors->has('kabupaten') ? 'error' : '' }}"
                                   value="{{ old('kabupaten', $data['kabupaten'] ?? '') }}" placeholder="Banyumas" required aria-required="true">
                            @error('kabupaten')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="propinsi">Propinsi <span class="required">*</span></label>
                            <input type="text" name="propinsi" id="propinsi" class="form-control {{ $errors->has('propinsi') ? 'error' : '' }}"
                                   value="{{ old('propinsi', $data['propinsi'] ?? '') }}" placeholder="Jawa Tengah" required aria-required="true">
                            @error('propinsi')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- 16. Penghasilan Bulanan — label opsi bisa diubah dari panel admin, kode tetap --}}
                @if (\App\Support\FormFieldConfig::tampil($konfig ?? null, 'penghasilan', true))
                @php
                    $hasilCfg = \App\Support\FormFieldConfig::field($konfig ?? null, 'penghasilan');
                    $hasilWajib = \App\Support\FormFieldConfig::wajib($konfig ?? null, 'penghasilan', true);
                    $hasilLabel = \App\Support\FormFieldConfig::label($konfig ?? null, 'penghasilan', '16. Penghasilan Bulanan Orang Tua');
                    $hasilOpsi = \App\Support\FormFieldConfig::opsiBerlabel($konfig ?? null, 'penghasilan', array_keys(\App\Models\Pendaftar::opsiPenghasilan()), array_values(\App\Models\Pendaftar::opsiPenghasilan()));
                @endphp
                <div class="form-group">
                    <label class="form-label">{{ $hasilLabel }} @if($hasilWajib)<span class="required">*</span>@endif</label>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:0.625rem;">
                        @foreach ($hasilOpsi as $i => $op)
                        <label class="checkbox-group" style="padding:0.75rem 0.875rem; border:2px solid {{ old('penghasilan', $data['penghasilan'] ?? $hasilCfg->nilai_default ?? '') === $op['value'] ? 'var(--color-primary)' : 'var(--color-border)' }}; border-radius:var(--radius-md); cursor:pointer; align-items:center;">
                            <input type="radio" name="penghasilan" value="{{ $op['value'] }}" {{ old('penghasilan', $data['penghasilan'] ?? $hasilCfg->nilai_default ?? '') === $op['value'] ? 'checked' : '' }} @if($hasilWajib) required @endif>
                            <div>
                                <div style="font-weight:700; font-size:0.8125rem;">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}).</div>
                                <div style="font-size:0.8125rem;">{{ $op['label'] }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @if($hasilCfg?->teks_bantuan)<div class="form-hint">{{ $hasilCfg->teks_bantuan }}</div>@endif
                    @error('penghasilan')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                @endif

                {{-- Referensi Pendaftar — bisa disembunyikan/diwajibkan dari panel admin --}}
                @if (\App\Support\FormFieldConfig::tampil($konfig ?? null, 'referensi_pendaftar', true))
                @php
                    $refCfg = \App\Support\FormFieldConfig::field($konfig ?? null, 'referensi_pendaftar');
                    $refWajib = \App\Support\FormFieldConfig::wajib($konfig ?? null, 'referensi_pendaftar', false);
                    $refLabel = \App\Support\FormFieldConfig::label($konfig ?? null, 'referensi_pendaftar', 'Referensi Pendaftar');
                @endphp
                <div class="form-group">
                    <label class="form-label" for="referensi_pendaftar">
                        {{ $refLabel }}
                        @if($refWajib)<span class="required">*</span>@else<span style="font-weight:400; color:var(--color-text-muted); font-size:0.8125rem;">(opsional)</span>@endif
                    </label>
                    <input type="text" name="referensi_pendaftar" id="referensi_pendaftar"
                           class="form-control {{ $errors->has('referensi_pendaftar') ? 'error' : '' }}"
                           value="{{ old('referensi_pendaftar', $data['referensi_pendaftar'] ?? $refCfg->nilai_default ?? '') }}"
                           placeholder="{{ $refCfg->placeholder ?? 'Dari siapa / dari mana informasi pendaftaran didapat' }}" @if($refWajib) required aria-required="true" @endif>
                    <div class="form-hint">{{ $refCfg->teks_bantuan ?? 'Dari siapa / dari mana informasi pendaftaran yang didapatkan calon murid baru.' }}</div>
                    @error('referensi_pendaftar')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                @endif

                {{-- Isian kustom tambahan dari panel admin (Isian Formulir → Tambah Isian) --}}
                @if (! empty($kustomStep5))
                <div style="padding:1.25rem; background:var(--color-background); border-radius:var(--radius-lg); border:1px dashed var(--color-border); margin-bottom:1.25rem;">
                    <h2 style="font-size:0.9375rem; font-weight:700; color:var(--color-primary-dark); margin:0 0 1rem;">Informasi Tambahan</h2>
                    @foreach ($kustomStep5 as $field)
                        @include('components.form-field', ['field' => $field, 'data' => $data])
                    @endforeach
                </div>
                @endif

                <div class="form-actions">
                    <a href="{{ route('ppdb.step4') }}" class="btn btn-ghost">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        Lanjut ke Data Orang Tua
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Batasi input numerik: NIK & No. KK = 16 digit, NISN = 10 digit.
[['nik', 16], ['no_kk', 16], ['nisn', 10]].forEach(function ([id, max]) {
    var el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, max);
    });
});
</script>
@endpush
