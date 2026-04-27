<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Support\PhotoUrl;

class ProfilController extends Controller
{
    // 1. Menampilkan Daftar Profil (Hanya untuk Admin)
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
            ->select('p.id_user', 'p.nama_lengkap', 'p.email', 'p.foto_profil', 'r.nama_role', 'r.id_role as role_id', 'u.aktif', 'u.kode_barcode');

        if ($search) $query->where('p.nama_lengkap', 'like', "%{$search}%");
        if ($filter_role) $query->where('u.id_role', $filter_role);

        $profiles = $query->orderBy('p.nama_lengkap', 'asc')->get();
        $profiles->transform(function ($profile) {
            $profile->foto_url = PhotoUrl::resolve($profile->foto_profil ?? null);
            return $profile;
        });
        $roles = DB::table('roles')->orderBy('nama_role', 'asc')->get();

        return view('settings.profil.index', compact('profiles', 'roles', 'search', 'filter_role'));
    }

    // 2. Menampilkan Form Edit Profil
    public function edit($id)
    {
        $profile = DB::table('profil_anggota as p')
            ->leftJoin('users as u', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('p.id_user', $id)
            ->select('p.id_user', 'p.nama_lengkap', 'p.email', 'p.foto_profil', 'r.nama_role', 'r.id_role as role_id', 'u.aktif', 'u.kode_barcode')
            ->first();

        if (!$profile) return redirect()->route('profil.index')->with('error', 'Profil tidak ditemukan');
        $profile->foto_url = PhotoUrl::resolve($profile->foto_profil ?? null);

        $roles = DB::table('roles')->orderBy('nama_role', 'asc')->get();
        return view('settings.profil.edit', compact('profile', 'roles'));
    }

    // 3. Memproses Update Profil & Foto Cloudinary
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'email'        => 'nullable|email|unique:profil_anggota,email,' . $id . ',id_user',
            'foto_profil'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'id_role'      => 'required|exists:roles,id_role',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            $updateData = [
                'nama_lengkap' => $request->nama_lengkap,
                'email'        => $request->email,
            ];

            // Cek jika ada upload foto baru
            $file = $request->file('foto_profil') ?? $request->file('foto');
            if ($file) {

                $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
                    'folder' => 'foto_users',
                    'transformation' => [
                        'width' => 400,
                        'height' => 400,
                        'crop' => 'limit',
                        'quality' => 'auto',
                        'fetch_format' => 'auto'
                    ]
                ])->getSecurePath();

                $updateData['foto_profil'] = $uploadedFileUrl; 
            }

            // Update Tabel Profil
            DB::table('profil_anggota')->where('id_user', $id)->update($updateData);

            // Update Tabel Users (Role & Status Aktif)
            DB::table('users')->where('id_user', $id)->update([
                'id_role' => $request->id_role, 
                'aktif'   => $request->aktif ?? 1
            ]);

            DB::commit();
            return redirect()->back()->with('success', '✅ Berhasil diperbarui');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }
}
