<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard PPDB') — SMK Ma'arif NU 1 Wangon</title>

    {{-- Typography: Fira Code (headings/data) + Fira Sans (body) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ─── Admin Design Tokens ─── */
        :root {
            --admin-sidebar-w: 260px;
            --admin-font-sans: 'Fira Sans', system-ui, -apple-system, sans-serif;
            --admin-font-mono: 'Fira Code', ui-monospace, monospace;

            /* NU Emerald Palette */
            --nu-50:  #ECFDF3;
            --nu-100: #D1FAE5;
            --nu-200: #A7F3D0;
            --nu-400: #34D399;
            --nu-500: #057A3C;
            --nu-600: #047733;
            --nu-700: #024B23;
            --nu-800: #022C16;
            --nu-900: #012A14;

            /* Surface system */
            --sf-page:    #F7FAF8;
            --sf-card:    #FFFFFF;
            --sf-hover:   #F0F7F2;
            --sf-border:  #DCE8E0;
            --sf-border-h:#B8D4C0;

            /* Text */
            --tx-primary:   #0F1F15;
            --tx-secondary: #3D5A47;
            --tx-muted:     #7A9484;

            /* Gold accent */
            --gold-500: #D97706;
            --gold-100: #FEF3C7;

            /* Status */
            --st-error:   #DC2626;
            --st-error-bg:#FEE2E2;
            --st-info:    #0284C7;
            --st-info-bg: #E0F2FE;

            /* Effects */
            --card-radius: 20px;
            --card-shadow: 0 1px 3px rgba(2,75,35,.06);
            --card-shadow-hover: 0 8px 30px -4px rgba(2,75,35,.12);
            --transition: 200ms cubic-bezier(.4,0,.2,1);
        }

        /* ─── Reset & Base ─── */
        *, *::before, *::after { box-sizing: border-box; }

        body.admin-body {
            font-family: var(--admin-font-sans);
            background: var(--sf-page);
            color: var(--tx-primary);
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ─── Sidebar ─── */
        .admin-sidebar {
            width: var(--admin-sidebar-w);
            background: var(--sf-card);
            border-right: 1px solid var(--sf-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            inset-block: 0;
            left: 0;
            z-index: 40;
            transition: transform .3s ease;
        }

        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid var(--sf-border);
        }

        .sidebar-brand-name {
            font-family: var(--admin-font-mono);
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--nu-700);
            line-height: 1.3;
            letter-spacing: -.02em;
        }

        .sidebar-brand-tag {
            font-family: var(--admin-font-mono);
            font-size: .65rem;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--nu-500);
            margin-top: .35rem;
            padding: .2rem .5rem;
            background: var(--nu-50);
            border-radius: 4px;
            display: inline-block;
        }

        .sidebar-nav {
            padding: 1rem .75rem;
            flex: 1;
            overflow-y: auto;
        }

        .sidebar-section-label {
            font-family: var(--admin-font-mono);
            font-size: .6rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: var(--tx-muted);
            padding: .5rem .75rem;
            margin-bottom: .25rem;
        }

        .sidebar-link {
            display: block;
            padding: .6rem .75rem;
            border-radius: 10px;
            color: var(--tx-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: .85rem;
            transition: all var(--transition);
            margin-bottom: 2px;
            position: relative;
        }

        .sidebar-link:hover {
            background: var(--sf-hover);
            color: var(--nu-500);
        }

        .sidebar-link.active {
            background: var(--nu-500);
            color: #FFFFFF;
            font-weight: 600;
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: -0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: var(--nu-500);
            border-radius: 0 3px 3px 0;
        }

        .sidebar-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid var(--sf-border);
        }

        .sidebar-footer-text {
            font-family: var(--admin-font-mono);
            font-size: .7rem;
            color: var(--tx-muted);
            letter-spacing: .02em;
        }

        .sidebar-footer-status {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: .35rem;
            font-size: .72rem;
            font-weight: 600;
            color: var(--nu-500);
        }

        .sidebar-footer-status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--nu-400);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .5; transform: scale(.85); }
        }

        /* ─── Main Area ─── */
        .admin-main {
            margin-left: var(--admin-sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* ─── Topbar ─── */
        .admin-topbar {
            height: 60px;
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1px solid var(--sf-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .topbar-search {
            width: 100%;
            max-width: 380px;
            padding: .5rem 1rem;
            background: var(--sf-page);
            border: 1px solid var(--sf-border);
            border-radius: 10px;
            font-family: var(--admin-font-sans);
            font-size: .82rem;
            color: var(--tx-primary);
            outline: none;
            transition: all var(--transition);
        }

        .topbar-search:focus {
            border-color: var(--nu-500);
            box-shadow: 0 0 0 3px rgba(5,122,60,.12);
        }

        .topbar-search::placeholder {
            color: var(--tx-muted);
        }

        .topbar-meta {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .topbar-chip {
            font-family: var(--admin-font-mono);
            font-size: .72rem;
            font-weight: 600;
            padding: .3rem .65rem;
            border-radius: 8px;
            background: var(--nu-50);
            color: var(--nu-700);
            border: 1px solid var(--sf-border);
        }

        .topbar-date {
            font-size: .78rem;
            color: var(--tx-muted);
            font-weight: 500;
        }

        /* ─── Content ─── */
        .admin-content {
            padding: 1.75rem 2rem 3rem;
            flex: 1;
        }

        /* ─── Bento Grid (Apple-style) ─── */
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 16px;
        }

        .bento-card {
            background: var(--sf-card);
            border: 1px solid var(--sf-border);
            border-radius: var(--card-radius);
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: all var(--transition);
            position: relative;
            overflow: hidden;
        }

        .bento-card:hover {
            box-shadow: var(--card-shadow-hover);
            border-color: var(--sf-border-h);
            transform: translateY(-2px);
        }

        /* ─── Mobile Hamburger ─── */
        .mobile-toggle {
            display: none;
            background: none;
            border: 1px solid var(--sf-border);
            border-radius: 8px;
            padding: .4rem .6rem;
            font-family: var(--admin-font-mono);
            font-size: .72rem;
            font-weight: 600;
            color: var(--tx-secondary);
            cursor: pointer;
            transition: all var(--transition);
        }

        .mobile-toggle:hover {
            background: var(--sf-hover);
        }

        /* ─── Responsive ─── */
        @media (max-width: 1024px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-sidebar.open {
                transform: translateX(0);
                box-shadow: 8px 0 30px rgba(0,0,0,.12);
            }
            .admin-main {
                margin-left: 0;
            }
            .bento-grid {
                grid-template-columns: 1fr;
            }
            .bento-card {
                grid-column: span 1 !important;
            }
            .mobile-toggle {
                display: inline-flex;
            }
        }

        @media (max-width: 640px) {
            .admin-content {
                padding: 1rem;
            }
            .admin-topbar {
                padding: 0 1rem;
            }
            .topbar-meta {
                display: none;
            }
        }

        /* ─── Reduced motion ─── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
    @stack('head')
</head>
<body class="admin-body">
    {{-- Sidebar --}}
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-name">SMK Ma'arif NU 1 Wangon</div>
            <div class="sidebar-brand-tag">Admin PPDB</div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard', 'admin.index') ? 'active' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('admin.participants') }}" class="sidebar-link {{ request()->routeIs('admin.participants') ? 'active' : '' }}">
                Data Pendaftar
            </a>
            <a href="{{ route('admin.documents') }}" class="sidebar-link {{ request()->routeIs('admin.documents') ? 'active' : '' }}">
                Verifikasi & OCR
            </a>

            <div class="sidebar-section-label" style="margin-top: 1rem;">Aksi & Ekspor</div>
            <a href="{{ route('admin.export.xls') }}" class="sidebar-link">
                Ekspor Rekap XLS
            </a>
            <a href="{{ route('admin.kuota.index') }}" class="sidebar-link {{ request()->routeIs('admin.kuota.*') ? 'active' : '' }}">
                Kelola Kuota
            </a>
            <a href="{{ route('admin.manual.create') }}" class="sidebar-link {{ request()->routeIs('admin.manual.*') ? 'active' : '' }}">
                Input Manual Luring
            </a>

            <div class="sidebar-section-label" style="margin-top: 1rem;">Tautan</div>
            <a href="{{ route('ppdb.index') }}" target="_blank" class="sidebar-link">
                Portal Publik ↗
            </a>
            <a href="{{ route('ppdb.step1') }}" target="_blank" class="sidebar-link">
                Formulir Baru ↗
            </a>

            <div class="sidebar-section-label" style="margin-top: 1rem;">Konten Landing Page</div>
            <a href="{{ route('admin.berita.index') }}" class="sidebar-link {{ request()->routeIs('admin.berita.*') ? 'active' : '' }}">
                Berita
            </a>
            <a href="{{ route('admin.event.index') }}" class="sidebar-link {{ request()->routeIs('admin.event.*') ? 'active' : '' }}">
                Agenda &amp; Event
            </a>
            <a href="{{ route('admin.galeri.index') }}" class="sidebar-link {{ request()->routeIs('admin.galeri.*') ? 'active' : '' }}">
                Galeri Foto
            </a>
            <a href="{{ route('admin.testimoni.index') }}" class="sidebar-link {{ request()->routeIs('admin.testimoni.*') ? 'active' : '' }}">
                Testimoni Alumni
            </a>
            <a href="{{ route('admin.pengaturan.index') }}" class="sidebar-link {{ request()->routeIs('admin.pengaturan.*') ? 'active' : '' }}">
                Pengaturan Situs
            </a>
            <a href="{{ route('admin.form-field.index') }}" class="sidebar-link {{ request()->routeIs('admin.form-field.*') ? 'active' : '' }}">
                Isian Formulir
            </a>

            <div class="sidebar-section-label" style="margin-top: 1rem;">Chatbot</div>
            <a href="{{ route('admin.faq.index') }}" class="sidebar-link {{ request()->routeIs('admin.faq.*') ? 'active' : '' }}">
                Basis Pengetahuan FAQ
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-footer-text">Panitia PPDB 2027</div>
            <div class="sidebar-footer-status">Sistem Aktif</div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="admin-main">
        {{-- Topbar --}}
        <header class="admin-topbar">
            <div style="display: flex; align-items: center; gap: .75rem;">
                <button class="mobile-toggle" onclick="document.getElementById('adminSidebar').classList.toggle('open')">
                    Menu
                </button>
                <input type="text"
                       class="topbar-search"
                       placeholder="Cari NISN, nama, atau no. registrasi...">
            </div>

            <div class="topbar-meta">
                <span class="topbar-chip">Gelombang 1</span>
                <span class="topbar-date">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                @if (session('admin_logged_in'))
                    <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-ghost" style="padding:.4rem .8rem; font-size:.78rem;">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('admin.login') }}" class="btn btn-ghost" style="padding:.4rem .8rem; font-size:.78rem;">Login Admin</a>
                @endif
            </div>
        </header>

        {{-- Content --}}
        <main class="admin-content">
            @yield('content')
        </main>
    </div>
</body>
</html>
