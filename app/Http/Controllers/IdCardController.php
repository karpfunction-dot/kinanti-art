<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\PhotoUrl;

class IdCardController extends Controller
{
    private function isAdmin(): bool
    {
        return strtolower(auth()->user()->role->nama_role ?? '') === 'admin';
    }

    /**
     * Display list of members for ID Card printing.
     */
    public function index(Request $request)
    {
        $isAdmin = $this->isAdmin();
        $currentUserId = auth()->id();
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

        if ($isAdmin) {
            if ($cari) {
                $query->where(function($q) use ($cari) {
                    $q->where('p.nama_lengkap', 'like', "%{$cari}%")
                      ->orWhere('u.kode_barcode', 'like', "%{$cari}%");
                });
            }

            if ($role) {
                $query->where('r.nama_role', $role);
            }
        } else {
            $query->where('p.id_user', $currentUserId);
            $cari = '';
            $role = '';
        }
        
        $members = $query->orderBy('p.nama_lengkap', 'asc')->get();
        $members->transform(function ($member) {
            $member->foto_url = PhotoUrl::resolve($member->foto_profil ?? null);
            return $member;
        });
        
        $roles = $isAdmin
            ? DB::table('roles')->select('nama_role')->orderBy('nama_role')->get()
            : collect();
        
        return view('idcard.index', compact('members', 'roles', 'cari', 'role', 'isAdmin'));
    }
    
    /**
     * Preview single ID Card (Native Style with QR Code).
     */
    public function preview($id)
    {
        if (!$this->isAdmin() && (int) auth()->id() !== (int) $id) {
            abort(403, 'Akses ditolak');
        }

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
        
        $fotoPath = PhotoUrl::resolve($member->foto_profil ?? null);
        
        // QR Code URL
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=" . urlencode($idcode);
        
        return view('idcard.preview', compact('member', 'nama', 'idcode', 'role', 'fotoPath', 'qrCodeUrl'));
    }
    
    /**
     * Print all ID Cards.
     */
    public function printAll(Request $request)
    {
        if (!$this->isAdmin()) {
            abort(403, 'Akses ditolak');
        }

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
        $members->transform(function ($member) {
            $member->foto_url = PhotoUrl::resolve($member->foto_profil ?? null);
            return $member;
        });
        
        return view('idcard.print-all', compact('members'));
    }
}
