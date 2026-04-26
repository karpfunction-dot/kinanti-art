<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // Tambahkan ini
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProfilController extends Controller
{
    // ... (index dan edit tetap sama)

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'nullable|email|unique:profil_anggota,email,' . $id . ',id_user',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Samakan nama input
            'id_role' => 'required|exists:roles,id_role',
        ]);

        if ($validator->fails()) return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        
        try {
            DB::beginTransaction();
            
            $updateData = [
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
            ];

            // Logika Cloudinary
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');

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

                // Masukkan URL ke array update
                $updateData['foto_profil'] = $uploadedFileUrl; 
            }

            // Update tabel profil_anggota
            DB::table('profil_anggota')->where('id_user', $id)->update($updateData);

            // Update tabel users
            DB::table('users')->where('id_user', $id)->update([
                'id_role' => $request->id_role, 
                'aktif' => $request->aktif ?? 1
            ]);

            DB::commit();
            return redirect()->back()->with('success', '✅ Berhasil diperbarui');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }
}