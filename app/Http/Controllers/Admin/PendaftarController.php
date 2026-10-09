<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ParticipantsExport;
use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Pendaftar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Aksi operasional panitia PPDB: ekspor rekapitulasi, pengaturan kuota
 * daya tampung, input manual pendaftar, dan edit data teks pendaftar.
 *
 * Semua aksi di sini berasal dari kartu "Aksi & Ekspor" pada dashboard admin.
 */
class PendaftarController extends Controller
{
    /**
     * Kolom teks yang boleh diubah admin (berkas/dokumen read-only).
     */
    private const EDITABLE = [
        'nama_lengkap', 'nisn', 'nik', 'no_kk', 'jenis_kelamin',
        'tempat_lahir', 'tanggal_lahir', 'agama', 'anak_ke', 'jumlah_saudara',
        'alamat', 'rt', 'rw', 'desa', 'kecamatan', 'kabupaten', 'propinsi',
        'asal_sekolah', 'tamat_tahun', 'no_ijazah', 'no_hp', 'no_wa', 'email',
        'penghasilan', 'referensi_pendaftar',
        'nama_ayah', 'ayah_ttl', 'ayah_pendidikan', 'pekerjaan_ayah',
        'nama_ibu', 'ibu_ttl', 'ibu_pendidikan', 'pekerjaan_ibu',
        'no_hp_ortu', 'alamat_ortu', 'ortu_rt', 'ortu_rw', 'ortu_desa',
        'ortu_kecamatan', 'ortu_kabupaten', 'ortu_propinsi',
        'smp_kecamatan', 'smp_kabupaten', 'smp_propinsi',
        'ijazah_nomor_tahun', 'lama_belajar',
        'golongan_darah', 'tinggi_badan', 'berat_badan', 'penyakit_pernah_diderita',
        'hobi_olahraga', 'hobi_kesenian', 'hobi_organisasi', 'hobi_prestasi', 'hobi_lain',
        'ukuran_seragam', 'jurusan_1_id', 'jurusan_2_id', 'minat', 'status_verifikasi',
    ];
    /**
     * Aturan validasi bersama untuk data pendaftar (dipakai input manual).
     */
    private const RULES = [
        'nama_lengkap'   => 'required|string|min:3|max:255',
        'nik'            => 'required|digits:16',
        'nisn'           => 'nullable|digits:10',
        'jenis_kelamin'  => 'required|in:L,P',
        'tempat_lahir'   => 'required|string|max:100',
        'tanggal_lahir'  => 'required|date|before:today',
        'agama'          => 'required|string|max:30',
        'alamat'         => 'required|string|max:500',
        'asal_sekolah'   => 'required|string|max:200',
        'no_hp'          => 'required|string|max:20',
        'no_wa'          => 'nullable|string|max:20',
        'email'          => 'nullable|email|max:255',
        'nama_ayah'      => 'required|string|max:255',
        'pekerjaan_ayah' => 'required|string|max:100',
        'nama_ibu'       => 'required|string|max:255',
        'pekerjaan_ibu'  => 'required|string|max:100',
        'no_hp_ortu'     => 'required|string|max:20',
        'alamat_ortu'    => 'nullable|string|max:500',
        'jurusan_1_id'   => 'required|exists:jurusan,id',
        'jurusan_2_id'   => 'nullable|exists:jurusan,id|different:jurusan_1_id',
        'minat'          => 'nullable|string|max:100',
    ];

    /**
     * Pesan validasi berbahasa Indonesia.
     */
    private const MESSAGES = [
        'nama_lengkap.required' => 'Nama lengkap calon siswa wajib diisi.',
        'nik.required'          => 'NIK wajib diisi.',
        'nik.digits'            => 'NIK harus terdiri dari 16 digit angka.',
        'nisn.digits'           => 'NISN harus terdiri dari 10 digit angka.',
        'jenis_kelamin.required'=> 'Jenis kelamin wajib dipilih.',
        'tanggal_lahir.before'  => 'Tanggal lahir tidak boleh melebihi hari ini.',
        'jurusan_1_id.required' => 'Jurusan pilihan 1 wajib dipilih.',
        'jurusan_2_id.different'=> 'Jurusan pilihan 2 harus berbeda dari pilihan 1.',
    ];

    /* ─────────────────────────── Ekspor XLS ─────────────────────────── */

