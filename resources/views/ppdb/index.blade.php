@extends('layouts.app')

@section('title')PPDB {{ $tahunAjaran ?? '2027/2028' }}@endsection
@section('meta_description')Pendaftaran Peserta Didik Baru SMK Ma'arif NU 1 Wangon Tahun Ajaran {{ $tahunAjaran ?? '2027/2028' }}. Daftar online, cepat, dan mudah.@endsection

@section('content')

<style>
    /* ── Framer Motion Style Animations ── */
    :root {
        --spring-easing: linear(0, 0.006, 0.025 2.8%, 0.101 6.1%, 0.539 18.9%, 0.728 25.3%, 0.849 31.5%, 0.937 38.1%, 0.968 41.8%, 0.991 45.7%, 1.006 50.1%, 1.015 55%, 1.017 63.9%, 1.001);
        --spring-duration: 0.8s;
    }

    .fm-element {
        opacity: 0;
        will-change: transform, opacity;
    }

    .fm-fade-up { transform: translateY(40px); }
    .fm-scale-in { transform: scale(0.9); }
    .fm-fade-right { transform: translateX(-40px); }
    .fm-fade-left { transform: translateX(40px); }

    .fm-element.fm-visible {
        opacity: 1;
        transform: none;
        transition: opacity var(--spring-duration) ease-out, transform var(--spring-duration) var(--spring-easing);
    }

    /* ── Advanced Hero ── */
    .hero-advanced {
        position: relative;
        min-height: 90vh;
        display: flex;
        align-items: center;
        background: radial-gradient(circle at top right, #076d37 0%, #023619 100%);
        overflow: hidden;
        padding: 6rem 0;
    }
    
    .hero-glow {
        position: absolute;
        width: 800px;
        height: 800px;
        background: radial-gradient(circle, rgba(22, 163, 74, 0.15) 0%, transparent 60%);
        top: -400px;
        right: -200px;
        pointer-events: none;
        z-index: 0;
    }

    .hero-bento {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        z-index: 10;
        position: relative;
    }
    @media (min-width: 992px) {
        .hero-bento {
            grid-template-columns: 1.1fr 0.9fr;
            align-items: center;
        }
    }

    .hero-text-content h1 {
        font-size: clamp(2.5rem, 5vw, 4rem);
        line-height: 1.1;
        background: linear-gradient(to right, #ffffff, #dcfce7);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 1.5rem;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-xl);
        overflow: hidden;
    }

    .hero-collage {
        position: relative;
        height: 500px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 1rem;
    }
    .hero-collage-item {
        border-radius: var(--radius-lg);
        overflow: hidden;
        position: relative;
    }
    .hero-collage-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.7s var(--spring-easing);
    }
    .hero-collage-item:hover img {
        transform: scale(1.05);
    }
    .hci-1 { grid-column: 1 / 3; grid-row: 1 / 2; background: white; }
    .hci-1 img { object-fit: contain; object-position: center; width: 100%; height: 100%; }
    .hci-2 { grid-column: 1 / 2; grid-row: 2 / 3; }
    .hci-3 { grid-column: 2 / 3; grid-row: 2 / 3; }

    .floating-badge-dynamic {
        position: absolute;
        background: rgba(255, 255, 255, 0.9);
        color: var(--color-primary-dark);
        padding: 0.75rem 1.25rem;
        border-radius: var(--radius-full);
        font-weight: 700;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 5;
        animation: float 6s ease-in-out infinite;
    }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }

    /* ── Bento Grid Section ── */
    .bento-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        grid-auto-rows: minmax(200px, auto);
    }
    .bento-item {
        background: white;
        border-radius: var(--radius-xl);
        padding: 2rem;
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-card);
        position: relative;
        overflow: hidden;
        transition: transform 0.4s var(--spring-easing), box-shadow 0.4s ease;
    }
    .bento-item:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-elevated);
    }
    .bento-col-2 { grid-column: span 2; }
    .bento-row-2 { grid-row: span 2; }
    @media (max-width: 768px) {
        .bento-col-2, .bento-row-2 { grid-column: span 4; grid-row: span 1; }
    }

    /* ── Modern Jurusan Cards ── */
    .jurusan-bento {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
    }
    @media (max-width: 1024px) {
        .jurusan-bento {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 640px) {
        .jurusan-bento {
            grid-template-columns: 1fr;
        }
    }
    .jurusan-card-modern {
        background: white;
        border-radius: var(--radius-xl);
        padding: 2rem;
        border: 1px solid var(--color-border);
        position: relative;
        z-index: 1;
        transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .jurusan-card-modern::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: var(--radius-xl);
        box-shadow: 0 15px 35px -5px rgba(5, 122, 60, 0.15);
        opacity: 0;
        transition: opacity 0.5s ease;
        z-index: -1;
    }
    .jurusan-card-modern:hover {
        transform: translateY(-8px);
        border-color: var(--j-color);
    }
    .jurusan-card-modern:hover::after {
        opacity: 1;
    }
    .j-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        background: var(--j-bg);
        color: var(--j-color);
        transition: transform 0.5s var(--spring-easing);
    }
    .jurusan-card-modern:hover .j-icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }

    /* ── Steps Timeline ── */
    .step-track {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        position: relative;
    }
    .step-card {
        display: flex;
        align-items: flex-start;
        gap: 1.5rem;
        background: white;
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        border: 1px solid var(--color-border);
        transition: transform 0.3s ease;
    }
    .step-card:hover {
        transform: translateX(10px);
        border-color: var(--color-primary-light);
    }
    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--color-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.125rem;
        flex-shrink: 0;
    }

    /* FAQ Custom */
    .faq-item {
        background: white;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        margin-bottom: 1rem;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .faq-item:hover {
        border-color: var(--color-primary-light);
    }
    .faq-summary {
        padding: 1.5rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--color-text);
        list-style: none;
    }
    .faq-summary::-webkit-details-marker { display: none; }
    .faq-content {
        padding: 0 1.5rem 1.5rem;
        color: var(--color-text-secondary);
        line-height: 1.7;
    }

    /* ── Berita (dikelola admin) ── */
    .news-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.75rem;
    }
    @media (max-width: 900px) { .news-grid { grid-template-columns: 1fr; } }

    .news-card {
        display: flex;
        flex-direction: column;
        background: white;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: var(--shadow-card);
        height: 100%;
        transition: transform 0.45s var(--spring-easing), box-shadow 0.45s ease, border-color 0.3s ease;
    }
    .news-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-elevated);
        border-color: var(--color-primary-light);
    }
    .news-media {
        position: relative;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        background: var(--color-muted);
    }
    .news-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.7s var(--spring-easing);
    }
    .news-card:hover .news-media img { transform: scale(1.06); }
    .media-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-text-muted);
        background: linear-gradient(135deg, var(--color-muted), #ffffff);
    }
    .news-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        flex: 1;
    }
    .news-date {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--color-primary);
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .news-title {
        font-size: 1.15rem;
        line-height: 1.35;
        color: var(--color-text);
        margin: 0;
    }
    .news-excerpt {
        font-size: 0.9rem;
        color: var(--color-text-secondary);
        line-height: 1.65;
        margin: 0;
    }

    /* ── Agenda & Event ── */
    .event-list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        max-width: 900px;
        margin: 0 auto;
    }
    .event-card {
        display: flex;
        align-items: stretch;
        gap: 1.25rem;
        background: white;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-xl);
        padding: 1.25rem;
        box-shadow: var(--shadow-card);
        transition: border-color 0.3s ease, transform 0.45s var(--spring-easing);
    }
    .event-card:hover {
        border-color: var(--color-primary-light);
        transform: translateX(6px);
    }
    .event-date {
        flex-shrink: 0;
        width: 78px;
        height: 78px;
        border-radius: var(--radius-lg);
        background: linear-gradient(150deg, var(--color-primary), var(--color-primary-dark));
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .event-date .d { font-size: 1.65rem; font-weight: 800; line-height: 1; }
    .event-date .m { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.9; margin-top: 0.2rem; }
    .event-date.tba {
        background: var(--color-muted);
        color: var(--color-text-secondary);
        font-size: 0.72rem;
        font-weight: 700;
        text-align: center;
        padding: 0.5rem;
        letter-spacing: 0.06em;
    }
    .event-body { flex: 1; min-width: 0; }
    .event-title { font-size: 1.1rem; margin: 0 0 0.4rem; color: var(--color-text); }
    .event-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 1.1rem;
        font-size: 0.82rem;
        color: var(--color-text-muted);
        margin-bottom: 0.6rem;
    }
    .event-meta span { display: inline-flex; align-items: center; gap: 0.35rem; }
    .event-desc { font-size: 0.9rem; color: var(--color-text-secondary); line-height: 1.65; margin: 0; }
    .event-poster {
        width: 120px;
        flex-shrink: 0;
        border-radius: var(--radius-lg);
        overflow: hidden;
        background: var(--color-muted);
        display: none;
    }
    .event-poster img { width: 100%; height: 100%; object-fit: cover; }
    @media (min-width: 640px) { .event-poster { display: block; } }

    /* ── Galeri Kegiatan ── */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }
    @media (max-width: 1024px) { .gallery-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 640px) { .gallery-grid { grid-template-columns: repeat(2, 1fr); } }
    .gallery-item {
        position: relative;
        margin: 0;
        border-radius: var(--radius-lg);
        overflow: hidden;
        aspect-ratio: 1 / 1;
        background: var(--color-muted);
        box-shadow: var(--shadow-card);
    }
    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.7s var(--spring-easing);
    }
    .gallery-item:hover img { transform: scale(1.08); }
    .gallery-cap {
        position: absolute;
        inset: auto 0 0 0;
        padding: 1.75rem 0.9rem 0.8rem;
        background: linear-gradient(to top, rgba(2, 54, 25, 0.88), transparent);
        color: white;
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.35;
    }
