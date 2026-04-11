<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IdCardController extends Controller
{
    /**
     * Display list of members for ID Card printing.
     */
    public function index(Request $request)
    {
        $cari = $request->get('cari', '');
        $role = $request->get('role', '');
        
        $query = DB::table('profil_anggota as p')
            ->leftJoin('users as u', 'p.id_user', '=', 'u.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->select(
                'p.id_user',
                'p.nama_lengkap',
                'p.foto_profil',
                'u.kode_barcode',
                'r.nama_role'
            );
        
        if ($cari) {
            $query->where(function($q) use ($cari) {
                $q->where('p.nama_lengkap', 'like', "%{$cari}%")
                  ->orWhere('u.kode_barcode', 'like', "%{$cari}%");
            });
        }
        
        if ($role) {
            $query->where('r.nama_role', $role);
        }
        
        $members = $query->orderBy('p.nama_lengkap', 'asc')->get();
        
        $roles = DB::table('roles')->select('nama_role')->orderBy('nama_role')->get();
        
        return view('idcard.index', compact('members', 'roles', 'cari', 'role'));
    }
    
    /**
     * Preview single ID Card (Native Style with QR Code).
     */
    public function preview($id)
    {
        $member = DB::table('profil_anggota as p')
            ->leftJoin('users as u', 'p.id_user', '=', 'u.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('p.id_user', $id)
            ->select(
                'p.id_user',
                'p.nama_lengkap',
                'p.foto_profil',
                'u.kode_barcode',
                'r.nama_role'
            )
            ->first();
        
        if (!$member) {
            abort(404, 'Anggota tidak ditemukan');
        }
        
        // Prepare data
        $nama = $member->nama_lengkap ?? 'Tanpa Nama';
        $idcode = $member->kode_barcode ?? '---';
        $role = ucfirst($member->nama_role ?? 'Sanggar Member');
        
        // Foto path - coba beberapa kemungkinan lokasi
        $fotoPath = asset('assets/img/blank-profile.webp');
        if (!empty($member->foto_profil)) {
            if (file_exists(public_path('storage/foto_users/' . $member->foto_profil))) {
                $fotoPath = asset('storage/foto_users/' . $member->foto_profil);
            } elseif (file_exists(public_path('uploads/foto_users/' . $member->foto_profil))) {
                $fotoPath = asset('uploads/foto_users/' . $member->foto_profil);
            } elseif (file_exists(public_path('foto_users/' . $member->foto_profil))) {
                $fotoPath = asset('foto_users/' . $member->foto_profil);
            }
        }
        
        // QR Code URL
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=" . urlencode($idcode);
        
        return view('idcard.preview', compact('member', 'nama', 'idcode', 'role', 'fotoPath', 'qrCodeUrl'));
    }
    
    /**
     * Print all ID Cards.
     */
    public function printAll(Request $request)
    {
        $cari = $request->get('cari', '');
        $role = $request->get('role', '');
        
        $query = DB::table('profil_anggota as p')
            ->leftJoin('users as u', 'p.id_user', '=', 'u.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->select(
                'p.id_user',
                'p.nama_lengkap',
                'p.foto_profil',
                'u.kode_barcode',
                'r.nama_role'
            );
        
        if ($cari) {
            $query->where(function($q) use ($cari) {
                $q->where('p.nama_lengkap', 'like', "%{$cari}%")
                  ->orWhere('u.kode_barcode', 'like', "%{$cari}%");
            });
        }
        
        if ($role) {
            $query->where('r.nama_role', $role);
        }
        
        $members = $query->orderBy('p.nama_lengkap', 'asc')->get();
        
        return view('idcard.print-all', compact('members'));
    }
}