    /**
     * Unduh rekapitulasi data pendaftar dalam format XLSX.
     */
    public function exportXls(Request $request)
    {
        $query = Pendaftar::with(['jurusan1', 'jurusan2'])->orderBy('created_at', 'asc');

        if ($request->filled('status')) {
            $query->where('status_verifikasi', $request->input('status'));
        }

        $data = $query->get();

        $namaFile = 'rekap-pendaftar-ppdb-' . date('Ymd-His') . '.xlsx';

        return Excel::download(new ParticipantsExport($data), $namaFile);
    }

    /* ─────────────────────────── Kelola Kuota ────────────────────────── */

    /**
     * Halaman pengaturan kapasitas daya tampung per program keahlian.
     */
    public function kuotaIndex()
    {
        $jurusan = Jurusan::orderBy('kode')->get();

        $totalKuota = $jurusan->sum('kuota');
        $totalPendaftar = $jurusan->sum(fn ($j) => $j->pendaftar_count);

        return view('admin.kuota.index', compact('jurusan', 'totalKuota', 'totalPendaftar'));
    }

    /**
     * Simpan perubahan kuota seluruh program keahlian sekaligus.
     */
    public function kuotaUpdate(Request $request)
    {
        $validated = $request->validate([
            'kuota'   => 'required|array',
            'kuota.*' => 'required|integer|min:0|max:1000',
        ], [
            'kuota.*.required' => 'Jumlah kuota wajib diisi.',
            'kuota.*.integer'  => 'Kuota harus berupa angka bulat.',
            'kuota.*.min'      => 'Kuota tidak boleh negatif.',
            'kuota.*.max'      => 'Kuota maksimal 1000 kursi per jurusan.',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['kuota'] as $id => $jumlah) {
                Jurusan::whereKey($id)->update(['kuota' => $jumlah]);
            }
        });

