<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PPDB') — SMK Ma'arif NU 1 Wangon</title>
    <meta name="description" content="@hasSection('meta_description')@yield('meta_description')@else Pendaftaran Peserta Didik Baru (PPDB) SMK Ma'arif NU 1 Wangon Tahun Ajaran {{ $tahunAjaran ?? '2027/2028' }}. Sekolah Pusat Keunggulan Berkarakter Islami & Siap Kerja.@endif">

    {{-- Google Fonts: Atkinson Hyperlegible (Accessible & Ethical per UI/UX Pro Max) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
    {{-- Skip link for keyboard accessibility --}}
    <a href="#main-content" class="skip-link">Langsung ke konten utama</a>

    {{-- Top bar credentials --}}
    <div class="topbar">
        <div class="topbar-inner">
            <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                <span class="topbar-badge">SMK Pusat Keunggulan</span>
                <span style="font-weight:700; color:#fef3c7;">Akreditasi A</span>
                <span style="color:rgba(255,255,255,0.4);">•</span>
                <span>NPSN: <strong>20330450</strong></span>
                <span style="color:rgba(255,255,255,0.4);">•</span>
                <span>LP Ma'arif NU Kab. Banyumas</span>
            </div>
            <div style="display:flex; align-items:center; gap:1rem;">
                <a href="tel:081628003566" style="color:rgba(255,255,255,0.85); text-decoration:none; display:flex; align-items:center; gap:0.375rem;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    0816-2800-3566
                </a>
                <span style="color:rgba(255,255,255,0.4);">•</span>
                <a href="https://www.smkmanu1wangon.sch.id" target="_blank" rel="noopener" style="color:#fef08a; text-decoration:none; font-weight:700;">
                    smkmanu1wangon.sch.id ↗
                </a>
            </div>
        </div>
    </div>

    {{-- Navbar --}}
    <nav class="navbar" role="navigation" aria-label="Navigasi utama">
        <div class="navbar-inner">
            <a href="{{ route('ppdb.index') }}" class="navbar-brand" aria-label="SMK Ma'arif NU 1 Wangon - Halaman Utama">
                <div class="navbar-emblem" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                    </svg>
                </div>
                <div>
                    <div class="navbar-title">SMK Ma'arif NU 1 Wangon</div>
                    <div class="navbar-subtitle">PPDB Terpadu {{ $tahunAjaran ?? '2027/2028' }}</div>
                </div>
            </a>

            <ul class="navbar-nav" id="navbar-menu">
                <li>
                    <a href="{{ route('ppdb.index') }}#jurusan">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        4 Jurusan
                    </a>
                </li>
                <li>
                    <a href="{{ route('ppdb.cekStatus') }}">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Cek Status
                    </a>
                </li>
                <li>
                    <a href="{{ route('ppdb.step1') }}" class="btn btn-primary btn-sm">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                        </svg>
                        Daftar PPDB
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    {{-- Main Content --}}
    <main id="main-content" tabindex="-1">
        @if (session('error'))
            <div class="container" style="padding-top:1.5rem;">
                <div class="alert alert-error" role="alert">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="container" style="padding-top:1.5rem;">
                <div class="alert alert-success" role="alert">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:2.5rem; margin-bottom:2.5rem;">
                <div>
                    <div style="display:flex; align-items:center; gap:0.625rem; margin-bottom:1rem;">
                        <div style="width:2.25rem; height:2.25rem; border-radius:6px; background:#057a3c; border:2px solid #f59e0b; display:flex; align-items:center; justify-content:center; color:white; font-weight:800;">NU</div>
                        <div style="font-weight:800; font-size:1.1rem; color:white;">SMK Ma'arif NU 1 Wangon</div>
                    </div>
                    <p style="font-size:0.875rem; color:rgba(255,255,255,0.8); line-height:1.7;">
                        Sekolah Menengah Kejuruan Pusat Keunggulan (SMK PK) berbasis nilai Ahlussunnah wal Jama'ah An-Nahdliyyah, berkomitmen mencetak lulusan berakhlak mulia, terampil, dan siap kerja.
                    </p>
                    <div style="margin-top:1rem; font-size:0.8125rem; color:#fef08a;">
                        Kepala Sekolah: <strong>Drs. Mukhtarom, M.Si</strong>
                    </div>
                </div>

                <div>
                    <div style="font-weight:700; color:white; font-size:0.95rem; margin-bottom:1rem; text-transform:uppercase; letter-spacing:0.06em;">Program Keahlian</div>
                    <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.5rem; font-size:0.875rem;">
                        <li><a href="{{ route('ppdb.index') }}#jurusan">Akuntansi dan Keuangan Lembaga (AKL)</a></li>
                        <li><a href="{{ route('ppdb.index') }}#jurusan">Manajemen Perkantoran & Layanan Bisnis (MPLB)</a></li>
                        <li><a href="{{ route('ppdb.index') }}#jurusan">Farmasi Klinis dan Komunitas (FKK)</a></li>
                        <li><a href="{{ route('ppdb.index') }}#jurusan">Teknik Kendaraan Ringan Otomotif (TKR)</a></li>
                    </ul>
                </div>

                <div>
                    <div style="font-weight:700; color:white; font-size:0.95rem; margin-bottom:1rem; text-transform:uppercase; letter-spacing:0.06em;">Kontak & Lokasi</div>
                    <div style="font-size:0.875rem; color:rgba(255,255,255,0.85); line-height:1.7;">
                        <div><strong>Alamat:</strong> Jl. Karangjengkol, Wangon, Kec. Wangon, Kab. Banyumas, Jawa Tengah 53176</div>
                        <div style="margin-top:0.375rem;"><strong>NPSN:</strong> 20330450 | <strong>Akreditasi:</strong> A</div>
                        <div style="margin-top:0.375rem;"><strong>Telp / WhatsApp:</strong> <a href="tel:081628003566" style="color:#fef08a; font-weight:700;">0816-2800-3566</a></div>
                        <div style="margin-top:0.375rem;"><strong>Website Resmi:</strong> <a href="https://www.smkmanu1wangon.sch.id" target="_blank" rel="noopener">www.smkmanu1wangon.sch.id</a></div>
                    </div>
                </div>
            </div>

            <div style="padding-top:1.5rem; border-top:1px solid rgba(255,255,255,0.15); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; font-size:0.8125rem; color:rgba(255,255,255,0.65);">
                <div>
                    © {{ date('Y') }} SMK Ma'arif NU 1 Wangon (MANUSAWA). Hak Cipta Dilindungi.
                </div>
                <div style="display:flex; align-items:center; gap:1rem;">
                    <span>Sistem Penerimaan Peserta Didik Baru (PPDB) Digital Terpusat</span>
                    <span>•</span>
                    @if (session('admin_logged_in'))
                        <a href="{{ route('admin.dashboard') }}" style="color:#fef08a; text-decoration:none; font-weight:700;">Panel Admin ↗</a>
                    @else
                        <a href="{{ route('admin.login') }}" style="color:#fef08a; text-decoration:none; font-weight:700;">Login Admin</a>
                    @endif
                </div>
            </div>
        </div>
    </footer>

    {{-- Chatbot Widget --}}
    @include('components.chatbot')

    @stack('scripts')
</body>
</html>
