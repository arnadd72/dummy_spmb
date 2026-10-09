@extends('layouts.admin')

@section('title', ($mode === 'edit' ? 'Edit FAQ' : 'Tambah FAQ') . ' — Admin PPDB')

@push('head')
@include('admin.content.partials.styles')
@endpush

@section('content')
@php
    $isEdit = $mode === 'edit';
    $keywordsValue = old('keywords', is_array($item->keywords) ? implode(', ', $item->keywords) : '');
@endphp

<div class="content-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">{{ $isEdit ? 'Edit FAQ' : 'Tambah FAQ' }}</h1>
            <p class="page-sub">
                FAQ ini menjadi sumber jawaban chatbot. Gunakan bahasa yang jelas dan kata kunci
                yang benar-benar biasa dipakai pengunjung.
            </p>
        </div>
        <a href="{{ route('admin.faq.index') }}" class="btn btn-ghost">
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
          action="{{ $isEdit ? route('admin.faq.update', $item->id) : route('admin.faq.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Detail Pertanyaan &amp; Jawaban</h2>
                <p class="panel-sub">Kolom bertanda <span style="color:var(--st-error)">*</span> wajib diisi.</p>
            </div>

            <div class="panel-body">
                <div class="form-grid">
                    <div class="field field-full">
                        <label class="field-label" for="pertanyaan">Pertanyaan <span class="req">*</span></label>
                        <input type="text" id="pertanyaan" name="pertanyaan"
                               class="field-input {{ $errors->has('pertanyaan') ? 'err' : '' }}"
                               value="{{ old('pertanyaan', $item->pertanyaan) }}"
                               placeholder="Contoh: Apa saja syarat pendaftaran?" required>
                        @error('pertanyaan') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field field-full">
                        <label class="field-label" for="jawaban">Jawaban <span class="req">*</span></label>
                        <textarea id="jawaban" name="jawaban"
                                  class="field-textarea {{ $errors->has('jawaban') ? 'err' : '' }}"
                                  placeholder="Tuliskan jawaban lengkap yang akan ditampilkan chatbot..."
                                  required>{{ old('jawaban', $item->jawaban) }}</textarea>
                        @error('jawaban') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field-label" for="kategori">Kategori</label>
                        <input type="text" id="kategori" name="kategori"
                               class="field-input {{ $errors->has('kategori') ? 'err' : '' }}"
                               value="{{ old('kategori', $item->kategori) }}"
                               placeholder="Contoh: Pendaftaran, Jadwal, Biaya" maxlength="50">
                        <span class="field-help">Label bebas untuk mengelompokkan FAQ.</span>
                        @error('kategori') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field-label" for="keywords">Kata Kunci Pemicu</label>
                        <input type="text" id="keywords" name="keywords"
                               class="field-input {{ $errors->has('keywords') ? 'err' : '' }}"
                               value="{{ $keywordsValue }}"
                               placeholder="syarat, persyaratan, dokumen, berkas">
                        <span class="field-help">Pisahkan dengan koma. Kata kunci ini membantu chatbot mencocokkan pertanyaan pengunjung.</span>
                        @error('keywords') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field field-full">
                        <div class="switch-row">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $isEdit ? $item->is_active : true) ? 'checked' : '' }}>
                            <label for="is_active">Aktifkan FAQ ini pada chatbot</label>
                        </div>
                        <span class="field-help">FAQ nonaktif disimpan tetapi tidak akan dipakai chatbot.</span>
                    </div>
                </div>
            </div>

            <div class="actions-bar">
                <span class="actions-note">Perubahan langsung berlaku pada asisten chatbot.</span>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    <a href="{{ route('admin.faq.index') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan FAQ' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
