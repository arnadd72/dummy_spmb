@extends('layouts.admin')

@section('title', 'Detail Testimoni — Admin PPDB')

@push('head')
@include('admin.content.partials.styles')
@endpush

@section('content')
<div class="content-page">
    <div class="page-head">
        <div>
            <h1 class="page-title">Detail Testimoni</h1>
            <p class="page-sub">{{ $item->nama }}{{ $item->angkatan ? ' · Angkatan ' . $item->angkatan : '' }}</p>
        </div>
        <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
            <a href="{{ route('admin.testimoni.index') }}" class="btn btn-ghost">Kembali</a>
            <a href="{{ route('admin.testimoni.edit', $item->id) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    <div class="panel">
        <div class="panel-body">
            <div style="display:flex; gap:1.25rem; align-items:flex-start; flex-wrap:wrap;">
                @if ($item->foto)
                    <img src="{{ $item->url }}" alt="{{ $item->nama }}" style="width:120px; height:120px; object-fit:cover; border-radius:16px; border:1px solid var(--sf-border);">
                @endif
                <div style="flex:1; min-width:240px;">
                    <span class="badge {{ $item->is_active ? 'badge-on' : 'badge-off' }}">
                        {{ $item->is_active ? 'Tampil di landing' : 'Disembunyikan' }}
                    </span>
                    <h2 style="margin:.6rem 0 .25rem; font-size:1.25rem;">{{ $item->nama }}</h2>
                    <div style="font-size:.85rem; color:var(--tx-muted); margin-bottom:.75rem;">
                        {{ $item->jurusan_kode ? $item->jurusan_kode . ' · ' : '' }}{{ $item->pekerjaan ?? '—' }}
                    </div>
                    <blockquote style="margin:0; padding:1rem 1.25rem; background:var(--sf-page); border-left:4px solid var(--nu-500); border-radius:0 12px 12px 0; font-size:.95rem; line-height:1.7;">
                        “{{ $item->testimoni }}”
                    </blockquote>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.testimoni.destroy', $item->id) }}"
          onsubmit="return confirm('Hapus testimoni ini?');" style="margin-top:1rem;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Hapus Testimoni</button>
    </form>
</div>
@endsection
