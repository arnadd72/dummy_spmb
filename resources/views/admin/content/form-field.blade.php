@extends('layouts.admin')

@section('title', 'Isian Formulir — Admin PPDB')

@push('head')
@include('admin.content.partials.styles')
<style>
    .langkah-group { margin-bottom: 1.5rem; }
    .langkah-head { display:flex; align-items:center; gap:.6rem; margin-bottom:.7rem; }
    .langkah-num { width:30px; height:30px; border-radius:9px; background:var(--nu-500); color:#fff; font-weight:800; font-size:.8rem; display:flex; align-items:center; justify-content:center; }
    .langkah-title { font-weight:700; color:var(--nu-700); font-size:.95rem; margin:0; }
    .langkah-count { font-size:.72rem; color:var(--tx-muted); background:var(--sf-page); border:1px solid var(--sf-border); padding:.15rem .55rem; border-radius:99px; }
    .isian-card { display:flex; align-items:center; gap:.8rem; background:var(--sf-card); border:1px solid var(--sf-border); border-radius:12px; padding:.7rem .9rem; margin-bottom:.5rem; }
    .isian-card.nonaktif { opacity:.65; background:var(--sf-page); }
    .isian-pos { width:52px; flex-shrink:0; }
    .isian-pos input { width:52px; text-align:center; padding:.4rem .2rem; border:1px solid var(--sf-border); border-radius:8px; font-weight:700; font-size:.82rem; }
    .isian-main { flex:1; min-width:0; }
    .isian-label { font-weight:600; font-size:.88rem; margin:0; }
    .isian-sub { font-size:.72rem; color:var(--tx-muted); margin:.1rem 0 0; }
    .switch-pill { display:inline-flex; align-items:center; gap:.35rem; font-size:.75rem; font-weight:600; border:1px solid var(--sf-border); border-radius:99px; padding:.3rem .6rem; cursor:pointer; background:#fff; white-space:nowrap; }
    .switch-pill input { accent-color:var(--nu-500); width:15px; height:15px; }
    .switch-pill.off { background:#f1f5f9; color:var(--tx-muted); }
    .filter-bar { display:flex; gap:.6rem; flex-wrap:wrap; align-items:center; margin-bottom:1rem; }
</style>
@endpush

@section('content')
<div class="content-page">
    <div class="page-head">
        <div>
            <h1 class="page-title">Isian Formulir Pendaftaran</h1>
            <p class="page-sub">Atur pertanyaan yang dilihat calon siswa. <strong>Tampil</strong> = dimunculkan, <strong>Wajib</strong> = harus diisi, <strong>Posisi</strong> = nomor urut dari atas (1 = paling atas).</p>
        </div>
        <div style="display:flex; gap:.6rem; flex-wrap:wrap;">
            <button type="submit" form="form-simpan" class="btn btn-primary">💾 Simpan Semua</button>
            <a href="{{ route('admin.form-field.create') }}" class="btn btn-ghost">+ Tambah Pertanyaan</a>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-error"><ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="alert alert-info">
        💡 <strong>Cara pakai:</strong> sembunyikan yang tidak perlu (matikan <em>Tampil</em>), wajibkan yang penting (nyalakan <em>Wajib</em>),
        geser <em>Posisi</em> untuk mengatur urutan, lalu klik <strong>💾 Simpan Semua di atas</strong>. Klik <strong>Ubah</strong> untuk ganti tulisan & pilihan jawaban.
    </div>

    @php
        $namaLangkah = ['step1'=>'Langkah 1 — Persetujuan','step2'=>'Langkah 2 — Berkas KK & Akta','step3'=>'Langkah 3 — Nilai Rapor','step4'=>'Langkah 4 — Jurusan','step5'=>'Langkah 5 — Data Siswa','step6'=>'Langkah 6 — Data Orang Tua','step7'=>'Langkah 7 — Tambahan','step8'=>'Langkah 8 — Konfirmasi'];
        $namaTipe = ['text'=>'Isian singkat','number'=>'Angka','tel'=>'No. HP','email'=>'Email','date'=>'Tanggal','textarea'=>'Isian panjang','select'=>'Pilihan dropdown','radio'=>'Pilihan bulat','checkbox'=>'Centang','file'=>'Upload berkas'];
        $filter = request('langkah', '');
        $cari = request('search', '');
        $items = $list instanceof \Illuminate\Pagination\LengthAwarePaginator ? $list->getCollection() : collect($list);
        if($filter) $items = $items->where('langkah', $filter);
        if($cari) $items = $items->filter(fn($i) => stripos($i->label.' '.$i->kunci, $cari) !== false);
        $grup = $items->groupBy('langkah');
    @endphp

    <form method="GET" action="{{ route('admin.form-field.index') }}" class="filter-bar">
        <select name="langkah" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua langkah</option>
            @foreach($namaLangkah as $k=>$v)<option value="{{ $k }}" {{ $filter===$k?'selected':'' }}>{{ $v }}</option>@endforeach
        </select>
        <div class="search-wrap" style="max-width:320px;">
            <input type="text" name="search" class="search-input" placeholder="Cari: misal agama, alamat…" value="{{ $cari }}">
        </div>
        @if($filter || $cari)<a href="{{ route('admin.form-field.index') }}" class="btn btn-ghost btn-sm">Reset</a>@endif
    </form>

    <form method="POST" action="{{ route('admin.form-field.simpan-urutan') }}" id="form-simpan">
        @csrf
        @forelse($grup as $langkah => $rows)
        <div class="langkah-group">
            <div class="langkah-head">
                <div class="langkah-num">{{ substr($langkah, 4) }}</div>
                <p class="langkah-title">{{ $namaLangkah[$langkah] ?? $langkah }}</p>
                <span class="langkah-count">{{ $rows->count() }} pertanyaan</span>
            </div>
            @foreach($rows->sortBy('urutan') as $item)
            <div class="isian-card {{ $item->tampil ? '' : 'nonaktif' }}">
                <div class="isian-pos" title="Posisi tampil (1 = paling atas)">
                    <input type="number" name="fields[{{ $item->id }}][urutan]" value="{{ $item->urutan }}" min="0" max="9999">
                    <div style="font-size:.62rem;color:var(--tx-muted);text-align:center;">posisi</div>
                </div>
                <div class="isian-main">
                    <p class="isian-label">{{ $item->label }} @if($item->wajib)<span style="color:#dc2626;">*</span>@endif</p>
                    <p class="isian-sub">{{ $namaTipe[$item->tipe] ?? $item->tipe }}@if($item->is_kustom) • buatan sendiri @else • bawaan @endif</p>
                </div>
                <label class="switch-pill {{ $item->tampil?'':'off' }}" title="Tampilkan / sembunyikan dari formulir publik">
                    <input type="checkbox" name="fields[{{ $item->id }}][tampil]" value="1" {{ $item->tampil?'checked':'' }}> Tampil
                </label>
                <label class="switch-pill {{ $item->wajib?'':'off' }}" title="Calon siswa wajib mengisi">
                    <input type="checkbox" name="fields[{{ $item->id }}][wajib]" value="1" {{ $item->wajib?'checked':'' }}> Wajib
                </label>
                <a href="{{ route('admin.form-field.edit', $item->id) }}" class="btn btn-ghost btn-sm">Ubah</a>
            </div>
            @endforeach
        </div>
        @empty
        <div class="empty-box"><h3>Tidak ada isian</h3><p>Coba ubah filter atau tambah pertanyaan baru.</p></div>
        @endforelse

        <p style="font-size:.76rem;color:var(--tx-muted);margin-top:1rem;">Posisi kecil = tampil di atas. Contoh: 10 di atas 40.</p>
    </form>

    @if($list->hasPages())<div class="pagination-wrap">{{ $list->links() }}</div>@endif
</div>
@endsection
