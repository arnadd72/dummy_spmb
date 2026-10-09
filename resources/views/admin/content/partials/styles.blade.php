{{--
    Gaya bersama untuk modul Manajemen Konten (Berita, Event, FAQ, Galeri, Pengaturan).
    Dipakai lewat @include('admin.content.partials.styles') di dalam @push('head').
    Semua token (--nu-*, --sf-*, --tx-*) berasal dari layouts/admin.blade.php.
--}}
<style>
    /* ─── Halaman ─── */
    .content-page { max-width: 1120px; margin: 0 auto; }

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .page-title {
        font-family: var(--admin-font-mono);
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--nu-700);
        letter-spacing: -.03em;
        margin: 0 0 .3rem;
    }

    .page-sub {
        font-size: .85rem;
        color: var(--tx-muted);
        margin: 0;
        max-width: 620px;
        line-height: 1.55;
    }

    /* ─── Alert ─── */
    .alert {
        display: flex;
        align-items: flex-start;
        gap: .6rem;
        border-radius: 12px;
        padding: .8rem 1rem;
        font-size: .85rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
        line-height: 1.5;
    }

    .alert svg { flex-shrink: 0; margin-top: .1rem; }

    .alert-success {
        background: var(--nu-50);
        border: 1px solid var(--nu-200);
        color: var(--nu-700);
    }

    .alert-error {
        background: var(--st-error-bg);
        border: 1px solid #FCA5A5;
        color: var(--st-error);
    }

    .alert-info {
        background: #E0F2FE;
        border: 1px solid #BAE6FD;
        color: #075985;
    }

    /* ─── Toolbar / Filter ─── */
    .toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        align-items: flex-end;
        margin-bottom: 1.25rem;
    }

    .search-wrap { position: relative; flex: 1 1 240px; min-width: 200px; }

    .search-wrap svg {
        position: absolute;
        left: .8rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--tx-muted);
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        padding: .6rem .9rem .6rem 2.4rem;
        border: 1px solid var(--sf-border);
        border-radius: 10px;
        background: var(--sf-card);
        font-family: var(--admin-font-sans);
        font-size: .85rem;
        color: var(--tx-primary);
        outline: none;
        transition: all var(--transition);
    }

    .search-input:focus {
        border-color: var(--nu-500);
        box-shadow: 0 0 0 3px rgba(5,122,60,.12);
    }

    .filter-select {
        padding: .6rem .9rem;
        border: 1px solid var(--sf-border);
        border-radius: 10px;
        background: var(--sf-card);
        font-family: var(--admin-font-sans);
        font-size: .85rem;
        color: var(--tx-primary);
        outline: none;
        cursor: pointer;
        transition: all var(--transition);
    }

    .filter-select:focus {
        border-color: var(--nu-500);
        box-shadow: 0 0 0 3px rgba(5,122,60,.12);
    }

    /* ─── Panel ─── */
    .panel {
        background: var(--sf-card);
        border: 1px solid var(--sf-border);
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .panel-head {
        padding: 1.15rem 1.5rem;
        border-bottom: 1px solid var(--sf-border);
        background: var(--sf-page);
    }

    .panel-title {
        font-family: var(--admin-font-mono);
        font-size: .95rem;
        font-weight: 700;
        color: var(--nu-700);
        margin: 0 0 .15rem;
        letter-spacing: -.02em;
    }

    .panel-sub { font-size: .76rem; color: var(--tx-muted); margin: 0; }

    .panel-body { padding: 1.5rem; }

    /* ─── Statistik ringkas ─── */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }

    .stat-tile {
        background: var(--sf-card);
        border: 1px solid var(--sf-border);
        border-radius: var(--card-radius);
        padding: 1.1rem 1.25rem;
        box-shadow: var(--card-shadow);
        transition: all var(--transition);
    }

    .stat-tile:hover { border-color: var(--sf-border-h); transform: translateY(-2px); }

    .stat-label {
        font-family: var(--admin-font-mono);
        font-size: .58rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: var(--tx-muted);
        margin-bottom: .45rem;
    }

    .stat-value {
        font-family: var(--admin-font-mono);
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
        letter-spacing: -.03em;
        color: var(--nu-700);
        font-variant-numeric: tabular-nums;
    }

    .stat-value.green { color: var(--nu-500); }
    .stat-value.gold  { color: var(--gold-500); }
    .stat-value.red   { color: var(--st-error); }

    /* ─── Daftar data ─── */
    .data-list { display: flex; flex-direction: column; gap: 12px; }

    .data-item {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        background: var(--sf-card);
        border: 1px solid var(--sf-border);
        border-radius: 14px;
        padding: 1rem 1.15rem;
        box-shadow: var(--card-shadow);
        transition: all var(--transition);
    }

    .data-item:hover {
        border-color: var(--sf-border-h);
        box-shadow: var(--card-shadow-hover);
        transform: translateY(-1px);
    }

    .data-item-body { flex: 1; min-width: 0; }

    .data-item-title {
        font-size: .95rem;
        font-weight: 600;
        color: var(--tx-primary);
        margin: 0 0 .3rem;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .data-item-text {
        font-size: .8rem;
        color: var(--tx-secondary);
        margin: 0;
        line-height: 1.55;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .data-item-meta {
        font-family: var(--admin-font-mono);
        font-size: .68rem;
        color: var(--tx-muted);
        margin-top: .45rem;
        display: flex;
        flex-wrap: wrap;
        gap: .35rem .9rem;
    }

    .data-item-actions {
        display: flex;
        gap: .4rem;
        flex-shrink: 0;
        align-items: flex-start;
    }

    .thumb {
        width: 96px;
        height: 72px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid var(--sf-border);
        background: var(--sf-page);
    }

    .thumb-empty {
        width: 96px;
        height: 72px;
        border-radius: 10px;
        flex-shrink: 0;
        border: 1px dashed var(--sf-border-h);
        background: var(--sf-page);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--tx-muted);
        font-size: .58rem;
        font-family: var(--admin-font-mono);
        text-align: center;
        padding: .25rem;
    }

    /* ─── Badge ─── */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .2rem .55rem;
        border-radius: 99px;
        font-family: var(--admin-font-mono);
        font-size: .62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
    }

    .badge-on  { background: var(--nu-100); color: var(--nu-700); }
    .badge-off { background: #EEF2F0; color: var(--tx-muted); }
    .badge-cat { background: var(--gold-100); color: #92400E; }

    .badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .badge-cat::before { display: none; }

    /* ─── Tombol ─── */
    .btn {
        font-family: var(--admin-font-sans);
        font-size: .82rem;
        font-weight: 600;
        padding: .6rem 1.15rem;
        border-radius: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        transition: all var(--transition);
        line-height: 1.2;
    }

    .btn-primary { background: var(--nu-500); color: #fff; }
    .btn-primary:hover { background: var(--nu-600); transform: translateY(-1px); box-shadow: 0 6px 18px -4px rgba(2,75,35,.35); }

    .btn-ghost { background: var(--sf-card); border-color: var(--sf-border); color: var(--tx-secondary); }
    .btn-ghost:hover { border-color: var(--nu-500); color: var(--nu-500); background: var(--nu-50); }

    .btn-danger { background: var(--sf-card); border-color: #FCA5A5; color: var(--st-error); }
    .btn-danger:hover { background: var(--st-error-bg); border-color: var(--st-error); }

    .btn-sm { padding: .4rem .7rem; font-size: .74rem; border-radius: 8px; }

    .btn-outline { background: transparent; border-color: var(--nu-500); color: var(--nu-500); }
    .btn-outline:hover { background: var(--nu-50); }
    .btn-outline:disabled { opacity: .5; cursor: not-allowed; background: transparent; }

    /* ─── Form ─── */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .field { display: flex; flex-direction: column; gap: .4rem; }
    .field-full { grid-column: 1 / -1; }

    .field-label {
        font-family: var(--admin-font-mono);
        font-size: .66rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--tx-secondary);
    }

    .field-label .req { color: var(--st-error); }

    .field-input,
    .field-textarea {
        width: 100%;
        padding: .65rem .85rem;
        border: 1px solid var(--sf-border);
        border-radius: 10px;
        background: var(--sf-card);
        font-family: var(--admin-font-sans);
        font-size: .88rem;
        color: var(--tx-primary);
        outline: none;
        transition: all var(--transition);
    }

    .field-textarea { resize: vertical; min-height: 110px; line-height: 1.6; }

    .field-input:focus,
    .field-textarea:focus {
        border-color: var(--nu-500);
        box-shadow: 0 0 0 3px rgba(5,122,60,.12);
    }

    .field-input.err,
    .field-textarea.err { border-color: var(--st-error); }

    .field-help { font-size: .72rem; color: var(--tx-muted); line-height: 1.5; }
    .field-error { font-size: .74rem; color: var(--st-error); font-weight: 600; }

    /* File input (drop-zone-ish) */
    .file-input {
        width: 100%;
        font-family: var(--admin-font-sans);
        font-size: .8rem;
        color: var(--tx-secondary);
        padding: .6rem;
        border: 1px dashed var(--sf-border-h);
        border-radius: 10px;
        background: var(--sf-page);
        cursor: pointer;
        transition: all var(--transition);
    }

    .file-input:hover { border-color: var(--nu-500); background: var(--nu-50); }

    .file-input::file-selector-button {
        font-family: var(--admin-font-sans);
        font-size: .76rem;
        font-weight: 600;
        padding: .35rem .75rem;
        margin-right: .75rem;
        border: none;
        border-radius: 7px;
        background: var(--nu-500);
        color: #fff;
        cursor: pointer;
        transition: background var(--transition);
    }

    .file-input::file-selector-button:hover { background: var(--nu-600); }

    /* Toggle aktif */
    .switch-row {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .7rem .9rem;
        border: 1px solid var(--sf-border);
        border-radius: 10px;
        background: var(--sf-page);
    }

    .switch-row input { width: 18px; height: 18px; accent-color: var(--nu-500); cursor: pointer; }
    .switch-row label { font-size: .82rem; font-weight: 600; color: var(--tx-secondary); cursor: pointer; }

    /* ─── Preview gambar ─── */
    .preview-box {
        display: flex;
        gap: 1rem;
        align-items: center;
        padding: .9rem;
        border: 1px solid var(--sf-border);
        border-radius: 12px;
        background: var(--sf-page);
    }

    .preview-img {
        width: 140px;
        height: 96px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid var(--sf-border);
        background: #fff;
    }

    /* ─── Actions bar ─── */
    .actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1.1rem 1.5rem;
        border-top: 1px solid var(--sf-border);
        background: var(--sf-page);
    }

    .actions-note {
        font-family: var(--admin-font-mono);
        font-size: .68rem;
        color: var(--tx-muted);
    }

    /* ─── Read-only detail ─── */
    .detail-grid {
        display: grid;
        grid-template-columns: 180px 1fr;
        gap: .75rem 1.5rem;
        font-size: .86rem;
    }

    .detail-key {
        font-family: var(--admin-font-mono);
        font-size: .66rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--tx-muted);
        padding-top: .15rem;
    }

    .detail-val { color: var(--tx-primary); line-height: 1.65; overflow-wrap: anywhere; }

    /* ─── Empty state ─── */
    .empty-box {
        text-align: center;
        padding: 3rem 1.5rem;
        border: 1px dashed var(--sf-border-h);
        border-radius: 14px;
        background: var(--sf-card);
    }

    .empty-box svg { color: var(--tx-muted); margin-bottom: .75rem; }
    .empty-box h3 { font-family: var(--admin-font-mono); font-size: .95rem; color: var(--nu-700); margin: 0 0 .35rem; }
    .empty-box p { font-size: .82rem; color: var(--tx-muted); margin: 0 0 1rem; }

    /* ─── Pagination ─── */
    .pagination-wrap { margin-top: 1.5rem; display: flex; justify-content: center; }
    .pagination-wrap nav > div:first-child { display: none; }
    .pagination-wrap a, .pagination-wrap span[aria-current] span, .pagination-wrap span[aria-disabled] span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 .5rem;
        margin: 0 .15rem;
        border-radius: 8px;
        border: 1px solid var(--sf-border);
        background: var(--sf-card);
        font-family: var(--admin-font-mono);
        font-size: .76rem;
        font-weight: 600;
        color: var(--tx-secondary);
        text-decoration: none;
        transition: all var(--transition);
    }

    .pagination-wrap a:hover { border-color: var(--nu-500); color: var(--nu-500); background: var(--nu-50); }
    .pagination-wrap span[aria-current] span { background: var(--nu-500); border-color: var(--nu-500); color: #fff; }
    .pagination-wrap svg { width: 14px; height: 14px; }

    /* ─── Responsive ─── */
    @media (max-width: 720px) {
        .form-grid { grid-template-columns: 1fr; }
        .detail-grid { grid-template-columns: 1fr; gap: .15rem 0; }
        .detail-key { padding-top: .6rem; }
        .data-item { flex-direction: column; }
        .data-item-actions { width: 100%; }
        .thumb, .thumb-empty { width: 100%; height: 140px; }
    }
</style>
