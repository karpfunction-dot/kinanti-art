<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total kelas
        $data_kelas = DB::table('kelas')->get();
        
        // Total siswa
        $total_siswa = DB::table('users as u')
            ->join('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('r.nama_role', 'siswa')
            ->count();
        
        // Total pelatih
        $total_pelatih = DB::table('users as u')
            ->join('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('r.nama_role', 'pelatih')
            ->count();
        
        // Total absensi hari ini
        $absensi_hari_ini = DB::table('absensi')
            ->whereDate('tanggal', date('Y-m-d'))
            ->count();
        
        // Statistik kehadiran bulan ini
        $bulan_ini = date('Y-m');
        $statistik = [
            'hadir' => DB::table('absensi')
                ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan_ini])
                ->where('status', 'Hadir')
                ->count(),
            'izin' => DB::table('absensi')
                ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan_ini])
                ->where('status', 'Izin')
                ->count(),
            'alfa' => DB::table('absensi')
                ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan_ini])
                ->where('status', 'Alfa')
                ->count(),
        ];
        
        return view('dashboard', compact('data_kelas', 'total_siswa', 'total_pelatih', 'absensi_hari_ini', 'statistik'));
    }
}