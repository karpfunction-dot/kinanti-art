<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProfilController extends Controller
{
    /**
     * Tampilan daftar profil (Admin Only).
     */
    public function index(Request $request)
    {
        $userRole = strtolower(auth()->user()->role->nama_role ?? 'guest');
        
        if ($userRole !== 'admin') {
            return redirect()->route('profil.edit', auth()->user()->id_user);
        }
        
        $search = $request->get('search', '');
        $filter_role = $request->get('role', '');
        
        $query = DB::table('profil_anggota as p')
            ->leftJoin('users as u', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->select(
                'p.id_user', 'p.nama_lengkap', 'p.email', 'p.foto_profil',
                'r.nama_role', 'r.id_role as role_id', 'u.aktif', 'u.kode_barcode'
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
     * Form edit profil.
     */
    public function edit($id)
    {
        $profile = DB::table('profil_anggota as p')
            ->leftJoin('users as u', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('p.id_user', $id)
            ->select(
                'p.id_user', 'p.nama_lengkap', 'p.email', 'p.foto_profil',
                'p.telepon', 'p.alamat', 'p.tanggal_lahir', 'p.jenis_kelamin',
                'r.nama_role', 'r.id_role as role_id', 'u.aktif', 'u.kode_barcode'
            )
            ->first();
        
        if (!$profile) {
            return redirect()->route('profil.index')->with('error', 'Profil tidak ditemukan');
        }
        
        $roles = DB::table('roles')->orderBy('nama_role', 'asc')->get();
        return view('settings.profil.edit', compact('profile', 'roles'));
    }
    
    /**
     * Update profil.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'nullable|email|unique:profil_anggota,email,' . $id . ',id_user',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'aktif' => 'boolean',
            'id_role' => 'required|exists:roles,id_role',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Variabel update teks (Telepon, Alamat, dll SEKARANG AKTIF)
            $updateData = [
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
                'telepon' => $request->telepon,
                'alamat' => $request->alamat,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
            ];
            
            // Handle foto upload ke Cloudinary dengan proteksi error
            if ($request->hasFile('foto_profil')) {
                try {
                    $file = $request->file('foto_profil');
                    $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
                        'folder' => 'aradea_office/profiles',
                        'transformation' => [
                            'width' => 400, 'height' => 400, 'crop' => 'limit'
                        ]
                    ])->getSecurePath();

                    $updateData['foto_profil'] = $uploadedFileUrl;
                } catch (\Exception $e) {
                    // Jika Cloudinary gagal (misal: API Key belum dipasang di Railway)
                    return redirect()->back()->with('error', 'Gagal upload foto. Cek konfigurasi CLOUDINARY_URL di Railway!');
                }
            }
            
            DB::table('profil_anggota')->where('id_user', $id)->update($updateData);
            
            DB::table('users')->where('id_user', $id)->update([
                'id_role' => $request->id_role,
                'aktif' => $request->aktif ?? 1,
            ]);
            
            DB::commit();
            
            $userRole = strtolower(auth()->user()->role->nama_role ?? 'guest');
            $redirectRoute = $userRole === 'admin' ? route('profil.index') : route('dashboard');
            
            return redirect($redirectRoute)->with('success', '✅ Profil berhasil diperbarui');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
