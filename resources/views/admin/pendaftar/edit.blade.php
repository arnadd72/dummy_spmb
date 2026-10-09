@extends('layouts.admin')

@section('title', 'Edit Data Pendaftar — Admin PPDB')

@push('head')
@include('admin.content.partials.styles')
<style>
.edit-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(240px,1fr)); gap:1rem; }
.field-label { display:block; font-size:.78rem; font-weight:700; color:var(--tx-secondary); margin-bottom:.35rem; }
.field-input, select.field-input, textarea.field-input { width:100%; border:1px solid var(--sf-border); border-radius:10px; padding:.6rem .8rem; font-size:.88rem; }
.field-input.err { border-color:#ef4444; }
.field-error { color:#dc2626; font-size:.75rem; }
.section-title { font-size:.95rem; font-weight:800; color:var(--nu-700); margin:0 0 1rem; padding-bottom:.5rem; border-bottom:2px solid var(--nu-100); }
</style>
@endpush

@section('content')
<div class="content-page">
    <div class="page-head">
        <div>
            <h1 class="page-title">Edit Data Pendaftar</h1>
            <p class="page-sub">{{ $pendaftar->nama_lengkap }} · {{ $pendaftar->nomor_pendaftaran }}. Hanya field teks yang bisa diubah — berkas unggahan tetap (read-only).</p>
        </div>
        <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="btn btn-ghost">← Kembali ke Detail</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" role="alert" style="margin-bottom:1rem;">
            Terdapat {{ $errors->count() }} kesalahan. Periksa kembali isian bertanda merah.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pendaftar.update', $pendaftar->id) }}">
        @csrf
        @method('PUT')

        <div class="panel" style="margin-bottom:1.25rem;">
            <div class="panel-body">
                <h2 class="section-title">A. Identitas Siswa</h2>
                <div class="edit-grid">
                    <div><label class="field-label" for="nama_lengkap">Nama Lengkap *</label><input class="field-input" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $pendaftar->nama_lengkap) }}" required>@error('nama_lengkap')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="nisn">NISN</label><input class="field-input" id="nisn" name="nisn" value="{{ old('nisn', $pendaftar->nisn) }}" maxlength="10">@error('nisn')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="nik">NIK *</label><input class="field-input" id="nik" name="nik" value="{{ old('nik', $pendaftar->nik) }}" maxlength="16" required>@error('nik')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="no_kk">No. KK</label><input class="field-input" id="no_kk" name="no_kk" value="{{ old('no_kk', $pendaftar->no_kk) }}">@error('no_kk')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="jenis_kelamin">Jenis Kelamin *</label><select class="field-input" id="jenis_kelamin" name="jenis_kelamin"><option value="L" @selected(old('jenis_kelamin', $pendaftar->jenis_kelamin)==='L')>Laki-laki</option><option value="P" @selected(old('jenis_kelamin', $pendaftar->jenis_kelamin)==='P')>Perempuan</option></select></div>
                    <div><label class="field-label" for="tempat_lahir">Tempat Lahir</label><input class="field-input" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $pendaftar->tempat_lahir) }}">@error('tempat_lahir')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="tanggal_lahir">Tanggal Lahir</label><input type="date" class="field-input" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($pendaftar->tanggal_lahir)->format('Y-m-d')) }}">@error('tanggal_lahir')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="agama">Agama</label><input class="field-input" id="agama" name="agama" value="{{ old('agama', $pendaftar->agama) }}">@error('agama')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="anak_ke">Anak Ke</label><input type="number" class="field-input" id="anak_ke" name="anak_ke" value="{{ old('anak_ke', $pendaftar->anak_ke) }}" min="1">@error('anak_ke')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="jumlah_saudara">Jumlah Saudara</label><input type="number" class="field-input" id="jumlah_saudara" name="jumlah_saudara" value="{{ old('jumlah_saudara', $pendaftar->jumlah_saudara) }}" min="0">@error('jumlah_saudara')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div style="grid-column:1/-1;"><label class="field-label" for="alamat">Alamat Lengkap</label><textarea class="field-input" id="alamat" name="alamat" rows="2">{{ old('alamat', $pendaftar->alamat) }}</textarea></div>
                    <div><label class="field-label" for="rt">RT</label><input class="field-input" id="rt" name="rt" value="{{ old('rt', $pendaftar->rt) }}">@error('rt')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="rw">RW</label><input class="field-input" id="rw" name="rw" value="{{ old('rw', $pendaftar->rw) }}">@error('rw')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="desa">Desa</label><input class="field-input" id="desa" name="desa" value="{{ old('desa', $pendaftar->desa) }}">@error('desa')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="kecamatan">Kecamatan</label><input class="field-input" id="kecamatan" name="kecamatan" value="{{ old('kecamatan', $pendaftar->kecamatan) }}">@error('kecamatan')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="kabupaten">Kabupaten</label><input class="field-input" id="kabupaten" name="kabupaten" value="{{ old('kabupaten', $pendaftar->kabupaten) }}">@error('kabupaten')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="propinsi">Provinsi</label><input class="field-input" id="propinsi" name="propinsi" value="{{ old('propinsi', $pendaftar->propinsi) }}">@error('propinsi')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="asal_sekolah">Asal Sekolah</label><input class="field-input" id="asal_sekolah" name="asal_sekolah" value="{{ old('asal_sekolah', $pendaftar->asal_sekolah) }}">@error('asal_sekolah')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="tamat_tahun">Tamat Tahun</label><input class="field-input" id="tamat_tahun" name="tamat_tahun" value="{{ old('tamat_tahun', $pendaftar->tamat_tahun) }}">@error('tamat_tahun')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="no_ijazah">No. Ijazah</label><input class="field-input" id="no_ijazah" name="no_ijazah" value="{{ old('no_ijazah', $pendaftar->no_ijazah) }}">@error('no_ijazah')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="no_hp">No. HP Siswa *</label><input class="field-input" id="no_hp" name="no_hp" value="{{ old('no_hp', $pendaftar->no_hp) }}" required>@error('no_hp')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="no_wa">No. WA</label><input class="field-input" id="no_wa" name="no_wa" value="{{ old('no_wa', $pendaftar->no_wa) }}">@error('no_wa')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="email">Email</label><input type="email" class="field-input" id="email" name="email" value="{{ old('email', $pendaftar->email) }}">@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
                </div>
            </div>
        </div>

        <div class="panel" style="margin-bottom:1.25rem;">
            <div class="panel-body">
                <h2 class="section-title">B. Data Orang Tua</h2>
                <div class="edit-grid">
                    <div><label class="field-label" for="nama_ayah">Nama Ayah</label><input class="field-input" id="nama_ayah" name="nama_ayah" value="{{ old('nama_ayah', $pendaftar->nama_ayah) }}">@error('nama_ayah')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="pekerjaan_ayah">Pekerjaan Ayah</label><input class="field-input" id="pekerjaan_ayah" name="pekerjaan_ayah" value="{{ old('pekerjaan_ayah', $pendaftar->pekerjaan_ayah) }}">@error('pekerjaan_ayah')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="ayah_pendidikan">Pendidikan Ayah</label><input class="field-input" id="ayah_pendidikan" name="ayah_pendidikan" value="{{ old('ayah_pendidikan', $pendaftar->ayah_pendidikan) }}">@error('ayah_pendidikan')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="nama_ibu">Nama Ibu</label><input class="field-input" id="nama_ibu" name="nama_ibu" value="{{ old('nama_ibu', $pendaftar->nama_ibu) }}">@error('nama_ibu')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="pekerjaan_ibu">Pekerjaan Ibu</label><input class="field-input" id="pekerjaan_ibu" name="pekerjaan_ibu" value="{{ old('pekerjaan_ibu', $pendaftar->pekerjaan_ibu) }}">@error('pekerjaan_ibu')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="ibu_pendidikan">Pendidikan Ibu</label><input class="field-input" id="ibu_pendidikan" name="ibu_pendidikan" value="{{ old('ibu_pendidikan', $pendaftar->ibu_pendidikan) }}">@error('ibu_pendidikan')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="no_hp_ortu">No. HP Ortu</label><input class="field-input" id="no_hp_ortu" name="no_hp_ortu" value="{{ old('no_hp_ortu', $pendaftar->no_hp_ortu) }}">@error('no_hp_ortu')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div style="grid-column:1/-1;"><label class="field-label" for="alamat_ortu">Alamat Ortu</label><textarea class="field-input" id="alamat_ortu" name="alamat_ortu" rows="2">{{ old('alamat_ortu', $pendaftar->alamat_ortu) }}</textarea></div>
                    <div><label class="field-label" for="penghasilan">Penghasilan Ortu</label><input class="field-input" id="penghasilan" name="penghasilan" value="{{ old('penghasilan', $pendaftar->penghasilan) }}">@error('penghasilan')<span class="field-error">{{ $message }}</span>@enderror</div>
                </div>
            </div>
        </div>

        <div class="panel" style="margin-bottom:1.25rem;">
            <div class="panel-body">
                <h2 class="section-title">C. Pilihan Jurusan & Nilai</h2>
                <div class="edit-grid">
                    <div><label class="field-label" for="jurusan_1_id">Jurusan Pilihan 1 *</label><select class="field-input" id="jurusan_1_id" name="jurusan_1_id" required>@foreach($jurusan as $j)<option value="{{ $j->id }}" @selected((int) old('jurusan_1_id', $pendaftar->jurusan_1_id) === (int) $j->id)>{{ $j->kode }} — {{ $j->nama }}</option>@endforeach</select>@error('jurusan_1_id')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="jurusan_2_id">Jurusan Pilihan 2</label><select class="field-input" id="jurusan_2_id" name="jurusan_2_id"><option value="">— Tidak ada —</option>@foreach($jurusan as $j)<option value="{{ $j->id }}" @selected((int) old('jurusan_2_id', $pendaftar->jurusan_2_id) === (int) $j->id)>{{ $j->kode }} — {{ $j->nama }}</option>@endforeach</select>@error('jurusan_2_id')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="minat">Minat</label><input class="field-input" id="minat" name="minat" value="{{ old('minat', $pendaftar->minat) }}">@error('minat')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div><label class="field-label" for="status_verifikasi">Status Verifikasi *</label><select class="field-input" id="status_verifikasi" name="status_verifikasi" required><option value="pending" @selected(old('status_verifikasi', $pendaftar->status_verifikasi)==='pending')>Menunggu</option><option value="approved" @selected(old('status_verifikasi', $pendaftar->status_verifikasi)==='approved')>Diterima</option><option value="rejected" @selected(old('status_verifikasi', $pendaftar->status_verifikasi)==='rejected')>Ditolak</option><option value="revisi" @selected(old('status_verifikasi', $pendaftar->status_verifikasi)==='revisi')>Revisi</option></select></div>
                    @php $nu = is_array($pendaftar->nilai_ujian) ? $pendaftar->nilai_ujian : []; @endphp
                    <div><label class="field-label" for="nu_indo">Nilai B. Indonesia</label><input type="number" step="0.01" min="0" max="100" class="field-input" id="nu_indo" name="nilai_ujian[b_indonesia]" value="{{ old('nilai_ujian.b_indonesia', $nu['b_indonesia'] ?? '') }}"></div>
                    <div><label class="field-label" for="nu_ing">Nilai B. Inggris</label><input type="number" step="0.01" min="0" max="100" class="field-input" id="nu_ing" name="nilai_ujian[b_inggris]" value="{{ old('nilai_ujian.b_inggris', $nu['b_inggris'] ?? '') }}"></div>
                    <div><label class="field-label" for="nu_mtk">Nilai Matematika</label><input type="number" step="0.01" min="0" max="100" class="field-input" id="nu_mtk" name="nilai_ujian[matematika]" value="{{ old('nilai_ujian.matematika', $nu['matematika'] ?? '') }}"></div>
                    <div><label class="field-label" for="nu_ipa">Nilai IPA</label><input type="number" step="0.01" min="0" max="100" class="field-input" id="nu_ipa" name="nilai_ujian[ipa]" value="{{ old('nilai_ujian.ipa', $nu['ipa'] ?? '') }}"></div>
                </div>
            </div>
        </div>

        <div class="panel" style="margin-bottom:1.25rem;">
            <div class="panel-body">
                <h2 class="section-title">D. Berkas (Read-Only)</h2>
                <p style="font-size:.85rem; color:var(--tx-muted); margin:0;">Berkas unggahan tidak bisa diubah dari form ini. Untuk mengganti berkas, minta siswa mengunggah ulang atau hubungi panitia. Daftar berkas dapat dilihat di halaman detail pendaftar.</p>
            </div>
        </div>

        <div style="display:flex; gap:.75rem; flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
@endsection
