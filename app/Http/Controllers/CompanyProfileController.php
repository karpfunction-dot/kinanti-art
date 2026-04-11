<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CompanyProfileController extends Controller
{
    /**
     * Display company profile.
     */
    public function index()
    {
        // Cek apakah user adalah admin
        if (auth()->user()->role->nama_role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $profile = DB::table('setting_company')->first();
        
        // Jika belum ada data, buat default
        if (!$profile) {
            $profile = (object)[
                'id_company' => null,
                'nama_lembaga' => 'Kinanti Art Productions',
                'alamat' => '',
                'telp' => '',
                'email' => '',
                'website' => '',
                'logo' => null,
            ];
        }
        
        return view('settings.company.index', compact('profile'));
    }
    
    /**
     * Update company profile.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lembaga' => 'required|string|max:200',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:100', // HAPUS validasi 'url'
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('settings.company')
                ->with('error', $validator->errors()->first());
        }
        
        // Format website - tambahkan https:// jika tidak ada
        $website = $request->website;
        if (!empty($website) && !preg_match('/^https?:\/\//', $website)) {
            $website = 'https://' . $website;
        }
        
        try {
            $data = [
                'nama_lembaga' => $request->nama_lembaga,
                'alamat' => $request->alamat,
                'telp' => $request->telp,
                'email' => $request->email,
                'website' => $website,
                'updated_at' => now(),
            ];
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Hapus logo lama jika ada
                $oldLogo = DB::table('setting_company')->value('logo');
                if ($oldLogo && Storage::disk('public')->exists('company/' . $oldLogo)) {
                    Storage::disk('public')->delete('company/' . $oldLogo);
                }
                
                // Upload logo baru
                $file = $request->file('logo');
                $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('company', $filename, 'public');
                $data['logo'] = $filename;
            }
            
            $exists = DB::table('setting_company')->exists();
            
            if ($exists) {
                DB::table('setting_company')->update($data);
            } else {
                $data['created_at'] = now();
                DB::table('setting_company')->insert($data);
            }
            
            return redirect()->route('settings.company')
                ->with('success', '✅ Profil sanggar berhasil diperbarui');
                
        } catch (\Exception $e) {
            return redirect()->route('settings.company')
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}