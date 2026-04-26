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

        // 1. Ambil data profil (Untuk Nama & Foto Cloudinary)
        $profil = DB::table('profil_anggota')->where('id_user', $user->id_user)->first();
        $nama = $profil->nama_lengkap ?? 'User';
        $foto = $profil->foto_profil ?? 'https://ui-avatars.com/api/?name='.urlencode($nama);

        // --- DASHBOARD ADMIN & MANAJEMEN (Role 1 & 2) ---
if ($role_id == 1 || $role_id == 2) {
    // Statistik Dasar
    $total_siswa = DB::table('users')->where('id_role', 4)->count();
    $total_pelatih = DB::table('users')->where('id_role', 3)->count();
    $absensi_hari_ini = DB::table('absensi')->whereDate('tanggal', date('Y-m-d'))->count();
    
    // Ambil data kelas (Collection agar bisa pakai ->count())
    $data_kelas = DB::table('kelas')->get();

    // Statistik Kehadiran Bulan Ini (Untuk bagian Izin, Hadir, Alfa)
    $statistik = [
        'hadir' => DB::table('absensi')->whereMonth('tanggal', date('m'))->where('status', 'Hadir')->count(),
        'izin'  => DB::table('absensi')->whereMonth('tanggal', date('m'))->where('status', 'Izin')->count(),
        'alfa'  => DB::table('absensi')->whereMonth('tanggal', date('m'))->where('status', 'Alfa')->count(),
    ];

    // Kirim semua variabel ke view dashboard.admin
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

        // --- DASHBOARD PELATIH (Role 3 - Nabila) ---
        elseif ($role_id == 3) {
            $hari_indo = $this->getHariIndo(date('l'));
            
            // Ambil jadwal hari ini
            $jadwal_hari_ini = DB::table('jadwal_dev as j')
                ->join('kelas as k', 'j.id_kelas', '=', 'k.id_kelas')
                ->where('j.id_pelatih', $user->id_user)
                ->where('j.hari', $hari_indo)
                ->select('j.*', 'k.nama_kelas')
                ->get();

            // Total sesi mengajar yang sudah dilakukan (Hadir)
            $total_mengajar = DB::table('absensi')
                ->where('id_user', $user->id_user)
                ->where('status', 'Hadir')
                ->count();

            return view('dashboard.pelatih', compact('jadwal_hari_ini', 'total_mengajar', 'nama', 'foto'));
        }

        // --- DASHBOARD SISWA (Role 4) ---
        elseif ($role_id == 4) {
            $kelas_diikuti = DB::table('kelas_siswa as ks')
                ->join('kelas as k', 'ks.id_kelas', '=', 'k.id_kelas')
                ->where('ks.id_user', $user->id_user)
                ->select('k.nama_kelas')
                ->get();

            $riwayat_absensi = DB::table('absensi')
                ->where('id_user', $user->id_user)
                ->orderBy('tanggal', 'desc')
                ->limit(5)
                ->get();

            return view('dashboard.siswa', compact('kelas_diikuti', 'riwayat_absensi', 'nama', 'foto'));
        }

        return redirect('/login');
    }

    /**
     * Konversi Nama Hari ke Bahasa Indonesia
     */
    private function getHariIndo($day) {
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