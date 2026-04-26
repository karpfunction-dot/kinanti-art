<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Menampilkan daftar riwayat absensi.
     */
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        $filter_role = $request->get('role', '');
        $search = $request->get('search', '');
        
        $query = DB::table('absensi as a')
            ->leftJoin('users as u', 'a.id_user', '=', 'u.id_user')
            ->leftJoin('profil_anggota as p', 'p.id_user', '=', 'a.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->select(
                'a.id_absensi',
                'a.tanggal',
                'a.waktu',
                'a.status',
                'a.kategori',
                'p.nama_lengkap',
                'p.foto_profil',
                'u.kode_barcode',
                DB::raw('COALESCE(r.nama_role, "Member") AS role_name')
            );
        
        if ($bulan) {
            $query->whereRaw("DATE_FORMAT(a.tanggal, '%Y-%m') = ?", [$bulan]);
        }
        
        if ($filter_role) {
            $query->where('r.nama_role', $filter_role);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('p.nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('u.kode_barcode', 'like', "%{$search}%");
            });
        }
        
        $rows = $query->orderBy('a.tanggal', 'desc')
            ->orderBy('a.waktu', 'desc')
            ->paginate(20);
        
        $roles = DB::table('roles')->select('nama_role')->get();
        $months = $this->getMonths();
        $years = range(date('Y') - 2, date('Y') + 1);
        
        return view('absensi.index', compact('rows', 'bulan', 'filter_role', 'search', 'roles', 'months', 'years'));
    }

    /**
     * Tampilan form scanner barcode.
     */
    public function scan()
    {
        return view('absensi.scan');
    }

    /**
     * Proses absensi (Form Submit biasa).
     */
    public function proses(Request $request)
    {
        $request->validate([
            'kode_barcode' => 'required|string|min:3|max:50'
        ]);
        
        $barcode = trim($request->kode_barcode);
        $currentUser = auth()->user();
        
        $currentRoleName = strtolower($currentUser->role->nama_role ?? 'member');
        $allowedRoles = ['admin', 'pelatih', 'manajemen'];

        if (!in_array($currentRoleName, $allowedRoles)) {
            return redirect()->route('absensi.scan')
                ->with('error', '❌ Akses ditolak!')
                ->withInput();
        }
        
        $user = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('u.kode_barcode', $barcode)
            ->select('u.id_user', 'u.kode_barcode', 'r.nama_role as role', 'p.nama_lengkap', 'p.foto_profil')
            ->first();
        
        if (!$user) {
            return redirect()->route('absensi.scan')
                ->with('error', '❌ Barcode tidak terdaftar.')
                ->withInput();
        }
        
        DB::beginTransaction();
        try {
            $sudahAbsen = DB::table('absensi')
                ->where('id_user', $user->id_user)
                ->whereDate('tanggal', date('Y-m-d'))
                ->lockForUpdate()
                ->exists();
            
            if ($sudahAbsen) {
                DB::rollBack();
                return redirect()->route('absensi.scan')
                    ->with('warning', '⚠️ ' . $user->nama_lengkap . ' sudah absen hari ini.');
            }
            
            $kategori = in_array(strtolower($user->role ?? ''), $allowedRoles) ? 'Pelatih' : 'Siswa';
            
            DB::table('absensi')->insert([
                'id_user' => $user->id_user,
                'kode_barcode' => $barcode,
                'tanggal' => date('Y-m-d'),
                'waktu' => date('H:i:s'),
                'status' => 'Hadir',
                'kategori' => $kategori,
                'lokasi' => 'Studio',
                'keterangan' => "Absen via scanner oleh: " . ($currentUser->profil->nama_lengkap ?? $currentUser->name),
                'status_absen' => 'tercatat',
                'created_at' => now(),
            ]);
            
            DB::commit();
            return redirect()->route('absensi.scan')->with('success', '✅ Absensi berhasil: ' . $user->nama_lengkap);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('absensi.scan')->with('error', '❌ Error: ' . $e->getMessage());
        }
    }

    /**
     * Proses absensi (API/AJAX) - Digunakan oleh Scanner Camera.
     */
    public function prosesApi(Request $request)
    {
        try {
            $request->validate(['kode_barcode' => 'required|string|min:3']);
            $barcode = trim($request->kode_barcode);
            $currentUser = auth()->user();
            
            if (!$currentUser) {
                return response()->json(['success' => false, 'message' => 'Silakan login ulang.'], 401);
            }
            
            $userRole = strtolower($currentUser->role->nama_role ?? '');
            $allowedRoles = ['admin', 'pelatih', 'manajemen'];
            
            if (!in_array($userRole, $allowedRoles)) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak!'], 403);
            }
            
            $user = DB::table('users as u')
                ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
                ->where('u.kode_barcode', $barcode)
                ->select('u.id_user', 'u.kode_barcode', 'r.nama_role as role', 'p.nama_lengkap', 'p.foto_profil')
                ->first();
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Barcode tidak ditemukan.'], 404);
            }
            
            $sudahAbsen = DB::table('absensi')
                ->where('id_user', $user->id_user)
                ->whereDate('tanggal', date('Y-m-d'))
                ->exists();
            
            if ($sudahAbsen) {
                return response()->json(['success' => false, 'message' => $user->nama_lengkap . ' sudah absen hari ini.'], 409);
            }
            
            $kategori = in_array(strtolower($user->role ?? ''), $allowedRoles) ? 'Pelatih' : 'Siswa';
            
            DB::table('absensi')->insert([
                'id_user' => $user->id_user,
                'kode_barcode' => $barcode,
                'tanggal' => date('Y-m-d'),
                'waktu' => date('H:i:s'),
                'status' => 'Hadir',
                'kategori' => $kategori,
                'lokasi' => 'Studio',
                'keterangan' => "Absen via API scanner",
                'status_absen' => 'tercatat',
                'created_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Absensi Berhasil: ' . $user->nama_lengkap,
                'user' => $user
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sistem Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menampilkan daftar siswa per kelas untuk absen manual.
     */
    public function inputKelas($id_kelas)
    {
        $kelas = DB::table('kelas')->where('id_kelas', $id_kelas)->first();
        if (!$kelas) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan.');
        }

        $siswas = DB::table('pendaftaran_kelas as pk')
            ->join('users as u', 'pk.id_user', '=', 'u.id_user')
            ->join('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('pk.id_kelas', $id_kelas)
            ->select('u.id_user', 'u.kode_barcode', 'p.nama_lengkap', 'p.foto_profil', 'r.nama_role')
            ->get();

        $absensi_hari_ini = DB::table('absensi')
            ->where('id_kelas', $id_kelas)
            ->whereDate('tanggal', date('Y-m-d'))
            ->pluck('status', 'id_user')
            ->toArray();

        return view('absensi.input_massal', compact('siswas', 'kelas', 'absensi_hari_ini'));
    }

    /**
     * Menyimpan data absensi massal.
     */
    public function storeMassal(Request $request)
    {
        $id_kelas = $request->id_kelas;
        $statuses = $request->status; 
        $currentUser = auth()->user();

        if (!$statuses) {
            return redirect()->back()->with('error', 'Tidak ada data kehadiran yang dipilih.');
        }

        DB::beginTransaction();
        try {
            foreach ($statuses as $id_user => $status) {
                $user = DB::table('users as u')
                    ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
                    ->where('u.id_user', $id_user)
                    ->select('u.kode_barcode', 'r.nama_role')
                    ->first();

                $kategori = (strtolower($user->nama_role ?? '') == 'siswa') ? 'Siswa' : 'Pelatih';

                DB::table('absensi')->updateOrInsert(
                    [
                        'id_user' => $id_user,
                        'id_kelas' => $id_kelas,
                        'tanggal' => date('Y-m-d')
                    ],
                    [
                        'kode_barcode' => $user->kode_barcode,
                        'waktu' => date('H:i:s'),
                        'status' => $status,
                        'kategori' => $kategori,
                        'lokasi' => 'Studio',
                        'keterangan' => "Diinput manual oleh: " . ($currentUser->profil->nama_lengkap ?? $currentUser->name),
                        'status_absen' => 'tercatat',
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );
            }
            DB::commit();
            return redirect()->route('absensi.index')->with('success', '✅ Data berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('absensi')->where('id_absensi', $id)->delete();
            return redirect()->route('absensi.index')->with('success', '✅ Data dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('absensi.index')->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    private function getMonths()
    {
        return ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
    }
} // Penutup Class Harus Paling Bawah