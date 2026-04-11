<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TugasWewenangController extends Controller
{
    /**
     * Display tugas and wewenang management page.
     */
    public function index()
    {
        // Cek apakah user adalah admin
        $userRole = strtolower(auth()->user()->role->nama_role ?? 'guest');
        
        if ($userRole !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
        }
        
        // Ambil data tugas
        $tugas = DB::table('tugas')
            ->orderBy('id_tugas', 'asc')
            ->get();
        
        // Ambil data wewenang dengan join
        $wewenang = DB::table('wewenang as w')
            ->leftJoin('profil_anggota as p', 'w.id_user', '=', 'p.id_user')
            ->leftJoin('tugas as t', 'w.id_tugas', '=', 't.id_tugas')
            ->select(
                'w.id_wewenang',
                'w.id_user',
                'w.id_tugas',
                'w.aktif',
                'w.periode_mulai',
                'w.periode_selesai',
                'w.catatan',
                'p.nama_lengkap',
                't.nama_tugas'
            )
            ->orderBy('w.id_wewenang', 'asc')
            ->get();
        
        // Ambil daftar user untuk dropdown
        $users = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
            ->orderBy('p.nama_lengkap', 'asc')
            ->get();
        
        return view('settings.tugas_wewenang.index', compact('tugas', 'wewenang', 'users'));
    }
    
    /**
     * Store a new task (tugas).
     */
    public function storeTugas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_tugas' => 'required|string|max:100',
            'kategori' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'aktif' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        
        try {
            $id = DB::table('tugas')->insertGetId([
                'nama_tugas' => $request->nama_tugas,
                'kategori' => $request->kategori,
                'deskripsi' => $request->deskripsi,
                'aktif' => $request->aktif ?? 1,
                'created_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil ditambahkan',
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan tugas: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Update an existing task.
     */
    public function updateTugas(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_tugas' => 'required|string|max:100',
            'kategori' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'aktif' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        
        try {
            DB::table('tugas')->where('id_tugas', $id)->update([
                'nama_tugas' => $request->nama_tugas,
                'kategori' => $request->kategori,
                'deskripsi' => $request->deskripsi,
                'aktif' => $request->aktif ?? 1,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui tugas: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Delete a task.
     */
    public function destroyTugas($id)
    {
        try {
            // Cek apakah tugas memiliki relasi wewenang
            $hasRelation = DB::table('wewenang')->where('id_tugas', $id)->exists();
            
            if ($hasRelation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas tidak dapat dihapus karena masih memiliki wewenang terkait'
                ]);
            }
            
            DB::table('tugas')->where('id_tugas', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tugas: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Store a new wewenang.
     */
    public function storeWewenang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|exists:users,id_user',
            'id_tugas' => 'required|exists:tugas,id_tugas',
            'aktif' => 'boolean',
            'periode_mulai' => 'nullable|date',
            'periode_selesai' => 'nullable|date|after_or_equal:periode_mulai',
            'catatan' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        
        try {
            // Cek duplikasi
            $exists = DB::table('wewenang')
                ->where('id_user', $request->id_user)
                ->where('id_tugas', $request->id_tugas)
                ->exists();
            
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wewenang sudah ada untuk pengguna dan tugas ini'
                ]);
            }
            
            $id = DB::table('wewenang')->insertGetId([
                'id_user' => $request->id_user,
                'id_tugas' => $request->id_tugas,
                'aktif' => $request->aktif ?? 1,
                'periode_mulai' => $request->periode_mulai,
                'periode_selesai' => $request->periode_selesai,
                'catatan' => $request->catatan,
                'created_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Wewenang berhasil ditambahkan',
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan wewenang: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Update an existing wewenang.
     */
    public function updateWewenang(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|exists:users,id_user',
            'id_tugas' => 'required|exists:tugas,id_tugas',
            'aktif' => 'boolean',
            'periode_mulai' => 'nullable|date',
            'periode_selesai' => 'nullable|date|after_or_equal:periode_mulai',
            'catatan' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        
        try {
            DB::table('wewenang')->where('id_wewenang', $id)->update([
                'id_user' => $request->id_user,
                'id_tugas' => $request->id_tugas,
                'aktif' => $request->aktif ?? 1,
                'periode_mulai' => $request->periode_mulai,
                'periode_selesai' => $request->periode_selesai,
                'catatan' => $request->catatan,
                'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Wewenang berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui wewenang: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Delete a wewenang.
     */
    public function destroyWewenang($id)
    {
        try {
            DB::table('wewenang')->where('id_wewenang', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Wewenang berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus wewenang: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Search users for wewenang form (AJAX).
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $users = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->where('p.nama_lengkap', 'like', "%{$query}%")
            ->orWhere('u.kode_barcode', 'like', "%{$query}%")
            ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
            ->limit(10)
            ->get();
        
        return response()->json($users);
    }
    
    /**
     * Get tugas data for edit (AJAX).
     */
    public function editTugas($id)
    {
        $tugas = DB::table('tugas')->where('id_tugas', $id)->first();
        
        if ($tugas) {
            return response()->json([
                'success' => true,
                'data' => $tugas
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Tugas tidak ditemukan'
        ]);
    }
    
    /**
     * Get wewenang data for edit (AJAX).
     */
    public function editWewenang($id)
    {
        $wewenang = DB::table('wewenang as w')
            ->leftJoin('profil_anggota as p', 'w.id_user', '=', 'p.id_user')
            ->leftJoin('tugas as t', 'w.id_tugas', '=', 't.id_tugas')
            ->where('w.id_wewenang', $id)
            ->select('w.*', 'p.nama_lengkap', 't.nama_tugas')
            ->first();
        
        if ($wewenang) {
            return response()->json([
                'success' => true,
                'data' => $wewenang
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Wewenang tidak ditemukan'
        ]);
    }
}