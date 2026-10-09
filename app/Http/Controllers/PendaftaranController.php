<?php

namespace App\Http\Controllers;

use App\Mail\KodePendaftaranMail;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Faq;
use App\Models\Berita;
use App\Models\Event;
use App\Models\Galeri;
use App\Models\Testimoni;
use App\Models\Pengaturan;
use App\Support\GoogleDrive;
use App\Support\PenyimpananDokumen;
use App\Support\FormFieldConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class PendaftaranController extends Controller
{
    // ─── Landing Page ───────────────────────────────────────────────
    public function index()
    {
        $jurusan = Jurusan::where('is_active', true)->get();
        $faqs    = Faq::where('is_active', true)->take(6)->get();

        // Konten dinamis yang dikelola admin melalui panel Manajemen Konten.
        $berita  = Berita::where('is_active', true)->latest()->take(3)->get();
        $events  = Event::where('is_active', true)
            ->orderByRaw('tanggal IS NULL')
            ->orderBy('tanggal')
            ->take(3)
            ->get();
        $galeris = Galeri::where('is_active', true)->latest()->take(8)->get();
        $testimonis = Testimoni::where('is_active', true)->latest()->take(6)->get();

        // Tahun ajaran dinamis dari pengaturan admin.
        $tahunAjaran = Pengaturan::get('tahun_ajaran', '2027/2028');

        // Hanya pakai gambar hero bila berkasnya benar-benar ada di storage.
        $heroImage = Pengaturan::heroExists() ? Pengaturan::heroUrl() : null;

        return view('ppdb.index', compact(
            'jurusan', 'faqs', 'berita', 'events', 'galeris', 'heroImage', 'testimonis', 'tahunAjaran'
        ));
    }

    // ─── Detail Jurusan ────────────────────────────────────────────
    /**
     * Halaman lengkap satu jurusan — dibuka saat kartu jurusan di landing diklik.
     */
    public function jurusanShow(string $kode)
    {
        $jurusan = Jurusan::where('kode', strtoupper($kode))->where('is_active', true)->firstOrFail();
        $tahunAjaran = Pengaturan::get('tahun_ajaran', '2027/2028');
        $lainnya = Jurusan::where('is_active', true)->where('id', '!=', $jurusan->id)->get();

        // Hitung pendaftar pilihan 1 untuk jurusan ini.
        $jurusan->loadCount('pendaftarPilihan1 as pendaftar_count');

        return view('ppdb.jurusan-show', compact('jurusan', 'tahunAjaran', 'lainnya'));
    }

    // ─── Step 1: Persetujuan & Identitas Awal ──────────────────────
    public function step1()
    {
        $step1 = Session::get('ppdb.step1', []);
        $konfig = FormFieldConfig::map();
        $kustomStep1 = FormFieldConfig::kustomUntuk($konfig, 'step1');
        return view('ppdb.step1', compact('step1', 'konfig', 'kustomStep1'));
    }

    public function step1Post(Request $request)
    {
        $konfig = FormFieldConfig::map();
        $base = [
            'nama_lengkap' => 'required|string|min:3|max:255',
            'nisn'         => 'required|digits:10',
            'setuju'       => 'required|accepted',
        ];
        $rules = array_merge(
            FormFieldConfig::sesuaikanAturan($konfig, $base, []),
            FormFieldConfig::aturanKustom($konfig, 'step1')
        );

        $request->validate($rules, [
            'nama_lengkap.required' => 'Nama lengkap calon siswa wajib diisi.',
            'nama_lengkap.min'      => 'Nama lengkap minimal 3 karakter.',
            'nisn.required'         => 'NISN wajib diisi (10 digit).',
            'nisn.digits'           => 'NISN harus terdiri dari tepat 10 digit angka.',
            'setuju.required'       => 'Anda harus menyetujui persyaratan sebelum melanjutkan.',
            'setuju.accepted'       => 'Anda harus menyetujui persyaratan sebelum melanjutkan.',
        ]);

        $lama = Session::get('ppdb.step1', []);
        $data = [
            'nama_lengkap' => trim((string) ($request->nama_lengkap ?? $lama['nama_lengkap'] ?? '')),
            'nisn'         => trim((string) ($request->nisn ?? $lama['nisn'] ?? '')),
            'setuju'       => true,
        ];
        // Bila admin menyembunyikan nama/nisn, pertahankan nilai lama agar alur tidak putus.
        foreach (['nama_lengkap', 'nisn'] as $k) {
            if (empty($data[$k]) && isset($lama[$k])) {
                $data[$k] = $lama[$k];
            }
        }
        $data['data_kustom_step1'] = array_merge(
            $lama['data_kustom_step1'] ?? [],
            FormFieldConfig::ambilKustom($request, $konfig, 'step1')
        );

        Session::put('ppdb.step1', $data);

        // Simpan data awal siswa ke step5
        $step5 = Session::get('ppdb.step5', []);
        if (! empty($data['nama_lengkap'])) {
            $step5['nama_lengkap'] = $data['nama_lengkap'];
        }
        if (! empty($data['nisn'])) {
            $step5['nisn'] = $data['nisn'];
        }
        Session::put('ppdb.step5', $step5);

        return redirect()->route('ppdb.step2');
    }

    // ─── Step 2: Upload Dokumen (KK & Akta) ─────────────────────────
    public function step2()
    {
        $this->requireStep(1);
        $step1 = Session::get('ppdb.step1', []);
        $step2 = Session::get('ppdb.step2', []);
        $step5 = Session::get('ppdb.step5', []);
        $step6 = Session::get('ppdb.step6', []);
        $konfig = FormFieldConfig::map();
        $kustomStep2 = FormFieldConfig::kustomUntuk($konfig, 'step2');
        return view('ppdb.step2', compact('step1', 'step2', 'step5', 'step6', 'konfig', 'kustomStep2'));
    }

    public function step2Post(Request $request)
    {
        $this->requireStep(1);

        $konfig = FormFieldConfig::map();
        $base2 = [
            'kk'                        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'akta'                      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'extracted_nama_akta'       => 'nullable|string|max:255',
            'extracted_tempat_lahir_akta'=> 'nullable|string|max:100',
            'extracted_tanggal_lahir_akta'=> 'nullable|date',
            'extracted_jenis_kelamin'   => 'nullable|in:L,P',
            'extracted_nik'             => 'nullable|string|size:16',
            'extracted_alamat_kk'       => 'nullable|string|max:500',
            'extracted_nama_ayah_kk'    => 'nullable|string|max:255',
            'extracted_pekerjaan_ayah_kk'=> 'nullable|string|max:100',
            'extracted_nama_ibu_kk'     => 'nullable|string|max:255',
            'extracted_pekerjaan_ibu_kk'=> 'nullable|string|max:100',
        ];
        // Hormati toggle wajib admin untuk berkas: wajib+tampil => required.
        foreach (['kk', 'akta'] as $dok) {
            $f = $konfig[$dok] ?? null;
            if ($f && $f->tampil && $f->wajib) {
                $base2[$dok] = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
            }
        }
        // Bila admin sembunyikan slot upload, validasi file dilewati (nilai lama dipertahankan).
        foreach (['kk', 'akta'] as $dok) {
            if (! FormFieldConfig::tampil($konfig, $dok, true)) {
                unset($base2[$dok]);
            }
        }
        $request->validate(array_merge(
            FormFieldConfig::sesuaikanAturan($konfig, $base2, []),
            FormFieldConfig::aturanKustom($konfig, 'step2')
        ));

        $data = Session::get('ppdb.step2', []);

        if ($request->hasFile('kk')) {
            $data['kk_path'] = $request->file('kk')->store('temp/kk', 'local');
        }
        if ($request->hasFile('akta')) {
            $data['akta_path'] = $request->file('akta')->store('temp/akta', 'local');
        }

        $step5 = Session::get('ppdb.step5', []);
        $step6 = Session::get('ppdb.step6', []);

        // 1. Data Nama & TTL Siswa strictly diambil dari Akta Kelahiran
        if ($request->filled('extracted_nama_akta')) {
            $step5['nama_lengkap'] = trim($request->extracted_nama_akta);
            $data['source_nama'] = 'akta';
        }
        if ($request->filled('extracted_tempat_lahir_akta')) {
            $step5['tempat_lahir'] = trim($request->extracted_tempat_lahir_akta);
            $data['source_tempat_lahir'] = 'akta';
        }
        if ($request->filled('extracted_tanggal_lahir_akta')) {
            $step5['tanggal_lahir'] = $request->extracted_tanggal_lahir_akta;
            $data['source_tanggal_lahir'] = 'akta';
        }
        if ($request->filled('extracted_jenis_kelamin')) {
            $step5['jenis_kelamin'] = $request->extracted_jenis_kelamin;
        }

        // 2. NIK Siswa diambil dari hasil pencocokan Kartu Keluarga (KK) dengan nama siswa
        if ($request->filled('extracted_nik')) {
            $step5['nik'] = trim($request->extracted_nik);
            $data['source_nik'] = 'kk_matched';
        }

        // Alamat diambil dari KK
        if ($request->filled('extracted_alamat_kk')) {
            $step5['alamat'] = trim($request->extracted_alamat_kk);
            $step6['alamat_ortu'] = trim($request->extracted_alamat_kk);
            $data['source_alamat'] = 'kk';
        }

        // 3. Data Orang Tua diambil dari KK
        if ($request->filled('extracted_nama_ayah_kk')) {
            $step6['nama_ayah'] = trim($request->extracted_nama_ayah_kk);
        }
        if ($request->filled('extracted_pekerjaan_ayah_kk')) {
            $step6['pekerjaan_ayah'] = trim($request->extracted_pekerjaan_ayah_kk);
        }
        if ($request->filled('extracted_nama_ibu_kk')) {
            $step6['nama_ibu'] = trim($request->extracted_nama_ibu_kk);
        }
        if ($request->filled('extracted_pekerjaan_ibu_kk')) {
            $step6['pekerjaan_ibu'] = trim($request->extracted_pekerjaan_ibu_kk);
        }

        Session::put('ppdb.step2', $data);
        Session::put('ppdb.step5', $step5);
        Session::put('ppdb.step6', $step6);

        // Simpan jawaban kustom step2 agar ikut tersimpan saat submit.
        $lama2 = Session::get('ppdb.step2', []);
        $data['data_kustom_step2'] = array_merge(
            $lama2['data_kustom_step2'] ?? [],
            FormFieldConfig::ambilKustom($request, $konfig, 'step2')
        );
        Session::put('ppdb.step2', $data);

        return redirect()->route('ppdb.step3');
    }

    // ─── Step 3: Upload Rapor ────────────────────────────────────────
    public function step3()
    {
        $this->requireStep(1);
        $konfig = FormFieldConfig::map();
        $kustomStep3 = FormFieldConfig::kustomUntuk($konfig, 'step3');
        return view('ppdb.step3', compact('konfig', 'kustomStep3'));
    }

    public function step3Post(Request $request)
    {
        $this->requireStep(1);

        $konfig = FormFieldConfig::map();
        $base3 = [
            'rapor' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
        $fr = $konfig['rapor'] ?? null;
        if ($fr && $fr->tampil && $fr->wajib) {
            $base3['rapor'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }
        if (! FormFieldConfig::tampil($konfig, 'rapor', true)) {
            unset($base3['rapor']);
        }
        $request->validate(array_merge(
            FormFieldConfig::sesuaikanAturan($konfig, $base3, []),
            FormFieldConfig::aturanKustom($konfig, 'step3')
        ));

        $data = Session::get('ppdb.step3', []);

        if ($request->hasFile('rapor')) {
            $data['rapor_path'] = $request->file('rapor')->store('temp/rapor', 'local');
        }
        $data['data_kustom_step3'] = array_merge(
            $data['data_kustom_step3'] ?? [],
            FormFieldConfig::ambilKustom($request, $konfig, 'step3')
        );

        Session::put('ppdb.step3', $data);
        return redirect()->route('ppdb.step4');
    }

    public function step3Lewati()
    {
        Session::put('ppdb.step3', ['lewati' => true]);
        return redirect()->route('ppdb.step4');
    }

    // ─── Step 4: Pilih Jurusan & Rekomendasi ─────────────────────────
    public function step4(Request $request)
    {
        $this->requireStep(1);

        $jurusan = Jurusan::where('is_active', true)->get();
        $nilaiRapor = $request->session()->get('ppdb.step5.nilai_rapor', []);
        $minat = $request->session()->get('ppdb.step4.minat', '');

        $rekomendasiData = [];
        foreach ($jurusan as $j) {
            $skor = $j->hitungSkor($nilaiRapor, $minat);
            $rekomendasiData[$j->id] = [
                'skor'  => $skor,
                'label' => $j->getLabelRekomendasi($skor),
                'color' => $j->getColorRekomendasi($skor),
            ];
        }

        arsort($rekomendasiData);

        $konfig = FormFieldConfig::map();
        $kustomStep4 = FormFieldConfig::kustomUntuk($konfig, 'step4');
        return view('ppdb.step4', compact('jurusan', 'rekomendasiData', 'konfig', 'kustomStep4'));
    }

    public function step4Post(Request $request)
    {
        $this->requireStep(1);

        $konfig = FormFieldConfig::map();
        $base4 = [
            'jurusan_1'  => 'required|exists:jurusan,id',
            'jurusan_2'  => 'nullable|exists:jurusan,id|different:jurusan_1',
            'minat'      => 'required|string|max:100',
            'nilai_rapor'=> 'nullable|array',
        ];
        $rules4 = array_merge(
            FormFieldConfig::sesuaikanAturan($konfig, $base4, []),
            FormFieldConfig::aturanKustom($konfig, 'step4')
        );
        // Bila pilihan jurusan disembunyikan admin, jangan paksa validasi exists.
        foreach (['jurusan_1', 'jurusan_2', 'minat'] as $k4) {
            if (! FormFieldConfig::tampil($konfig, $k4, true) && isset($rules4[$k4])) {
                $rules4[$k4] = 'nullable';
            }
        }
        $request->validate($rules4);

        Session::put('ppdb.step4', [
            'jurusan_1_id' => $request->jurusan_1,
            'jurusan_2_id' => $request->jurusan_2,
            'minat'        => $request->minat,
            'data_kustom_step4' => array_merge(
                Session::get('ppdb.step4.data_kustom_step4', []),
                FormFieldConfig::ambilKustom($request, $konfig, 'step4')
            ),
        ]);

        Session::put('ppdb.nilai_rapor', $request->nilai_rapor ?? []);

        return redirect()->route('ppdb.step5');
    }

    // ─── Step 5: Data Siswa ──────────────────────────────────────────
    public function step5()
    {
        $this->requireStep(4);
        $data = Session::get('ppdb.step5', []);
        $konfig = FormFieldConfig::map();
        $kustomStep5 = FormFieldConfig::kustomUntuk($konfig, 'step5');
        return view('ppdb.step5', compact('data', 'konfig', 'kustomStep5'));
    }

    public function step5Post(Request $request)
    {
        $this->requireStep(4);

        // Aturan bawaan disesuaikan toggle tampil/wajib dari panel admin,
        // lalu digabung dengan aturan field kustom step5.
        $konfig = FormFieldConfig::map();
        $base = [
            'nama_lengkap'   => 'required|string|max:255',
            'nik'            => 'required|digits:16',
            'no_kk'          => 'required|digits:16',
            'nisn'           => 'nullable|digits:10',
            'jenis_kelamin'  => 'required|in:L,P',
            'tempat_lahir'   => 'required|string|max:100',
            'tanggal_lahir'  => 'required|date|before:today',
            'agama'          => 'required|string|max:30',
            'anak_ke'        => 'required|integer|min:1|max:20',
            'jumlah_saudara' => 'required|integer|min:0|max:20',
            'asal_sekolah'   => 'required|string|max:200',
            'tamat_tahun'    => 'required|string|max:10',
            'no_ijazah'      => 'nullable|string|max:60',
            'no_hp'          => 'required|string|max:20',
            'no_wa'          => 'nullable|string|max:20',
            'email'          => 'required|email|max:255',
            'rt'             => 'required|string|max:5',
            'rw'             => 'required|string|max:5',
            'desa'           => 'required|string|max:100',
            'kecamatan'      => 'required|string|max:100',
            'kabupaten'      => 'required|string|max:100',
            'propinsi'       => 'required|string|max:100',
            'penghasilan'    => 'required|in:01,02,03,04,05',
            'referensi_pendaftar' => 'nullable|string|max:150',
        ];
        $rules = array_merge(
            FormFieldConfig::sesuaikanAturan($konfig, $base, []),
            FormFieldConfig::aturanKustom($konfig, 'step5')
        );

        $request->validate($rules, [
            'nama_lengkap.required'   => 'Nama lengkap murid baru wajib diisi.',
            'nik.required'            => 'NIK wajib diisi.',
            'nik.digits'              => 'NIK harus terdiri dari 16 digit angka.',
            'no_kk.required'          => 'Nomor Kartu Keluarga (KK) wajib diisi.',
            'no_kk.digits'            => 'Nomor Kartu Keluarga harus 16 digit angka.',
            'nisn.digits'             => 'NISN harus terdiri dari 10 digit angka.',
            'jenis_kelamin.required'  => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.before'    => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'anak_ke.required'        => 'Anak ke berapa wajib diisi.',
            'jumlah_saudara.required' => 'Jumlah saudara kandung wajib diisi.',
            'tamat_tahun.required'    => 'Tahun tamat SMP/MTs wajib diisi.',
            'no_hp.required'          => 'Nomor telepon murid wajib diisi.',
            'email.required'          => 'Alamat email aktif wajib diisi.',
            'email.email'             => 'Format alamat email tidak valid.',
            'rt.required'             => 'RT wajib diisi.',
            'rw.required'             => 'RW wajib diisi.',
            'desa.required'           => 'Desa/Kelurahan wajib diisi.',
            'kecamatan.required'      => 'Kecamatan wajib diisi.',
            'kabupaten.required'      => 'Kabupaten/Kota wajib diisi.',
            'propinsi.required'       => 'Propinsi wajib diisi.',
            'penghasilan.required'    => 'Penghasilan bulanan orang tua wajib dipilih.',
            'penghasilan.in'          => 'Pilihan penghasilan bulanan tidak valid.',
        ]);

        $boleh = [
            'nisn', 'nik', 'no_kk', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir',
            'tanggal_lahir', 'agama', 'anak_ke', 'jumlah_saudara', 'asal_sekolah',
            'tamat_tahun', 'no_ijazah', 'no_hp', 'no_wa', 'email',
            'rt', 'rw', 'desa', 'kecamatan', 'kabupaten', 'propinsi',
            'penghasilan', 'referensi_pendaftar',
        ];
        $lama = Session::get('ppdb.step5', []);
        $data = $request->only($boleh);
        // Nama & NISN dikunci dari Langkah 1: bila hilang dari POST, pakai sesi Langkah 1 agar tidak wajib isi 2x.
        $step1addr = Session::get('ppdb.step1', []);
        foreach (['nama_lengkap', 'nisn'] as $k) {
            if (empty($data[$k]) && ! empty($step1addr[$k])) {
                $data[$k] = $step1addr[$k];
            }
        }

        // Field yang disembunyikan admin: pertahankan nilai lama agar data tidak hilang.
        foreach ($boleh as $k) {
            if (! array_key_exists($k, $data) || $data[$k] === null) {
                if (array_key_exists($k, $lama)) {
                    $data[$k] = $lama[$k];
                }
            }
            if (! FormFieldConfig::tampil($konfig, $k, true) && array_key_exists($k, $lama) && empty($data[$k])) {
                $data[$k] = $lama[$k];
            }
        }

        // Field kustom step5 -> data_kustom_step5.
        $data['data_kustom_step5'] = array_merge(
            $lama['data_kustom_step5'] ?? [],
            FormFieldConfig::ambilKustom($request, $konfig, 'step5')
        );

        // Alamat satu baris tetap disimpan untuk kartu, ekspor, dan data lama.
        $data['alamat'] = collect([
            ! empty($data['rt'] ?? null) || ! empty($data['rw'] ?? null) ? trim('RT ' . ($data['rt'] ?? '') . '/RW ' . ($data['rw'] ?? ''), '/ ') : null,
            ! empty($data['desa'] ?? null) ? 'Desa ' . $data['desa'] : null,
            ! empty($data['kecamatan'] ?? null) ? 'Kec. ' . $data['kecamatan'] : null,
            $data['kabupaten'] ?? null,
            $data['propinsi'] ?? null,
        ])->filter()->implode(', ');

        Session::put('ppdb.step5', $data);

        return redirect()->route('ppdb.step6');
    }

    // ─── Step 6: Data Orang Tua ──────────────────────────────────────
    public function step6()
    {
        $this->requireStep(5);
        $data = Session::get('ppdb.step6', []);
        $step5 = Session::get('ppdb.step5', []);
        // Prefill awal alamat ortu dari alamat siswa agar tidak ketik 2x (bisa diubah).
        foreach (['rt' => 'ortu_rt', 'rw' => 'ortu_rw', 'desa' => 'ortu_desa', 'kecamatan' => 'ortu_kecamatan', 'kabupaten' => 'ortu_kabupaten', 'propinsi' => 'ortu_propinsi'] as $dari => $ke) {
            if (empty($data[$ke]) && ! empty($step5[$dari])) {
                $data[$ke] = $step5[$dari];
            }
        }
        $konfig = FormFieldConfig::map();
        $kustomStep6 = FormFieldConfig::kustomUntuk($konfig, 'step6');
        return view('ppdb.step6', compact('data', 'step5', 'konfig', 'kustomStep6'));
    }

    public function step6Post(Request $request)
    {
        $this->requireStep(4);

        $konfig = FormFieldConfig::map();
        $base = [
            'nama_ayah'        => 'required|string|max:255',
            'ayah_ttl'         => 'required|string|max:150',
            'ayah_pendidikan'  => 'required|string|max:100',
            'pekerjaan_ayah'   => 'required|string|max:100',
            'nama_ibu'         => 'required|string|max:255',
            'ibu_ttl'          => 'required|string|max:150',
            'ibu_pendidikan'   => 'required|string|max:100',
            'pekerjaan_ibu'    => 'required|string|max:100',
            'no_hp_ortu'       => 'required|string|max:20',
            'ortu_rt'          => 'required|string|max:5',
            'ortu_rw'          => 'required|string|max:5',
            'ortu_desa'        => 'required|string|max:100',
            'ortu_kecamatan'   => 'required|string|max:100',
            'ortu_kabupaten'   => 'required|string|max:100',
            'ortu_propinsi'    => 'required|string|max:100',
        ];
        $rules = array_merge(
            FormFieldConfig::sesuaikanAturan($konfig, $base, []),
            FormFieldConfig::aturanKustom($konfig, 'step6')
        );

        $request->validate($rules, [
            'nama_ayah.required'       => 'Nama ayah wajib diisi.',
            'ayah_ttl.required'        => 'Tempat/tanggal lahir ayah wajib diisi.',
            'ayah_pendidikan.required' => 'Pendidikan terakhir ayah wajib diisi.',
            'pekerjaan_ayah.required'  => 'Pekerjaan ayah wajib diisi.',
            'nama_ibu.required'        => 'Nama ibu wajib diisi.',
            'ibu_ttl.required'         => 'Tempat/tanggal lahir ibu wajib diisi.',
            'ibu_pendidikan.required'  => 'Pendidikan terakhir ibu wajib diisi.',
            'pekerjaan_ibu.required'   => 'Pekerjaan ibu wajib diisi.',
            'no_hp_ortu.required'      => 'Nomor telepon wali/orang tua wajib diisi.',
            'ortu_rt.required'         => 'RT alamat orang tua wajib diisi.',
            'ortu_rw.required'         => 'RW alamat orang tua wajib diisi.',
            'ortu_desa.required'       => 'Desa/Kelurahan alamat orang tua wajib diisi.',
            'ortu_kecamatan.required'  => 'Kecamatan alamat orang tua wajib diisi.',
            'ortu_kabupaten.required'  => 'Kabupaten/Kota alamat orang tua wajib diisi.',
            'ortu_propinsi.required'   => 'Propinsi alamat orang tua wajib diisi.',
        ]);

        $boleh = [
            'nama_ayah', 'ayah_ttl', 'ayah_pendidikan', 'pekerjaan_ayah',
            'nama_ibu', 'ibu_ttl', 'ibu_pendidikan', 'pekerjaan_ibu',
            'no_hp_ortu', 'ortu_rt', 'ortu_rw', 'ortu_desa',
            'ortu_kecamatan', 'ortu_kabupaten', 'ortu_propinsi',
        ];
        $lama = Session::get('ppdb.step6', []);
        $data = $request->only($boleh);
        // Fallback: bila alamat ortu kosong (JS nonaktif), pakai alamat siswa Langkah 5 agar cukup isi sekali.
        $step5addr = Session::get('ppdb.step5', []);
        foreach (['rt' => 'ortu_rt', 'rw' => 'ortu_rw', 'desa' => 'ortu_desa', 'kecamatan' => 'ortu_kecamatan', 'kabupaten' => 'ortu_kabupaten', 'propinsi' => 'ortu_propinsi'] as $dari => $ke) {
            if (empty($data[$ke]) && ! empty($step5addr[$dari])) {
                $data[$ke] = $step5addr[$dari];
            }
        }
        foreach ($boleh as $k) {
            if ((! array_key_exists($k, $data) || $data[$k] === null) && array_key_exists($k, $lama)) {
                $data[$k] = $lama[$k];
            }
            if (! FormFieldConfig::tampil($konfig, $k, true) && array_key_exists($k, $lama) && empty($data[$k])) {
                $data[$k] = $lama[$k];
            }
        }
        $data['data_kustom_step6'] = array_merge(
            $lama['data_kustom_step6'] ?? [],
            FormFieldConfig::ambilKustom($request, $konfig, 'step6')
        );

        // Alamat orang tua satu baris untuk kartu, ekspor, dan tampilan admin.
        $data['alamat_ortu'] = collect([
            ! empty($data['ortu_rt'] ?? null) || ! empty($data['ortu_rw'] ?? null) ? trim('RT ' . ($data['ortu_rt'] ?? '') . '/RW ' . ($data['ortu_rw'] ?? ''), '/ ') : null,
            ! empty($data['ortu_desa'] ?? null) ? 'Desa ' . $data['ortu_desa'] : null,
            ! empty($data['ortu_kecamatan'] ?? null) ? 'Kec. ' . $data['ortu_kecamatan'] : null,
            $data['ortu_kabupaten'] ?? null,
            $data['ortu_propinsi'] ?? null,
        ])->filter()->implode(', ');

        Session::put('ppdb.step6', $data);

        return redirect()->route('ppdb.step7');
    }

    // ─── Step 7: Bagian B, C, D Formulir SPMB ───────────────────────
    public function step7()
    {
        $this->requireStep(6);

        $data    = Session::get('ppdb.step7', []);
        $step5   = Session::get('ppdb.step5', []);
        $jurusan = Jurusan::where('is_active', true)->orderBy('kode')->get();
        $konfig = FormFieldConfig::map();
        $kustomStep7 = FormFieldConfig::kustomUntuk($konfig, 'step7');

        return view('ppdb.step7', compact('data', 'step5', 'jurusan', 'konfig', 'kustomStep7'));
    }

    public function step7Post(Request $request)
    {
        $this->requireStep(6);

        $konfig = FormFieldConfig::map();
        $base = [
            'smp_kecamatan'    => 'required|string|max:100',
            'smp_kabupaten'    => 'required|string|max:100',
            'smp_propinsi'     => 'required|string|max:100',
            'ijazah_nomor_tahun' => 'nullable|string|max:100',
            'lama_belajar'     => 'required|string|max:10',
            'nilai_ujian'      => 'required|array',
            'nilai_ujian.*'    => 'nullable|numeric|min:0|max:100',
            'golongan_darah'   => 'required|string|max:5',
            'tinggi_badan'     => 'required|integer|min:80|max:250',
            'berat_badan'      => 'required|integer|min:20|max:200',
            'penyakit_pernah_diderita' => 'nullable|string|max:200',
            'hobi_olahraga'    => 'nullable|string|max:100',
            'hobi_kesenian'    => 'nullable|string|max:100',
            'hobi_organisasi'  => 'nullable|string|max:100',
            'hobi_prestasi'    => 'nullable|string|max:150',
            'hobi_lain'        => 'nullable|string|max:150',
            'ukuran_seragam'   => 'required|in:M,L,XL,XXL,XXXL,Jumbo',
        ];
        $rules = array_merge(
            FormFieldConfig::sesuaikanAturan($konfig, $base, []),
            FormFieldConfig::aturanKustom($konfig, 'step7')
        );

        $request->validate($rules, [
            'smp_kecamatan.required'    => 'Kecamatan asal SMP/MTs wajib diisi.',
            'smp_kabupaten.required'    => 'Kabupaten asal SMP/MTs wajib diisi.',
            'smp_propinsi.required'     => 'Propinsi asal SMP/MTs wajib diisi.',
            'lama_belajar.required'     => 'Lama belajar wajib diisi.',
            'nilai_ujian.required'      => 'Nilai ujian wajib diisi.',
            'nilai_ujian.*.numeric'     => 'Nilai ujian harus berupa angka.',
            'nilai_ujian.*.max'         => 'Nilai ujian maksimal 100.',
            'golongan_darah.required'   => 'Golongan darah wajib dipilih.',
            'tinggi_badan.required'     => 'Tinggi badan wajib diisi.',
            'berat_badan.required'      => 'Berat badan wajib diisi.',
            'ukuran_seragam.required'   => 'Ukuran baju/seragam wajib dipilih.',
            'ukuran_seragam.in'         => 'Pilihan ukuran seragam tidak valid.',
        ]);

        $boleh = [
            'smp_kecamatan', 'smp_kabupaten', 'smp_propinsi', 'ijazah_nomor_tahun',
            'lama_belajar', 'nilai_ujian', 'golongan_darah', 'tinggi_badan', 'berat_badan',
            'penyakit_pernah_diderita', 'hobi_olahraga', 'hobi_kesenian', 'hobi_organisasi',
            'hobi_prestasi', 'hobi_lain', 'ukuran_seragam',
        ];
        $lama = Session::get('ppdb.step7', []);
        $baru = $request->only($boleh);
        foreach ($boleh as $k) {
            if ((! array_key_exists($k, $baru) || $baru[$k] === null) && array_key_exists($k, $lama)) {
                $baru[$k] = $lama[$k];
            }
            if (! FormFieldConfig::tampil($konfig, $k, true) && array_key_exists($k, $lama) && empty($baru[$k])) {
                $baru[$k] = $lama[$k];
            }
        }
        $baru['data_kustom_step7'] = array_merge(
            $lama['data_kustom_step7'] ?? [],
            FormFieldConfig::ambilKustom($request, $konfig, 'step7')
        );
        Session::put('ppdb.step7', $baru);

        return redirect()->route('ppdb.step8');
    }

    // ─── Step 8: Review & Submit ─────────────────────────────────────
    public function step8()
    {
        $this->requireStep(7);

        $step4 = Session::get('ppdb.step4', []);
        $step5 = Session::get('ppdb.step5', []);
        $step6 = Session::get('ppdb.step6', []);
        $step7 = Session::get('ppdb.step7', []);
        $step2 = Session::get('ppdb.step2', []);
        $step3 = Session::get('ppdb.step3', []);

        $jurusan1 = isset($step4['jurusan_1_id']) ? Jurusan::find($step4['jurusan_1_id']) : null;
        $jurusan2 = isset($step4['jurusan_2_id']) ? Jurusan::find($step4['jurusan_2_id']) : null;

        $konfig = FormFieldConfig::map();
        $kustomStep8 = FormFieldConfig::kustomUntuk($konfig, 'step8');
        return view('ppdb.step8', compact('step4', 'step5', 'step6', 'step7', 'step2', 'step3', 'jurusan1', 'jurusan2', 'konfig', 'kustomStep8'));
    }

    public function submit(Request $request)
    {
        $this->requireStep(7);

        $konfig = FormFieldConfig::map();
        $fk = $konfig['konfirmasi_final'] ?? null;
        // Bila admin menyembunyikan/menonaktifkan konfirmasi, lewati validasi accepted.
        if ($fk && $fk->tampil && $fk->wajib) {
            $request->validate(['konfirmasi_final' => 'required|accepted']);
        }
        $rules8 = FormFieldConfig::aturanKustom($konfig, 'step8');
        if (! empty($rules8)) {
            $request->validate($rules8);
            Session::put(
                'ppdb.step8',
                ['data_kustom_step8' => array_merge(
                    Session::get('ppdb.step8.data_kustom_step8', []),
                    FormFieldConfig::ambilKustom($request, $konfig, 'step8')
                )]
            );
        }

        $step4 = Session::get('ppdb.step4', []);
        $step5 = Session::get('ppdb.step5', []);
        $step6 = Session::get('ppdb.step6', []);
        $step7 = Session::get('ppdb.step7', []);
        $step2 = Session::get('ppdb.step2', []);
        $step3 = Session::get('ppdb.step3', []);
        $nilaiRapor = Session::get('ppdb.nilai_rapor', []);

        // Hitung rekomendasi terbaik
        $jurusan = Jurusan::where('is_active', true)->get();
        $minat = $step4['minat'] ?? '';
        $skorTertinggi = 0;
        $rekomendasiId = null;
        $rekomendasiSkor = [];

        foreach ($jurusan as $j) {
            $skor = $j->hitungSkor($nilaiRapor, $minat);
            $rekomendasiSkor[$j->id] = $skor;
            if ($skor > $skorTertinggi) {
                $skorTertinggi = $skor;
                $rekomendasiId = $j->id;
            }
        }

        $nomorPendaftaran = Pendaftar::generateNomor();

        // Dokumen dipindahkan dari penyimpanan sementara ke penyimpanan permanen
        // (dan disalin ke Google Drive bila integrasinya aktif). Folder dibuat per
        // nomor pendaftaran supaya berkas satu siswa terkumpul rapi.
        $folderDokumen = 'dokumen/' . $nomorPendaftaran;

        $kk    = PenyimpananDokumen::pindahkan($step2['kk_path']    ?? null, $folderDokumen, 'kk',    $nomorPendaftaran);
        $akta  = PenyimpananDokumen::pindahkan($step2['akta_path']  ?? null, $folderDokumen, 'akta',  $nomorPendaftaran);
        $rapor = PenyimpananDokumen::pindahkan($step3['rapor_path'] ?? null, $folderDokumen, 'rapor', $nomorPendaftaran);

        // ID folder Drive untuk siswa ini (bila salah satu dokumen berhasil diunggah).
        $driveFolder = null;
        foreach ([$kk, $akta, $rapor] as $dok) {
            if (!empty($dok['path'])) {
                $driveFolder = GoogleDrive::subFolderId($nomorPendaftaran);
                break;
            }
        }

        $pendaftar = Pendaftar::create([
            'nomor_pendaftaran'     => $nomorPendaftaran,
            'periode'               => Pengaturan::get('tahun_ajaran', '2027/2028'),
            'sumber'                => 'daring',
            'nisn'                  => $step5['nisn'] ?? null,
            'nik'                   => $step5['nik'],
            'no_kk'                 => $step5['no_kk'] ?? null,
            'nama_lengkap'          => $step5['nama_lengkap'],
            'jenis_kelamin'         => $step5['jenis_kelamin'],
            'tempat_lahir'          => $step5['tempat_lahir'],
            'tanggal_lahir'         => $step5['tanggal_lahir'],
            'agama'                 => $step5['agama'],
            'anak_ke'               => $step5['anak_ke'] ?? null,
            'jumlah_saudara'        => $step5['jumlah_saudara'] ?? null,
            'alamat'                => $step5['alamat'] ?? collect([
                !empty($step5['rt']) && !empty($step5['rw']) ? "RT {$step5['rt']}/RW {$step5['rw']}" : null,
                !empty($step5['desa']) ? 'Desa ' . $step5['desa'] : null,
                !empty($step5['kecamatan']) ? 'Kec. ' . $step5['kecamatan'] : null,
                $step5['kabupaten'] ?? null,
                $step5['propinsi'] ?? null,
            ])->filter()->implode(', '),
            'rt'                    => $step5['rt'] ?? null,
            'rw'                    => $step5['rw'] ?? null,
            'desa'                  => $step5['desa'] ?? null,
            'kecamatan'             => $step5['kecamatan'] ?? null,
            'kabupaten'             => $step5['kabupaten'] ?? null,
            'propinsi'              => $step5['propinsi'] ?? null,
            'asal_sekolah'          => $step5['asal_sekolah'],
            'tamat_tahun'           => $step5['tamat_tahun'] ?? null,
            'no_ijazah'             => $step5['no_ijazah'] ?? null,
            'no_hp'                 => $step5['no_hp'],
            'no_wa'                 => $step5['no_wa'] ?? null,
            'email'                 => $step5['email'] ?? null,
            'penghasilan'           => $step5['penghasilan'] ?? null,
            'referensi_pendaftar'   => $step5['referensi_pendaftar'] ?? null,
            // Bagian B — keterangan pendidikan
            'smp_kecamatan'         => $step7['smp_kecamatan'] ?? null,
            'smp_kabupaten'         => $step7['smp_kabupaten'] ?? null,
            'smp_propinsi'          => $step7['smp_propinsi'] ?? null,
            'ijazah_nomor_tahun'    => $step7['ijazah_nomor_tahun'] ?? null,
            'lama_belajar'          => $step7['lama_belajar'] ?? null,
            'nilai_ujian'           => $step7['nilai_ujian'] ?? null,
            // Bagian C — keterangan kesehatan
            'golongan_darah'        => $step7['golongan_darah'] ?? null,
            'tinggi_badan'          => $step7['tinggi_badan'] ?? null,
            'berat_badan'           => $step7['berat_badan'] ?? null,
            'penyakit_pernah_diderita' => $step7['penyakit_pernah_diderita'] ?? null,
            // Bagian D — keterangan lain-lain
            'hobi_olahraga'         => $step7['hobi_olahraga'] ?? null,
            'hobi_kesenian'         => $step7['hobi_kesenian'] ?? null,
            'hobi_organisasi'       => $step7['hobi_organisasi'] ?? null,
            'hobi_prestasi'         => $step7['hobi_prestasi'] ?? null,
            'hobi_lain'             => $step7['hobi_lain'] ?? null,
            'ukuran_seragam'        => $step7['ukuran_seragam'] ?? null,
            // Data orang tua (Bagian A no. 14–15)
            'nama_ayah'             => $step6['nama_ayah'],
            'ayah_ttl'              => $step6['ayah_ttl'] ?? null,
            'ayah_pendidikan'       => $step6['ayah_pendidikan'] ?? null,
            'pekerjaan_ayah'        => $step6['pekerjaan_ayah'],
            'nama_ibu'              => $step6['nama_ibu'],
            'ibu_ttl'               => $step6['ibu_ttl'] ?? null,
            'ibu_pendidikan'        => $step6['ibu_pendidikan'] ?? null,
            'pekerjaan_ibu'         => $step6['pekerjaan_ibu'],
            'no_hp_ortu'            => $step6['no_hp_ortu'],
            'alamat_ortu'           => $step6['alamat_ortu'] ?? null,
            'ortu_rt'               => $step6['ortu_rt'] ?? null,
            'ortu_rw'               => $step6['ortu_rw'] ?? null,
            'ortu_desa'             => $step6['ortu_desa'] ?? null,
            'ortu_kecamatan'        => $step6['ortu_kecamatan'] ?? null,
            'ortu_kabupaten'        => $step6['ortu_kabupaten'] ?? null,
            'ortu_propinsi'         => $step6['ortu_propinsi'] ?? null,
            'jurusan_1_id'          => $step4['jurusan_1_id'] ?? null,
            'jurusan_2_id'          => $step4['jurusan_2_id'] ?? null,
            'rekomendasi_jurusan_id'=> $rekomendasiId,
            'rekomendasi_skor'      => $rekomendasiSkor,
            'kk_path'               => $kk['path'],
            'akta_path'             => $akta['path'],
            'rapor_path'            => $rapor['path'],
            'kk_drive_id'           => $kk['drive_id'],
            'akta_drive_id'         => $akta['drive_id'],
            'rapor_drive_id'        => $rapor['drive_id'],
            'drive_folder_id'       => $driveFolder,
            'nilai_rapor'           => $nilaiRapor ?: null,
            'minat'                 => $minat,
            'status_verifikasi'     => 'pending',
            // Jawaban field kustom (kunci kustom_*) dari panel admin, digabung semua langkah.
            'data_kustom'           => array_filter(array_merge(
                Session::get('ppdb.step1.data_kustom_step1', []),
                Session::get('ppdb.step2.data_kustom_step2', []),
                Session::get('ppdb.step3.data_kustom_step3', []),
                $step4['data_kustom_step4'] ?? [],
                $step5['data_kustom_step5'] ?? [],
                $step6['data_kustom_step6'] ?? [],
                $step7['data_kustom_step7'] ?? [],
                Session::get('ppdb.step8.data_kustom_step8', [])
            ), fn ($v) => $v !== null && $v !== '') ?: null,
        ]);

        // Kirim kode pendaftaran ke email calon siswa. Kegagalan pengiriman tidak
        // boleh membatalkan pendaftaran yang sudah tersimpan.
        $this->kirimKodePendaftaran($pendaftar);

        // Bersihkan session
        Session::forget(['ppdb']);

        return redirect()->route('ppdb.sukses', $pendaftar->nomor_pendaftaran);
    }

    /**
     * Kirim email berisi kode pendaftaran (sekali saja).
     *
     * `notifikasi_email_at` menjadi penjaga agar siswa tidak menerima email
     * ganda bila halaman submit dikirim ulang.
     */
    public function kirimKodePendaftaran(Pendaftar $pendaftar): void
    {
        if ($pendaftar->notifikasi_email_at) {
            return;
        }

        // Tanpa alamat email tidak ada yang bisa dikirimi; panitia tetap bisa
        // melihat kode pendaftaran dari panel admin.
        if (blank($pendaftar->email)) {
            return;
        }

        try {
            Mail::to($pendaftar->email)
                ->send(new KodePendaftaranMail($pendaftar, static::grupWaUrl()));

            $pendaftar->forceFill(['notifikasi_email_at' => now()])->save();
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email kode pendaftaran.', [
                'nomor' => $pendaftar->nomor_pendaftaran,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Tautan grup WhatsApp untuk calon siswa (dari pengaturan admin, atau .env).
     */
    public static function grupWaUrl(): ?string
    {
        return Pengaturan::get('wa_group_url')
            ?: config('services.ppdb.wa_group_url')
            ?: env('WA_GROUP_URL')
            ?: null;
    }

    // ─── Halaman Sukses ──────────────────────────────────────────────
    public function sukses(string $nomor)
    {
        $pendaftar = Pendaftar::where('nomor_pendaftaran', $nomor)
            ->with(['jurusan1', 'jurusan2', 'rekomendasiJurusan'])
            ->firstOrFail();

        $grupWaUrl = static::grupWaUrl();

        return view('ppdb.sukses', compact('pendaftar', 'grupWaUrl'));
    }

    // ─── Cetak Formulir (PDF sesuai formulir resmi SPMB) ─────────────
    /**
     * Unduh formulir pendaftaran siswa dalam bentuk PDF.
     *
     * Isi, urutan, dan tata letaknya disalin dari berkas resmi
     * "Formulir Pendaftaran SPMB 2026-2027" (kop surat + Bagian A–D +
     * blok tanda tangan + dua lembar Tanda Terima Berkas Persyaratan).
     * Nilai yang diisi siswa dicetak; kolom administratif (No. Pendaftaran
     * di kepala formulir, petugas, tanggal datang) dibiarkan sesuai berkas.
     */
    public function formulir(string $nomor)
    {
        $pendaftar = Pendaftar::where('nomor_pendaftaran', $nomor)
            ->with(['jurusan1', 'jurusan2'])
            ->firstOrFail();

        $pdf = Pdf::loadView('ppdb.formulir-pdf', compact('pendaftar'))
            ->setPaper([0, 0, 595.28, 935.43], 'portrait');

        $namaBerkas = 'formulir-pendaftaran-' . $pendaftar->nomor_pendaftaran . '.pdf';

        return $pdf->download($namaBerkas);
    }

    // ─── Cek Status ──────────────────────────────────────────────────
    public function cekStatus()
    {
        return view('ppdb.cek-status');
    }

    public function cekStatusPost(Request $request)
    {
        $request->validate([
            'nomor_pendaftaran' => 'required|string',
        ]);

        $pendaftar = Pendaftar::where('nomor_pendaftaran', $request->nomor_pendaftaran)
            ->with(['jurusan1', 'jurusan2'])
            ->first();

        return view('ppdb.cek-status', compact('pendaftar'));
    }

    // ─── Chatbot ─────────────────────────────────────────────────────
    public function chatbot(Request $request)
    {
        $request->validate(['query' => 'required|string|max:300']);

        $results = Faq::cari($request->query);

        $jawaban = null;
        $cukup = false;

        if (!empty($results)) {
            $top = $results[0];
            if ($top['cukup']) {
                $jawaban = $top['faq']->jawaban;
                $cukup = true;
            }
        }

        return response()->json([
            'jawaban' => $cukup ? $jawaban : null,
            'fallback' => !$cukup,
            'message' => $cukup ? $jawaban : 'Maaf, saya belum menemukan jawaban. Silakan hubungi panitia PPDB melalui nomor 081234567890 atau datang langsung ke sekolah.',
        ]);
    }

    // ─── Helper ──────────────────────────────────────────────────────
    private function requireStep(int $step): void
    {
        if ($step >= 1 && !Session::has('ppdb.step1')) {
            abort(redirect()->route('ppdb.step1'));
        }
        if ($step >= 4 && !Session::has('ppdb.step4')) {
            abort(redirect()->route('ppdb.step4'));
        }
        if ($step >= 5 && !Session::has('ppdb.step5')) {
            abort(redirect()->route('ppdb.step5'));
        }
        if ($step >= 6 && !Session::has('ppdb.step6')) {
            abort(redirect()->route('ppdb.step6'));
        }
        if ($step >= 7 && !Session::has('ppdb.step7')) {
            abort(redirect()->route('ppdb.step7'));
        }
    }
}
