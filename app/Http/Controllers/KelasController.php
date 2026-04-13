<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KelasController extends Controller
{
    /**
     * Display kelas management page.
     */
    public function index()
    {
        if (auth()->user()->role->nama_role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $jenjang = DB::table('jenjang')->orderBy('id_jenjang')->get();
        $lagu = DB::table('lagu')->where('status', 'aktif')->orderBy('judul_lagu')->get();
        
        $pelatih = DB::table('wewenang as w')
            ->join('profil_anggota as p', 'w.id_user', '=', 'p.id_user')
            ->where('w.aktif', 1)
            ->select('p.id_user', 'p.nama_lengkap')
            ->distinct()
            ->orderBy('p.nama_lengkap')
            ->get();
        
        $kelas = DB::table('kelas as k')
            ->leftJoin('jenjang as j', 'k.id_jenjang', '=', 'j.id_jenjang')
            ->leftJoin('lagu as l', 'k.id_lagu', '=', 'l.id_lagu')
            ->leftJoin('profil_anggota as p', 'k.pelatih', '=', 'p.id_user')
            ->select(
                'k.id_kelas',
                'k.nama_kelas',
                'k.deskripsi',
                'k.aktif',
                'j.nama_jenjang',
                'j.id_jenjang',
                'l.judul_lagu',
                'l.id_lagu',
                'p.nama_lengkap as nama_pelatih',
                'k.pelatih'
            )
            ->orderBy('j.id_jenjang')
            ->orderBy('k.nama_kelas')
            ->get();
        
        return view('kelas.index', compact('jenjang', 'lagu', 'pelatih', 'kelas'));
    }
    
    /**
     * Display entri anggota kelas page.
     */
    public function entri(Request $request)
    {
        if (auth()->user()->role->nama_role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        // Ambil daftar kelas
        $kelasList = DB::table('kelas')
            ->where('aktif', 1)
            ->orderBy('nama_kelas')
            ->get();
        
        $id_kelas = $request->get('id', 0);
        $nama_kelas = '';
        $siswaAll = [];
        $anggotaList = [];
        
        if ($id_kelas > 0) {
            // Ambil nama kelas
            $kelas = DB::table('kelas')->where('id_kelas', $id_kelas)->first();
            if ($kelas) {
                $nama_kelas = $kelas->nama_kelas;
            }
            
            // Siswa yang belum memiliki kelas aktif
            $siswaAll = DB::table('users as u')
                ->join('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                ->join('roles as r', 'u.id_role', '=', 'r.id_role')
                ->leftJoin('kelas_siswa as ks', function($join) {
                    $join->on('u.id_user', '=', 'ks.id_user')
                         ->where('ks.aktif', 1);
                })
                ->where('r.nama_role', 'siswa')
                ->whereNull('ks.id_kelas')
                ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
                ->orderBy('p.nama_lengkap')
                ->get();
            
            // Anggota kelas saat ini
            $anggotaList = DB::table('kelas_siswa as ks')
                ->join('profil_anggota as p', 'ks.id_user', '=', 'p.id_user')
                ->where('ks.id_kelas', $id_kelas)
                ->where('ks.aktif', 1)
                ->select('ks.id_user', 'p.nama_lengkap', 'ks.tanggal_gabung')
                ->orderBy('p.nama_lengkap')
                ->get();
        }
        
        return view('kelas.entri', compact('kelasList', 'id_kelas', 'nama_kelas', 'siswaAll', 'anggotaList'));
    }
    
    /**
     * Store anggota to kelas.
     */
    public function storeAnggota(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_user' => 'required|exists:users,id_user',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        
        try {
            // Cek apakah siswa sudah punya kelas aktif
            $exists = DB::table('kelas_siswa')
                ->where('id_user', $request->id_user)
                ->where('aktif', 1)
                ->exists();
            
            if ($exists) {
                return redirect()->back()->with('error', 'Siswa ini sudah memiliki kelas aktif.');
            }
            
            DB::table('kelas_siswa')->insert([
                'id_kelas' => $request->id_kelas,
                'id_user' => $request->id_user,
                'tanggal_gabung' => date('Y-m-d'),
                'aktif' => 1,
                'created_at' => now(),
            ]);
            
            return redirect()->route('kelas.entri', ['id' => $request->id_kelas])
                ->with('success', 'Anggota berhasil ditambahkan ke kelas.');
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan: ' . $e->getMessage());
        }
    }
    
    /**
     * Destroy anggota from kelas.
     */
    public function destroyAnggota($id_kelas, $id_user)
    {
        try {
            DB::table('kelas_siswa')
                ->where('id_kelas', $id_kelas)
                ->where('id_user', $id_user)
                ->update(['aktif' => 0, 'updated_at' => now()]);
            
            return redirect()->route('kelas.entri', ['id' => $id_kelas])
                ->with('success', 'Anggota berhasil dihapus dari kelas.');
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
    
    /**
     * Display naik kelas page.
     */
    public function naik(Request $request)
    {
        if (auth()->user()->role->nama_role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        // Ambil daftar kelas aktif
        $kelasList = DB::table('kelas')
            ->where('aktif', 1)
            ->orderBy('nama_kelas')
            ->get();
        
        $kelasAsal = $request->get('kelas_asal', 0);
        $siswaList = [];
        $kelasTujuanList = [];
        
        if ($kelasAsal > 0) {
            // Ambil siswa di kelas asal
            $siswaList = DB::table('kelas_siswa as ks')
                ->join('profil_anggota as p', 'ks.id_user', '=', 'p.id_user')
                ->where('ks.id_kelas', $kelasAsal)
                ->where('ks.aktif', 1)
                ->select('ks.id_user', 'p.nama_lengkap', 'ks.tanggal_gabung')
                ->orderBy('p.nama_lengkap')
                ->get();
            
            // Ambil kelas tujuan (semua kelas kecuali kelas asal)
            $kelasTujuanList = DB::table('kelas')
                ->where('aktif', 1)
                ->where('id_kelas', '!=', $kelasAsal)
                ->orderBy('nama_kelas')
                ->get();
        }
        
        return view('kelas.naik', compact('kelasList', 'kelasAsal', 'siswaList', 'kelasTujuanList'));
    }
    
    /**
     * Process naik kelas (mutasi siswa).
     */
    public function prosesNaikKelas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kelas_asal' => 'required|exists:kelas,id_kelas',
            'kelas_tujuan' => 'required|exists:kelas,id_kelas',
            'id_siswa' => 'required|array',
            'id_siswa.*' => 'exists:users,id_user',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        
        try {
            DB::beginTransaction();
            
            foreach ($request->id_siswa as $id_siswa) {
                // Nonaktifkan kelas lama
                DB::table('kelas_siswa')
                    ->where('id_user', $id_siswa)
                    ->where('id_kelas', $request->kelas_asal)
                    ->where('aktif', 1)
                    ->update(['aktif' => 0, 'updated_at' => now()]);
                
                // Tambahkan ke kelas baru
                DB::table('kelas_siswa')->insert([
                    'id_kelas' => $request->kelas_tujuan,
                    'id_user' => $id_siswa,
                    'tanggal_gabung' => date('Y-m-d'),
                    'aktif' => 1,
                    'created_at' => now(),
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('kelas.naik')
                ->with('success', count($request->id_siswa) . ' siswa berhasil dinaikkan kelasnya.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }
    
    // ... (method CRUD jenjang dan kelas lainnya tetap sama)
}