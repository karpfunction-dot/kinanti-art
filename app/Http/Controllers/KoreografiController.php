<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KoreografiController extends Controller
{
    /**
     * Display a listing of koreografi.
     */
    public function index(Request $request)
    {
        // Cek apakah user adalah admin
        if (auth()->user()->role->nama_role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $bulan = $request->get('bulan', date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $bulan = date('Y-m');
        }
        
        // 1. KOREOGRAFI BULAN BERJALAN (bulan yang dipilih)
        $koreografiBulanIni = DB::table('accounting_koreografi as k')
            ->leftJoin('lagu as l', 'k.id_lagu', '=', 'l.id_lagu')
            ->leftJoin('users as u', 'k.id_pelatih', '=', 'u.id_user')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->where('k.tahun_bulan', $bulan)
            ->select(
                'k.id_koreografi',
                'k.tahun_bulan',
                'k.id_lagu',
                'k.id_pelatih',
                'k.percent_koreo',
                'l.judul_lagu',
                'u.kode_barcode',
                'p.nama_lengkap as nama_pelatih'
            )
            ->orderBy('k.id_koreografi', 'desc')
            ->get();
        
        // 2. HISTORI KOREOGRAFI (semua bulan, untuk arsip)
        $riwayatKoreografi = DB::table('accounting_koreografi as k')
            ->leftJoin('lagu as l', 'k.id_lagu', '=', 'l.id_lagu')
            ->leftJoin('users as u', 'k.id_pelatih', '=', 'u.id_user')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->select(
                'k.id_koreografi',
                'k.tahun_bulan',
                'k.id_lagu',
                'k.id_pelatih',
                'k.percent_koreo',
                'l.judul_lagu',
                'u.kode_barcode',
                'p.nama_lengkap as nama_pelatih'
            )
            ->orderBy('k.tahun_bulan', 'desc')
            ->orderBy('k.id_koreografi', 'desc')
            ->get();
        
        // Ambil daftar lagu untuk form
        $laguList = DB::table('lagu')
            ->orderBy('judul_lagu')
            ->get();
        
        // Ambil daftar pelatih dari wewenang
        $pelatihList = DB::table('wewenang as w')
            ->join('users as u', 'u.id_user', '=', 'w.id_user')
            ->join('profil_anggota as p', 'p.id_user', '=', 'u.id_user')
            ->join('tugas as t', 't.id_tugas', '=', 'w.id_tugas')
            ->where('w.aktif', 1)
            ->where(function($q) {
                $q->where('t.nama_tugas', 'like', '%pelatih%')
                  ->orWhere('t.kategori', 'pelatih');
            })
            ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
            ->distinct()
            ->orderBy('p.nama_lengkap')
            ->get();
        
        // Jika tidak ada pelatih, ambil dari barcode PLT
        if ($pelatihList->isEmpty()) {
            $pelatihList = DB::table('users as u')
                ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                ->where('u.kode_barcode', 'like', 'PLT-%')
                ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
                ->orderBy('u.id_user')
                ->get();
        }
        
        // Daftar bulan unik untuk filter
        $availableMonths = DB::table('accounting_koreografi')
            ->select('tahun_bulan')
            ->distinct()
            ->orderBy('tahun_bulan', 'desc')
            ->get();
        
        $editing = null;
        if ($request->has('edit') && is_numeric($request->edit)) {
            $editing = DB::table('accounting_koreografi')
                ->where('id_koreografi', $request->edit)
                ->first();
        }
        
        return view('koreografi.index', compact(
            'koreografiBulanIni', 
            'riwayatKoreografi', 
            'laguList', 
            'pelatihList', 
            'bulan', 
            'editing', 
            'availableMonths'
        ));
    }
    
    /**
     * Store a newly created koreografi.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun_bulan' => 'required|date_format:Y-m',
            'id_lagu' => 'required|exists:lagu,id_lagu',
            'id_pelatih' => 'required|exists:users,id_user',
            'percent_koreo' => 'required|numeric|min:0|max:100',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('koreografi.index', ['bulan' => $request->tahun_bulan])
                ->with('error', 'Validasi gagal: ' . $validator->errors()->first());
        }
        
        try {
            // Cek duplikasi - jika sudah ada, update saja
            $existing = DB::table('accounting_koreografi')
                ->where('tahun_bulan', $request->tahun_bulan)
                ->where('id_lagu', $request->id_lagu)
                ->first();
            
            if ($existing) {
                // Update existing
                DB::table('accounting_koreografi')->where('id_koreografi', $existing->id_koreografi)->update([
                    'id_pelatih' => $request->id_pelatih,
                    'percent_koreo' => $request->percent_koreo,
                    'updated_by' => auth()->user()->id_user,
                    'updated_at' => now(),
                ]);
                $message = '✅ Koreografi berhasil diperbarui';
            } else {
                // Insert new
                DB::table('accounting_koreografi')->insert([
                    'tahun_bulan' => $request->tahun_bulan,
                    'id_lagu' => $request->id_lagu,
                    'id_pelatih' => $request->id_pelatih,
                    'percent_koreo' => $request->percent_koreo,
                    'created_by' => auth()->user()->id_user,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $message = '✅ Koreografi berhasil ditambahkan';
            }
            
            return redirect()->route('koreografi.index', ['bulan' => $request->tahun_bulan])
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return redirect()->route('koreografi.index', ['bulan' => $request->tahun_bulan])
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
    
    /**
     * Update the specified koreografi.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tahun_bulan' => 'required|date_format:Y-m',
            'id_lagu' => 'required|exists:lagu,id_lagu',
            'id_pelatih' => 'required|exists:users,id_user',
            'percent_koreo' => 'required|numeric|min:0|max:100',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('koreografi.index', ['bulan' => $request->tahun_bulan])
                ->with('error', 'Validasi gagal: ' . $validator->errors()->first());
        }
        
        try {
            // Cek duplikasi (kecuali diri sendiri)
            $exists = DB::table('accounting_koreografi')
                ->where('tahun_bulan', $request->tahun_bulan)
                ->where('id_lagu', $request->id_lagu)
                ->where('id_koreografi', '!=', $id)
                ->exists();
            
            if ($exists) {
                return redirect()->route('koreografi.index', ['bulan' => $request->tahun_bulan])
                    ->with('error', 'Koreografi untuk lagu ini sudah ada bulan ini.');
            }
            
            DB::table('accounting_koreografi')->where('id_koreografi', $id)->update([
                'id_lagu' => $request->id_lagu,
                'id_pelatih' => $request->id_pelatih,
                'percent_koreo' => $request->percent_koreo,
                'updated_by' => auth()->user()->id_user,
                'updated_at' => now(),
            ]);
            
            return redirect()->route('koreografi.index', ['bulan' => $request->tahun_bulan])
                ->with('success', '✏️ Koreografi berhasil diperbarui');
                
        } catch (\Exception $e) {
            return redirect()->route('koreografi.index', ['bulan' => $request->tahun_bulan])
                ->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified koreografi.
     */
    public function destroy(Request $request, $id)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        
        try {
            DB::table('accounting_koreografi')->where('id_koreografi', $id)->delete();
            
            return redirect()->route('koreografi.index', ['bulan' => $bulan])
                ->with('success', '🗑️ Koreografi berhasil dihapus');
                
        } catch (\Exception $e) {
            return redirect()->route('koreografi.index', ['bulan' => $bulan])
                ->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}