</style>

{{-- ── HERO (Advanced, Interactive) ── --}}
<section class="hero-advanced" aria-labelledby="hero-heading">
    <div class="hero-glow"></div>
    <div class="container hero-bento">
        <div class="hero-text-content fm-element fm-fade-up">
            <div class="hero-badge fm-element fm-fade-right" style="transition-delay: 0.1s" aria-hidden="true">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                Tahun Ajaran {{ $tahunAjaran ?? '2027/2028' }} • Ma'arif NU Wangon
            </div>
            
            <h1 id="hero-heading">Pendaftaran Peserta<br>Didik Baru Online</h1>
            
            <p class="hero-lead fm-element fm-fade-up" style="transition-delay: 0.2s">
                Penerimaan siswa baru SMK Ma'arif NU 1 Wangon secara digital — terverifikasi dengan pemindaian dokumen cerdas (Akta & KK) serta sistem rekomendasi jurusan terpadu yang memadukan teknologi mutakhir dengan nilai-nilai Ahlussunnah wal Jama'ah.
            </p>
            
            <div class="hero-actions fm-element fm-fade-up" style="transition-delay: 0.3s">
                <a href="{{ route('ppdb.step1') }}" class="btn btn-accent btn-lg" style="box-shadow: 0 10px 25px rgba(217,119,6,0.4);">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                    Mulai Pendaftaran
                </a>
                <a href="{{ route('ppdb.cekStatus') }}" class="btn btn-outline" style="color:white; border-color:rgba(255,255,255,0.4);">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    Cek Status
                </a>
            </div>

            <div class="hero-stats fm-element fm-fade-up" style="transition-delay: 0.4s" role="list">
                <div role="listitem">
                    <span class="hero-stat-number">A</span>
                    <span class="hero-stat-label">Akreditasi BAN-SM</span>
                </div>
                <div role="listitem">
                    <span class="hero-stat-number">100%</span>
                    <span class="hero-stat-label">Terverifikasi Digital</span>
                </div>
                <div role="listitem">
                    <span class="hero-stat-number">4</span>
                    <span class="hero-stat-label">Jurusan Unggulan</span>
                </div>
            </div>
        </div>

        <div class="fm-element fm-scale-in" style="transition-delay: 0.3s">
            <div class="hero-collage glass-card p-2">
                <div class="floating-badge-dynamic" style="top: -20px; left: 20px;">
                    <span style="display:flex; align-items:center; justify-content:center; width:28px; height:28px; background:var(--color-primary); color:white; border-radius:50%;">✓</span>
                    Terakreditasi A
                </div>
                
                <div class="floating-badge-dynamic" style="bottom: 30px; right: -20px; animation-delay: -3s;">
                    <svg width="24" height="24" fill="var(--color-accent)" viewBox="0 0 24 24"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
                    Fasilitas Industri
                </div>

                <div class="hero-collage-item hci-1">
                    <img src="{{ $heroImage ?? asset('images/campus-front.jpg') }}" alt="SMK Ma'arif NU 1 Wangon" loading="lazy">
                </div>
                <div class="hero-collage-item hci-2">
                    <img src="{{ asset('images/lab-praktik.jpg') }}" alt="Lab Praktik" loading="lazy">
                </div>
                <div class="hero-collage-item hci-3">
                    <img src="{{ asset('images/kegiatan-siswa.jpg') }}" alt="Kegiatan Siswa" loading="lazy">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── PROFIL & KEUNGGULAN (Bento Grid) ── --}}
