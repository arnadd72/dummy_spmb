@extends('layouts.app')
@section('title', 'Langkah 3: Upload Rapor — Pendaftaran PPDB')

@section('content')
<div class="form-page">
    <div class="container">
        <x-step-indicator :currentStep="3" />

        <div class="form-card">
            <div style="margin-bottom:1.5rem;">
                <h1 class="form-page-title">Upload Rapor <span style="font-weight:400; color:var(--color-text-muted); font-size:1rem;">(Opsional)</span></h1>
                <p class="form-page-subtitle">Rapor digunakan untuk meningkatkan akurasi rekomendasi jurusan. Anda bisa melewati langkah ini.</p>
            </div>

            <div class="alert alert-warning" role="note">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
                </svg>
                <div>
                    <strong>Belum punya rapor?</strong> Anda bisa melewati langkah ini dan tetap mendaftar. Rekomendasi jurusan akan berdasarkan minat yang dipilih saja.
                </div>
            </div>

            <form method="POST" action="{{ route('ppdb.step3.post') }}" enctype="multipart/form-data" novalidate>
                @csrf

                @php $cfgRapor = ($konfig ?? [])['rapor'] ?? null; $showRapor = ! $cfgRapor || $cfgRapor->tampil; $wajibRapor = $cfgRapor ? ($cfgRapor->tampil && $cfgRapor->wajib) : false; @endphp
                @if($showRapor)
                <div class="form-group">
                    <label class="form-label" for="rapor">
                        {{ $cfgRapor->label ?? 'Rapor Semester Terakhir' }}
                        @if($wajibRapor)<span style="color:var(--color-error); font-weight:bold;">*</span>@else<span style="font-size:0.8125rem; font-weight:400; color:var(--color-text-muted);">(Opsional)</span>@endif
                    </label>
                    <div class="upload-zone" id="rapor-zone" role="button" tabindex="0" aria-label="Upload Rapor">
                        <input type="file" name="rapor" id="rapor" accept="image/*,.pdf" capture="environment" @if($wajibRapor) required @endif>
                        <svg class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <div class="upload-label" id="rapor-filename">Klik atau seret file rapor ke sini</div>
                        <div class="upload-hint">{{ $cfgRapor->teks_bantuan ?? 'JPG, PNG, atau PDF • Maks 5 MB' }}</div>
                    </div>
                    <div id="rapor-preview" style="display:none; margin-top:0.75rem; border-radius:var(--radius-md); overflow:hidden; border:1px solid var(--color-border);"></div>
                </div>
                @endif

                @php $cfgNilai = ($konfig ?? [])['nilai_rapor'] ?? null; $showNilai = ! $cfgNilai || $cfgNilai->tampil; @endphp
                @if($showNilai)

                {{-- Input Nilai Manual --}}
                <div id="nilai-manual-section" style="display:none; margin-top:1.5rem; padding:1.25rem; background:var(--color-background); border:1px solid var(--color-border); border-radius:var(--radius-lg);">
                    <h2 style="font-size:1rem; font-weight:700; margin-bottom:1rem; color:var(--color-primary-dark);">
                        Input Nilai Rata-rata Rapor
                        <span style="font-size:0.8125rem; font-weight:400; color:var(--color-text-muted);">(opsional, untuk rekomendasi lebih akurat)</span>
                    </h2>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
                        @php
                        $mapel = [
                            'matematika'    => 'Matematika',
                            'ipa_biologi'   => 'IPA / Biologi',
                            'ipa_fisika'    => 'IPA / Fisika',
                            'ipa_kimia'     => 'IPA / Kimia',
                            'b_indonesia'   => 'Bahasa Indonesia',
                            'b_inggris'     => 'Bahasa Inggris',
                            'ips_ekonomi'   => 'IPS / Ekonomi',
                            'prakarya'      => 'Prakarya',
                        ];
                        @endphp
                        @foreach ($mapel as $key => $nama)
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="nilai_{{ $key }}" style="font-size:0.875rem;">{{ $nama }}</label>
                            <input type="number" name="nilai_rapor[{{ $key }}]" id="nilai_{{ $key }}"
                                   class="form-control"
                                   min="0" max="100" step="0.1"
                                   placeholder="0–100"
                                   style="font-size:0.9375rem;">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif {{-- /showNilai --}}

                {{-- Field kustom Step 3 dari panel admin --}}
                @if(!empty($kustomStep3))
                    <div style="display:grid; gap:1rem; margin-top:1.25rem;">
                        @foreach($kustomStep3 as $field)
                            @include('components.form-field', ['field' => $field, 'data' => []])
                        @endforeach
                    </div>
                @endif

                <div class="form-actions">
                    <a href="{{ route('ppdb.step2') }}" class="btn btn-ghost">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        Kembali
                    </a>
                    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                        <a href="{{ route('ppdb.step3.lewati') }}" class="btn btn-ghost" id="btn-lewati">
                            Lewati Langkah Ini
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8.689c0-.864.933-1.406 1.683-.977l7.108 4.061a1.125 1.125 0 010 1.954l-7.108 4.061A1.125 1.125 0 013 16.811V8.69zM12.75 8.689c0-.864.933-1.406 1.683-.977l7.108 4.061a1.125 1.125 0 010 1.954l-7.108 4.061a1.125 1.125 0 01-1.683-.977V8.69z"/></svg>
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            Simpan & Lanjut
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const raporInput = document.getElementById('rapor');
const zone = document.getElementById('rapor-zone');
const preview = document.getElementById('rapor-preview');
const filename = document.getElementById('rapor-filename');
const nilaiSection = document.getElementById('nilai-manual-section');

function handleFile(file) {
    if (!file) return;
    if (filename) {
        filename.textContent = `✓ ${file.name}`;
        filename.style.color = 'var(--color-accent)';
    }
    if (nilaiSection) nilaiSection.style.display = 'block';

    if (!preview) return;
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.style.display = 'block';
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview rapor" style="width:100%; max-height:220px; object-fit:contain; background:#f8fafc; padding:0.5rem;">`;
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'block';
        preview.innerHTML = `<div style="padding:0.875rem; background:#f8fafc; font-size:0.875rem; color:var(--color-text-secondary);">PDF: ${file.name}</div>`;
    }
}

if (raporInput) raporInput.addEventListener('change', () => handleFile(raporInput.files[0]));
if (zone && raporInput) {
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file) { raporInput.files = e.dataTransfer.files; handleFile(file); }
    });
    zone.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') raporInput.click(); });
}
</script>
@endpush
