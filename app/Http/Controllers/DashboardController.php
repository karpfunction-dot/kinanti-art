<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Support\PhotoUrl;
use App\Constants\RoleConstant;
use App\Services\MenuService;
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

        // Get menu data untuk semua view
        $menuData = MenuService::getMenuTree($role_id);

        // ==============================
        // ADMIN & MANAJEMEN (Role 1 & 2)
        // ==============================
        if ($role_id === RoleConstant::ADMIN || $role_id === RoleConstant::MANAJEMEN) {

            $total_siswa = DB::table('users')->where('id_role', RoleConstant::SISWA)->count();
            $total_pelatih = DB::table('users')->where('id_role', RoleConstant::PELATIH)->count();
            $absensi_hari_ini = DB::table('absensi')->whereDate('tanggal', date('Y-m-d'))->count();
            $data_kelas = DB::table('kelas')->get();

            $now = Carbon::now();
            $statistik = [
                'hadir' => DB::table('absensi')
                    ->whereMonth('tanggal', $now->month)
                    ->whereYear('tanggal', $now->year)
                    ->where('status', 'Hadir')
                    ->count(),
                'izin'  => DB::table('absensi')
                    ->whereMonth('tanggal', $now->month)
                    ->whereYear('tanggal', $now->year)
                    ->where('status', 'Izin')
                    ->count(),
                'alfa'  => DB::table('absensi')
                    ->whereMonth('tanggal', $now->month)
                    ->whereYear('tanggal', $now->year)
                    ->where('status', 'Alfa')
                    ->count(),
            ];

            return view('dashboard.admin', compact(
                'total_siswa', 
                'total_pelatih', 
                'absensi_hari_ini', 
                'data_kelas', 
                'nama', 
                'foto', 
                'statistik',
                'menuData'
            ));
        }

        // ==============================
        // PELATIH (Role 3)
        // ==============================
        elseif ($role_id === RoleConstant::PELATIH) {

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
                'foto',
                'menuData'
            ));
        }

        // ==============================
        // SISWA (Role 4)
        // ==============================
        elseif ($role_id === RoleConstant::SISWA) {

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
                'foto',
                'menuData'
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