<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role_id = $user->id_role; // 1:Admin, 2:Manajemen, 3:Pelatih, 4:Siswa
        $bulan_ini = date('Y-m');

        // --- DATA UNTUK ADMIN & MANAJEMEN (ROLE 1 & 2) ---
        if ($role_id == 1 || $role_id == 2) {
            $data_kelas = DB::table('kelas')->get();
            $total_siswa = DB::table('users')->where('id_role', 4)->count();
            $total_pelatih = DB::table('users')->where('id_role', 3)->count();
            $absensi_hari_ini = DB::table('absensi')->whereDate('tanggal', date('Y-m-d'))->count();
            
            $statistik = [
                'hadir' => DB::table('absensi')->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan_ini])->where('status', 'Hadir')->count(),
                'izin' => DB::table('absensi')->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan_ini])->where('status', 'Izin')->count(),
                'alfa' => DB::table('absensi')->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan_ini])->where('status', 'Alfa')->count(),
            ];

            return view('dashboard.admin', compact('data_kelas', 'total_siswa', 'total_pelatih', 'absensi_hari_ini', 'statistik'));
        }

        // --- DATA UNTUK PELATIH (ROLE 3) ---
        elseif ($role_id == 3) {
            // Jadwal Mengajar Nabila hari ini
            $hari_ini = $this->getHariIndo(date('l'));
            $jadwal_saya = DB::table('jadwal as j')
                ->join('kelas as k', 'j.id_kelas', '=', 'k.id_kelas')
                ->where('j.id_pelatih', $user->id_user)
                ->where('j.hari', $hari_ini)
                ->select('j.*', 'k.nama_kelas')
                ->get();

            $total_sesi = DB::table('absensi')->where('id_user', $user->id_user)->where('status', 'Hadir')->count();

            return view('dashboard.pelatih', compact('jadwal_saya', 'total_sesi'));
        }

        // --- DATA UNTUK SISWA (ROLE 4) ---
        elseif ($role_id == 4) {
            // Riwayat absensi siswa itu sendiri
            $riwayat_absensi = DB::table('absensi')
                ->where('id_user', $user->id_user)
                ->orderBy('tanggal', 'desc')
                ->limit(5)
                ->get();

            $kehadiran_bulan_ini = DB::table('absensi')
                ->where('id_user', $user->id_user)
                ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan_ini])
                ->where('status', 'Hadir')
                ->count();

            return view('dashboard.siswa', compact('riwayat_absensi', 'kehadiran_bulan_ini'));
        }

        return redirect('/login');
    }

    // Helper untuk konversi hari ke Bahasa Indonesia
    private function getHariIndo($day) {
        $days = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        return $days[$day] ?? $day;
    }
}