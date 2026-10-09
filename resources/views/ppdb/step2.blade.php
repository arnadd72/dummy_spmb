@extends('layouts.app')
@section('title', 'Langkah 2: Upload Dokumen & Verifikasi Cerdas — Pendaftaran PPDB')

@section('content')
<div class="form-page">
    <div class="container">
        <x-step-indicator :currentStep="2" />

        <div class="form-card" style="max-width:880px;">
            <div style="margin-bottom:1.5rem;">
                <h1 class="form-page-title">Upload Dokumen & Ekstraksi Data</h1>
                <p class="form-page-subtitle">Sistem memindai dokumen Anda secara lokal di peramban. Data resmi nama dan TTL diambil dari Akta Kelahiran, dan NIK dicocokkan dari Kartu Keluarga.</p>
            </div>

            {{-- Applicant Identity Banner --}}
            <div style="background:linear-gradient(135deg, #f0fdf4, #ffffff); border:1.5px solid var(--color-border); border-radius:var(--radius-lg); padding:1.25rem 1.5rem; margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; box-shadow:var(--shadow-card);">
                <div>
                    <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-primary); font-weight:800;">Identitas Calon Peserta Didik (Step 1)</div>
                    <div style="font-size:1.25rem; font-weight:800; color:var(--color-primary-dark); margin-top:0.25rem;">
                        {{ $step1['nama_lengkap'] ?? 'Nama Belum Diisi' }}
                    </div>
                    <div style="font-size:0.875rem; color:var(--color-text-secondary); margin-top:0.125rem;">
                        NISN: <strong style="font-family:var(--font-mono, monospace); color:var(--color-text);">{{ $step1['nisn'] ?? '—' }}</strong>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <a href="{{ route('ppdb.step1') }}" class="btn btn-ghost btn-sm" title="Ubah Nama/NISN">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
                        Ubah Identitas Awal
                    </a>
                </div>
            </div>

            {{-- Method Transparency Alert --}}
            <div class="alert alert-info" role="note" style="margin-bottom:1.75rem;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0; margin-top:0.125rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <div style="font-size:0.875rem; line-height:1.6;">
                    <strong>Transparansi Pembacaan Dokumen <span style="font-weight:400;color:#0369a1;">(Self-Hosted &mdash; data tidak dikirim ke cloud)</span>:</strong>
                    <ul style="margin:0.25rem 0 0; padding-left:1.25rem;">
                        <li><strong>Akta Kelahiran:</strong> Membaca <em>Nama Lengkap Resmi</em>, <em>Tempat/Tanggal Lahir</em>, dan Jenis Kelamin via analisis layout + konteks label.</li>
                        <li><strong>Kartu Keluarga:</strong> Mendeteksi struktur tabel. NIK, Nama, dan Hubungan keluarga diambil dari <strong>baris yang sama</strong> &mdash; bukan angka 16-digit pertama.</li>
                        <li><strong>Nomor KK</strong> otomatis dibedakan dari NIK anggota berdasarkan posisi &amp; label header.</li>
                        <li><em>Catatan:</em> Jika kualitas foto buram, Anda dapat mengoreksi data di formulir di bawah ini.</li>
                    </ul>
                </div>
            </div>

            <form method="POST" action="{{ route('ppdb.step2.post') }}" enctype="multipart/form-data" novalidate id="form-step2">
                @csrf

                @if($errors->any())
                <div class="alert alert-error" role="alert" style="margin-bottom:1.5rem;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    <ul style="margin:0; padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                {{-- DOKUMEN 1: AKTA KELAHIRAN --}}
                @php $cfgAkta = ($konfig ?? [])['akta'] ?? null; $showAkta = ! $cfgAkta || $cfgAkta->tampil; $wajibAkta = $cfgAkta ? ($cfgAkta->tampil && $cfgAkta->wajib) : false; @endphp
                @if($showAkta)
                <div style="background:white; border:2px solid var(--color-border); border-radius:var(--radius-lg); padding:1.5rem; margin-bottom:1.75rem; box-shadow:var(--shadow-card);">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
                        <div>
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <span style="background:var(--color-primary); color:#fff; font-size:0.75rem; font-weight:800; padding:0.15rem 0.5rem; border-radius:999px;">DOKUMEN 1</span>
                                <h2 style="font-size:1.15rem; font-weight:800; color:var(--color-primary-dark); margin:0;">{{ $cfgAkta->label ?? 'Akta Kelahiran' }}</h2>
                                @if($wajibAkta)<span class="required" aria-label="wajib">*</span>@endif
                            </div>
                            <p style="font-size:0.8125rem; color:var(--color-text-secondary); margin:0.25rem 0 0;">
                                {{ $cfgAkta->teks_bantuan ?? 'Sumber resmi untuk mengekstrak Nama Lengkap Siswa, Tempat Lahir, dan Tanggal Lahir.' }}
                            </p>
                        </div>
                    </div>

                    <div class="upload-zone" id="akta-zone" role="button" tabindex="0" aria-label="Upload Akta Kelahiran">
                        <input type="file" name="akta" id="akta" accept="image/*,.pdf" capture="environment" aria-describedby="akta-hint" @if($wajibAkta) required @endif>
                        <svg class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        <div class="upload-label" id="akta-filename">Klik atau seret file Akta Kelahiran ke sini</div>
                        <div class="upload-hint" id="akta-hint">Format JPG, PNG, atau PDF • Maks 5 MB • Pastikan dokumen tegak & pencahayaan cukup</div>
                    </div>

                    <div id="akta-preview" style="display:none; margin-top:0.75rem; border-radius:var(--radius-md); overflow:hidden; border:1px solid var(--color-border); text-align:center;"></div>

                    <div class="ocr-progress" id="akta-ocr-progress" role="status" aria-live="polite">
                        <div class="ocr-spinner" aria-hidden="true"></div>
                        <span>Membaca teks dari Akta Kelahiran... <strong id="akta-ocr-pct">0%</strong></span>
                    </div>

                    {{-- Hasil Ekstraksi Akta --}}
                    <div id="akta-result-box" style="display:none; margin-top:1.25rem; background:#f9fcf9; border:1.5px solid var(--color-border); border-radius:var(--radius-md); padding:1.25rem;">
                        <div style="font-weight:800; color:var(--color-primary); font-size:0.95rem; margin-bottom:1rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Hasil Ekstraksi Data dari Akta Kelahiran</span>
                            </div>
                            <button type="button" class="btn btn-ghost btn-sm" id="btn-toggle-raw-akta" style="font-size:0.75rem; padding:0.2rem 0.6rem;">
                                🔍 Lihat Teks Scan Asli
                            </button>
                        </div>

                        {{-- Raw OCR viewer --}}
                        <div id="raw-akta-box" style="display:none; margin-bottom:1rem; background:#1e293b; color:#f8fafc; font-family:var(--font-mono, monospace); font-size:0.8rem; padding:0.75rem 1rem; border-radius:var(--radius-sm); max-height:160px; overflow-y:auto; white-space:pre-wrap;"></div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                            <div class="form-group" style="grid-column:1/-1; margin:0;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.25rem;">
                                    <label class="form-label" style="font-size:0.8125rem; margin:0;">Nama Lengkap Siswa</label>
                                    <span id="badge-nama-akta" class="badge badge-neutral" style="font-size:0.7rem;">Menunggu upload</span>
                                </div>
                                <input type="text" id="display_nama_akta" class="form-control" style="font-weight:700;" placeholder="Nama lengkap sesuai akta">
                                <div id="hint-nama-akta" class="form-hint"></div>
                            </div>

                            <div class="form-group" style="margin:0;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.25rem;">
                                    <label class="form-label" style="font-size:0.8125rem; margin:0;">Tempat Lahir</label>
                                    <span id="badge-tempat-akta" class="badge badge-neutral" style="font-size:0.7rem;">Menunggu upload</span>
                                </div>
                                <input type="text" id="display_tempat_lahir_akta" class="form-control" placeholder="Contoh: Medan / Banyumas">
                                <div id="hint-tempat-akta" class="form-hint"></div>
                            </div>

                            <div class="form-group" style="margin:0;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.25rem;">
                                    <label class="form-label" style="font-size:0.8125rem; margin:0;">Tanggal Lahir</label>
                                    <span id="badge-tgl-akta" class="badge badge-neutral" style="font-size:0.7rem;">Menunggu upload</span>
                                </div>
                                <input type="date" id="display_tanggal_lahir_akta" class="form-control">
                                <div id="hint-tgl-akta" class="form-hint"></div>
                            </div>

                            <div class="form-group" style="margin:0;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.25rem;">
                                    <label class="form-label" style="font-size:0.8125rem; margin:0;">Jenis Kelamin</label>
                                    <span id="badge-jk-akta" class="badge badge-neutral" style="font-size:0.7rem;">Menunggu upload</span>
                                </div>
                                <select id="display_jenis_kelamin" class="form-control">
                                    <option value="">— Pilih —</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                @endif {{-- /showAkta --}}

                {{-- DOKUMEN 2: KARTU KELUARGA --}}
                @php $cfgKk = ($konfig ?? [])['kk'] ?? null; $showKk = ! $cfgKk || $cfgKk->tampil; $wajibKk = $cfgKk ? ($cfgKk->tampil && $cfgKk->wajib) : false; @endphp
                @if($showKk)
                <div style="background:white; border:2px solid var(--color-border); border-radius:var(--radius-lg); padding:1.5rem; margin-bottom:1.75rem; box-shadow:var(--shadow-card);">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
                        <div>
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <span style="background:var(--color-accent); color:#fff; font-size:0.75rem; font-weight:800; padding:0.15rem 0.5rem; border-radius:999px;">DOKUMEN 2</span>
                                <h2 style="font-size:1.15rem; font-weight:800; color:var(--color-primary-dark); margin:0;">{{ $cfgKk->label ?? 'Kartu Keluarga (KK)' }}</h2>
                                @if($wajibKk)<span class="required" aria-label="wajib">*</span>@endif
                            </div>
                            <p style="font-size:0.8125rem; color:var(--color-text-secondary); margin:0.25rem 0 0;">
                                {{ $cfgKk->teks_bantuan ?? 'Sistem memindai seluruh NIK pada KK dan mencocokkan dengan nama pendaftar untuk mengambil NIK pendaftar yang tepat (bukan NIK orang tua), serta data keluarga & alamat.' }}
                            </p>
                        </div>
                    </div>

                    <div class="upload-zone" id="kk-zone" role="button" tabindex="0" aria-label="Upload Kartu Keluarga">
                        <input type="file" name="kk" id="kk" accept="image/*,.pdf" capture="environment" aria-describedby="kk-hint" @if($wajibKk) required @endif>
                        <svg class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                        </svg>
                        <div class="upload-label" id="kk-filename">Klik atau seret file Kartu Keluarga ke sini</div>
                        <div class="upload-hint" id="kk-hint">Format JPG, PNG, atau PDF • Maks 5 MB • Pastikan tabel anggota keluarga terbaca jelas</div>
                    </div>

                    <div id="kk-preview" style="display:none; margin-top:0.75rem; border-radius:var(--radius-md); overflow:hidden; border:1px solid var(--color-border); text-align:center;"></div>

                    <div class="ocr-progress" id="kk-ocr-progress" role="status" aria-live="polite">
                        <div class="ocr-spinner" aria-hidden="true"></div>
                        <span>Membaca tabel Kartu Keluarga... <strong id="kk-ocr-pct">0%</strong></span>
                    </div>

                    {{-- Hasil Pencocokan KK --}}
                    <div id="kk-result-box" style="display:none; margin-top:1.25rem; background:#f9fcf9; border:1.5px solid var(--color-border); border-radius:var(--radius-md); padding:1.25rem;">
                        <div style="font-weight:800; color:var(--color-primary); font-size:0.95rem; margin-bottom:1rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Hasil Pencocokan NIK & Data Kartu Keluarga</span>
                            </div>
                            <button type="button" class="btn btn-ghost btn-sm" id="btn-toggle-raw-kk" style="font-size:0.75rem; padding:0.2rem 0.6rem;">
                                🔍 Lihat Teks Scan Asli
                            </button>
                        </div>

                        {{-- Raw KK Box --}}
                        <div id="raw-kk-box" style="display:none; margin-bottom:1rem; background:#1e293b; color:#f8fafc; font-family:var(--font-mono, monospace); font-size:0.8rem; padding:0.75rem 1rem; border-radius:var(--radius-sm); max-height:160px; overflow-y:auto; white-space:pre-wrap;"></div>

                        {{-- NIK Calon Siswa (Primary Highlight Input) --}}
                        <div class="form-group" style="margin-bottom:1rem; background:#f0fdf4; border:1.5px solid #86efac; border-radius:var(--radius-md); padding:1rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.375rem;">
                                <label class="form-label" for="display_nik_siswa" style="margin:0; font-size:0.875rem; font-weight:800; color:#14532d;">
                                    NIK Calon Siswa Terpilih (16 digit) <span class="required">*</span>
                                </label>
                                <span id="badge-nik-kk" class="badge badge-primary" style="font-size:0.75rem;">—</span>
                            </div>
                            <input type="text" id="display_nik_siswa" class="form-control" maxlength="16" inputmode="numeric"
                                   style="font-family:var(--font-mono, monospace); font-size:1.1rem; font-weight:800; letter-spacing:0.08em; background:#fff;"
                                   placeholder="Contoh: 3302xxxxxxxx0001">
                            <div id="hint-nik-kk" class="form-hint" style="color:#166534;">
                                NIK yang sesuai dengan calon siswa (bukan NIK Ayah, Ibu, atau Nomor Kartu Keluarga).
                            </div>
                        </div>

                        {{-- Detected Members List --}}
                        <div id="kk-members-container" style="margin-bottom:1rem; display:none;">
                            <div style="font-size:0.8125rem; font-weight:700; color:var(--color-text-secondary); margin-bottom:0.5rem;">
                                Seluruh Anggota Keluarga yang Terbaca di KK (Klik untuk memilih NIK Anda):
                            </div>
                            <div id="kk-members-list" style="display:flex; flex-direction:column; gap:0.5rem;"></div>
                        </div>

                        {{-- Address & Parents from KK --}}
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.875rem; padding-top:0.75rem; border-top:1px dashed var(--color-border);">
                            <div class="form-group" style="grid-column:1/-1; margin:0;">
                                <label class="form-label" style="font-size:0.8125rem;">Alamat Tempat Tinggal (dari KK)</label>
                                <input type="text" id="display_alamat_kk" class="form-control" placeholder="RT/RW, Dusun, Desa, Kecamatan, Kabupaten">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:0.8125rem;">Nama Ayah (Kepala Keluarga)</label>
                                <input type="text" id="display_nama_ayah" class="form-control" placeholder="Nama lengkap ayah">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:0.8125rem;">Nama Ibu</label>
                                <input type="text" id="display_nama_ibu" class="form-control" placeholder="Nama lengkap ibu">
                            </div>
                        </div>
                    </div>
                </div>
                @endif {{-- /showKk --}}

                {{-- Field kustom Step 2 dari panel admin --}}
                @if(!empty($kustomStep2))
                    <div style="display:grid; gap:1rem; margin-bottom:1.75rem;">
                        @foreach($kustomStep2 as $field)
                            @include('components.form-field', ['field' => $field, 'data' => array_merge($step2, $step2['data_kustom_step2'] ?? [])])
                        @endforeach
                    </div>
                @endif

                {{-- Hidden Fields submitted to server --}}
                <input type="hidden" name="extracted_nama_akta" id="input_nama_akta">
                <input type="hidden" name="extracted_tempat_lahir_akta" id="input_tempat_lahir_akta">
                <input type="hidden" name="extracted_tanggal_lahir_akta" id="input_tanggal_lahir_akta">
                <input type="hidden" name="extracted_jenis_kelamin" id="input_jenis_kelamin">
                <input type="hidden" name="extracted_nik" id="input_nik">
                <input type="hidden" name="extracted_alamat_kk" id="input_alamat_kk">
                <input type="hidden" name="extracted_nama_ayah_kk" id="input_nama_ayah_kk">
                <input type="hidden" name="extracted_pekerjaan_ayah_kk" id="input_pekerjaan_ayah_kk">
                <input type="hidden" name="extracted_nama_ibu_kk" id="input_nama_ibu_kk">
                <input type="hidden" name="extracted_pekerjaan_ibu_kk" id="input_pekerjaan_ibu_kk">

                <div class="form-actions">
                    <a href="{{ route('ppdb.step1') }}" class="btn btn-ghost">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        Kembali ke Step 1
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg" id="btn-next-2">
                        Lanjut ke Upload Rapor
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
window.OCR_TARGET_NAME = @json($step1['nama_lengkap'] ?? '');
</script>
<script src="{{ asset('js/ocr-engine.js') }}"></script>
<script>
// Inisialisasi upload + setup raw text toggles setelah ocr-engine.js dimuat (null-guarded: dokumen bisa disembunyikan admin)
if (document.getElementById('akta') && document.getElementById('akta-zone') && typeof setupUpload === 'function') {
    setupUpload('akta', 'akta-zone', 'akta-preview', 'akta-filename', function(img){ runOCR(img,'akta'); });
}
if (document.getElementById('kk') && document.getElementById('kk-zone') && typeof setupUpload === 'function') {
    setupUpload('kk',   'kk-zone',   'kk-preview',   'kk-filename',   function(img){ runOCR(img,'kk'); });
}

var btnRawAkta = document.getElementById('btn-toggle-raw-akta');
if (btnRawAkta) btnRawAkta.addEventListener('click', function() {
    var box = document.getElementById('raw-akta-box');
    if (!box) return;
    box.style.display = box.style.display === 'none' ? 'block' : 'none';
    this.textContent  = box.style.display === 'none' ? 'Lihat Teks Scan Asli' : 'Tutup Teks Scan';
});
var btnRawKk = document.getElementById('btn-toggle-raw-kk');
if (btnRawKk) btnRawKk.addEventListener('click', function() {
    var box = document.getElementById('raw-kk-box');
    if (!box) return;
    box.style.display = box.style.display === 'none' ? 'block' : 'none';
    this.textContent  = box.style.display === 'none' ? 'Lihat Teks Scan Asli' : 'Tutup Teks Scan';
});
</script>
@endpush