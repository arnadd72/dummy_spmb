<?php

namespace App\Http\Controllers;

use App\Models\Pendaftar;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Exports\ParticipantsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Berita;
use App\Models\Event;
use App\Models\Faq;
use App\Models\Galeri;

class AdminController extends Controller
{
    /**
     * Dashboard Admin
     */
    public function dashboard(Request $request)
    {
        // Statistik peserta
        $totalPendaftar = Pendaftar::count();

        $menungguVerifikasi = Pendaftar::where(
            'status_verifikasi',
            'pending'
        )->count();

        $diterima = Pendaftar::where(
            'status_verifikasi',
            'approved'
        )->count();

        $revisi = Pendaftar::whereIn(
            'status_verifikasi',
            ['revision', 'rejected']
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Data Jurusan
        |--------------------------------------------------------------------------
        */

        $jurusanList = Jurusan::where('is_active', true)
            ->get()
            ->map(function ($j) {

                $count = Pendaftar::where(
                    'jurusan_1_id',
                    $j->id
                )->count();

                $kuota = max(0, (int) $j->kuota);

                return [
                    'id' => $j->id,
                    'kode' => $j->kode,
                    'nama' => $j->nama,
                    'kuota' => $kuota,
                    'pendaftar_count' => $count,
                    'sisa_kuota' => max(0, $kuota - $count),
                    'persentase' => $kuota > 0
                        ? min(100, round(($count / $kuota) * 100))
                        : 0,
                ];
            });

        // Total daya tampung seluruh program keahlian (dipakai pada header kartu kuota).
        $totalKuota = $jurusanList->sum('kuota');

        /*
        |--------------------------------------------------------------------------
        | Peserta Terbaru
        |--------------------------------------------------------------------------
        */

        $recentPendaftar = Pendaftar::with([
                'jurusan1',
                'jurusan2'
            ])
            ->latest('created_at')
            ->take(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Demo Data
        |--------------------------------------------------------------------------
        |
        | Jika database masih kosong, dashboard akan menampilkan
        | data contoh agar tampilan tidak kosong.
        |
        */

        $isDemo = false;

        if ($totalPendaftar === 0) {

            $isDemo = true;

            $totalPendaftar = 142;
            $menungguVerifikasi = 18;
            $diterima = 116;
            $revisi = 8;

            $jurusanList = collect([
                [
                    'id' => 1,
                    'kode' => 'TKR',
                    'nama' => 'Teknik Kendaraan Ringan Otomotif',
                    'kuota' => 108,
                    'pendaftar_count' => 54,
                    'sisa_kuota' => 54,
                    'persentase' => 50,
                ],
                [
                    'id' => 2,
                    'kode' => 'FKK',
                    'nama' => 'Farmasi Klinis dan Komunitas',
                    'kuota' => 72,
                    'pendaftar_count' => 38,
                    'sisa_kuota' => 34,
                    'persentase' => 53,
                ],
                [
                    'id' => 3,
                    'kode' => 'AKL',
                    'nama' => 'Akuntansi dan Keuangan Lembaga',
                    'kuota' => 72,
                    'pendaftar_count' => 28,
                    'sisa_kuota' => 44,
                    'persentase' => 39,
                ],
                [
                    'id' => 4,
                    'kode' => 'MPLB',
                    'nama' => 'Manajemen Perkantoran & Bisnis',
                    'kuota' => 72,
                    'pendaftar_count' => 22,
                    'sisa_kuota' => 50,
                    'persentase' => 31,
                ],
            ]);

            $totalKuota = $jurusanList->sum('kuota');

            $recentPendaftar = collect([
                (object) [
                    'nomor_pendaftaran' => 'PPDB-2026-00142',
                    'nama_lengkap' => 'Muhammad Rizky Pratama',
                    'nisn' => '0081234567',
                    'asal_sekolah' => 'SMP Negeri 1 Wangon',
                    'jurusan1' => (object) [
                        'kode' => 'TKR',
                        'nama' => 'Teknik Kendaraan Ringan',
                    ],
                    'status_verifikasi' => 'pending',
                    'created_at' => Carbon::now()->subMinutes(12),
                ],

                (object) [
                    'nomor_pendaftaran' => 'PPDB-2026-00141',
                    'nama_lengkap' => 'Aisyah Putri Azzahra',
                    'nisn' => '0089876543',
                    'asal_sekolah' => 'MTs Ma\'arif NU 1 Wangon',
                    'jurusan1' => (object) [
                        'kode' => 'FKK',
                        'nama' => 'Farmasi Klinis & Komunitas',
                    ],
                    'status_verifikasi' => 'approved',
                    'created_at' => Carbon::now()->subHours(1),
                ],

                (object) [
                    'nomor_pendaftaran' => 'PPDB-2026-00140',
                    'nama_lengkap' => 'Bagas Dwi Saputra',
                    'nisn' => '0084567890',
                    'asal_sekolah' => 'SMP Negeri 2 Jatilawang',
                    'jurusan1' => (object) [
                        'kode' => 'TKR',
                        'nama' => 'Teknik Kendaraan Ringan',
                    ],
                    'status_verifikasi' => 'approved',
                    'created_at' => Carbon::now()->subHours(3),
                ],

                (object) [
                    'nomor_pendaftaran' => 'PPDB-2026-00139',
                    'nama_lengkap' => 'Nabila Khairunnisa',
                    'nisn' => '0082345678',
                    'asal_sekolah' => 'SMP Ma\'arif NU 2 Rawalo',
                    'jurusan1' => (object) [
                        'kode' => 'AKL',
                        'nama' => 'Akuntansi & Keuangan',
                    ],
                    'status_verifikasi' => 'revision',
                    'created_at' => Carbon::now()->subHours(5),
                ],

                (object) [
                    'nomor_pendaftaran' => 'PPDB-2026-00138',
                    'nama_lengkap' => 'Fajar Alamsyah',
                    'nisn' => '0086789012',
                    'asal_sekolah' => 'SMP Negeri 1 Lumbir',
                    'jurusan1' => (object) [
                        'kode' => 'MPLB',
                        'nama' => 'Manajemen Perkantoran',
                    ],
                    'status_verifikasi' => 'approved',
                    'created_at' => Carbon::now()->subHours(8),
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Statistik Pendaftar per Tahun (untuk grafik AI admin)
        |--------------------------------------------------------------------------
        */

        $statistikTahun = Pendaftar::selectRaw('SUBSTR(created_at, 1, 4) as tahun, COUNT(*) as jumlah')
            ->groupBy('tahun')
            ->orderBy('tahun', 'asc')
            ->get();

        if ($isDemo && $statistikTahun->isEmpty()) {
            $statistikTahun = collect([
                (object) ['tahun' => (int) date('Y') - 2, 'jumlah' => 98],
                (object) ['tahun' => (int) date('Y') - 1, 'jumlah' => 121],
                (object) ['tahun' => (int) date('Y'), 'jumlah' => $totalPendaftar],
            ]);
        }

        return view(
            'admin.dashboard',
            compact(
                'totalPendaftar',
                'menungguVerifikasi',
                'diterima',
                'revisi',
                'jurusanList',
                'totalKuota',
                'recentPendaftar',
                'isDemo',
                'statistikTahun'
            )
        );
    }

    /**
     * Manajemen Peserta
     */
    public function participants(Request $request)
    {
        $query = Pendaftar::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {

                $q->where(
                    'nama_lengkap',
                    'like',
                    '%' . $search . '%'
                )
                ->orWhere(
                    'nik',
                    'like',
                    '%' . $search . '%'
                )
                ->orWhere(
                    'nisn',
                    'like',
                    '%' . $search . '%'
                )
                ->orWhere(
                    'no_hp',
                    'like',
                    '%' . $search . '%'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status_verifikasi',
                $request->input('status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Export
        |--------------------------------------------------------------------------
        */

        if ($request->filled('export')) {

            $format = $request->input('export');

            $data = $query
                ->orderBy('created_at', 'asc')
                ->get();

            if ($format === 'excel') {

                return Excel::download(
                    new ParticipantsExport($data),
                    'peserta.xlsx'
                );
            }

            if ($format === 'pdf') {

                $pdf = Pdf::loadView(
                    'admin.participants_pdf',
                    [
                        'participants' => $data
                    ]
                );

                return $pdf->download('peserta.pdf');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $participants = $query
            ->orderBy('created_at', 'asc')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.participants',
            compact('participants')
        );
    }

    /**
     * Verifikasi Dokumen
     */
    public function documents()
    {
        $pendaftar = Pendaftar::with([
            'jurusan1',
            'jurusan2'
        ])
        ->orderBy('created_at', 'asc')
        ->get();

        return view(
            'admin.documents',
            compact('pendaftar')
        );
    }

    /**
     * Approve dokumen
     */
    public function approveDocument(Request $request, $id)
    {
        $pendaftar = Pendaftar::findOrFail($id);

        $pendaftar->status_verifikasi = 'approved';

        $pendaftar->save();

        return redirect()
            ->back()
            ->with(
                'success',
                'Berkas berhasil disetujui.'
            );
    }

    /**
     * Reject dokumen
     */
    public function rejectDocument(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'nullable|string|max:1000',
        ]);

        $pendaftar = Pendaftar::findOrFail($id);

        $pendaftar->status_verifikasi = 'rejected';

        if ($request->filled('alasan')) {
            // catatan_penolakan menyimpan alasan teknis; catatan_verifikasi dipakai
            // oleh halaman "Cek Status" milik calon siswa agar alasannya terlihat.
            $pendaftar->catatan_penolakan = $request->input('alasan');
            $pendaftar->catatan_verifikasi = $request->input('alasan');
        }

        $pendaftar->save();

        return redirect()
            ->back()
            ->with(
                'success',
                'Berkas berhasil ditolak.'
            );
    }
}