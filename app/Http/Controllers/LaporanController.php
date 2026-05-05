<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Enums\RoleType;

class LaporanController extends Controller
{
    /**
     * Reusable date range logic for performance optimization.
     */
    private function getDateRange(string $bulan): array
    {
        $start = $bulan . '-01';
        $end = date('Y-m-t', strtotime($start));
        return [$start, $end];
    }

    /**
     * Display attendance report page.
     */
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        $role = $request->get('role', '');
        $kelas = $request->get('kelas', '');
        
        $dateRange = $this->getDateRange($bulan);
        
        // Get available classes for filter
        $kelasList = DB::table('kelas')
            ->select('id_kelas', 'nama_kelas')
            ->orderBy('nama_kelas')
            ->get();
        
        // Get available roles for filter
        $roleList = DB::table('roles')
            ->select('nama_role')
            ->where('nama_role', '!=', RoleType::ADMIN->label())
            ->get();
        
        // ==================== REKAP PER SISWA (untuk evaluasi) ====================
        $rekapSiswa = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->leftJoin('kelas_siswa as ks', 'ks.id_user', '=', 'u.id_user')
            ->leftJoin('kelas as k', 'k.id_kelas', '=', 'ks.id_kelas')
            ->leftJoin('absensi as a', function($join) use ($dateRange) {
                $join->on('a.id_user', '=', 'u.id_user')
                     ->whereBetween('a.tanggal', $dateRange)
                     ->where('a.status', 'Hadir');
            })
            ->where('u.id_role', RoleType::SISWA->value)
            ->select(
                'u.id_user',
                'p.nama_lengkap',
                'u.kode_barcode',
                'k.nama_kelas',
                DB::raw('COUNT(DISTINCT a.id_absensi) as total_hadir'),
                DB::raw('COUNT(DISTINCT CASE WHEN a.status = "Hadir" THEN a.id_absensi END) as hadir'),
                DB::raw('COUNT(DISTINCT CASE WHEN a.status = "Izin" THEN a.id_absensi END) as izin'),
                DB::raw('COUNT(DISTINCT CASE WHEN a.status = "Alfa" THEN a.id_absensi END) as alfa')
            )
            ->groupBy('u.id_user', 'p.nama_lengkap', 'u.kode_barcode', 'k.nama_kelas');
        
        if ($kelas) {
            $rekapSiswa->where('k.id_kelas', $kelas);
        }
        
        $rekapSiswa = $rekapSiswa->orderBy('p.nama_lengkap')->get();
        
