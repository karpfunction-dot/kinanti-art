<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AbsensiService;
use App\Enums\RoleType;

class AbsensiController extends Controller
{
    protected $absensiService;

    public function __construct(AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }

    /**
     * Menampilkan daftar riwayat absensi.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['bulan', 'role', 'search']);
        $rows = $this->absensiService->getHistory($filters);
        
        $roles = DB::table('roles')->select('nama_role')->get();
        $months = $this->getMonths();
        $years = range(date('Y') - 2, date('Y') + 1);
        
        return view('absensi.index', array_merge($filters, [
            'rows' => $rows,
            'roles' => $roles,
            'months' => $months,
            'years' => $years
        ]));
    }

    public function scan()
    {
        return view('absensi.scan');
    }

    /**
     * Unified Process method for both Form and API.
     */
    public function proses(Request $request)
    {
        $request->validate(['kode_barcode' => 'required|string|min:3']);

        try {
            $user = $this->absensiService->processBarcode($request->kode_barcode, auth()->user());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '✅ Absensi Berhasil: ' . $user->nama_lengkap,
                    'user' => $user
                ]);
            }

            return redirect()->route('absensi.scan')->with('success', '✅ Absensi berhasil: ' . $user->nama_lengkap);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return redirect()->route('absensi.scan')->with('error', '❌ ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Kept for backward compatibility with routes/web.php
     */
    public function prosesApi(Request $request)
    {
        return $this->proses($request);
    }

    public function pilihKelas()
    {
        $kelas = DB::table('kelas')->where('aktif', 1)->get();
        return view('absensi.pilih_kelas', compact('kelas'));
    }

    public function inputKelas($id_kelas)
    {
        $kelas = DB::table('kelas')->where('id_kelas', $id_kelas)->first();
        if (!$kelas) {
            return redirect()->route('absensi.pilih_kelas')->with('error', 'Kelas tidak ditemukan.');
        }

        $siswas = DB::table('kelas_siswa as ks')
            ->join('users as u', 'ks.id_user', '=', 'u.id_user')
            ->join('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('ks.id_kelas', $id_kelas)
            ->where('ks.aktif', 1)
            ->select('u.id_user', 'u.kode_barcode', 'p.nama_lengkap', 'p.foto_profil', 'r.nama_role')
            ->get();

        $absensi_hari_ini = DB::table('absensi')
            ->where('id_kelas', $id_kelas)
            ->whereDate('tanggal', date('Y-m-d'))
            ->pluck('status', 'id_user')
            ->toArray();

        return view('absensi.input_massal', compact('siswas', 'kelas', 'absensi_hari_ini'));
    }

    public function storeMassal(Request $request)
    {
        try {
            $this->absensiService->storeMassal($request->id_kelas, $request->status ?? [], auth()->user());
            return redirect()->route('absensi.index')->with('success', '✅ Data absensi berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('absensi')->where('id_absensi', $id)->delete();
            return redirect()->route('absensi.index')->with('success', '✅ Data dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('absensi.index')->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    private function getMonths()
    {
        return ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
    }
}