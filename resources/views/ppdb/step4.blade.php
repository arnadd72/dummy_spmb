@extends('layouts.app')
@section('title', 'Langkah 4: Pilih Jurusan — Pendaftaran PPDB')

@section('content')
<div class="form-page">
    <div class="container">
        <x-step-indicator :currentStep="4" />

        <div class="form-card" style="max-width:900px;">
            <div style="margin-bottom:1.5rem;">
                <h1 class="form-page-title">Pilih Jurusan & Rekomendasi</h1>
                <p class="form-page-subtitle">Sistem kami menghitung rekomendasi jurusan berdasarkan nilai dan minat Anda. Pilih maksimal 2 jurusan (pilihan 1 & 2).</p>
            </div>

            @if($errors->any())
            <div class="alert alert-error" role="alert">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <ul style="margin:0; padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('ppdb.step4.post') }}" novalidate id="form-step4">
                @csrf

                {{-- Minat Selection --}}
                @php $cfgMinat = ($konfig ?? [])['minat'] ?? null; $showMinat = ! $cfgMinat || $cfgMinat->tampil; $wajibMinat = $cfgMinat ? ($cfgMinat->tampil && $cfgMinat->wajib) : true; @endphp
                @if($showMinat)
                <div class="form-group">
                    <label class="form-label" for="minat">
                        {{ $cfgMinat->label ?? 'Bidang Minat Anda' }}
                        @if($wajibMinat)<span class="required" aria-label="wajib">*</span>@endif
                    </label>
                    <select name="minat" id="minat" class="form-control {{ $errors->has('minat') ? 'error' : '' }}" @if($wajibMinat) required aria-required="true" @endif>
                        <option value="">— Pilih bidang minat —</option>
                        <optgroup label="Akuntansi & Keuangan (AKL)">
                            <option value="angka" {{ old('minat') === 'angka' ? 'selected' : '' }}>Angka & Hitungan</option>
                            <option value="finansial" {{ old('minat') === 'finansial' ? 'selected' : '' }}>Keuangan & Perbankan</option>
                        </optgroup>
                        <optgroup label="Manajemen Perkantoran (MPLB)">
                            <option value="administrasi" {{ old('minat') === 'administrasi' ? 'selected' : '' }}>Administrasi & Perkantoran</option>
                            <option value="komunikasi" {{ old('minat') === 'komunikasi' ? 'selected' : '' }}>Komunikasi & Layanan Bisnis</option>
                        </optgroup>
                        <optgroup label="Farmasi (FKK)">
                            <option value="kesehatan" {{ old('minat') === 'kesehatan' ? 'selected' : '' }}>Kesehatan & Farmasi</option>
                            <option value="biologi" {{ old('minat') === 'biologi' ? 'selected' : '' }}>Sains & Biologi</option>
                        </optgroup>
                        <optgroup label="Teknik Kendaraan (TKR)">
                            <option value="otomotif" {{ old('minat') === 'otomotif' ? 'selected' : '' }}>Otomotif & Mesin</option>
                            <option value="teknik" {{ old('minat') === 'teknik' ? 'selected' : '' }}>Teknik & Perbaikan</option>
                        </optgroup>
                    </select>
                    @if(!empty($cfgMinat->teks_bantuan))<div class="form-hint">{{ $cfgMinat->teks_bantuan }}</div>@endif
                    @error('minat')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                @endif

                @php
                    $cfgJ1 = ($konfig ?? [])['jurusan_1'] ?? null;
                    $cfgJ2 = ($konfig ?? [])['jurusan_2'] ?? null;
                    $showJ1 = ! $cfgJ1 || $cfgJ1->tampil;
                    $showJ2 = ! $cfgJ2 || $cfgJ2->tampil;
                    $wajibJ1 = $cfgJ1 ? ($cfgJ1->tampil && $cfgJ1->wajib) : true;
                @endphp
                {{-- Jurusan Cards dengan Rekomendasi --}}
                @php
                $jurusanConfig = [
                    'AKL'  => ['color' => '#1E40AF', 'bg' => '#dbeafe', 'icon_path' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z'],
                    'MPLB' => ['color' => '#0369A1', 'bg' => '#e0f2fe', 'icon_path' => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z'],
                    'FKK'  => ['color' => '#166534', 'bg' => '#dcfce7', 'icon_path' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z'],
                    'TKR'  => ['color' => '#9A3412', 'bg' => '#ffedd5', 'icon_path' => 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z'],
                ];
                $badgeMap = ['green' => 'badge-green', 'yellow' => 'badge-yellow', 'gray' => 'badge-gray'];
                @endphp

                @if($showJ1)
                <div style="margin-bottom:1.5rem;">
                    <div style="font-weight:700; font-size:0.9375rem; margin-bottom:0.5rem; color:var(--color-text);">
                        {{ $cfgJ1->label ?? 'Pilihan Pertama' }} @if($wajibJ1)<span class="required" aria-label="wajib">*</span>@endif
                    </div>
                    <p style="font-size:0.875rem; color:var(--color-text-muted); margin-bottom:1rem;">{{ $cfgJ1->teks_bantuan ?? 'Jurusan yang paling Anda inginkan' }}</p>

                    <div class="jurusan-selector" role="radiogroup" aria-label="Pilihan jurusan pertama" aria-required="true">
                        @foreach ($jurusan as $j)
                        @php
                            $cfg = $jurusanConfig[$j->kode] ?? ['color' => '#1E40AF', 'bg' => '#dbeafe', 'icon_path' => ''];
                            $rek = $rekomendasiData[$j->id] ?? ['skor' => 0, 'label' => 'Kurang Cocok', 'color' => 'gray'];
                        @endphp
                        <div class="jurusan-option">
                            <input type="radio" name="jurusan_1" id="j1_{{ $j->id }}"
                                   value="{{ $j->id }}"
                                   {{ old('jurusan_1') == $j->id ? 'checked' : '' }}>
                            <label class="jurusan-option-label" for="j1_{{ $j->id }}" style="--card-color:{{ $cfg['color'] }};">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.625rem;">
                                    <div style="width:2.25rem; height:2.25rem; background:{{ $cfg['bg'] }}; border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="{{ $cfg['color'] }}" stroke-width="1.75" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cfg['icon_path'] }}"/>
                                        </svg>
                                    </div>
                                    <span class="rekomendasi-badge {{ $badgeMap[$rek['color']] ?? 'badge-gray' }}" style="font-size:0.6875rem;">
                                        {{ $rek['label'] }}
                                    </span>
                                </div>
                                <div style="font-size:0.75rem; font-weight:700; letter-spacing:0.06em; color:{{ $cfg['color'] }}; margin-bottom:0.25rem;">{{ $j->kode }}</div>
                                <div style="font-weight:700; font-size:0.875rem; color:var(--color-text); line-height:1.3; margin-bottom:0.375rem;">{{ $j->nama }}</div>
                                @if ($rek['skor'] > 0)
                                <div style="font-size:0.75rem; color:var(--color-text-muted);">Skor: {{ number_format($rek['skor'], 1) }}%</div>
                                @endif
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('jurusan_1')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                @endif

                @if($showJ2)
                <div style="margin-bottom:1.5rem; padding-top:1.25rem; border-top:1px solid var(--color-border);">
                    <div style="font-weight:700; font-size:0.9375rem; margin-bottom:0.375rem; color:var(--color-text);">{{ $cfgJ2->label ?? 'Pilihan Kedua' }} <span style="font-size:0.8125rem; font-weight:400; color:var(--color-text-muted);">(Opsional)</span></div>
                    <p style="font-size:0.875rem; color:var(--color-text-muted); margin-bottom:1rem;">{{ $cfgJ2->teks_bantuan ?? 'Jurusan cadangan jika pilihan pertama penuh' }}</p>

                    <div class="jurusan-selector" role="radiogroup" aria-label="Pilihan jurusan kedua (opsional)">
                        <div class="jurusan-option">
                            <input type="radio" name="jurusan_2" id="j2_none" value="">
                            <label class="jurusan-option-label" for="j2_none" style="border-style:dashed; color:var(--color-text-muted);">
                                <div style="text-align:center; padding:0.5rem 0; font-size:0.875rem; color:var(--color-text-muted);">
                                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="margin:0 auto 0.25rem; display:block;" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    Tidak Memilih
                                </div>
                            </label>
                        </div>
                        @foreach ($jurusan as $j)
                        @php $cfg = $jurusanConfig[$j->kode] ?? ['color' => '#1E40AF', 'bg' => '#dbeafe', 'icon_path' => '']; @endphp
                        <div class="jurusan-option">
                            <input type="radio" name="jurusan_2" id="j2_{{ $j->id }}"
                                   value="{{ $j->id }}"
                                   {{ old('jurusan_2') == $j->id ? 'checked' : '' }}>
                            <label class="jurusan-option-label" for="j2_{{ $j->id }}">
                                <div style="width:2rem; height:2rem; background:{{ $cfg['bg'] }}; border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:center; margin-bottom:0.5rem;">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="{{ $cfg['color'] }}" stroke-width="1.75" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cfg['icon_path'] }}"/>
                                    </svg>
                                </div>
                                <div style="font-size:0.75rem; font-weight:700; color:{{ $cfg['color'] }}; margin-bottom:0.125rem;">{{ $j->kode }}</div>
                                <div style="font-size:0.8125rem; font-weight:600; color:var(--color-text); line-height:1.3;">{{ $j->nama }}</div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('jurusan_2')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                @endif

                {{-- Field kustom Step 4 dari panel admin --}}
                @if(!empty($kustomStep4))
                    <div style="display:grid; gap:1rem; margin-bottom:1.5rem;">
                        @foreach($kustomStep4 as $field)
                            @include('components.form-field', ['field' => $field, 'data' => []])
                        @endforeach
                    </div>
                @endif

                <div class="form-actions">
                    <a href="{{ route('ppdb.step3') }}" class="btn btn-ghost">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        Lanjut ke Data Siswa
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
// Validate pilihan 2 tidak sama dengan pilihan 1
document.getElementById('form-step4').addEventListener('submit', function(e) {
    const j1 = document.querySelector('input[name="jurusan_1"]:checked')?.value;
    const j2 = document.querySelector('input[name="jurusan_2"]:checked')?.value;
    if (j1 && j2 && j1 === j2) {
        e.preventDefault();
        alert('Pilihan 1 dan Pilihan 2 tidak boleh sama. Silakan pilih jurusan yang berbeda.');
    }
});

// Update rekomendasi saat minat berubah (opsional: bisa reload dengan AJAX, null-guarded)
const minatEl = document.getElementById('minat');
if (minatEl) {
    minatEl.addEventListener('change', function() {
        // Simple: reload form dengan nilai minat terpilih via GET param (atau biarkan server-side)
    });
}
</script>
@endpush