<section class="section" aria-labelledby="profil-heading">
    <div class="container">
        <div class="section-heading fm-element fm-fade-up">
            <div class="section-eyebrow">Pendidikan Berkarakter Aswaja</div>
            <h2 id="profil-heading" class="section-title">Membina Generasi Unggul, Terampil,<br>dan Berakhlak Mulia</h2>
        </div>

        <div class="bento-grid">
            <div class="bento-item bento-col-2 bento-row-2 fm-element fm-scale-in" style="background: var(--color-primary-dark); color: white; display: flex; flex-direction: column; justify-content: center;">
                <svg width="48" height="48" fill="var(--color-accent)" viewBox="0 0 24 24" style="margin-bottom: 1.5rem;">
                    <path d="M12 3L1 9L5 11.18V17.18L12 21L19 17.18V11.18L21 10.09V17H23V9L12 3ZM18.82 9L12 12.72L5.18 9L12 5.28L18.82 9ZM17 15.9L12 18.64L7 15.9V12.27L12 15L17 12.27V15.9Z"/>
                </svg>
                <h3 style="color: white; font-size: 1.75rem; margin-bottom: 1rem;">Filosofi Pendidikan Kami</h3>
                <p style="color: rgba(255,255,255,0.8); line-height: 1.7; font-size: 1.1rem; margin-bottom: 2rem;">
                    SMK Ma'arif NU 1 Wangon di bawah naungan LP Ma'arif NU Kabupaten Banyumas berkomitmen menghadirkan kurikulum berbasis industri yang terintegrasi dengan nilai-nilai luhur Ahlussunnah wal Jama'ah An-Nahdliyyah.
                </p>
                <div style="background: rgba(255,255,255,0.1); padding: 1.5rem; border-radius: var(--radius-lg); border-left: 4px solid var(--color-accent);">
                    <p style="font-style: italic; font-size: 0.95rem; margin-bottom: 0.75rem;">
                        "Mempersiapkan lulusan yang siap kerja di dunia usaha dan industri, berjiwa wirausaha mandiri, serta berpegang teguh pada akhlakul karimah."
                    </p>
                    <strong style="color: var(--color-accent-gold);">— Drs. Mukhtarom, M.Si</strong>
                </div>
            </div>

            <div class="bento-item bento-col-2 fm-element fm-fade-left">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 1rem;">
                    <div>
                        <h3 style="font-size: 1.25rem; margin-bottom: 0.25rem;">Lab & Bengkel Modern</h3>
                        <p style="color: var(--color-text-secondary); font-size: 0.9rem;">Fasilitas standar industri untuk praktik kejuruan.</p>
                    </div>
                    <div style="width:40px; height:40px; border-radius:10px; background:#dbeafe; color:#1d4ed8; display:flex; align-items:center; justify-content:center;">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    </div>
                </div>
                <div style="height: 140px; border-radius: var(--radius-md); overflow: hidden; margin-top: 1rem;">
                    <img src="{{ asset('images/lab-praktik.jpg') }}" alt="Laboratorium" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            </div>

            <div class="bento-item fm-element fm-fade-up">
                <div style="width:40px; height:40px; border-radius:10px; background:#fef3c7; color:#b45309; display:flex; align-items:center; justify-content:center; margin-bottom: 1rem;">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Beasiswa Prestasi & Tahfidz</h3>
                <p style="color: var(--color-text-secondary); font-size: 0.85rem;">Berbagai dukungan finansial bagi siswa berprestasi akademik maupun non-akademik.</p>
            </div>

            <div class="bento-item fm-element fm-fade-up" style="transition-delay: 0.1s">
                <div style="width:40px; height:40px; border-radius:10px; background:#dcfce7; color:#15803d; display:flex; align-items:center; justify-content:center; margin-bottom: 1rem;">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
                <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Kerjasama Industri</h3>
                <p style="color: var(--color-text-secondary); font-size: 0.85rem;">Magang dan rekrutmen langsung dengan mitra perusahaan nasional ternama.</p>
            </div>
        </div>
    </div>