        return redirect()
            ->route('admin.kuota.index')
            ->with('success', 'Kuota daya tampung berhasil diperbarui.');
    }

    /* ────────────────────────── Input Manual ────────────────────────── */

    /**
     * Input Manual — langsung lempar ke form formulir pendaftaran (wizard daring).
     * Admin mengisi sebagai siswa lewat alur normal agar data konsisten.
     */
    public function manualCreate()
    {
        return redirect()->route('ppdb.step1');
    }

    /**
     * Simpan pendaftar luring (bila form manual dipakai), lalu ke detail.
     */
    public function manualStore(Request $request)
    {
        $validated = $request->validate(self::RULES, self::MESSAGES);

        $pendaftar = DB::transaction(function () use ($validated) {
            return Pendaftar::create([
                'nomor_pendaftaran' => Pendaftar::generateNomor(),
                'periode'           => \App\Models\Pengaturan::get('tahun_ajaran', '2027/2028'),
                'sumber'            => 'luring',
                // Pendaftar luring sudah diinput & diverifikasi langsung oleh panitia.
                'status_verifikasi' => 'approved',
                'rekomendasi_jurusan_id' => null,
                'rekomendasi_skor'  => null,
                ...$validated,
            ]);
        });

        return redirect()
            ->route('admin.pendaftar.show', $pendaftar->id)
            ->with('success', "Pendaftar luring {$pendaftar->nama_lengkap} berhasil didaftarkan dengan nomor {$pendaftar->nomor_pendaftaran}.");
    }

    /* ────────────────────────── Detail Pendaftar ────────────────────── */

    /**
     * Halaman detail satu pendaftar: menampilkan seluruh isian formulir yang
     * diisi siswa (identitas, kontak, data orang tua, pilihan jurusan, nilai
     * rapor, rekomendasi, dan berkas) dalam satu tampilan read-only.
     */
    public function show(Pendaftar $pendaftar)
    {
        $pendaftar->load(['jurusan1', 'jurusan2', 'rekomendasiJurusan']);

        return view('admin.pendaftar.show', compact('pendaftar'));
    }

    /**
     * Form edit data teks pendaftar (berkas read-only, hanya field teks diubah).
     */
    public function edit(Pendaftar $pendaftar)
    {
        $pendaftar->load(['jurusan1', 'jurusan2']);
        $jurusan = Jurusan::where('is_active', true)->orderBy('kode')->get();

        return view('admin.pendaftar.edit', compact('pendaftar', 'jurusan'));
    }

    /**
     * Simpan perubahan data teks pendaftar.
     */
    public function update(Request $request, Pendaftar $pendaftar)
    {
        $validated = $request->validate([
            'nama_lengkap'   => 'required|string|min:3|max:255',
            'nisn'           => 'nullable|digits:10',
            'nik'            => 'required|digits:16',
            'no_kk'          => 'nullable|string|max:20',
            'jenis_kelamin'  => 'required|in:L,P',
            'tempat_lahir'   => 'nullable|string|max:100',
            'tanggal_lahir'  => 'nullable|date|before:today',
            'agama'          => 'nullable|string|max:30',
            'anak_ke'        => 'nullable|integer|min:1|max:30',
            'jumlah_saudara' => 'nullable|integer|min:0|max:30',
            'alamat'         => 'nullable|string|max:500',
            'rt' => 'nullable|string|max:10', 'rw' => 'nullable|string|max:10',
            'desa' => 'nullable|string|max:100', 'kecamatan' => 'nullable|string|max:100',
            'kabupaten' => 'nullable|string|max:100', 'propinsi' => 'nullable|string|max:100',
            'asal_sekolah'   => 'nullable|string|max:200',
            'tamat_tahun'    => 'nullable|string|max:10',
            'no_ijazah'      => 'nullable|string|max:100',
            'no_hp'          => 'required|string|max:20',
            'no_wa'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'penghasilan'    => 'nullable|string|max:100',
            'referensi_pendaftar' => 'nullable|string|max:255',
            'nama_ayah'      => 'nullable|string|max:255',
            'ayah_ttl'       => 'nullable|string|max:255',
            'ayah_pendidikan'=> 'nullable|string|max:100',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'nama_ibu'       => 'nullable|string|max:255',
            'ibu_ttl'        => 'nullable|string|max:255',
            'ibu_pendidikan' => 'nullable|string|max:100',
            'pekerjaan_ibu'  => 'nullable|string|max:100',
            'no_hp_ortu'     => 'nullable|string|max:20',
            'alamat_ortu'    => 'nullable|string|max:500',
            'ortu_rt' => 'nullable|string|max:10', 'ortu_rw' => 'nullable|string|max:10',
            'ortu_desa' => 'nullable|string|max:100', 'ortu_kecamatan' => 'nullable|string|max:100',
            'ortu_kabupaten' => 'nullable|string|max:100', 'ortu_propinsi' => 'nullable|string|max:100',
            'smp_kecamatan' => 'nullable|string|max:100', 'smp_kabupaten' => 'nullable|string|max:100',
            'smp_propinsi' => 'nullable|string|max:100',
            'ijazah_nomor_tahun' => 'nullable|string|max:100',
            'lama_belajar'   => 'nullable|string|max:50',
            'golongan_darah' => 'nullable|string|max:5',
            'tinggi_badan'   => 'nullable|integer|min:50|max:250',
            'berat_badan'    => 'nullable|integer|min:10|max:300',
            'penyakit_pernah_diderita' => 'nullable|string|max:255',
            'hobi_olahraga' => 'nullable|string|max:255', 'hobi_kesenian' => 'nullable|string|max:255',
            'hobi_organisasi' => 'nullable|string|max:255', 'hobi_prestasi' => 'nullable|string|max:255',
            'hobi_lain' => 'nullable|string|max:255',
            'ukuran_seragam' => 'nullable|string|max:10',
            'jurusan_1_id'   => 'required|exists:jurusan,id',
            'jurusan_2_id'   => 'nullable|exists:jurusan,id|different:jurusan_1_id',
            'minat'          => 'nullable|string|max:100',
            'status_verifikasi' => 'required|in:pending,approved,rejected,revisi',
            'nilai_ujian.b_indonesia' => 'nullable|numeric|min:0|max:100',
            'nilai_ujian.b_inggris' => 'nullable|numeric|min:0|max:100',
            'nilai_ujian.matematika' => 'nullable|numeric|min:0|max:100',
            'nilai_ujian.ipa' => 'nullable|numeric|min:0|max:100',
        ]);

        // Hanya kolom teks yang disimpan — berkas tidak tersentuh.
        $data = collect($validated)->only(self::EDITABLE)->toArray();
        if (isset($validated['nilai_ujian'])) {
            $data['nilai_ujian'] = $validated['nilai_ujian'];
        }

        $pendaftar->update($data);

        return redirect()
            ->route('admin.pendaftar.show', $pendaftar->id)
            ->with('success', 'Data pendaftar berhasil diperbarui.');
    }
}
