<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $filter_role = $request->get('role', '');
        
        $query = DB::table('users as u')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->select(
                'u.id_user',
                'u.kode_barcode',
                'u.id_role',
                'u.aktif',
                'u.created_at',
                'r.nama_role',
                'p.nama_lengkap',
                'p.email',
                'p.foto_profil'
            );
        
        if ($search) {
            $query->where('p.nama_lengkap', 'like', "%{$search}%");
        }
        
        if ($filter_role) {
            $query->where('u.id_role', $filter_role);
        }
        
        $users = $query->orderBy('u.id_user', 'asc')->get();
        $roles = DB::table('roles')->orderBy('id_role')->get();
        
        return view('settings.users.index', compact('users', 'roles', 'search', 'filter_role'));
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_barcode' => 'required|string|unique:users,kode_barcode',
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'nullable|email|unique:profil_anggota,email',
            'id_role' => 'required|exists:roles,id_role',
            'aktif' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('settings.users')
                ->with('error', 'Validasi gagal: ' . $validator->errors()->first());
        }
        
        try {
            DB::beginTransaction();
            $temporaryPassword = Str::upper(Str::random(10));
            
            $id_user = DB::table('users')->insertGetId([
                'kode_barcode' => $request->kode_barcode,
                'password' => Hash::make($temporaryPassword),
                'id_role' => $request->id_role,
                'aktif' => $request->aktif ?? 1,
                'created_at' => now(),
                // HAPUS updated_at
            ]);
            
            DB::table('profil_anggota')->insert([
                'id_user' => $id_user,
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
                'created_at' => now(),
                // HAPUS updated_at
            ]);
            
            DB::commit();
            
            return redirect()->route('settings.users')
                ->with('success', 'Pengguna baru berhasil ditambahkan. Password sementara: ' . $temporaryPassword);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('settings.users')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function edit($id)
    {
        try {
            $user = DB::table('users as u')
                ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                ->where('u.id_user', $id)
                ->select(
                    'u.id_user',
                    'u.kode_barcode',
                    'u.id_role',
                    'u.aktif',
                    'p.nama_lengkap',
                    'p.email'
                )
                ->first();
            
            if ($user) {
                return response()->json([
                    'success' => true,
                    'data' => $user
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kode_barcode' => 'required|string|unique:users,kode_barcode,' . $id . ',id_user',
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'nullable|email',
            'id_role' => 'required|exists:roles,id_role',
            'aktif' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('settings.users')
                ->with('error', 'Validasi gagal: ' . $validator->errors()->first());
        }
        
        try {
            DB::beginTransaction();
            
            // Update users - HAPUS updated_at
            DB::table('users')->where('id_user', $id)->update([
                'kode_barcode' => $request->kode_barcode,
                'id_role' => $request->id_role,
                'aktif' => $request->aktif ?? 1,
                // HAPUS: 'updated_at' => now(),
            ]);
            
            // Update profil_anggota - HAPUS updated_at
            DB::table('profil_anggota')->where('id_user', $id)->update([
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
                // HAPUS: 'updated_at' => now(),
            ]);
            
            DB::commit();
            
            return redirect()->route('settings.users')
                ->with('success', '✅ Data pengguna berhasil diperbarui');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('settings.users')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            DB::table('profil_anggota')->where('id_user', $id)->delete();
            DB::table('users')->where('id_user', $id)->delete();
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => '🗑️ Pengguna berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ]);
        }
    }
    
    public function resetPassword($id)
    {
        try {
            $temporaryPassword = Str::upper(Str::random(10));

            DB::table('users')->where('id_user', $id)->update([
                'password' => Hash::make($temporaryPassword),
                // HAPUS: 'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset. Password sementara: ' . $temporaryPassword
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset password: ' . $e->getMessage()
            ]);
        }
    }
}
