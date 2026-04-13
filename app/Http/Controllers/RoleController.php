<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        // Cek apakah user adalah admin
        if (auth()->user()->role->nama_role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $roles = DB::table('roles')
            ->orderBy('id_role', 'asc')
            ->get();
        
        return view('settings.roles.index', compact('roles'));
    }
    
    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_role' => 'required|string|max:50|unique:roles,nama_role',
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
            DB::table('roles')->insert([
                'nama_role' => $request->nama_role,
                'deskripsi' => $request->deskripsi,
                'aktif' => $request->aktif ?? 1,
                'created_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Role baru berhasil ditambahkan'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Update the specified role.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_role' => 'required|string|max:50|unique:roles,nama_role,' . $id . ',id_role',
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
            // Update data role tanpa mengubah id_role
            DB::table('roles')
                ->where('id_role', $id)
                ->update([
                    'nama_role' => $request->nama_role,
                    'deskripsi' => $request->deskripsi,
                    'aktif' => $request->aktif ?? 1,
                ]);
            
            return response()->json([
                'success' => true,
                'message' => '✏️ Role berhasil diperbarui'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Deactivate a role (soft delete - set aktif=0).
     */
    public function deactivate($id)
    {
        try {
            // Cek apakah role masih digunakan oleh user
            $userCount = DB::table('users')->where('id_role', $id)->count();
            
            if ($userCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role tidak dapat dinonaktifkan karena masih digunakan oleh ' . $userCount . ' pengguna.'
                ]);
            }
            
            DB::table('roles')->where('id_role', $id)->update([
                'aktif' => 0,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '🚫 Role berhasil dinonaktifkan'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menonaktifkan: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Activate a role.
     */
    public function activate($id)
    {
        try {
            DB::table('roles')->where('id_role', $id)->update([
                'aktif' => 1,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Role berhasil diaktifkan kembali'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengaktifkan: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get role data for edit (AJAX).
     */
    public function getRole($id)
    {
        $role = DB::table('roles')->where('id_role', $id)->first();
        
        if ($role) {
            return response()->json([
                'success' => true,
                'data' => $role
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Role tidak ditemukan'
        ]);
    }
}