<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        $filter_role = $request->get('role', '');
        
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
        
        $rows = $query->orderBy('a.tanggal', 'desc')
            ->orderBy('a.waktu', 'desc')
            ->get();
        
        return view('absensi.index', compact('rows', 'bulan', 'filter_role'));
    }

    /**
     * Show the scan barcode form.
     */
    public function scan()
    {
        return view('absensi.scan');
    }

    /**
 * Process barcode scan and store attendance.
 */
public function proses(Request $request)
{
    $request->validate([
        'kode_barcode' => 'required|string'
    ]);
    
    $barcode = trim($request->kode_barcode);
    
    // Cari user berdasarkan barcode
    $user = DB::table('users as u')
        ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
        ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
        ->where('u.kode_barcode', $barcode)
        ->select(
            'u.id_user', 
            'u.kode_barcode', 
            'r.nama_role as role', 
            'p.nama_lengkap', 
            'p.foto_profil'
        )
        ->first();
    
    if (!$user) {
        return redirect()->route('absensi.scan')
            ->with('error', '❌ Barcode tidak ditemukan di sistem.')
            ->withInput();
    }
    
    // Cek apakah sudah absen hari ini
    $sudahAbsen = DB::table('absensi')
        ->where('id_user', $user->id_user)
        ->whereDate('tanggal', date('Y-m-d'))
        ->exists();
    
    // Siapkan data user untuk ditampilkan
    $userData = [
        'nama_lengkap' => $user->nama_lengkap ?? 'Tidak ada nama',
        'role' => $user->role ?? 'Member',
        'kode_barcode' => $user->kode_barcode,
        'foto_profil' => $user->foto_profil,
    ];
    
    if ($sudahAbsen) {
        return redirect()->route('absensi.scan')
            ->with('warning', '⚠️ Pengguna ' . ($user->nama_lengkap ?? $user->kode_barcode) . ' sudah absen hari ini.')
            ->with('scanned_user', $userData);
    }
    
    // Simpan absensi - SESUAIKAN DENGAN STRUKTUR TABEL
    $kategori = in_array($user->role, ['pelatih', 'manajemen', 'admin']) ? 'Pelatih' : 'Siswa';
    $keterangan = "Absen otomatis: {$user->nama_lengkap} ({$user->role})";
    
    DB::table('absensi')->insert([
        'id_user' => $user->id_user,
        'kode_barcode' => $barcode,
        'tanggal' => date('Y-m-d'),
        'waktu' => date('H:i:s'),
        'status' => 'Hadir',
        'kategori' => $kategori,
        'lokasi' => 'Studio',
        'keterangan' => $keterangan,
        'status_absen' => 'tercatat',  // Kolom yang ada di tabel
        'created_at' => now(),          // Kolom created_at ada
        // 'updated_at' => now(),       // HAPUS karena kolom ini TIDAK ADA
    ]);
    
    return redirect()->route('absensi.scan')
        ->with('success', '✅ Absensi berhasil dicatat untuk ' . htmlspecialchars($user->nama_lengkap))
        ->with('scanned_user', $userData);
}

    /**
     * Remove the specified attendance record.
     */
    public function destroy($id)
    {
        try {
            DB::table('absensi')->where('id_absensi', $id)->delete();
            return redirect()->route('absensi.index')
                ->with('success', 'Data absensi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('absensi.index')
                ->with('error', 'Gagal menghapus data absensi.');
        }
    }
}