</section>

{{-- ── JURUSAN (Modern Cards) ── --}}
<section class="section" id="jurusan" style="background: var(--color-background);" aria-labelledby="jurusan-heading">
    <div class="container">
        <div class="section-heading fm-element fm-fade-up">
            <div class="section-eyebrow">Program Keahlian</div>
            <h2 class="section-title" id="jurusan-heading">Pilih Masa Depanmu</h2>
            <p class="section-desc">Sistem PPDB kami dilengkapi AI rekomendasi untuk membantu kamu menemukan jurusan terbaik sesuai minat, bakat, dan nilai rapormu.</p>
        </div>

        @php
        $jurusanConfig = [
            'AKL'  => ['color' => '#1E40AF', 'bg' => '#dbeafe', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />'],
            'MPLB' => ['color' => '#0369A1', 'bg' => '#e0f2fe', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />'],
            'FKK'  => ['color' => '#166534', 'bg' => '#dcfce7', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />'],
            'TKR'  => ['color' => '#9A3412', 'bg' => '#ffedd5', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014-5.395c-.312.053-.627.1-.942.146m1.956 5.249c-.067.447-.145.89-.234 1.328" />'],
        ];
        @endphp

        <div class="jurusan-bento">
            @forelse ($jurusan as $idx => $j)
            @php $cfg = $jurusanConfig[$j->kode] ?? ['color' => '#057A3C', 'bg' => '#dcfce7', 'icon' => '']; @endphp
            <a href="{{ route('ppdb.jurusan.show', $j->kode) }}" class="jurusan-card-modern fm-element fm-fade-up" style="--j-color: {{ $cfg['color'] }}; --j-bg: {{ $cfg['bg'] }}; transition-delay: {{ $idx * 0.1 }}s; text-decoration:none; color:inherit; display:block; cursor:pointer;">
                <div class="j-icon-wrapper">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $cfg['icon'] !!}
                    </svg>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <span style="background:var(--j-bg); color:var(--j-color); padding:0.25rem 0.75rem; border-radius:99px; font-weight:800; font-size:0.75rem; letter-spacing:0.05em;">{{ $j->kode }}</span>
                    <span style="font-size:.75rem; font-weight:700; color:var(--j-color);">Lihat Detail →</span>
                </div>
                <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--color-text);">{{ $j->nama }}</h3>
                <p style="color: var(--color-text-secondary); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">
                    {{ Str::limit($j->deskripsi, 120) }}
                </p>
                @if ($j->prospek_karir)
                <div style="border-top: 1px dashed var(--color-border); padding-top: 1.25rem;">
                    <div style="font-size:0.75rem; font-weight:700; color:var(--color-text-muted); text-transform:uppercase; margin-bottom:0.5rem;">Prospek Karir Utama</div>
                    <div style="font-size:0.875rem; color:var(--color-text); font-weight:500;">
                        {{ Str::limit($j->prospek_karir, 80) }}
                    </div>
                </div>
                @endif
            </a>
            @empty
            <p style="grid-column:1/-1; text-align:center; color:var(--color-text-muted); padding:3rem;">Data jurusan belum tersedia.</p>
            @endforelse
        </div>

        <div style="text-align:center; margin-top:3.5rem;" class="fm-element fm-scale-in">
            <a href="{{ route('ppdb.step1') }}" class="btn btn-primary btn-lg" style="box-shadow: 0 10px 25px rgba(5,122,60,0.3);">
                Mulai Pendaftaran & Pilih Jurusan
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="margin-left:0.5rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- ── ALUR PENDAFTARAN & JADWAL ── --}}
<section class="section" style="background:white;" aria-labelledby="cara-heading">
    <div class="container">
        <div class="section-heading fm-element fm-fade-up">
            <div class="section-eyebrow">Alur & Informasi</div>
            <h2 class="section-title" id="cara-heading">Proses Pendaftaran Cepat & Mudah</h2>
            <p class="section-desc">Dengan teknologi OCR (Optical Character Recognition), pengisian formulir menjadi jauh lebih cepat dengan scan KK dan Akta.</p>
        </div>

        <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap: 4rem; align-items: start;">
            
            {{-- Alur Pendaftaran --}}
            <div class="step-track">
                @php
                $steps = [
                    ['Persetujuan Ketentuan', 'Membaca dan menyetujui komitmen sekolah', 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z'],
                    ['Upload Dokumen Inti', 'Sistem OCR akan otomatis membaca KK dan Akta', 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5'],
                    ['Pilih Jurusan', 'Mendapat rekomendasi AI berdasarkan nilai Rapor (Opsional)', 'M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z'],
                    ['Submit Formulir', 'Verifikasi data yang otomatis terisi, lalu kirim', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ];
                @endphp
                
                @foreach ($steps as $i => $step)
                <div class="step-card fm-element fm-fade-right" style="transition-delay: {{ $i * 0.1 }}s">
                    <div class="step-number">{{ $i + 1 }}</div>
                    <div>
                        <h4 style="font-size: 1.1rem; margin-bottom: 0.25rem; color: var(--color-primary-dark);">{{ $step[0] }}</h4>
                        <p style="color: var(--color-text-secondary); font-size: 0.95rem;">{{ $step[1] }}</p>
                    </div>
                    <div style="margin-left:auto; color: var(--color-border);">
                        <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step[2] }}" />
                        </svg>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Jadwal & Info --}}
            <div class="fm-element fm-fade-left">
                <div style="background: var(--color-background); border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: 2rem;">
                    <h3 style="font-size: 1.4rem; margin-bottom: 1.5rem; display:flex; align-items:center; gap:0.5rem;">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--color-primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" /></svg>
                        Jadwal Pendaftaran
                    </h3>
                    
                    <div class="timeline" role="list">
                        <div class="timeline-item active" role="listitem">
                            <div class="timeline-date">1 Jan – 31 Mar 2027</div>
                            <div style="font-weight:700; color:var(--color-primary-dark); font-size:1.05rem;">Gelombang I</div>
                            <div style="font-size:0.9rem; color:var(--color-text-secondary);">Potongan biaya & kuota terbatas</div>
                        </div>
                        <div class="timeline-item" role="listitem">
                            <div class="timeline-date">1 Apr – 30 Jun 2027</div>
                            <div style="font-weight:700; color:var(--color-text); font-size:1.05rem;">Gelombang II</div>
                            <div style="font-size:0.9rem; color:var(--color-text-secondary);">Pendaftaran reguler</div>
                        </div>
                        <div class="timeline-item" role="listitem">
                            <div class="timeline-date">Juli 2027</div>
                            <div style="font-weight:700; color:var(--color-text); font-size:1.05rem;">Pengumuman & Daftar Ulang</div>
                        </div>
                    </div>

                    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed var(--color-border);">
                        <a href="{{ route('ppdb.cekStatus') }}" class="btn btn-outline" style="width: 100%;">Cek Status Pendaftaran Saya</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── BERITA TERBARU (dikelola admin) ── --}}
