<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Support\PhotoUrl;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role_id = $user->id_role; 

        // Ambil data profil
        $profil = DB::table('profil_anggota')->where('id_user', $user->id_user)->first();
        $nama = $profil->nama_lengkap ?? 'User';
        $foto = PhotoUrl::resolve($profil->foto_profil ?? null);

        // ==============================
        // ADMIN & MANAJEMEN (Role 1 & 2)
        // ==============================
        if ($role_id == 1 || $role_id == 2) {

            $total_siswa = DB::table('users')->where('id_role', 4)->count();
            $total_pelatih = DB::table('users')->where('id_role', 3)->count();
            $absensi_hari_ini = DB::table('absensi')->whereDate('tanggal', date('Y-m-d'))->count();
            $data_kelas = DB::table('kelas')->get();

            $statistik = [
                'hadir' => DB::table('absensi')->whereMonth('tanggal', date('m'))->where('status', 'Hadir')->count(),
                'izin'  => DB::table('absensi')->whereMonth('tanggal', date('m'))->where('status', 'Izin')->count(),
                'alfa'  => DB::table('absensi')->whereMonth('tanggal', date('m'))->where('status', 'Alfa')->count(),
            ];

            return view('dashboard.admin', compact(
                'total_siswa', 
                'total_pelatih', 
                'absensi_hari_ini', 
                'data_kelas', 
                'nama', 
                'foto', 
                'statistik'
            ));
        }

        // ==============================
        // PELATIH (Role 3)
        // ==============================
        elseif ($role_id == 3) {

            $hari_indo = $this->getHariIndo(date('l'));

            $jadwal_hari_ini = DB::table('jadwal_dev as j')
                ->join('kelas as k', 'j.id_kelas', '=', 'k.id_kelas')
                ->where('j.id_pelatih', $user->id_user)
                ->where('j.hari', $hari_indo)
                ->select('j.*', 'k.nama_kelas')
                ->get();

            $total_mengajar = DB::table('absensi')
                ->where('id_user', $user->id_user)
                ->where('status', 'Hadir')
                ->count();

            return view('dashboard.pelatih', compact(
                'jadwal_hari_ini', 
                'total_mengajar', 
                'nama', 
                'foto'
            ));
        }

        // ==============================
        // SISWA (Role 4) 🔥 FIX DI SINI
        // ==============================
        elseif ($role_id == 4) {

            $userId = $user->id_user;

            $kelas_diikuti = DB::table('kelas_siswa as ks')
                ->join('kelas as k', 'ks.id_kelas', '=', 'k.id_kelas')
                ->where('ks.id_user', $userId)
                ->select('k.nama_kelas')
                ->get();

            $riwayat_absensi = DB::table('absensi')
                ->where('id_user', $userId)
                ->orderBy('tanggal', 'desc')
                ->limit(5)
                ->get();

            // 🔥 INI YANG SEBELUMNYA HILANG
            $kehadiran_bulan_ini = DB::table('absensi')
                ->where('id_user', $userId)
                ->whereMonth('tanggal', Carbon::now()->month)
                ->whereYear('tanggal', Carbon::now()->year)
                ->where('status', 'Hadir')
                ->count();

            return view('dashboard.siswa', compact(
                'kelas_diikuti',
                'riwayat_absensi',
                'kehadiran_bulan_ini',
                'nama',
                'foto'
            ));
        }

        return redirect('/login');
    }

    /**
     * Konversi Hari ke Bahasa Indonesia
     */
    private function getHariIndo($day)
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];

        return $days[$day] ?? $day;
    }
}