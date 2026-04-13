<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProfilController extends Controller
{
    /**
     * Display a listing of profiles (Admin only).
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
     * Show edit form
     */
    public function edit($id)
    {
        $profile = DB::table('profil_anggota as p')
            ->leftJoin('users as u', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('p.id_user', $id)
            ->select(
                'p.id_user',
                'p.nama_lengkap',
                'p.email',
                'p.foto_profil',
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
     * Update profile
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

            // DATA UPDATE PROFIL
            $updateData = [
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
            ];

            /**
             * =========================
             * CLOUDINARY UPLOAD FIX
             * =========================
             */
            if ($request->hasFile('foto_profil')) {
                try {
                    $file = $request->file('foto_profil');

                    $result = Cloudinary::upload($file->getRealPath(), [
                        'folder' => 'foto_users',
                        'public_id' => 'profil_' . $id,
                        'overwrite' => true,
                        'resource_type' => 'image',
                    ]);

                    $updateData['foto_profil'] = $result->getSecurePath();

                } catch (\Exception $e) {
                    return redirect()->back()
                        ->with('error', 'Upload foto gagal: ' . $e->getMessage());
                }
            }

            DB::table('profil_anggota')
                ->where('id_user', $id)
                ->update($updateData);

            // UPDATE USERS TABLE
            DB::table('users')
                ->where('id_user', $id)
                ->update([
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
     * AJAX get profile
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
