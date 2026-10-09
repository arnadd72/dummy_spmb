@extends('layouts.admin')

@section('title', ($mode === 'edit' ? 'Edit Testimoni' : 'Tambah Testimoni') . ' — Admin PPDB')

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
            <h1 class="page-title">{{ $isEdit ? 'Edit Testimoni' : 'Tambah Testimoni' }}</h1>
            <p class="page-sub">Testimoni aktif akan tampil pada bagian “Kata Alumni” di landing page.</p>
        </div>
        <a href="{{ route('admin.testimoni.index') }}" class="btn btn-ghost">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" role="alert">
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
          action="{{ $isEdit ? route('admin.testimoni.update', $item->id) : route('admin.testimoni.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Detail Alumni</h2>
                <p class="panel-sub">Kolom bertanda <span style="color:var(--st-error)">*</span> wajib diisi.</p>
            </div>

            <div class="panel-body">
                <div class="form-grid">
                    <div class="field">
                        <label class="field-label" for="nama">Nama Alumni <span class="req">*</span></label>
                        <input type="text" id="nama" name="nama"
                               class="field-input {{ $errors->has('nama') ? 'err' : '' }}"
                               value="{{ old('nama', $item->nama) }}"
                               placeholder="Contoh: Budi Santoso" required>
                        @error('nama') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field-label" for="angkatan">Angkatan / Tahun Lulus</label>
                        <input type="text" id="angkatan" name="angkatan"
                               class="field-input"
                               value="{{ old('angkatan', $item->angkatan) }}"
                               placeholder="Contoh: 2020">
                    </div>

                    <div class="field">
                        <label class="field-label" for="jurusan_kode">Jurusan</label>
                        <input type="text" id="jurusan_kode" name="jurusan_kode"
                               class="field-input"
                               value="{{ old('jurusan_kode', $item->jurusan_kode) }}"
                               placeholder="Contoh: TKR / AKL / MPLB / FKK">
                    </div>

                    <div class="field">
                        <label class="field-label" for="pekerjaan">Pekerjaan Saat Ini</label>
                        <input type="text" id="pekerjaan" name="pekerjaan"
                               class="field-input"
                               value="{{ old('pekerjaan', $item->pekerjaan) }}"
                               placeholder="Contoh: Teknisi di Astra Honda">
                    </div>

                    <div class="field field-full">
                        <label class="field-label" for="testimoni">Isi Testimoni <span class="req">*</span></label>
                        <textarea id="testimoni" name="testimoni" rows="5"
                                  class="field-textarea {{ $errors->has('testimoni') ? 'err' : '' }}"
                                  placeholder="Ceritakan pengalaman selama sekolah..." required>{{ old('testimoni', $item->testimoni) }}</textarea>
                        @error('testimoni') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field field-full">
                        <label class="field-label" for="foto">Foto Alumni</label>
                        @if ($isEdit && $item->foto)
                            <div class="preview-box">
                                <img src="{{ $item->url }}" alt="Foto saat ini" class="preview-img">
                                <div>
                                    <div style="font-weight:600; font-size:.82rem;">Foto saat ini</div>
                                    <div class="field-help">Biarkan kosong bila tidak ingin mengganti foto.</div>
                                </div>
                            </div>
                        @endif
                        <input type="file" id="foto" name="foto" accept="image/png,image/jpeg,image/webp"
                               class="file-input">
                        <span class="field-help">Format JPG, PNG, atau WEBP. Maksimal 4 MB (opsional).</span>
                    </div>

                    <div class="field field-full">
                        <div class="switch-row">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $isEdit ? $item->is_active : true) ? 'checked' : '' }}>
                            <label for="is_active">Tampilkan testimoni ini di landing page</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions-bar">
                <span class="actions-note">Pastikan testimoni sopan dan memotivasi.</span>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    <a href="{{ route('admin.testimoni.index') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Testimoni' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
