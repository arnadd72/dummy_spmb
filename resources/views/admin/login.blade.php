@extends('layouts.app')

@section('title', 'Login Admin — SPMB SMK NU')

@section('content')
<div style="min-height:70vh; display:flex; align-items:center; justify-content:center; padding:3rem 1rem;">
    <div style="width:100%; max-width:420px; background:#fff; border:1px solid #e5e7eb; border-radius:20px; padding:2rem; box-shadow:0 20px 50px rgba(5,122,60,.12);">
        <div style="text-align:center; margin-bottom:1.5rem;">
            <div style="width:56px; height:56px; margin:0 auto .75rem; border-radius:16px; background:linear-gradient(135deg,#0A3D1F,#057A3C); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:1.4rem;">A</div>
            <h1 style="font-size:1.35rem; font-weight:800; color:#0A3D1F; margin:0;">Login Panel Admin</h1>
            <p style="font-size:.85rem; color:#6b7280; margin:.35rem 0 0;">Masuk untuk mengelola SPMB. Kredensial bisa diganti di Pengaturan.</p>
        </div>

        @if ($errors->any())
            <div style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:12px; padding:.75rem 1rem; font-size:.85rem; margin-bottom:1rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label for="username" style="display:block; font-size:.82rem; font-weight:700; color:#374151; margin-bottom:.35rem;">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                       placeholder="Masukkan username admin"
                       style="width:100%; border:1px solid #d1d5db; border-radius:12px; padding:.7rem .9rem; font-size:.92rem;">
            </div>
            <div style="margin-bottom:1.25rem;">
                <label for="password" style="display:block; font-size:.82rem; font-weight:700; color:#374151; margin-bottom:.35rem;">Kata Sandi</label>
                <input type="password" id="password" name="password" required
                       placeholder="Masukkan kata sandi"
                       style="width:100%; border:1px solid #d1d5db; border-radius:12px; padding:.7rem .9rem; font-size:.92rem;">
            </div>
            <button type="submit" style="width:100%; background:linear-gradient(135deg,#057A3C,#0D9050); color:#fff; border:none; border-radius:12px; padding:.8rem; font-weight:800; font-size:.95rem; cursor:pointer;">
                Masuk ke Panel Admin
            </button>
        </form>

        <div style="text-align:center; margin-top:1rem;">
            <a href="{{ route('ppdb.index') }}" style="font-size:.82rem; color:#057A3C; font-weight:600;">← Kembali ke Beranda</a>
        </div>
    </div>
</div>
@endsection
