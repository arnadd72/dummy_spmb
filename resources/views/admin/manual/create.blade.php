@extends('layouts.admin')

@section('title', 'Input Manual Pendaftar — Admin PPDB')

@push('head')
<style>
    .manual-page { max-width: 1000px; margin: 0 auto; }

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .page-title {
        font-family: var(--admin-font-mono);
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--nu-700);
        letter-spacing: -.03em;
        margin: 0 0 .3rem;
    }

    .page-sub { font-size: .85rem; color: var(--tx-muted); margin: 0; max-width: 620px; line-height: 1.5; }

    .notice {
        display: flex;
        gap: .6rem;
        align-items: flex-start;
        background: var(--gold-100);
        border: 1px solid rgba(217,119,6,.3);
        color: #92400E;
        border-radius: 12px;
        padding: .85rem 1rem;
        font-size: .82rem;
        line-height: 1.6;
        margin-bottom: 1.25rem;
    }

    .notice svg { flex-shrink: 0; margin-top: .1rem; }

    .form-card {
        background: var(--sf-card);
        border: 1px solid var(--sf-border);
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .section { padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--sf-border); }
    .section:last-of-type { border-bottom: none; }

    .section-head { display: flex; align-items: center; gap: .7rem; margin-bottom: 1.25rem; }

    .section-num {
        font-family: var(--admin-font-mono);
        font-size: .7rem;
        font-weight: 700;
        color: #fff;
        background: var(--nu-500);
        width: 1.6rem;
        height: 1.6rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .section-title {
        font-family: var(--admin-font-mono);
        font-size: .92rem;
        font-weight: 700;
        color: var(--nu-700);
        letter-spacing: -.01em;
        margin: 0;
    }

    .section-hint { font-size: .74rem; color: var(--tx-muted); margin: 0; }

    .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    .col-span-2 { grid-column: span 2; }

    .field { display: flex; flex-direction: column; gap: .35rem; }

    .label {
        font-family: var(--admin-font-mono);
        font-size: .62rem;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--tx-muted);
    }

    .label .req { color: var(--st-error); }

    .input, .select, .textarea {
        width: 100%;
        font-family: var(--admin-font-sans);
        font-size: .87rem;
        color: var(--tx-primary);
        padding: .6rem .8rem;
        border: 1px solid var(--sf-border);
        border-radius: 10px;
        background: var(--sf-page);
        outline: none;
        transition: all var(--transition);
    }

    .textarea { resize: vertical; min-height: 74px; line-height: 1.6; }
    .select { cursor: pointer; }

    .input:focus, .select:focus, .textarea:focus {
        border-color: var(--nu-500);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(5,122,60,.12);
    }

    .input.err, .select.err, .textarea.err { border-color: var(--st-error); background: #FFF5F5; }

    .help { font-size: .7rem; color: var(--tx-muted); }
    .error-text { font-size: .72rem; color: var(--st-error); font-weight: 600; }

    .actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: .6rem;
        padding: 1.2rem 1.75rem;
        background: var(--sf-page);
        border-top: 1px solid var(--sf-border);
        flex-wrap: wrap;
    }

    .btn {
        font-family: var(--admin-font-sans);
        font-size: .84rem;
        font-weight: 600;
        padding: .65rem 1.3rem;
        border-radius: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        transition: all var(--transition);
    }

    .btn-primary { background: var(--nu-500); color: #fff; }
    .btn-primary:hover { background: var(--nu-600); transform: translateY(-1px); box-shadow: 0 6px 18px -4px rgba(2,75,35,.35); }
    .btn-ghost { background: var(--sf-card); border-color: var(--sf-border); color: var(--tx-secondary); }
    .btn-ghost:hover { border-color: var(--nu-500); color: var(--nu-500); }

    @media (max-width: 720px) {
        .grid, .grid-3 { grid-template-columns: 1fr; }
        .col-span-2 { grid-column: span 1; }
    }
</style>
@endpush

@section('content')
<div class="manual-page">

    <div class="page-head">
        <div>
            <h1 class="page-title">Input Manual</h1>
            <p class="page-sub">Daftarkan calon siswa secara luring dari loket panitia. Data tersimpan langsung sebagai pendaftar terverifikasi, tanpa unggah berkas.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Dashboard
        </a>
    </div>

    <div class="notice" role="note">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
        <div>
            Nomor pendaftaran dibuat otomatis dan status langsung <strong>Diterima</strong> karena panitia memasukkan data secara langsung.
            Setelah tersimpan, Anda akan diarahkan ke detail pendaftar.
        </div>
    </div>

    <form method="POST" action="{{ route('admin.manual.store') }}" novalidate>
        @csrf

        <div class="form-card">

            {{-- 1. Identitas Siswa --}}
            <div class="section">
                <div class="section-head">
                    <span class="section-num" aria-hidden="true">1</span>
                    <div>
                        <h2 class="section-title">Identitas Calon Siswa</h2>
                        <p class="section-hint">Sesuai Akta Kelahiran &amp; Kartu Keluarga.</p>
                    </div>
                </div>

                <div class="grid">
                    <div class="field col-span-2">
                        <label class="label" for="nama_lengkap">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap"
                               class="input {{ $errors->has('nama_lengkap') ? 'err' : '' }}"
                               value="{{ old('nama_lengkap') }}" placeholder="Contoh: Muhammad Rizky Pratama"
                               required aria-required="true" autocomplete="name">
                        @error('nama_lengkap')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="nik">NIK <span class="req">*</span></label>
                        <input type="text" id="nik" name="nik"
                               class="input {{ $errors->has('nik') ? 'err' : '' }}"
                               value="{{ old('nik') }}" placeholder="16 digit angka"
                               inputmode="numeric" maxlength="16" required aria-required="true">
                        @error('nik')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="nisn">NISN</label>
                        <input type="text" id="nisn" name="nisn"
                               class="input {{ $errors->has('nisn') ? 'err' : '' }}"
                               value="{{ old('nisn') }}" placeholder="10 digit angka (opsional)"
                               inputmode="numeric" maxlength="10">
                        @error('nisn')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="jenis_kelamin">Jenis Kelamin <span class="req">*</span></label>
                        <select id="jenis_kelamin" name="jenis_kelamin"
                                class="select {{ $errors->has('jenis_kelamin') ? 'err' : '' }}" required aria-required="true">
                            <option value="">— Pilih —</option>
                            <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="agama">Agama <span class="req">*</span></label>
                        <input type="text" id="agama" name="agama"
                               class="input {{ $errors->has('agama') ? 'err' : '' }}"
                               value="{{ old('agama', 'Islam') }}" placeholder="Contoh: Islam"
                               required aria-required="true">
                        @error('agama')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="tempat_lahir">Tempat Lahir <span class="req">*</span></label>
                        <input type="text" id="tempat_lahir" name="tempat_lahir"
                               class="input {{ $errors->has('tempat_lahir') ? 'err' : '' }}"
                               value="{{ old('tempat_lahir') }}" placeholder="Contoh: Banyumas"
                               required aria-required="true">
                        @error('tempat_lahir')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="tanggal_lahir">Tanggal Lahir <span class="req">*</span></label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                               class="input {{ $errors->has('tanggal_lahir') ? 'err' : '' }}"
                               value="{{ old('tanggal_lahir') }}" max="{{ date('Y-m-d') }}"
                               required aria-required="true">
                        @error('tanggal_lahir')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="no_hp">No. HP Siswa <span class="req">*</span></label>
                        <input type="tel" id="no_hp" name="no_hp"
                               class="input {{ $errors->has('no_hp') ? 'err' : '' }}"
                               value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx"
                               required aria-required="true" autocomplete="tel">
                        @error('no_hp')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="no_wa">No. WhatsApp Siswa</label>
                        <input type="tel" id="no_wa" name="no_wa"
                               class="input {{ $errors->has('no_wa') ? 'err' : '' }}"
                               value="{{ old('no_wa') }}" placeholder="08xxxxxxxxxx (opsional)"
                               autocomplete="tel">
                        @error('no_wa')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="email">Alamat Email Aktif</label>
                        <input type="email" id="email" name="email"
                               class="input {{ $errors->has('email') ? 'err' : '' }}"
                               value="{{ old('email') }}" placeholder="nama@email.com (opsional)"
                               autocomplete="email">
                        @error('email')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="asal_sekolah">Asal Sekolah <span class="req">*</span></label>
                        <input type="text" id="asal_sekolah" name="asal_sekolah"
                               class="input {{ $errors->has('asal_sekolah') ? 'err' : '' }}"
                               value="{{ old('asal_sekolah') }}" placeholder="Contoh: SMP Negeri 1 Wangon"
                               required aria-required="true">
                        @error('asal_sekolah')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field col-span-2">
                        <label class="label" for="alamat">Alamat Lengkap <span class="req">*</span></label>
                        <textarea id="alamat" name="alamat"
                                  class="textarea {{ $errors->has('alamat') ? 'err' : '' }}"
                                  placeholder="Jalan, RT/RW, Desa/Kelurahan, Kecamatan, Kabupaten"
                                  required aria-required="true">{{ old('alamat') }}</textarea>
                        @error('alamat')<span class="error-text">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            {{-- 2. Data Orang Tua --}}
            <div class="section">
                <div class="section-head">
                    <span class="section-num" aria-hidden="true">2</span>
                    <div>
                        <h2 class="section-title">Data Orang Tua / Wali</h2>
                        <p class="section-hint">Diambil dari Kartu Keluarga.</p>
                    </div>
                </div>

                <div class="grid">
                    <div class="field">
                        <label class="label" for="nama_ayah">Nama Ayah <span class="req">*</span></label>
                        <input type="text" id="nama_ayah" name="nama_ayah"
                               class="input {{ $errors->has('nama_ayah') ? 'err' : '' }}"
                               value="{{ old('nama_ayah') }}" required aria-required="true">
                        @error('nama_ayah')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="pekerjaan_ayah">Pekerjaan Ayah <span class="req">*</span></label>
                        <input type="text" id="pekerjaan_ayah" name="pekerjaan_ayah"
                               class="input {{ $errors->has('pekerjaan_ayah') ? 'err' : '' }}"
                               value="{{ old('pekerjaan_ayah') }}" placeholder="Contoh: Petani" required aria-required="true">
                        @error('pekerjaan_ayah')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="nama_ibu">Nama Ibu <span class="req">*</span></label>
                        <input type="text" id="nama_ibu" name="nama_ibu"
                               class="input {{ $errors->has('nama_ibu') ? 'err' : '' }}"
                               value="{{ old('nama_ibu') }}" required aria-required="true">
                        @error('nama_ibu')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="pekerjaan_ibu">Pekerjaan Ibu <span class="req">*</span></label>
                        <input type="text" id="pekerjaan_ibu" name="pekerjaan_ibu"
                               class="input {{ $errors->has('pekerjaan_ibu') ? 'err' : '' }}"
                               value="{{ old('pekerjaan_ibu') }}" placeholder="Contoh: Ibu Rumah Tangga" required aria-required="true">
                        @error('pekerjaan_ibu')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="no_hp_ortu">No. HP Orang Tua <span class="req">*</span></label>
                        <input type="tel" id="no_hp_ortu" name="no_hp_ortu"
                               class="input {{ $errors->has('no_hp_ortu') ? 'err' : '' }}"
                               value="{{ old('no_hp_ortu') }}" placeholder="08xxxxxxxxxx" required aria-required="true">
                        @error('no_hp_ortu')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="alamat_ortu">Alamat Orang Tua</label>
                        <input type="text" id="alamat_ortu" name="alamat_ortu"
                               class="input {{ $errors->has('alamat_ortu') ? 'err' : '' }}"
                               value="{{ old('alamat_ortu') }}" placeholder="Kosongkan bila sama dengan siswa">
                        @error('alamat_ortu')<span class="error-text">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            {{-- 3. Pilihan Jurusan --}}
            <div class="section">
                <div class="section-head">
                    <span class="section-num" aria-hidden="true">3</span>
                    <div>
                        <h2 class="section-title">Pilihan Program Keahlian</h2>
                        <p class="section-hint">Pilihan 1 wajib; pilihan 2 opsional &amp; harus berbeda.</p>
                    </div>
                </div>

                <div class="grid">
                    <div class="field">
                        <label class="label" for="jurusan_1_id">Jurusan Pilihan 1 <span class="req">*</span></label>
                        <select id="jurusan_1_id" name="jurusan_1_id"
                                class="select {{ $errors->has('jurusan_1_id') ? 'err' : '' }}" required aria-required="true">
                            <option value="">— Pilih Jurusan —</option>
                            @foreach ($jurusan as $j)
                                <option value="{{ $j->id }}" @selected((string) old('jurusan_1_id') === (string) $j->id)>
                                    {{ $j->kode }} — {{ $j->nama }} (sisa {{ $j->sisa_kuota }} kursi)
                                </option>
                            @endforeach
                        </select>
                        @error('jurusan_1_id')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="jurusan_2_id">Jurusan Pilihan 2</label>
                        <select id="jurusan_2_id" name="jurusan_2_id"
                                class="select {{ $errors->has('jurusan_2_id') ? 'err' : '' }}">
                            <option value="">— Tidak Ada —</option>
                            @foreach ($jurusan as $j)
                                <option value="{{ $j->id }}" @selected((string) old('jurusan_2_id') === (string) $j->id)>
                                    {{ $j->kode }} — {{ $j->nama }} (sisa {{ $j->sisa_kuota }} kursi)
                                </option>
                            @endforeach
                        </select>
                        @error('jurusan_2_id')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="field col-span-2">
                        <label class="label" for="minat">Minat / Catatan Panitia</label>
                        <input type="text" id="minat" name="minat"
                               class="input {{ $errors->has('minat') ? 'err' : '' }}"
                               value="{{ old('minat') }}" placeholder="Misal: berminat di bidang otomotif (opsional)">
                        @error('minat')<span class="error-text">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="actions">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Daftarkan &amp; Cetak Kartu
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Cegah memilih jurusan yang sama pada pilihan 1 & 2.
    (function () {
        const p1 = document.getElementById('jurusan_1_id');
        const p2 = document.getElementById('jurusan_2_id');
        if (!p1 || !p2) return;

        function syncOptions() {
            Array.from(p2.options).forEach(function (opt) {
                if (!opt.value) return;
                const isSame = opt.value === p1.value;
                opt.disabled = isSame;
                opt.hidden = isSame;
            });
            if (p2.value && p2.value === p1.value) p2.value = '';
        }

        p1.addEventListener('change', syncOptions);
        syncOptions();
    })();

    // Isi otomatis alamat orang tua dari alamat siswa bila dibiarkan kosong.
    (function () {
        const alamatSiswa = document.getElementById('alamat');
        const alamatOrtu = document.getElementById('alamat_ortu');
        if (!alamatSiswa || !alamatOrtu) return;

        alamatSiswa.addEventListener('blur', function () {
            if (!alamatOrtu.value.trim()) alamatOrtu.value = alamatSiswa.value;
        });
    })();

    // Batasi input numerik (NIK/NISN/HP) agar hanya menerima angka.
    document.querySelectorAll('#nik, #nisn').forEach(function (el) {
        el.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
    });
</script>
@endpush
