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
        $role_id = $user->id_role; 
        $bulan_ini = date('Y-m');

        // 1 & 2. ADMIN & MANAJEMEN (Dashboard Umum)
        if ($role_id == 1 || $role_id == 2) {
            $total_siswa = DB::table('users')->where('id_role', 4)->count();
            $total_pelatih = DB::table('users')->where('id_role', 3)->count();
            $absensi_hari_ini = DB::table('absensi')->whereDate('tanggal', date('Y-m-d'))->count();
            $data_kelas = DB::table('kelas')->get();

            return view('dashboard.admin', compact('total_siswa', 'total_pelatih', 'absensi_hari_ini', 'data_kelas'));
        }

        // 3. PELATIH (Dashboard Personal Nabila)
        elseif ($role_id == 3) {
            $hari_indo = $this->getHariIndo(date('l'));
            
            // Ambil jadwal dari jadwal_dev
            $jadwal_saya = DB::table('jadwal_dev as j')
                ->join('kelas as k', 'j.id_kelas', '=', 'k.id_kelas')
                ->where('j.id_pelatih', $user->id_user)
                ->where('j.hari', $hari_indo)
                ->select('j.*', 'k.nama_kelas')
                ->get();

            $total_mengajar = DB::table('absensi')
                ->where('id_user', $user->id_user)
                ->where('status', 'Hadir')
                ->count();

            return view('dashboard.pelatih', compact('jadwal_saya', 'total_mengajar'));
        }

        // 4. SISWA (Dashboard Personal Siswa)
        elseif ($role_id == 4) {
            // Cek kelas yang diikuti dari tabel kelas_siswa
            $kelas_diikuti = DB::table('kelas_siswa as ks')
                ->join('kelas as k', 'ks.id_kelas', '=', 'k.id_kelas')
                ->where('ks.id_user', $user->id_user)
                ->where('ks.aktif', 1)
                ->select('k.nama_kelas')
                ->get();

            $riwayat_absensi = DB::table('absensi')
                ->where('id_user', $user->id_user)
                ->orderBy('tanggal', 'desc')
                ->limit(5)
                ->get();

            return view('dashboard.siswa', compact('kelas_diikuti', 'riwayat_absensi'));
        }

        return redirect('/login');
    }

    private function getHariIndo($day) {
        $days = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
        return $days[$day] ?? 'Sabtu';
    }
}