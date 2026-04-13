<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProfilController extends Controller
{
    /**
     * Display a listing of profiles (Admin only).
     */
    public function index(Request $request)
    {
        // Cek apakah user adalah admin
        $userRole = strtolower(auth()->user()->role->nama_role ?? 'guest');
        
        if ($userRole !== 'admin') {
            // Non-admin redirect ke edit profil sendiri
            return redirect()->route('profil.edit', auth()->user()->id_user);
        }
        
        $search = $request->get('search', '');
        $filter_role = $request->get('role', '');
        
        $query = DB::table('profil_anggota as p')
            ->leftJoin('users as u', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->select(
                'p.id_user',
                'p.nama_lengkap',
                'p.email',
                'p.foto_profil',
                'r.nama_role',
                'r.id_role as role_id',
                'u.aktif',
                'u.kode_barcode'
            );
        
        if ($search) {
            $query->where('p.nama_lengkap', 'like', "%{$search}%");
        }
        
        if ($filter_role) {
            $query->where('u.id_role', $filter_role);
        }
        
        $profiles = $query->orderBy('p.nama_lengkap', 'asc')->get();
        $roles = DB::table('roles')->orderBy('nama_role', 'asc')->get();
        
        return view('settings.profil.index', compact('profiles', 'roles', 'search', 'filter_role'));
    }
    
    /**
     * Show the form for editing profile.
     */
    public function edit($id)
    {
        // HAPUS kolom yang tidak ada: telepon, alamat, tanggal_lahir, jenis_kelamin
        $profile = DB::table('profil_anggota as p')
            ->leftJoin('users as u', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('p.id_user', $id)
            ->select(
                'p.id_user',
                'p.nama_lengkap',
                'p.email',
                'p.foto_profil',
                // 'p.telepon',      // HAPUS - kolom tidak ada
                // 'p.alamat',       // HAPUS - kolom tidak ada
                // 'p.tanggal_lahir', // HAPUS - kolom tidak ada
                // 'p.jenis_kelamin', // HAPUS - kolom tidak ada
                'r.nama_role',
                'r.id_role as role_id',
                'u.aktif',
                'u.kode_barcode'
            )
            ->first();
        
        if (!$profile) {
            return redirect()->route('profil.index')
                ->with('error', 'Profil tidak ditemukan');
        }
        
        $roles = DB::table('roles')->orderBy('nama_role', 'asc')->get();
        
        return view('settings.profil.edit', compact('profile', 'roles'));
    }
    
    /**
     * Update the specified profile.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'nullable|email|unique:profil_anggota,email,' . $id . ',id_user',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aktif' => 'boolean',
            'id_role' => 'required|exists:roles,id_role',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('profil.edit', $id)
                ->with('error', 'Validasi gagal: ' . $validator->errors()->first())
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Update profil_anggota - HAPUS kolom yang tidak ada
            $updateData = [
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
                // 'telepon' => $request->telepon,        // HAPUS
                // 'alamat' => $request->alamat,          // HAPUS
                // 'tanggal_lahir' => $request->tanggal_lahir, // HAPUS
                // 'jenis_kelamin' => $request->jenis_kelamin,   // HAPUS
            ];
            
            // Handle foto upload
            if ($request->hasFile('foto_profil')) {

    $file = $request->file('foto_profil')->getRealPath();

    $result = Cloudinary::upload($file, [
        'folder' => 'foto_users',
        'public_id' => 'user_' . $id,
        'overwrite' => true
    ]);

    $url = $result->getSecurePath();

    $updateData['foto_profil'] = $url;
}
            
            DB::table('profil_anggota')->where('id_user', $id)->update($updateData);
            
            // Update users table
            DB::table('users')->where('id_user', $id)->update([
                'id_role' => $request->id_role,
                'aktif' => $request->aktif ?? 1,
            ]);
            
            DB::commit();
            
            $redirectRoute = auth()->user()->role->nama_role === 'admin' 
                ? route('profil.index') 
                : route('dashboard');
            
            return redirect($redirectRoute)
                ->with('success', '✅ Profil berhasil diperbarui');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('profil.edit', $id)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Get profile data for AJAX (optional).
     */
    public function getProfile($id)
    {
        $profile = DB::table('profil_anggota')
            ->where('id_user', $id)
            ->select('nama_lengkap', 'email', 'foto_profil')
            ->first();
        
        if ($profile) {
            return response()->json([
                'success' => true,
                'data' => $profile
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Profil tidak ditemukan'
        ]);
    }
}
