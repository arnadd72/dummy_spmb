@extends('layouts.admin')

@section('title', 'Pengaturan Situs — Admin PPDB')

@push('head')
@include('admin.content.partials.styles')
@endpush

@section('content')
<div class="content-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">Pengaturan Situs</h1>
            <p class="page-sub">
                Kelola tahun ajaran SPMB, kredensial admin, foto hero / galeri /
                kepala sekolah yang tampil di landing page, serta tautan grup WhatsApp.
            </p>
        </div>
        <a href="{{ route('ppdb.index') }}" target="_blank" class="btn btn-ghost">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            Lihat Landing Page
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Tahun Ajaran SPMB</h2>
            <p class="panel-sub">Tahun yang tampil di seluruh situs (landing, formulir, PDF, email). Contoh: 2027/2028.</p>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.pengaturan.update') }}">
                @csrf
                @method('PUT')
                <div class="field" style="margin-bottom:1.25rem;">
                    <label class="field-label" for="tahun_ajaran">Tahun Ajaran Aktif</label>
                    <input type="text" id="tahun_ajaran" name="tahun_ajaran"
                           value="{{ old('tahun_ajaran', $tahunAjaran ?? '2027/2028') }}"
                           placeholder="2027/2028" pattern="\d{4}/\d{4}" maxlength="9"
                           class="file-input {{ $errors->has('tahun_ajaran') ? 'err' : '' }}"
                           style="width:100%; max-width:260px;">
                    <span class="field-help">Format wajib: YYYY/YYYY, misalnya 2027/2028.</span>
                    @error('tahun_ajaran') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-primary">Simpan Tahun Ajaran</button>
            </form>
        </div>
    </div>

    <div class="panel" style="margin-top:1.5rem;">
        <div class="panel-head">
            <h2 class="panel-title">Kredensial Login Admin</h2>
            <p class="panel-sub">Username & kata sandi untuk masuk panel admin (tanpa database). Kosongkan kata sandi bila tidak ingin mengubah.</p>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.pengaturan.update') }}">
                @csrf
                @method('PUT')
                <div class="field" style="margin-bottom:1rem;">
                    <label class="field-label" for="admin_username">Username Admin</label>
                    <input type="text" id="admin_username" name="admin_username"
                           value="{{ old('admin_username', $adminUsername ?? 'admin') }}"
                           class="file-input" style="width:100%; max-width:320px;">
                    @error('admin_username') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field" style="margin-bottom:1.25rem;">
                    <label class="field-label" for="admin_password">Kata Sandi Baru</label>
                    <input type="password" id="admin_password" name="admin_password" placeholder="Minimal 6 karakter"
                           class="file-input {{ $errors->has('admin_password') ? 'err' : '' }}" style="width:100%; max-width:320px;">
                    @error('admin_password') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-primary">Simpan Kredensial Admin</button>
            </form>
        </div>
    </div>

    <div class="panel" style="margin-top:1.5rem;">
        <div class="panel-head">
            <h2 class="panel-title">Gambar Hero</h2>
            <p class="panel-sub">Gambar utama yang tampil pada hero landing page portal siswa.</p>
        </div>

        <div class="panel-body">
            @if ($heroPath && $heroUrl)
                <div class="preview-box" style="margin-bottom:1.25rem;">
                    <img src="{{ $heroUrl }}" alt="Gambar hero saat ini" class="preview-img" style="width:200px; height:120px;">
                    <div>
                        <div style="font-weight:600; font-size:.85rem; color:var(--tx-primary); margin-bottom:.25rem;">Gambar hero aktif</div>
                        <div class="field-help">Berkas: {{ $heroPath }}</div>
                        @if (!file_exists(public_path('storage/' . ltrim($heroPath, '/'))))
                            <div class="field-error" style="margin-top:.35rem;">Berkas tidak ditemukan di storage. Silakan unggah ulang.</div>
                        @endif
                    </div>
                </div>
            @else
                <div class="empty-box" style="margin-bottom:1.25rem;">
                    <svg width="42" height="42" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                    <h3>Belum Ada Gambar Hero</h3>
                    <p>Saat ini hero landing page memakai gambar bawaan. Unggah gambar sendiri untuk tampilan khusus.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.pengaturan.update') }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="field" style="margin-bottom:1.25rem;">
                    <label class="field-label" for="hero_image">Unggah Gambar Hero Baru</label>
                    <input type="file" id="hero_image" name="hero_image" accept="image/png,image/jpeg,image/webp"
                           class="file-input {{ $errors->has('hero_image') ? 'err' : '' }}">
                    <span class="field-help">Format JPG, PNG, atau WEBP. Maksimal 4 MB. Rasio ideal 4:3 atau 16:9.</span>
                    @error('hero_image') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Simpan Gambar Hero
                    </button>
                </div>
            </form>

            @if ($heroPath)
                <form method="POST" action="{{ route('admin.pengaturan.update') }}"
                      onsubmit="return confirm('Hapus gambar hero dan kembalikan ke tampilan bawaan?');"
                      style="margin-top:1rem; padding-top:1rem; border-top:1px dashed var(--sf-border);">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="hapus_hero" value="1">
                    <button type="submit" class="btn btn-danger">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        Hapus Gambar Hero
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ─────────── Foto Galeri Kegiatan (panel terpisah) ─────────── --}}
    <div class="panel" style="margin-top:1.5rem;">
        <div class="panel-head">
            <h2 class="panel-title">Foto Galeri Kegiatan</h2>
            <p class="panel-sub">Foto dokumentasi kegiatan yang tampil di section galeri landing page.</p>
        </div>
        <div class="panel-body">
            @if (!empty($galeriPath) && !empty($galeriUrl))
                <div class="preview-box" style="margin-bottom:1.25rem;">
                    <img src="{{ $galeriUrl }}" alt="Foto galeri saat ini" class="preview-img" style="width:200px; height:120px;">
                    <div>
                        <div style="font-weight:600; font-size:.85rem; color:var(--tx-primary); margin-bottom:.25rem;">Foto galeri aktif</div>
                        <div class="field-help">Berkas: {{ $galeriPath }}</div>
                    </div>
                </div>
            @else
                <div class="empty-box" style="margin-bottom:1.25rem;">
                    <h3>Belum Ada Foto Galeri</h3>
                    <p>Unggah foto kegiatan untuk mempercantik landing page.</p>
                </div>
            @endif
            <form method="POST" action="{{ route('admin.pengaturan.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="field" style="margin-bottom:1.25rem;">
                    <label class="field-label" for="galeri_kegiatan_image">Unggah Foto Galeri Baru</label>
                    <input type="file" id="galeri_kegiatan_image" name="galeri_kegiatan_image" accept="image/png,image/jpeg,image/webp" class="file-input">
                    <span class="field-help">Format JPG, PNG, atau WEBP. Maksimal 4 MB.</span>
                    @error('galeri_kegiatan_image') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Simpan Foto Galeri</button>
                </div>
            </form>
            @if (!empty($galeriPath))
                <form method="POST" action="{{ route('admin.pengaturan.update') }}" onsubmit="return confirm('Hapus foto galeri?');" style="margin-top:1rem; padding-top:1rem; border-top:1px dashed var(--sf-border);">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="hapus_galeri" value="1">
                    <button type="submit" class="btn btn-danger">Hapus Foto Galeri</button>
                </form>
            @endif
        </div>
    </div>

    {{-- ─────────── Foto Kepala Sekolah (panel terpisah) ─────────── --}}
    <div class="panel" style="margin-top:1.5rem;">
        <div class="panel-head">
            <h2 class="panel-title">Foto Kepala Sekolah</h2>
            <p class="panel-sub">Foto kepala sekolah yang tampil di sambutan landing page.</p>
        </div>
        <div class="panel-body">
            @if (!empty($kepsekPath) && !empty($kepsekUrl))
                <div class="preview-box" style="margin-bottom:1.25rem;">
                    <img src="{{ $kepsekUrl }}" alt="Foto kepala sekolah saat ini" class="preview-img" style="width:140px; height:170px; object-fit:cover;">
                    <div>
                        <div style="font-weight:600; font-size:.85rem; color:var(--tx-primary); margin-bottom:.25rem;">Foto kepala sekolah aktif</div>
                        <div class="field-help">Berkas: {{ $kepsekPath }}</div>
                    </div>
                </div>
            @else
                <div class="empty-box" style="margin-bottom:1.25rem;">
                    <h3>Belum Ada Foto Kepala Sekolah</h3>
                    <p>Unggah foto resmi kepala sekolah (potrait).</p>
                </div>
            @endif
            <form method="POST" action="{{ route('admin.pengaturan.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="field" style="margin-bottom:1.25rem;">
                    <label class="field-label" for="kepala_sekolah_image">Unggah Foto Kepala Sekolah Baru</label>
                    <input type="file" id="kepala_sekolah_image" name="kepala_sekolah_image" accept="image/png,image/jpeg,image/webp" class="file-input">
                    <span class="field-help">Format JPG, PNG, atau WEBP. Maksimal 4 MB. Rasio potrait 3:4.</span>
                    @error('kepala_sekolah_image') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Simpan Foto Kepala Sekolah</button>
                </div>
            </form>
            @if (!empty($kepsekPath))
                <form method="POST" action="{{ route('admin.pengaturan.update') }}" onsubmit="return confirm('Hapus foto kepala sekolah?');" style="margin-top:1rem; padding-top:1rem; border-top:1px dashed var(--sf-border);">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="hapus_kepsek" value="1">
                    <button type="submit" class="btn btn-danger">Hapus Foto Kepala Sekolah</button>
                </form>
            @endif
        </div>
    </div>

    {{-- ─────────── Tautan Grup WhatsApp ─────────── --}}
    <div class="panel" style="margin-top:1.5rem;">
        <div class="panel-head">
            <h2 class="panel-title">Tautan Grup WhatsApp</h2>
            <p class="panel-sub">Tautan undangan grup yang ditampilkan di halaman akhir pendaftaran dan pada email kode pendaftaran calon siswa.</p>
        </div>

        <div class="panel-body">
            <form method="POST" action="{{ route('admin.pengaturan.update') }}" id="form-wa">
                @csrf
                @method('PUT')

                <div class="field" style="margin-bottom:1.25rem;">
                    <label class="field-label" for="wa_group_url">Link Undangan Grup WhatsApp</label>
                    <input type="url" id="wa_group_url" name="wa_group_url"
                           value="{{ old('wa_group_url', $waGroupUrl) }}"
                           placeholder="https://chat.whatsapp.com/xxxxxxxxxxxxxxxx"
                           class="file-input {{ $errors->has('wa_group_url') ? 'err' : '' }}"
                           style="width:100%; max-width:520px;">
                    <span class="field-help">Kosongkan bila belum ada grup. Link grup WhatsApp bisa dibuat dari aplikasi WhatsApp → Info grup → Undang via tautan.</span>
                    @error('wa_group_url') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Simpan Tautan Grup
                </button>
            </form>

            @if ($waGroupUrl)
            <div class="alert alert-success" role="status" style="margin-top:1rem;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Tautan aktif: <a href="{{ $waGroupUrl }}" target="_blank" rel="noopener noreferrer" style="color:inherit; font-weight:700;">{{ $waGroupUrl }}</a></span>
            </div>
            @endif
        </div>
    </div>

    {{-- ─────────── Google Drive ─────────── --}}
    <div class="panel" style="margin-top:1.5rem;">
        <div class="panel-head">
            <h2 class="panel-title">Penyimpanan Google Drive</h2>
            <p class="panel-sub">Salinan berkas unggahan siswa dan gambar konten disimpan juga ke Google Drive. Berkas lokal tetap menjadi sumber utama bila Drive tidak tersedia.</p>
        </div>

        <div class="panel-body">
            @if ($hasilUji)
                <div class="alert {{ $hasilUji['ok'] ? 'alert-success' : 'alert-error' }}" role="status" style="margin-bottom:1rem;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $hasilUji['pesan'] }}</span>
                </div>
            @endif

            <dl style="display:grid; grid-template-columns:auto 1fr; gap:.5rem 1rem; font-size:.82rem; margin:0 0 1.25rem;">
                <dt style="color:var(--tx-muted); font-weight:600;">Status</dt>
                <dd style="margin:0; font-weight:700; color:{{ $drive['aktif'] ? 'var(--nu-500)' : 'var(--tx-muted)' }};">
                    {{ $drive['aktif'] ? 'Aktif' : 'Nonaktif' }}
                </dd>

                <dt style="color:var(--tx-muted); font-weight:600;">Folder Tujuan</dt>
                <dd style="margin:0; font-family:var(--admin-font-mono); font-size:.75rem; word-break:break-all;">
                    {{ $drive['folder_id'] ?: '—' }}
                    @if ($drive['folder_url'])
                        · <a href="{{ $drive['folder_url'] }}" target="_blank" rel="noopener noreferrer" style="color:var(--nu-500);">buka folder</a>
                    @endif
                </dd>

                <dt style="color:var(--tx-muted); font-weight:600;">Kredensial</dt>
                <dd style="margin:0; font-family:var(--admin-font-mono); font-size:.72rem; word-break:break-all;">{{ $drive['kredensial'] ?: '—' }}</dd>
            </dl>

            @if ($drive['pesan'])
                <div class="alert alert-info" role="note" style="margin-bottom:1rem;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                    <span>{{ $drive['pesan'] }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.pengaturan.ujiDrive') }}">
                @csrf
                <button type="submit" class="btn btn-outline" @disabled(!$drive['aktif'])>
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    Uji Koneksi Google Drive
                </button>
            </form>

            <div style="margin-top:1.25rem; padding-top:1rem; border-top:1px dashed var(--sf-border); font-size:.78rem; color:var(--tx-muted); line-height:1.7;">
                <strong style="color:var(--tx-secondary);">Belum aktif?</strong> Isi variabel berikut di berkas <code>.env</code> lalu jalankan <code>php artisan config:clear</code>:<br>
                <code style="font-family:var(--admin-font-mono);">GOOGLE_DRIVE_ENABLED=true</code><br>
                <code style="font-family:var(--admin-font-mono);">GOOGLE_DRIVE_FOLDER_ID=&lt;id folder Drive&gt;</code><br>
                <code style="font-family:var(--admin-font-mono);">GOOGLE_DRIVE_CREDENTIALS=&lt;path google-credentials.json&gt;</code><br>
                Jangan lupa membagikan folder Drive tersebut ke email service account dengan akses <strong>Editor</strong>.
            </div>
        </div>
    </div>
</div>
@endsection
