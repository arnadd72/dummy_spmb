@extends('layouts.admin')
@section('title', ($mode === 'create' ? 'Tambah' : 'Edit') . ' Isian Formulir — Admin PPDB')
@push('head')@include('admin.content.partials.styles')
<style>.help{font-size:.76rem;color:var(--tx-muted);margin-top:.25rem;line-height:1.5;}.req{color:#dc2626;}</style>
@endpush
@section('content')
<div class="content-page">
    <div class="page-head"><div>
        <h1 class="page-title">{{ $mode === 'create' ? 'Tambah Pertanyaan Baru' : 'Ubah: ' . $item->label }}</h1>
        <p class="page-sub">{{ $mode === 'create' ? 'Pertanyaan baru muncul di formulir publik sesuai langkah yang dipilih.' : (($item->is_kustom ? 'Pertanyaan buatan sendiri.' : 'Pertanyaan bawaan — kode kunci dikunci agar data tidak rusak.')) }}</p>
    </div><a href="{{ route('admin.form-field.index') }}" class="btn btn-ghost">← Kembali</a></div>
    @if($errors->any())<div class="alert alert-error"><ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ $mode === 'create' ? route('admin.form-field.store') : route('admin.form-field.update', $item->id) }}">
        @csrf @if($mode==='edit')@method('PUT')@endif
        <div class="panel"><div class="panel-body" style="display:grid;gap:1.1rem;max-width:720px;">
            <div><label class="form-label">1. Muncul di langkah mana? <span class="req">*</span></label>
            <select name="langkah" class="form-control">
                <option value="step1" {{ old('langkah',$item->langkah)==='step1'?'selected':'' }}>Langkah 1 — Persetujuan</option>
                <option value="step2" {{ old('langkah',$item->langkah)==='step2'?'selected':'' }}>Langkah 2 — Berkas KK & Akta</option>
                <option value="step3" {{ old('langkah',$item->langkah)==='step3'?'selected':'' }}>Langkah 3 — Nilai Rapor</option>
                <option value="step4" {{ old('langkah',$item->langkah)==='step4'?'selected':'' }}>Langkah 4 — Jurusan</option>
                <option value="step5" {{ old('langkah',$item->langkah)==='step5'?'selected':'' }}>Langkah 5 — Data Siswa</option>
                <option value="step6" {{ old('langkah',$item->langkah)==='step6'?'selected':'' }}>Langkah 6 — Data Orang Tua</option>
                <option value="step7" {{ old('langkah',$item->langkah)==='step7'?'selected':'' }}>Langkah 7 — Tambahan</option>
                <option value="step8" {{ old('langkah',$item->langkah)==='step8'?'selected':'' }}>Langkah 8 — Konfirmasi</option>
            </select>
            <div class="help">Pilih halaman formulir tempat pertanyaan ini tampil.</div></div>
            <div><label class="form-label">2. Tulisan pertanyaan <span class="req">*</span></label>
            <input type="text" name="label" class="form-control" value="{{ old('label',$item->label) }}" required placeholder="Contoh: Agama">
            <div class="help">Ini yang dibaca calon siswa. Contoh: “Agama”, “Hobi”, “No. HP Wali”.</div></div>
            <div><label class="form-label">3. Penjelasan tambahan (boleh kosong)</label>
            <input type="text" name="teks_bantuan" class="form-control" value="{{ old('teks_bantuan',$item->teks_bantuan) }}" placeholder="Contoh: Pilih sesuai KK">
            <div class="help">Tulisan kecil abu-abu di bawah pertanyaan.</div></div>
            <div><label class="form-label">4. Model jawaban <span class="req">*</span></label>
            <select name="tipe" class="form-control" id="tipe-select">
                @foreach(['text'=>'Ketik singkat (satu baris)','number'=>'Angka saja','tel'=>'Nomor HP','email'=>'Email','date'=>'Tanggal','textarea'=>'Ketik panjang','select'=>'Pilihan dropdown','radio'=>'Pilihan bulat','checkbox'=>'Centang ya/tidak','file'=>'Upload berkas'] as $v=>$l)
                <option value="{{ $v }}" {{ old('tipe',$item->tipe)===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <div class="help">“Pilihan dropdown / bulat” wajib isi daftar pilihan di bawah.</div></div>
            <div id="opsi-wrap"><label class="form-label">5. Daftar pilihan (satu baris = satu pilihan)</label>
            <textarea name="opsi_teks" rows="5" class="form-control" placeholder="Contoh:&#10;Islam&#10;Kristen&#10;Katolik">{{ old('opsi_teks', $item->opsi ? implode("\n", (array)$item->opsi) : '') }}</textarea>
            <div class="help">Hanya dipakai bila model jawaban = dropdown / pilihan bulat. Selain itu dikosongkan saja.</div></div>
            <div><label class="form-label">6. Contoh isi dalam kotak (boleh kosong)</label>
            <input type="text" name="placeholder" class="form-control" value="{{ old('placeholder',$item->placeholder) }}" placeholder="Contoh: 08xxxxxxxxxx">
            <div class="help">Tulisan bayangan di dalam kotak, hilang saat diketik.</div></div>
            <div><label class="form-label">7. Isi otomatis (boleh kosong)</label>
            <input type="text" name="nilai_default" class="form-control" value="{{ old('nilai_default',$item->nilai_default) }}" placeholder="Contoh: Wangon">
            <div class="help">Kotak langsung terisi ini; siswa bisa mengubahnya.</div></div>
            <div><label class="form-label">8. Posisi tampil (angka)</label>
            <input type="number" name="urutan" class="form-control" value="{{ old('urutan',$item->urutan ?? 999) }}" min="0" max="9999">
            <div class="help">📍 <strong>Artinya:</strong> nomor antrean dari atas. <strong>1 = paling atas</strong>, makin besar makin ke bawah. Contoh: Agama=110 tampil di atas Penghasilan=140. Biarkan 999 bila mau taruh paling bawah.</div></div>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                <label class="switch-pill"><input type="checkbox" name="tampil" value="1" {{ old('tampil',$item->tampil ?? true)?'checked':'' }}> 👁️ Tampilkan ke siswa</label>
                <label class="switch-pill"><input type="checkbox" name="wajib" value="1" {{ old('wajib',$item->wajib ?? false)?'checked':'' }}> * Wajib diisi</label>
            </div>
            <div class="help">Matikan “Tampilkan” = disembunyikan tapi data lama aman. Nyalakan “Wajib” = siswa tidak bisa lanjut sebelum mengisi.</div>
            <div><button type="submit" class="btn btn-primary">💾 {{ $mode==='create'?'Tambah Pertanyaan':'Simpan Perubahan' }}</button></div>
        </div></div>
    </form>
</div>
<script>
(function(){var s=document.getElementById('tipe-select');var w=document.getElementById('opsi-wrap');if(!s||!w)return;function t(){var v=s.value;w.style.display=(v==='select'||v==='radio')?'':'none';}s.addEventListener('change',t);t();})();
</script>
@endsection
