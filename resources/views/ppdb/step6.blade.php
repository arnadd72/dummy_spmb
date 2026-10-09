@extends('layouts.app')
@section('title', 'Langkah 6: Data Orang Tua — Pendaftaran PPDB')

@section('content')
<div class="form-page">
    <div class="container">
        <x-step-indicator :currentStep="6" />

        <div class="form-card">
            <div style="margin-bottom:1.5rem;">
                <h1 class="form-page-title">A. Data Orang Tua / Wali</h1>
                <p class="form-page-subtitle">Lengkapi data ayah dan ibu sesuai Kartu Keluarga (KK), termasuk tempat/tanggal lahir, pendidikan terakhir, dan alamat.</p>
            </div>

            @if (!empty($data['nama_ayah']) || !empty($data['nama_ibu']))
            <div class="alert alert-success" role="note" style="margin-bottom:1.5rem;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Data nama orang tua berhasil dideteksi dari Kartu Keluarga. Silakan lengkapi data lainnya.
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-error" role="alert">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <ul style="margin:0; padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('ppdb.step6.post') }}" novalidate>
                @csrf

                {{-- 14a. Ayah — tampil/wajib/label/opsi dari panel Isian Formulir --}}
                @php $adaAyah = collect(['nama_ayah','ayah_ttl','ayah_pendidikan','pekerjaan_ayah'])->contains(fn($k) => \App\Support\FormFieldConfig::tampil($konfig ?? null, $k, true)); @endphp
                @if($adaAyah)
                <div style="padding:1.25rem; background:var(--color-background); border-radius:var(--radius-lg); border:1px solid var(--color-border); margin-bottom:1.25rem;">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--color-primary-dark); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        14a. Ayah
                    </h2>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        @if(\App\Support\FormFieldConfig::tampil($konfig ?? null, 'nama_ayah', true))
                        @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,'nama_ayah'); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,'nama_ayah',true); @endphp
                        <div class="form-group" style="grid-column:1/-1;">
                            <label class="form-label" for="nama_ayah">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'nama_ayah','Nama Ayah') }} @if($w)<span class="required">*</span>@endif</label>
                            <input type="text" name="nama_ayah" id="nama_ayah" class="form-control {{ $errors->has('nama_ayah') ? 'error' : '' }}"
                                   value="{{ old('nama_ayah', $data['nama_ayah'] ?? $c->nilai_default ?? '') }}" placeholder="{{ $c->placeholder ?? 'Nama lengkap ayah' }}" @if($w) required aria-required="true" @endif>
                            @if($c?->teks_bantuan)<div class="form-hint">{{ $c->teks_bantuan }}</div>@endif
                            @error('nama_ayah')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        @if(\App\Support\FormFieldConfig::tampil($konfig ?? null, 'ayah_ttl', true))
                        @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,'ayah_ttl'); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,'ayah_ttl',true); @endphp
                        <div class="form-group">
                            <label class="form-label" for="ayah_ttl">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'ayah_ttl','Tempat / Tanggal Lahir') }} @if($w)<span class="required">*</span>@endif</label>
                            <input type="text" name="ayah_ttl" id="ayah_ttl" class="form-control {{ $errors->has('ayah_ttl') ? 'error' : '' }}"
                                   value="{{ old('ayah_ttl', $data['ayah_ttl'] ?? $c->nilai_default ?? '') }}" placeholder="{{ $c->placeholder ?? 'Contoh: Banyumas, 12 Mei 1975' }}" @if($w) required aria-required="true" @endif>
                            @if($c?->teks_bantuan)<div class="form-hint">{{ $c->teks_bantuan }}</div>@endif
                            @error('ayah_ttl')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        @if(\App\Support\FormFieldConfig::tampil($konfig ?? null, 'ayah_pendidikan', true))
                        @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,'ayah_pendidikan'); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,'ayah_pendidikan',true); $ops=\App\Support\FormFieldConfig::opsiMurni($konfig ?? null,'ayah_pendidikan',['SD','SMP/MTs','SMA/SMK/MA','D1','D2','D3','S1','S2','S3']); @endphp
                        <div class="form-group">
                            <label class="form-label" for="ayah_pendidikan">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'ayah_pendidikan','Pendidikan Terakhir') }} @if($w)<span class="required">*</span>@endif</label>
                            <select name="ayah_pendidikan" id="ayah_pendidikan" class="form-control {{ $errors->has('ayah_pendidikan') ? 'error' : '' }}" @if($w) required aria-required="true" @endif>
                                <option value="">— Pilih —</option>
                                @foreach ($ops as $p)
                                <option value="{{ $p }}" {{ old('ayah_pendidikan', $data['ayah_pendidikan'] ?? $c->nilai_default ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                            @if($c?->teks_bantuan)<div class="form-hint">{{ $c->teks_bantuan }}</div>@endif
                            @error('ayah_pendidikan')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        @if(\App\Support\FormFieldConfig::tampil($konfig ?? null, 'pekerjaan_ayah', true))
                        @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,'pekerjaan_ayah'); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,'pekerjaan_ayah',true); $ops=\App\Support\FormFieldConfig::opsiMurni($konfig ?? null,'pekerjaan_ayah',[]); @endphp
                        <div class="form-group" style="grid-column:1/-1;">
                            <label class="form-label" for="pekerjaan_ayah">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'pekerjaan_ayah','Pekerjaan') }} @if($w)<span class="required">*</span>@endif</label>
                            @if($ops)
                            <select name="pekerjaan_ayah" id="pekerjaan_ayah" class="form-control {{ $errors->has('pekerjaan_ayah') ? 'error' : '' }}" @if($w) required aria-required="true" @endif>
                                <option value="">— Pilih —</option>
                                @foreach($ops as $p)<option value="{{ $p }}" {{ old('pekerjaan_ayah', $data['pekerjaan_ayah'] ?? $c->nilai_default ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>@endforeach
                            </select>
                            @else
                            <input type="text" name="pekerjaan_ayah" id="pekerjaan_ayah" class="form-control {{ $errors->has('pekerjaan_ayah') ? 'error' : '' }}"
                                   value="{{ old('pekerjaan_ayah', $data['pekerjaan_ayah'] ?? $c->nilai_default ?? '') }}" placeholder="{{ $c->placeholder ?? 'Contoh: Wiraswasta, Petani, PNS' }}" @if($w) required aria-required="true" @endif>
                            @endif
                            @if($c?->teks_bantuan)<div class="form-hint">{{ $c->teks_bantuan }}</div>@endif
                            @error('pekerjaan_ayah')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- 14b. Ibu — dinamis panel admin --}}
                @php $adaIbu = collect(['nama_ibu','ibu_ttl','ibu_pendidikan','pekerjaan_ibu'])->contains(fn($k) => \App\Support\FormFieldConfig::tampil($konfig ?? null, $k, true)); @endphp
                @if($adaIbu)
                <div style="padding:1.25rem; background:var(--color-background); border-radius:var(--radius-lg); border:1px solid var(--color-border); margin-bottom:1.25rem;">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--color-primary-dark); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        14b. Ibu
                    </h2>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        @if(\App\Support\FormFieldConfig::tampil($konfig ?? null, 'nama_ibu', true))
                        @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,'nama_ibu'); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,'nama_ibu',true); @endphp
                        <div class="form-group" style="grid-column:1/-1;">
                            <label class="form-label" for="nama_ibu">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'nama_ibu','Nama Ibu') }} @if($w)<span class="required">*</span>@endif</label>
                            <input type="text" name="nama_ibu" id="nama_ibu" class="form-control {{ $errors->has('nama_ibu') ? 'error' : '' }}"
                                   value="{{ old('nama_ibu', $data['nama_ibu'] ?? $c->nilai_default ?? '') }}" placeholder="{{ $c->placeholder ?? 'Nama lengkap ibu' }}" @if($w) required aria-required="true" @endif>
                            @if($c?->teks_bantuan)<div class="form-hint">{{ $c->teks_bantuan }}</div>@endif
                            @error('nama_ibu')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        @if(\App\Support\FormFieldConfig::tampil($konfig ?? null, 'ibu_ttl', true))
                        @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,'ibu_ttl'); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,'ibu_ttl',true); @endphp
                        <div class="form-group">
                            <label class="form-label" for="ibu_ttl">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'ibu_ttl','Tempat / Tanggal Lahir') }} @if($w)<span class="required">*</span>@endif</label>
                            <input type="text" name="ibu_ttl" id="ibu_ttl" class="form-control {{ $errors->has('ibu_ttl') ? 'error' : '' }}"
                                   value="{{ old('ibu_ttl', $data['ibu_ttl'] ?? $c->nilai_default ?? '') }}" placeholder="{{ $c->placeholder ?? 'Contoh: Banyumas, 3 Agustus 1978' }}" @if($w) required aria-required="true" @endif>
                            @if($c?->teks_bantuan)<div class="form-hint">{{ $c->teks_bantuan }}</div>@endif
                            @error('ibu_ttl')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        @if(\App\Support\FormFieldConfig::tampil($konfig ?? null, 'ibu_pendidikan', true))
                        @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,'ibu_pendidikan'); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,'ibu_pendidikan',true); $ops=\App\Support\FormFieldConfig::opsiMurni($konfig ?? null,'ibu_pendidikan',['SD','SMP/MTs','SMA/SMK/MA','D1','D2','D3','S1','S2','S3']); @endphp
                        <div class="form-group">
                            <label class="form-label" for="ibu_pendidikan">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'ibu_pendidikan','Pendidikan Terakhir') }} @if($w)<span class="required">*</span>@endif</label>
                            <select name="ibu_pendidikan" id="ibu_pendidikan" class="form-control {{ $errors->has('ibu_pendidikan') ? 'error' : '' }}" @if($w) required aria-required="true" @endif>
                                <option value="">— Pilih —</option>
                                @foreach ($ops as $p)
                                <option value="{{ $p }}" {{ old('ibu_pendidikan', $data['ibu_pendidikan'] ?? $c->nilai_default ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                            @if($c?->teks_bantuan)<div class="form-hint">{{ $c->teks_bantuan }}</div>@endif
                            @error('ibu_pendidikan')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        @if(\App\Support\FormFieldConfig::tampil($konfig ?? null, 'pekerjaan_ibu', true))
                        @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,'pekerjaan_ibu'); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,'pekerjaan_ibu',true); $ops=\App\Support\FormFieldConfig::opsiMurni($konfig ?? null,'pekerjaan_ibu',[]); @endphp
                        <div class="form-group" style="grid-column:1/-1;">
                            <label class="form-label" for="pekerjaan_ibu">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'pekerjaan_ibu','Pekerjaan') }} @if($w)<span class="required">*</span>@endif</label>
                            @if($ops)
                            <select name="pekerjaan_ibu" id="pekerjaan_ibu" class="form-control {{ $errors->has('pekerjaan_ibu') ? 'error' : '' }}" @if($w) required aria-required="true" @endif>
                                <option value="">— Pilih —</option>
                                @foreach($ops as $p)<option value="{{ $p }}" {{ old('pekerjaan_ibu', $data['pekerjaan_ibu'] ?? $c->nilai_default ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>@endforeach
                            </select>
                            @else
                            <input type="text" name="pekerjaan_ibu" id="pekerjaan_ibu" class="form-control {{ $errors->has('pekerjaan_ibu') ? 'error' : '' }}"
                                   value="{{ old('pekerjaan_ibu', $data['pekerjaan_ibu'] ?? $c->nilai_default ?? '') }}" placeholder="{{ $c->placeholder ?? 'Contoh: Ibu Rumah Tangga, Guru' }}" @if($w) required aria-required="true" @endif>
                            @endif
                            @if($c?->teks_bantuan)<div class="form-hint">{{ $c->teks_bantuan }}</div>@endif
                            @error('pekerjaan_ibu')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($adaAlamat)
                <div style="padding:1.25rem; background:var(--color-background); border-radius:var(--radius-lg); border:1px solid var(--color-border); margin-bottom:1.25rem;">
                    <h2 style="font-size:0.9375rem; font-weight:700; color:var(--color-primary-dark); margin:0 0 0.5rem;">15. Alamat Orang Tua / Wali</h2>
                    <div class="form-hint" style="margin-bottom:0.75rem;">Sudah terisi otomatis dari <strong>Alamat Siswa (Langkah 5)</strong>. Centang di bawah bila tinggal serumah — tidak perlu ketik ulang. Ubah manual bila beda rumah.</div>
                    <label class="checkbox-group" style="margin-bottom:1rem; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius-md); padding:0.625rem 0.75rem;">
                        <input type="checkbox" id="alamat_sama" checked>
                        <div style="font-size:0.875rem;"><strong>Alamat sama dengan siswa</strong><br><span style="font-weight:400; color:var(--color-text-secondary); font-size:0.8125rem;" id="alamat_sama_label">{{ ($step5['desa'] ?? '') ? 'RT '.($step5['rt'] ?? '-').'/RW '.($step5['rw'] ?? '-').', '.($step5['desa'] ?? '').', Kec. '.($step5['kecamatan'] ?? '').', '.($step5['kabupaten'] ?? '').', '.($step5['propinsi'] ?? '') : 'Alamat siswa belum diisi' }}</span></div>
                    </label>
                    <div id="alamat_ortu_fields" style="opacity:0.6;">
                    <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:1rem;">
                        @foreach([['ortu_rt','RT','02'],['ortu_rw','RW','04']] as [$k,$lbl,$ph])
                        @if(\App\Support\FormFieldConfig::tampil($konfig ?? null,$k,true))
                        @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,$k); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,$k,true); @endphp
                        <div class="form-group">
                            <label class="form-label" for="{{ $k }}">{{ \App\Support\FormFieldConfig::label($konfig ?? null,$k,$lbl) }} @if($w)<span class="required">*</span>@endif</label>
                            <input type="text" name="{{ $k }}" id="{{ $k }}" class="form-control {{ $errors->has($k) ? 'error' : '' }}"
                                   value="{{ old($k, $data[$k] ?? $c->nilai_default ?? '') }}" placeholder="{{ $c->placeholder ?? $ph }}" maxlength="5" @if($w) required aria-required="true" @endif>
                            @error($k)<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @if(\App\Support\FormFieldConfig::tampil($konfig ?? null,'ortu_desa',true))
                    @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,'ortu_desa'); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,'ortu_desa',true); @endphp
                    <div class="form-group">
                        <label class="form-label" for="ortu_desa">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'ortu_desa','Desa / Kelurahan') }} @if($w)<span class="required">*</span>@endif</label>
                        <input type="text" name="ortu_desa" id="ortu_desa" class="form-control {{ $errors->has('ortu_desa') ? 'error' : '' }}"
                               value="{{ old('ortu_desa', $data['ortu_desa'] ?? $c->nilai_default ?? '') }}" placeholder="{{ $c->placeholder ?? 'Nama desa/kelurahan' }}" @if($w) required aria-required="true" @endif>
                        @error('ortu_desa')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    @endif
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem;">
                        @foreach([['ortu_kecamatan','Kecamatan','Wangon'],['ortu_kabupaten','Kabupaten','Banyumas'],['ortu_propinsi','Propinsi','Jawa Tengah']] as [$k,$lbl,$ph])
                        @if(\App\Support\FormFieldConfig::tampil($konfig ?? null,$k,true))
                        @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,$k); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,$k,true); @endphp
                        <div class="form-group">
                            <label class="form-label" for="{{ $k }}">{{ \App\Support\FormFieldConfig::label($konfig ?? null,$k,$lbl) }} @if($w)<span class="required">*</span>@endif</label>
                            <input type="text" name="{{ $k }}" id="{{ $k }}" class="form-control {{ $errors->has($k) ? 'error' : '' }}"
                                   value="{{ old($k, $data[$k] ?? $c->nilai_default ?? '') }}" placeholder="{{ $c->placeholder ?? $ph }}" @if($w) required aria-required="true" @endif>
                            @error($k)<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        @endforeach
                    </div>
                    </div>{{-- /#alamat_ortu_fields --}}
                </div>
                @endif

                {{-- No. Telepon Ortu — dinamis --}}
                @if(\App\Support\FormFieldConfig::tampil($konfig ?? null,'no_hp_ortu',true))
                @php $c=\App\Support\FormFieldConfig::field($konfig ?? null,'no_hp_ortu'); $w=\App\Support\FormFieldConfig::wajib($konfig ?? null,'no_hp_ortu',true); @endphp
                <div class="form-group">
                    <label class="form-label" for="no_hp_ortu">{{ \App\Support\FormFieldConfig::label($konfig ?? null,'no_hp_ortu','No. Telepon Wali / Orang Tua') }} @if($w)<span class="required">*</span>@endif</label>
                    <input type="tel" name="no_hp_ortu" id="no_hp_ortu" class="form-control {{ $errors->has('no_hp_ortu') ? 'error' : '' }}"
                           value="{{ old('no_hp_ortu', $data['no_hp_ortu'] ?? $c->nilai_default ?? '') }}" placeholder="{{ $c->placeholder ?? '08xxxxxxxxxx' }}" inputmode="tel" autocomplete="tel" @if($w) required aria-required="true" @endif>
                    <div class="form-hint">{{ $c->teks_bantuan ?? 'Nomor yang bisa dihubungi panitia PPDB untuk konfirmasi pendaftaran.' }}</div>
                    @error('no_hp_ortu')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                @endif

                @if(! empty($kustomStep6))
                <div style="padding:1.25rem; background:var(--color-background); border-radius:var(--radius-lg); border:1px dashed var(--color-border); margin-bottom:1.25rem;">
                    <h2 style="font-size:0.9375rem; font-weight:700; color:var(--color-primary-dark); margin:0 0 1rem;">Informasi Tambahan Orang Tua</h2>
                    @foreach($kustomStep6 as $field) @include('components.form-field',['field'=>$field,'data'=>$data]) @endforeach
                </div>
                @endif

                <div class="form-actions">
                    <a href="{{ route('ppdb.step5') }}" class="btn btn-ghost">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        Lanjut ke Bagian B–D
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function () {
    var cb = document.getElementById('alamat_sama');
    var wrap = document.getElementById('alamat_ortu_fields');
    if (!cb || !wrap) return;
    var siswa = {
        ortu_rt: @json($step5['rt'] ?? ''),
        ortu_rw: @json($step5['rw'] ?? ''),
        ortu_desa: @json($step5['desa'] ?? ''),
        ortu_kecamatan: @json($step5['kecamatan'] ?? ''),
        ortu_kabupaten: @json($step5['kabupaten'] ?? ''),
        ortu_propinsi: @json($step5['propinsi'] ?? '')
    };
    function terapkan(salinya) {
        Object.keys(siswa).forEach(function (k) {
            var el = document.getElementById(k);
            if (!el) return;
            if (salinya) {
                el.value = siswa[k] || el.value;
                el.readOnly = true;
                el.style.background = '#f8fafc';
            } else {
                el.readOnly = false;
                el.style.background = '';
            }
        });
        wrap.style.opacity = salinya ? '0.6' : '1';
    }
    cb.addEventListener('change', function () { terapkan(cb.checked); });
    terapkan(cb.checked);
})();
</script>
@endsection
