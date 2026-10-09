@extends('layouts.admin')

@section('title', ($mode === 'edit' ? 'Edit Event' : 'Tambah Event') . ' — Admin PPDB')

@push('head')
@include('admin.content.partials.styles')
@endpush

@section('content')
@php
    $isEdit = $mode === 'edit';
    $tanggalValue = old('tanggal', $isEdit && $item->tanggal ? $item->tanggal->format('Y-m-d') : '');
@endphp

<div class="content-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">{{ $isEdit ? 'Edit Event' : 'Tambah Event' }}</h1>
            <p class="page-sub">Event aktif akan tampil pada bagian “Agenda Mendatang” di landing page.</p>
        </div>
        <a href="{{ route('admin.event.index') }}" class="btn btn-ghost">
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
          action="{{ $isEdit ? route('admin.event.update', $item->id) : route('admin.event.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Detail Event</h2>
                <p class="panel-sub">Kolom bertanda <span style="color:var(--st-error)">*</span> wajib diisi.</p>
            </div>

            <div class="panel-body">
                <div class="form-grid">
                    <div class="field field-full">
                        <label class="field-label" for="judul">Judul Event <span class="req">*</span></label>
                        <input type="text" id="judul" name="judul"
                               class="field-input {{ $errors->has('judul') ? 'err' : '' }}"
                               value="{{ old('judul', $item->judul) }}"
                               placeholder="Contoh: Sosialisasi PPDB untuk Orang Tua" required>
                        @error('judul') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field-label" for="tanggal">Tanggal Pelaksanaan</label>
                        <input type="date" id="tanggal" name="tanggal"
                               class="field-input {{ $errors->has('tanggal') ? 'err' : '' }}"
                               value="{{ $tanggalValue }}">
                        <span class="field-help">Kosongkan bila tanggal belum ditentukan.</span>
                        @error('tanggal') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field-label" for="lokasi">Lokasi</label>
                        <input type="text" id="lokasi" name="lokasi"
                               class="field-input {{ $errors->has('lokasi') ? 'err' : '' }}"
                               value="{{ old('lokasi', $item->lokasi) }}"
                               placeholder="Contoh: Aula SMK Ma'arif NU 1 Wangon" maxlength="255">
                        @error('lokasi') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field field-full">
                        <label class="field-label" for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi"
                                  class="field-textarea {{ $errors->has('deskripsi') ? 'err' : '' }}"
                                  placeholder="Keterangan singkat mengenai event...">{{ old('deskripsi', $item->deskripsi) }}</textarea>
                        @error('deskripsi') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field field-full">
                        <label class="field-label" for="gambar">Poster / Flyer Event</label>
                        @if ($isEdit && $item->has_gambar)
                            <div class="preview-box">
                                <img src="{{ $item->gambar_url }}" alt="Poster saat ini" class="preview-img">
                                <div>
                                    <div style="font-weight:600; font-size:.82rem; color:var(--tx-primary);">Poster saat ini</div>
                                    <div class="field-help">Unggah gambar baru untuk menggantinya. Gambar lama akan dihapus otomatis.</div>
                                </div>
                            </div>
                        @endif
                        <input type="file" id="gambar" name="gambar" accept="image/png,image/jpeg,image/webp"
                               class="file-input {{ $errors->has('gambar') ? 'err' : '' }}">
                        <span class="field-help">Format JPG, PNG, atau WEBP. Maksimal 4 MB.</span>
                        @error('gambar') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field field-full">
                        <div class="switch-row">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $isEdit ? $item->is_active : true) ? 'checked' : '' }}>
                            <label for="is_active">Tampilkan event ini di landing page</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions-bar">
                <span class="actions-note">Perubahan otomatis terlihat di portal siswa.</span>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    <a href="{{ route('admin.event.index') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Event' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