@if ($berita->count() > 0)
<section class="section" style="background: var(--color-background);" aria-labelledby="berita-heading">
    <div class="container">
        <div class="section-heading fm-element fm-fade-up">
            <div class="section-eyebrow">Informasi Terkini</div>
            <h2 class="section-title" id="berita-heading">Berita Terbaru</h2>
            <p class="section-desc">Kabar dan pengumuman terbaru seputar penerimaan peserta didik baru.</p>
        </div>

        <div class="news-grid">
            @foreach ($berita as $idx => $post)
            <article class="news-card fm-element fm-fade-up" style="transition-delay: {{ $idx * 0.1 }}s">
                <div class="news-media">
                    @if ($post->has_gambar)
                        <img src="{{ $post->gambar_url }}" alt="{{ $post->judul }}" loading="lazy">
                    @else
                        <div class="media-fallback" aria-hidden="true">
                            <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z" />
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="news-body">
                    <div class="news-date">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        {{ $post->created_at?->translatedFormat('d F Y') }}
                    </div>
                    <h3 class="news-title">{{ $post->judul }}</h3>
                    <p class="news-excerpt">{{ Str::limit(strip_tags($post->isi), 130) }}</p>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── AGENDA & EVENT (dikelola admin) ── --}}
@if ($events->count() > 0)
<section class="section" style="background: white;" aria-labelledby="agenda-heading">
    <div class="container">
        <div class="section-heading fm-element fm-fade-up">
            <div class="section-eyebrow">Agenda</div>
            <h2 class="section-title" id="agenda-heading">Kegiatan &amp; Agenda Mendatang</h2>
            <p class="section-desc">Jadwal kegiatan penting yang perlu diketahui calon siswa dan orang tua.</p>
        </div>

        <div class="event-list">
            @foreach ($events as $idx => $ev)
            <article class="event-card fm-element fm-fade-up" style="transition-delay: {{ $idx * 0.1 }}s">
                @if ($ev->tanggal)
                    <div class="event-date" aria-hidden="true">
                        <span class="d">{{ $ev->tanggal->format('d') }}</span>
                        <span class="m">{{ $ev->tanggal->translatedFormat('M Y') }}</span>
                    </div>
                @else
                    <div class="event-date tba" aria-hidden="true">TBA</div>
                @endif

                <div class="event-body">
                    <h3 class="event-title">{{ $ev->judul }}</h3>
                    <div class="event-meta">
                        @if ($ev->tanggal)
                        <span>
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            {{ $ev->tanggal->translatedFormat('l, d F Y') }}
                        </span>
                        @endif
                        @if ($ev->lokasi)
                        <span>
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            {{ $ev->lokasi }}
                        </span>
                        @endif
                    </div>
                    @if ($ev->deskripsi)
                        <p class="event-desc">{{ Str::limit(strip_tags($ev->deskripsi), 180) }}</p>
                    @endif
                </div>

                @if ($ev->has_gambar)
                <div class="event-poster">
                    <img src="{{ $ev->gambar_url }}" alt="Poster {{ $ev->judul }}" loading="lazy">
                </div>
                @endif
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── GALERI KEGIATAN (dikelola admin) ── --}}
@php $galeriTampil = $galeris->filter(fn ($f) => $f->has_image)->values(); @endphp
@if ($galeriTampil->isNotEmpty())
<section class="section" style="background: var(--color-background);" aria-labelledby="galeri-heading">
    <div class="container">
        <div class="section-heading fm-element fm-fade-up">
            <div class="section-eyebrow">Galeri</div>
            <h2 class="section-title" id="galeri-heading">Galeri Kegiatan</h2>
            <p class="section-desc">Dokumentasi suasana belajar, praktik kejuruan, dan kegiatan siswa.</p>
        </div>

        <div class="gallery-grid">
            @foreach ($galeriTampil as $idx => $foto)
                <figure class="gallery-item fm-element fm-scale-in" style="transition-delay: {{ ($idx % 4) * 0.08 }}s">
                    <img src="{{ $foto->url }}" alt="{{ $foto->judul }}" loading="lazy">
                    @if ($foto->judul)
                        <figcaption class="gallery-cap">{{ $foto->judul }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── KATA ALUMNI / TESTIMONI (dikelola admin) ── --}}
