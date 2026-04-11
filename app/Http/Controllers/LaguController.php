<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LaguController extends Controller
{
    /**
     * Display a listing of lagu.
     */
    public function index()
    {
        if (auth()->user()->role->nama_role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        // Ambil data lagu dengan join kelas
        $lagu = DB::table('lagu as l')
            ->leftJoin('kelas as k', 'l.id_kelas', '=', 'k.id_kelas')
            ->select('l.*', 'k.nama_kelas')
            ->orderBy('l.id_lagu', 'desc')
            ->get();
        
        // Ambil data kelas untuk dropdown
        $kelas = DB::table('kelas')->where('aktif', 1)->orderBy('nama_kelas')->get();
        
        return view('lagu.index', compact('lagu', 'kelas'));
    }
    
    /**
     * Store a newly created lagu.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul_lagu' => 'required|string|max:150',
            'pencipta' => 'nullable|string|max:100',
            'lisensi' => 'required|in:gratis,berbayar',
            'status_lisensi' => 'required|in:bebas,izin,internal',
            'status' => 'required|in:aktif,nonaktif',
            'id_kelas' => 'nullable|exists:kelas,id_kelas',
            'link_lisensi' => 'nullable|url|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        
        try {
            DB::table('lagu')->insert([
                'judul_lagu' => $request->judul_lagu,
                'pencipta' => $request->pencipta,
                'lisensi' => $request->lisensi,
                'status_lisensi' => $request->status_lisensi,
                'status' => $request->status,
                'id_kelas' => $request->id_kelas ?: null,
                'link_lisensi' => $request->link_lisensi,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Lagu berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Update the specified lagu.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'judul_lagu' => 'required|string|max:150',
            'pencipta' => 'nullable|string|max:100',
            'lisensi' => 'required|in:gratis,berbayar',
            'status_lisensi' => 'required|in:bebas,izin,internal',
            'status' => 'required|in:aktif,nonaktif',
            'id_kelas' => 'nullable|exists:kelas,id_kelas',
            'link_lisensi' => 'nullable|url|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        
        try {
            DB::table('lagu')->where('id_lagu', $id)->update([
                'judul_lagu' => $request->judul_lagu,
                'pencipta' => $request->pencipta,
                'lisensi' => $request->lisensi,
                'status_lisensi' => $request->status_lisensi,
                'status' => $request->status,
                'id_kelas' => $request->id_kelas ?: null,
                'link_lisensi' => $request->link_lisensi,
                'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '✏️ Lagu berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Remove the specified lagu.
     */
    public function destroy($id)
    {
        try {
            DB::table('lagu')->where('id_lagu', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => '🗑️ Lagu berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get lagu data for edit (AJAX).
     */
    public function getLagu($id)
    {
        $lagu = DB::table('lagu')->where('id_lagu', $id)->first();
        
        if ($lagu) {
            return response()->json([
                'success' => true,
                'data' => $lagu
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan'
        ]);
    }
}