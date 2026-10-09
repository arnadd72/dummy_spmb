@extends('layouts.admin')

@section('title', ($mode === 'edit' ? 'Edit Foto Galeri' : 'Unggah Foto Galeri') . ' — Admin PPDB')

@push('head')
@include('admin.content.partials.styles')
@endpush

@section('content')
@php
    $isEdit = $mode === 'edit';
@endphp

<div class="content-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">{{ $isEdit ? 'Edit Foto Galeri' : 'Unggah Foto Galeri' }}</h1>
            <p class="page-sub">Foto aktif akan tampil pada bagian “Galeri Kegiatan” di landing page.</p>
        </div>
        <a href="{{ route('admin.galeri.index') }}" class="btn btn-ghost">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" role="alert">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
            <div>
                <div>Periksa kembali isian Anda:</div>
                <ul style="margin:.35rem 0 0; padding-left:1.1rem; font-weight:500;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST"
          action="{{ $isEdit ? route('admin.galeri.update', $item->id) : route('admin.galeri.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Detail Foto</h2>
                <p class="panel-sub">Kolom bertanda <span style="color:var(--st-error)">*</span> wajib diisi.</p>
            </div>

            <div class="panel-body">
                <div class="form-grid">
                    <div class="field field-full">
                        <label class="field-label" for="judul">Judul / Keterangan Foto <span class="req">*</span></label>
                        <input type="text" id="judul" name="judul"
                               class="field-input {{ $errors->has('judul') ? 'err' : '' }}"
                               value="{{ old('judul', $item->judul) }}"
                               placeholder="Contoh: Praktik di Bengkel TKR" required>
                        @error('judul') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field field-full">
                        <label class="field-label" for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi"
                                  class="field-textarea {{ $errors->has('deskripsi') ? 'err' : '' }}"
                                  placeholder="Keterangan tambahan mengenai foto (opsional)...">{{ old('deskripsi', $item->deskripsi) }}</textarea>
                        @error('deskripsi') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field field-full">
                        <label class="field-label" for="gambar">
                            Berkas Foto
                            @if (!$isEdit) <span class="req">*</span> @endif
                        </label>
                        @if ($isEdit && $item->has_image)
                            <div class="preview-box">
                                <img src="{{ $item->url }}" alt="Foto saat ini" class="preview-img">
                                <div>
                                    <div style="font-weight:600; font-size:.82rem; color:var(--tx-primary);">Foto saat ini</div>
                                    <div class="field-help">Biarkan kosong bila tidak ingin mengganti foto. Unggah gambar baru untuk menggantinya (foto lama otomatis dihapus).</div>
                                </div>
                            </div>
                        @endif
                        <input type="file" id="gambar" name="gambar" accept="image/png,image/jpeg,image/webp"
                               class="file-input {{ $errors->has('gambar') ? 'err' : '' }}"
                               {{ $isEdit ? '' : 'required' }}>
                        <span class="field-help">Format JPG, PNG, atau WEBP. Maksimal 4 MB.</span>
                        @error('gambar') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field field-full">
                        <div class="switch-row">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $isEdit ? $item->is_active : true) ? 'checked' : '' }}>
                            <label for="is_active">Tampilkan foto ini di galeri landing page</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions-bar">
                <span class="actions-note">Ukuran berkas besar dapat memperlambat halaman.</span>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    <a href="{{ route('admin.galeri.index') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Foto' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