@if (($testimonis ?? collect())->count() > 0)
<section class="section" style="background: white;" aria-labelledby="testimoni-heading">
    <div class="container">
        <div class="section-heading fm-element fm-fade-up">
            <div class="section-eyebrow">Kata Alumni</div>
            <h2 class="section-title" id="testimoni-heading">Cerita Sukses Alumni</h2>
            <p class="section-desc">Dengarkan langsung pengalaman para lulusan yang kini berkarier di berbagai bidang.</p>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px,1fr)); gap:1.25rem;">
            @foreach ($testimonis as $idx => $t)
            <figure class="fm-element fm-fade-up" style="transition-delay: {{ ($idx % 3) * 0.1 }}s; background:var(--color-background); border:1px solid var(--color-border); border-radius:18px; padding:1.5rem; margin:0; display:flex; flex-direction:column; gap:.9rem;">
                <div style="color:#D97706; font-size:1rem; letter-spacing:.15em;" aria-hidden="true">★★★★★</div>
                <blockquote style="margin:0; color:var(--color-text); line-height:1.7; font-size:.95rem; flex:1;">“{{ Str::limit($t->testimoni, 220) }}”</blockquote>
                <figcaption style="display:flex; align-items:center; gap:.8rem; border-top:1px dashed var(--color-border); padding-top:1rem;">
                    @if ($t->foto)
                        <img src="{{ $t->url }}" alt="{{ $t->nama }}" loading="lazy" style="width:48px; height:48px; border-radius:50%; object-fit:cover; border:2px solid #fff; box-shadow:0 2px 8px rgba(0,0,0,.15);">
                    @else
                        <div style="width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg,#0A3D1F,#057A3C); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800;" aria-hidden="true">{{ mb_strtoupper(mb_substr(trim($t->nama), 0, 1)) }}</div>
                    @endif
                    <div>
                        <div style="font-weight:800; color:var(--color-text); font-size:.92rem;">{{ $t->nama }}</div>
                        <div style="font-size:.78rem; color:var(--color-text-muted);">{{ $t->jurusan_kode ? $t->jurusan_kode . ' · ' : '' }}{{ $t->angkatan ? 'Angkatan ' . $t->angkatan : '' }}{{ $t->pekerjaan ? ($t->angkatan || $t->jurusan_kode ? ' · ' : '') . $t->pekerjaan : '' }}</div>
                    </div>
                </figcaption>
            </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── FAQ ── --}}
