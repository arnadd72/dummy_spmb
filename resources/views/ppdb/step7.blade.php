@extends('layouts.app')
@section('title', 'Langkah 7: Keterangan Pendidikan, Kesehatan & Lain-lain — Pendaftaran PPDB')

@section('content')
<div class="form-page">
    <div class="container">
        <x-step-indicator :currentStep="7" />

        <div class="form-card" style="max-width:900px;">
            <div style="margin-bottom:1.5rem;">
                <h1 class="form-page-title">B. Keterangan Pendidikan</h1>
                <p class="form-page-subtitle">Lengkapi riwayat pendidikan, nilai ujian, kondisi kesehatan, dan kegiatan murid baru sesuai formulir resmi SPMB {{ $tahunAjaran ?? '2027/2028' }}.</p>
            </div>

            @if($errors->any())
            <div class="alert alert-error" role="alert">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <ul style="margin:0; padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('ppdb.step7.post') }}" novalidate id="form-step7">
                @csrf

                {{-- ══ B. KETERANGAN PENDIDIKAN ══ --}}
                <section style="padding:1.25rem; background:var(--color-background); border-radius:var(--radius-lg); border:1px solid var(--color-border); margin-bottom:1.25rem;">
                    <h2 style="font-size:0.9375rem; font-weight:700; color:var(--color-primary-dark); margin:0 0 1rem;">1. Asal Sekolah</h2>

                    <div class="form-group">
                        <label class="form-label" for="smp_nama_tampil">a. SMP / MTs</label>
                        <input type="text" id="smp_nama_tampil" class="form-control" value="{{ $step5['asal_sekolah'] ?? '' }}" readonly aria-readonly="true" style="background:#fff; cursor:not-allowed;">
                        <div class="form-hint">Diambil dari Bagian A no. 9 (diisi pada langkah sebelumnya).</div>
                    </div>

                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem;">
                        <div class="form-group">
                            <label class="form-label" for="smp_kecamatan">Kecamatan <span class="required">*</span></label>
                            <input type="text" name="smp_kecamatan" id="smp_kecamatan" class="form-control {{ $errors->has('smp_kecamatan') ? 'error' : '' }}"
                                   value="{{ old('smp_kecamatan', $data['smp_kecamatan'] ?? '') }}" placeholder="Wangon" required aria-required="true">
                            @error('smp_kecamatan')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="smp_kabupaten">Kabupaten <span class="required">*</span></label>
                            <input type="text" name="smp_kabupaten" id="smp_kabupaten" class="form-control {{ $errors->has('smp_kabupaten') ? 'error' : '' }}"
                                   value="{{ old('smp_kabupaten', $data['smp_kabupaten'] ?? '') }}" placeholder="Banyumas" required aria-required="true">
                            @error('smp_kabupaten')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="smp_propinsi">Propinsi <span class="required">*</span></label>
                            <input type="text" name="smp_propinsi" id="smp_propinsi" class="form-control {{ $errors->has('smp_propinsi') ? 'error' : '' }}"
                                   value="{{ old('smp_propinsi', $data['smp_propinsi'] ?? '') }}" placeholder="Jawa Tengah" required aria-required="true">
                            @error('smp_propinsi')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:2fr 1fr; gap:1rem;">
                        @php $gabIjazah = ($step5['no_ijazah'] ?? '') . (($step5['no_ijazah'] ?? '') && ($step5['tamat_tahun'] ?? '') ? ' / ' : '') . ($step5['tamat_tahun'] ?? ''); @endphp
                        <div class="form-group">
                            <label class="form-label" for="ijazah_nomor_tahun">b. Nomor dan Tahun Ijazah</label>
                            <input type="text" name="ijazah_nomor_tahun" id="ijazah_nomor_tahun" class="form-control {{ $errors->has('ijazah_nomor_tahun') ? 'error' : '' }}"
                                   value="{{ old('ijazah_nomor_tahun', $data['ijazah_nomor_tahun'] ?? $gabIjazah) }}" placeholder="Contoh: DN-02 D/1234/2027">
                            @if(!empty($gabIjazah))<div class="form-hint">Terisi otomatis dari <strong>Langkah 5 (No. Ijazah + Tamat Tahun)</strong> — cek saja, ubah bila perlu. Tidak perlu isi 2x.</div>@endif
                            @error('ijazah_nomor_tahun')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="lama_belajar">c. Lama Belajar (Tahun) <span class="required">*</span></label>
                            <input type="text" name="lama_belajar" id="lama_belajar" class="form-control {{ $errors->has('lama_belajar') ? 'error' : '' }}"
                                   value="{{ old('lama_belajar', $data['lama_belajar'] ?? '3') }}" placeholder="3" maxlength="10" required aria-required="true">
                            @error('lama_belajar')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">d. Nilai Ujian <span class="required">*</span></label>
                        <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:1rem;">
                            @foreach (\App\Models\Pendaftar::opsiNilaiUjian() as $key => $label)
                            <div>
                                <label style="font-size:0.8125rem; font-weight:600; color:var(--color-text-secondary); display:block; margin-bottom:0.25rem;" for="nilai_{{ $key }}">
                                    {{ $loop->iteration }}. {{ $label }}
                                </label>
                                <input type="number" name="nilai_ujian[{{ $key }}]" id="nilai_{{ $key }}"
                                       class="form-control {{ $errors->has("nilai_ujian.$key") ? 'error' : '' }}"
                                       value="{{ old("nilai_ujian.$key", $data['nilai_ujian'][$key] ?? '') }}"
                                       placeholder="0 – 100" min="0" max="100" step="0.01" inputmode="decimal" required aria-required="true">
                                @error("nilai_ujian.$key")<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                            @endforeach
                        </div>
                        <div class="form-hint">Isi nilai ujian sekolah (skala 0–100) untuk setiap mata pelajaran.</div>
                    </div>
                </section>

                {{-- ══ C. KETERANGAN KESEHATAN ══ --}}
                <section style="padding:1.25rem; background:var(--color-background); border-radius:var(--radius-lg); border:1px solid var(--color-border); margin-bottom:1.25rem;">
                    <h2 style="font-size:0.9375rem; font-weight:700; color:var(--color-primary-dark); margin:0 0 1rem;">C. Keterangan Kesehatan</h2>
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem;">
                        @php $gdCfg=\App\Support\FormFieldConfig::field($konfig ?? null,'golongan_darah'); $gdWajib=\App\Support\FormFieldConfig::wajib($konfig ?? null,'golongan_darah',true); $gdOpsi=\App\Support\FormFieldConfig::opsiMurni($konfig ?? null,'golongan_darah',['A','B','AB','O','-']); @endphp
                        <div class="form-group">
                            <label class="form-label" for="golongan_darah">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'golongan_darah','1. Golongan Darah') }} @if($gdWajib)<span class="required">*</span>@endif</label>
                            <select name="golongan_darah" id="golongan_darah" class="form-control {{ $errors->has('golongan_darah') ? 'error' : '' }}" @if($gdWajib) required aria-required="true" @endif>
                                <option value="">— Pilih —</option>
                                @foreach ($gdOpsi as $gd)
                                <option value="{{ $gd }}" {{ old('golongan_darah', $data['golongan_darah'] ?? $gdCfg->nilai_default ?? '') === $gd ? 'selected' : '' }}>{{ $gd === '-' ? 'Belum diketahui' : $gd }}</option>
                                @endforeach
                            </select>
                            @if($gdCfg?->teks_bantuan)<div class="form-hint">{{ $gdCfg->teks_bantuan }}</div>@endif
                            @error('golongan_darah')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="tinggi_badan">2. Tinggi Badan (cm) <span class="required">*</span></label>
                            <input type="number" name="tinggi_badan" id="tinggi_badan" class="form-control {{ $errors->has('tinggi_badan') ? 'error' : '' }}"
                                   value="{{ old('tinggi_badan', $data['tinggi_badan'] ?? '') }}" placeholder="160" min="80" max="250" inputmode="numeric" required aria-required="true">
                            @error('tinggi_badan')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="berat_badan">2. Berat Badan (kg) <span class="required">*</span></label>
                            <input type="number" name="berat_badan" id="berat_badan" class="form-control {{ $errors->has('berat_badan') ? 'error' : '' }}"
                                   value="{{ old('berat_badan', $data['berat_badan'] ?? '') }}" placeholder="50" min="20" max="200" inputmode="numeric" required aria-required="true">
                            @error('berat_badan')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    @if(\App\Support\FormFieldConfig::tampil($konfig ?? null,'penyakit_pernah_diderita',true))
                    @php $pCfg=\App\Support\FormFieldConfig::field($konfig ?? null,'penyakit_pernah_diderita'); $pWajib=\App\Support\FormFieldConfig::wajib($konfig ?? null,'penyakit_pernah_diderita',false); @endphp
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="penyakit_pernah_diderita">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'penyakit_pernah_diderita','3. Penyakit yang Pernah Diderita') }} @if($pWajib)<span class="required">*</span>@endif</label>
                        <input type="text" name="penyakit_pernah_diderita" id="penyakit_pernah_diderita" class="form-control {{ $errors->has('penyakit_pernah_diderita') ? 'error' : '' }}"
                               value="{{ old('penyakit_pernah_diderita', $data['penyakit_pernah_diderita'] ?? $pCfg->nilai_default ?? '') }}" placeholder="{{ $pCfg->placeholder ?? 'Contoh: Maag, Asma (kosongkan jika tidak ada)' }}" @if($pWajib) required aria-required="true" @endif>
                        @if($pCfg?->teks_bantuan)<div class="form-hint">{{ $pCfg->teks_bantuan }}</div>@endif
                        @error('penyakit_pernah_diderita')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    @endif
                </section>

                {{-- ══ D. KETERANGAN LAIN-LAIN ══ --}}
                <section style="padding:1.25rem; background:var(--color-background); border-radius:var(--radius-lg); border:1px solid var(--color-border); margin-bottom:1.25rem;">
                    <h2 style="font-size:0.9375rem; font-weight:700; color:var(--color-primary-dark); margin:0 0 1rem;">D. Keterangan Lain-lain</h2>
                    <p style="font-size:0.8125rem; color:var(--color-text-muted); margin:0 0 1rem;">1. Kegemaran / Hobi / Prestasi murid baru</p>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div class="form-group">
                            <label class="form-label" for="hobi_olahraga">a. Olah Raga</label>
                            <input type="text" name="hobi_olahraga" id="hobi_olahraga" class="form-control"
                                   value="{{ old('hobi_olahraga', $data['hobi_olahraga'] ?? '') }}" placeholder="Contoh: Sepak bola">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="hobi_kesenian">b. Kesenian</label>
                            <input type="text" name="hobi_kesenian" id="hobi_kesenian" class="form-control"
                                   value="{{ old('hobi_kesenian', $data['hobi_kesenian'] ?? '') }}" placeholder="Contoh: Musik">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="hobi_organisasi">c. Keorganisasian</label>
                            <input type="text" name="hobi_organisasi" id="hobi_organisasi" class="form-control"
                                   value="{{ old('hobi_organisasi', $data['hobi_organisasi'] ?? '') }}" placeholder="Contoh: OSIS / Pramuka">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="hobi_prestasi">d. Prestasi yang Pernah Diraih</label>
                            <input type="text" name="hobi_prestasi" id="hobi_prestasi" class="form-control"
                                   value="{{ old('hobi_prestasi', $data['hobi_prestasi'] ?? '') }}" placeholder="Contoh: Juara 2 O2SN Kabupaten">
                        </div>
                        <div class="form-group" style="grid-column:1/-1;">
                            <label class="form-label" for="hobi_lain">e. Lain – Lain</label>
                            <input type="text" name="hobi_lain" id="hobi_lain" class="form-control"
                                   value="{{ old('hobi_lain', $data['hobi_lain'] ?? '') }}" placeholder="Contoh: Membaca, Coding">
                        </div>
                    </div>

                    @if(\App\Support\FormFieldConfig::tampil($konfig ?? null,'ukuran_seragam',true))
                    @php $uCfg=\App\Support\FormFieldConfig::field($konfig ?? null,'ukuran_seragam'); $uWajib=\App\Support\FormFieldConfig::wajib($konfig ?? null,'ukuran_seragam',true); $uOpsi=\App\Support\FormFieldConfig::opsiMurni($konfig ?? null,'ukuran_seragam',\App\Models\Pendaftar::opsiUkuranSeragam()); @endphp
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'ukuran_seragam','2. Ukuran Baju / Seragam') }} @if($uWajib)<span class="required">*</span>@endif</label>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(110px, 1fr)); gap:0.625rem;">
                            @foreach ($uOpsi as $ukuran)
                            <label class="checkbox-group" style="padding:0.75rem 0.875rem; border:2px solid {{ old('ukuran_seragam', $data['ukuran_seragam'] ?? $uCfg->nilai_default ?? '') === $ukuran ? 'var(--color-primary)' : 'var(--color-border)' }}; border-radius:var(--radius-md); cursor:pointer; justify-content:center;">
                                <input type="radio" name="ukuran_seragam" value="{{ $ukuran }}" {{ old('ukuran_seragam', $data['ukuran_seragam'] ?? $uCfg->nilai_default ?? '') === $ukuran ? 'checked' : '' }} @if($uWajib) required @endif>
                                <div style="font-weight:700; font-size:0.875rem;">{{ $ukuran }}</div>
                            </label>
                            @endforeach
                        </div>
                        @if($uCfg?->teks_bantuan)<div class="form-hint">{{ $uCfg->teks_bantuan }}</div>@endif
                        @error('ukuran_seragam')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    @endif
                </section>

                @if(! empty($kustomStep7))
                <section style="padding:1.25rem; background:var(--color-background); border-radius:var(--radius-lg); border:1px dashed var(--color-border); margin-bottom:1.25rem;">
                    <h2 style="font-size:0.9375rem; font-weight:700; color:var(--color-primary-dark); margin:0 0 1rem;">Informasi Tambahan</h2>
                    @foreach($kustomStep7 as $field) @include('components.form-field',['field'=>$field,'data'=>$data]) @endforeach
                </section>
                @endif

                <div class="form-actions">
                    <a href="{{ route('ppdb.step6') }}" class="btn btn-ghost">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        Lanjut ke Review
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
