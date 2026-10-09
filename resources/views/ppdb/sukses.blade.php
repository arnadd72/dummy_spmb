@extends('layouts.app')
@section('title', 'Pendaftaran Berhasil! — PPDB SMK Ma\'arif NU 1 Wangon')

@section('content')
<div style="min-height:calc(100vh - 4rem); background:var(--color-background); display:flex; align-items:center; padding:3rem 0;">
    <div class="container">
        <div style="max-width:640px; margin:0 auto; text-align:center;">

            {{-- Success Icon --}}
            <div style="width:6rem; height:6rem; background:linear-gradient(135deg, #16A34A, #22c55e); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 2rem; box-shadow:0 8px 32px rgb(22 163 74 / 0.3);" role="img" aria-label="Ikon sukses">
                <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </div>

            <div class="rekomendasi-badge badge-green" style="margin-bottom:1rem; font-size:0.875rem; padding:0.375rem 1rem;">
                Pendaftaran Berhasil Diterima
            </div>

            <h1 style="font-size:clamp(1.5rem, 4vw, 2.25rem); color:var(--color-primary-dark); margin-bottom:0.5rem;">
                Selamat, {{ $pendaftar->nama_lengkap }}!
            </h1>
            <p style="color:var(--color-text-secondary); font-size:1.0625rem; margin-bottom:2rem;">
                Pendaftaran Anda telah berhasil dikirim dan sedang menunggu verifikasi panitia PPDB.
            </p>

            {{-- Nomor Pendaftaran --}}
            <div style="background:white; border:2px solid var(--color-primary); border-radius:var(--radius-xl); padding:2rem; margin-bottom:2rem; position:relative; overflow:hidden;">
                <div style="position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg, var(--color-primary), var(--color-accent));"></div>
                <div style="font-size:0.8125rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--color-text-muted); margin-bottom:0.5rem;">Nomor Pendaftaran Anda</div>
                <div style="font-size:2rem; font-weight:800; color:var(--color-primary); letter-spacing:0.05em; font-variant-numeric:tabular-nums; margin-bottom:0.5rem;"
                     aria-label="Nomor pendaftaran {{ $pendaftar->nomor_pendaftaran }}">
                    {{ $pendaftar->nomor_pendaftaran }}
                </div>
                <div style="font-size:0.875rem; color:var(--color-text-muted);">Simpan nomor ini untuk mengecek status pendaftaran Anda</div>

                <button onclick="copyNomor()" id="copy-btn"
                        class="btn btn-ghost btn-sm" style="margin-top:1rem;"
                        aria-label="Salin nomor pendaftaran">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184"/></svg>
                    Salin Nomor
                </button>
            </div>

            {{-- Summary Info --}}
            <div style="background:white; border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1.25rem; margin-bottom:1.5rem; text-align:left;">
                <h2 style="font-size:0.9375rem; font-weight:700; color:var(--color-primary-dark); margin-bottom:1rem;">Ringkasan Pendaftaran</h2>
                <dl style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                    <div>
                        <dt style="font-size:0.75rem; color:var(--color-text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Jurusan Pilihan 1</dt>
                        <dd style="font-weight:700; color:var(--color-text); margin:0; font-size:0.9375rem;">{{ $pendaftar->jurusan1?->kode }} — {{ $pendaftar->jurusan1?->nama ?? '—' }}</dd>
                    </div>
                    @if ($pendaftar->jurusan2)
                    <div>
                        <dt style="font-size:0.75rem; color:var(--color-text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Jurusan Pilihan 2</dt>
                        <dd style="font-weight:700; color:var(--color-text); margin:0; font-size:0.9375rem;">{{ $pendaftar->jurusan2->kode }} — {{ $pendaftar->jurusan2->nama }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt style="font-size:0.75rem; color:var(--color-text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Status</dt>
                        <dd style="margin:0;">
                            <span class="rekomendasi-badge badge-blue" style="font-size:0.8125rem;">Menunggu Verifikasi</span>
                        </dd>
                    </div>
                    <div>
                        <dt style="font-size:0.75rem; color:var(--color-text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Tanggal Daftar</dt>
                        <dd style="font-weight:700; color:var(--color-text); margin:0; font-size:0.9375rem;">{{ $pendaftar->created_at->format('d F Y, H:i') }}</dd>
                    </div>
                </dl>
            </div>

            @if ($grupWaUrl)
            {{-- Ajakan bergabung ke grup WhatsApp angkatan --}}
            <div class="wa-cta" style="background:linear-gradient(135deg, #128C7E, #25D366); border-radius:var(--radius-xl); padding:1.75rem 2rem; margin-bottom:2rem; text-align:left; color:white; box-shadow:0 8px 28px rgb(37 211 102 / 0.28);">
                <div style="display:flex; align-items:flex-start; gap:1rem;">
                    <div style="width:3rem; height:3rem; background:rgb(255 255 255 / 0.18); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;" aria-hidden="true">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413"/></svg>
                    </div>
                    <div style="flex:1;">
                        <h2 style="font-size:1.125rem; font-weight:800; margin:0 0 0.35rem;">Gabung Grup WhatsApp PPDB {{ $tahunAjaran ?? '2027/2028' }}</h2>
                        <p style="font-size:0.9rem; line-height:1.6; margin:0 0 1.1rem; opacity:0.95;">
                            Bergabunglah ke grup resmi calon peserta didik baru untuk menerima pengumuman, jadwal verifikasi, dan informasi penting lainnya dari panitia.
                        </p>
                        <a href="{{ $grupWaUrl }}" target="_blank" rel="noopener noreferrer"
                           class="btn btn-lg" id="btn-wa-group"
                           style="background:white; color:#0B7B4B; font-weight:800;"
                           aria-label="Gabung grup WhatsApp PPDB">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413"/></svg>
                            Gabung Grup Sekarang
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Next Steps --}}
            <div class="alert alert-info" style="text-align:left; margin-bottom:2rem;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="flex-shrink:0; margin-top:0.125rem;"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                <div>
                    <strong>Langkah selanjutnya:</strong>
                    <ol style="margin:0.5rem 0 0; padding-left:1.25rem; font-size:0.9rem; line-height:1.8;">
                        <li>Simpan atau cetak bukti pendaftaran ini</li>
                        <li>Unduh <strong>Formulir Pendaftaran</strong> Anda dalam bentuk PDF untuk dicetak dan diserahkan ke panitia</li>
                        <li>Kode pendaftaran juga dikirim ke email Anda — cek kotak masuk atau folder spam</li>
                        @if ($grupWaUrl)
                        <li>Gabung grup WhatsApp di atas agar tidak ketinggalan pengumuman panitia</li>
                        @endif
                        <li>Panitia akan memverifikasi dokumen Anda dalam 1–3 hari kerja</li>
                        <li>Pantau status melalui menu <strong>"Cek Status Pendaftaran"</strong></li>
                        <li>Jika ada masalah, hubungi panitia di <strong>081234567890</strong></li>
                    </ol>
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
                <a href="{{ route('ppdb.formulir', $pendaftar->nomor_pendaftaran) }}" class="btn btn-accent btn-lg" id="btn-formulir" aria-label="Cetak atau unduh formulir pendaftaran dalam bentuk PDF">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm-4.5 9h6m-6 3.75h3.75"/></svg>
                    Cetak Formulir (PDF)
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-lg" id="btn-print" aria-label="Cetak bukti pendaftaran">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                    Cetak Bukti
                </button>
                <a href="{{ route('ppdb.cekStatus') }}" class="btn btn-outline btn-lg">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Cek Status
                </a>
                <a href="{{ route('ppdb.index') }}" class="btn btn-ghost">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                    Halaman Utama
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyNomor() {
    const nomor = '{{ $pendaftar->nomor_pendaftaran }}';
    navigator.clipboard.writeText(nomor).then(() => {
        const btn = document.getElementById('copy-btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = `<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg> Tersalin!`;
        btn.style.color = 'var(--color-accent)';
        setTimeout(() => { btn.innerHTML = originalText; btn.style.color = ''; }, 2000);
    });
}

// Confetti-like success animation (simple, no library)
(function() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const colors = ['#1E40AF', '#16A34A', '#3B82F6', '#22C55E', '#60A5FA'];
    for (let i = 0; i < 40; i++) {
        const el = document.createElement('div');
        const size = Math.random() * 8 + 4;
        el.style.cssText = `position:fixed; pointer-events:none; z-index:9999; width:${size}px; height:${size}px; background:${colors[Math.floor(Math.random() * colors.length)]}; border-radius:${Math.random() > 0.5 ? '50%' : '2px'}; left:${Math.random() * 100}%; top:-10px; opacity:0.8;`;
        document.body.appendChild(el);
        const duration = Math.random() * 2000 + 1500;
        el.animate([
            { transform: `translateY(0) rotate(0deg)`, opacity: 0.8 },
            { transform: `translateY(100vh) rotate(${Math.random() * 360}deg)`, opacity: 0 },
        ], { duration, delay: Math.random() * 1000, easing: 'ease-in' }).onfinish = () => el.remove();
    }
})();
</script>
<style>
@media print {
    .navbar, footer, .chatbot-btn, .chatbot-panel, #btn-print, a.btn, .wa-cta { display: none !important; }
    body { background: white !important; }
}
</style>
@endpush