@if ($faqs->count() > 0)
<section class="section" style="background: white;" aria-labelledby="faq-heading">
    <div class="container">
        <div class="section-heading fm-element fm-fade-up">
            <div class="section-eyebrow">Bantuan</div>
            <h2 class="section-title" id="faq-heading">Pertanyaan yang Sering Diajukan</h2>
        </div>
        
        <div style="max-width:800px; margin:0 auto; display:flex; flex-direction:column; gap:0.75rem;">
            @foreach ($faqs as $idx => $faq)
            <details class="faq-item fm-element fm-fade-up" style="transition-delay: {{ $idx * 0.05 }}s">
                <summary class="faq-summary">
                    {{ $faq->pertanyaan }}
                    <span class="faq-icon" style="transition: transform 0.3s ease;">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </span>
                </summary>
                <div class="faq-content">
                    {{ $faq->jawaban }}
                </div>
            </details>
            @endforeach
        </div>
        
        <div style="text-align:center; margin-top:2.5rem;" class="fm-element fm-fade-up">
            <p style="color:var(--color-text-secondary); font-size:0.95rem; display:inline-flex; align-items:center; gap:0.5rem; background:white; padding:0.5rem 1rem; border-radius:99px; border:1px solid var(--color-border); box-shadow:var(--shadow-card);">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--color-primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg>
                Masih ada pertanyaan? Tanyakan langsung ke asisten chatbot kami.
            </p>
        </div>
    </div>
</section>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // FAQ icon rotation
    const details = document.querySelectorAll('details.faq-item');
    details.forEach(detail => {
        detail.addEventListener('toggle', () => {
            const icon = detail.querySelector('.faq-icon');
            if (icon) {
                icon.style.transform = detail.open ? 'rotate(180deg)' : 'rotate(0)';
            }
        });
    });

    // Framer Motion Style Intersection Observer (Scroll Reveal)
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -50px 0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fm-visible');
                // Optional: stop observing once revealed
                // observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fm-element').forEach(el => {
        observer.observe(el);
    });
});
</script>
@endpush