        // ==================== REKAP PER PELATIH ====================
        $rekapPelatih = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->leftJoin('absensi as a', function($join) use ($dateRange) {
                $join->on('a.id_user', '=', 'u.id_user')
                     ->whereBetween('a.tanggal', $dateRange)
                     ->where('a.status', 'Hadir');
            })
            ->where('u.id_role', RoleType::PELATIH->value)
            ->select(
                'u.id_user',
                'p.nama_lengkap',
                'u.kode_barcode',
                DB::raw('COUNT(DISTINCT a.id_absensi) as total_hadir'),
                DB::raw('COUNT(DISTINCT CASE WHEN a.status = "Hadir" THEN a.id_absensi END) as hadir'),
                DB::raw('COUNT(DISTINCT CASE WHEN a.status = "Izin" THEN a.id_absensi END) as izin'),
                DB::raw('COUNT(DISTINCT CASE WHEN a.status = "Alfa" THEN a.id_absensi END) as alfa')
            )
            ->groupBy('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
            ->orderBy('p.nama_lengkap')
            ->get();
        
        // ==================== REKAP PER KELAS ====================
        $rekapPerKelas = DB::table('kelas as k')
            ->leftJoin('kelas_siswa as ks', 'ks.id_kelas', '=', 'k.id_kelas')
            ->leftJoin('users as u', 'u.id_user', '=', 'ks.id_user')
            ->leftJoin('absensi as a', function($join) use ($dateRange) {
                $join->on('a.id_user', '=', 'u.id_user')
                     ->whereBetween('a.tanggal', $dateRange)
                     ->where('a.status', 'Hadir');
            })
            ->select(
                'k.id_kelas',
                'k.nama_kelas',
                DB::raw('COUNT(DISTINCT u.id_user) as total_siswa'),
                DB::raw('COUNT(DISTINCT a.id_absensi) as total_kehadiran')
            )
            ->groupBy('k.id_kelas', 'k.nama_kelas')
            ->orderBy('k.nama_kelas')
            ->get();
        
        // ==================== DETAIL ABSENSI HARIAN ====================
        $detailAbsensiQuery = DB::table('absensi as a')
            ->leftJoin('users as u', 'a.id_user', '=', 'u.id_user')
            ->leftJoin('profil_anggota as p', 'p.id_user', '=', 'a.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->leftJoin('kelas_siswa as ks', 'ks.id_user', '=', 'u.id_user')
            ->leftJoin('kelas as k', 'k.id_kelas', '=', 'ks.id_kelas')
            ->select(
                'a.id_absensi', 'a.tanggal', 'a.waktu', 'a.status', 'a.kategori', 'a.lokasi',
                'p.nama_lengkap', 'u.kode_barcode', 'r.nama_role as role_name', 'k.nama_kelas'
            );
        
        if ($bulan) {
            $detailAbsensiQuery->whereBetween('a.tanggal', $dateRange);
        }
        
        if ($role) {
            $detailAbsensiQuery->where('r.nama_role', $role);
        }
        
        if ($kelas) {
            $detailAbsensiQuery->where('k.id_kelas', $kelas);
        }
        
        $detailAbsensi = $detailAbsensiQuery->orderBy('a.tanggal', 'desc')
            ->orderBy('a.waktu', 'desc')
            ->get();
        
        // ==================== STATISTIK UMUM ====================
        $statistik = [
            'total_hadir' => DB::table('absensi')
                ->whereBetween('tanggal', $dateRange)
                ->where('status', 'Hadir')
                ->count(),
            'total_izin' => DB::table('absensi')
                ->whereBetween('tanggal', $dateRange)
                ->where('status', 'Izin')
                ->count(),
            'total_alfa' => DB::table('absensi')
                ->whereBetween('tanggal', $dateRange)
                ->where('status', 'Alfa')
                ->count(),
            'total_siswa_aktif' => DB::table('users')
                ->where('id_role', RoleType::SISWA->value)
                ->count(),
            'total_pelatih_aktif' => DB::table('users')
                ->where('id_role', RoleType::PELATIH->value)
                ->count(),
        ];
        
        // Get month name in Indonesian
        $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $bulanText = $monthNames[date('m', strtotime($bulan))] . ' ' . date('Y', strtotime($bulan));
        
        // Get selected class name
        $selectedKelas = $kelas ? DB::table('kelas')->select('id_kelas', 'nama_kelas')->where('id_kelas', $kelas)->first() : null;
        
        return view('laporan.index', compact(
            'rekapSiswa', 'rekapPelatih', 'rekapPerKelas', 'detailAbsensi',
            'bulan', 'role', 'kelas', 'kelasList', 'roleList',
            'statistik', 'bulanText', 'selectedKelas'
        ));
    }
    
    /**
     * Export attendance report to PDF.
     */
    public function exportPdf(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        $role = $request->get('role', '');
        $kelas = $request->get('kelas', '');
        
        $dateRange = $this->getDateRange($bulan);
        
        // Rekap per siswa
        $rekapSiswaQuery = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->leftJoin('kelas_siswa as ks', 'ks.id_user', '=', 'u.id_user')
            ->leftJoin('kelas as k', 'k.id_kelas', '=', 'ks.id_kelas')
            ->leftJoin('absensi as a', function($join) use ($dateRange) {
                $join->on('a.id_user', '=', 'u.id_user')
                     ->whereBetween('a.tanggal', $dateRange)
                     ->where('a.status', 'Hadir');
            })
            ->where('u.id_role', RoleType::SISWA->value)
            ->select(
                'p.nama_lengkap',
                'k.nama_kelas',
                DB::raw('COUNT(DISTINCT a.id_absensi) as total_hadir')
            )
            ->groupBy('p.nama_lengkap', 'k.nama_kelas');
        
        if ($kelas) {
            $rekapSiswaQuery->where('k.id_kelas', $kelas);
        }
        
        $rekapSiswa = $rekapSiswaQuery->orderBy('p.nama_lengkap')->get();
        
        // Statistik
        $statistik = [
            'total_hadir' => DB::table('absensi')
                ->whereBetween('tanggal', $dateRange)
                ->where('status', 'Hadir')
                ->count(),
            'total_siswa' => $rekapSiswa->count(),
        ];
        
        $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $bulanText = $monthNames[date('m', strtotime($bulan))] . ' ' . date('Y', strtotime($bulan));
        
        $data = compact('rekapSiswa', 'statistik', 'bulanText', 'kelas');
        $pdf = Pdf::loadView('laporan.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('Laporan_Absensi_Siswa_' . $bulanText . '.pdf');
    }
}