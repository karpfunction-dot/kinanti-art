<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        // Filter bulan (default: null = semua bulan)
        $filterBulan = $request->get('bulan', null);
        
        // Build query for all transactions
        $query = DB::table('transaksi_spp')
            ->select(
                'id_transaksi_spp as id',
                'id_user',
                DB::raw("'SPP' as jenis"),
                'periode as detail',
                'tanggal_pembayaran',
                'tanggal_rekap',
                'total',
                'keterangan',
                DB::raw("'transaksi_spp' as sumber")
            )
            ->union(
                DB::table('transaksi_tabungan')
                    ->select(
                        'id_transaksi_tabungan as id',
                        'id_user',
                        DB::raw("'Tabungan' as jenis"),
                        'jenis as detail',
                        'tanggal_pembayaran',
                        'tanggal_rekap',
                        'total',
                        'keterangan',
                        DB::raw("'transaksi_tabungan' as sumber")
                    )
            )
            ->union(
                DB::table('transaksi_lainnya')
                    ->select(
                        'id_transaksi_lainnya as id',
                        'id_user',
                        DB::raw("'Lainnya' as jenis"),
                        'kategori as detail',
                        'tanggal_pembayaran',
                        'tanggal_rekap',
                        'total',
                        'keterangan',
                        DB::raw("'transaksi_lainnya' as sumber")
                    )
            );
        
        // Apply filter bulan if provided
        if ($filterBulan) {
            $query->whereRaw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') = ?", [$filterBulan]);
        }
        
        $transaksi = $query->orderByRaw('COALESCE(tanggal_rekap, tanggal_pembayaran) DESC')->get();
        
        // Get user names for each transaction
        foreach ($transaksi as $t) {
            $user = DB::table('users as u')
                ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                ->where('u.id_user', $t->id_user)
                ->select('p.nama_lengkap', 'u.kode_barcode')
                ->first();
            $t->nama_lengkap = $user->nama_lengkap ?? 'User ' . $t->id_user;
            $t->kode_barcode = $user->kode_barcode ?? '-';
        }
        
        // Get available months for filter
        $availableMonths = DB::table('transaksi_spp')
            ->select(DB::raw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') as bulan"))
            ->union(
                DB::table('transaksi_tabungan')
                    ->select(DB::raw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') as bulan"))
            )
            ->union(
                DB::table('transaksi_lainnya')
                    ->select(DB::raw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') as bulan"))
            )
            ->distinct()
            ->orderBy('bulan', 'desc')
            ->get();
        
        // Statistics per month
        $statistik = [];
        foreach ($availableMonths as $month) {
            $statistik[$month->bulan] = [
                'total_spp' => DB::table('transaksi_spp')->whereRaw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') = ?", [$month->bulan])->sum('total'),
                'total_tabungan' => DB::table('transaksi_tabungan')->whereRaw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') = ?", [$month->bulan])->sum('total'),
                'total_lainnya' => DB::table('transaksi_lainnya')->whereRaw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') = ?", [$month->bulan])->sum('total'),
                'total_semua' => 0
            ];
            $statistik[$month->bulan]['total_semua'] = 
                $statistik[$month->bulan]['total_spp'] + 
                $statistik[$month->bulan]['total_tabungan'] + 
                $statistik[$month->bulan]['total_lainnya'];
        }
        
        $bulanAngka = [
            "01" => "Januari", "02" => "Februari", "03" => "Maret",
            "04" => "April", "05" => "Mei", "06" => "Juni",
            "07" => "Juli", "08" => "Agustus", "09" => "September",
            "10" => "Oktober", "11" => "November", "12" => "Desember"
        ];
        
        return view('transaksi.index', compact('transaksi', 'bulanAngka', 'availableMonths', 'filterBulan', 'statistik'));
    }
    
    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('transaksi.index')->with('error', 'Akses ditolak');
        }
        
        $validator = Validator::make($request->all(), [
            'jenis' => 'required|in:SPP,Tabungan,Lainnya',
            'id_user' => 'required|exists:users,id_user',
            'total' => 'required|numeric|min:0',
            'tanggal_pembayaran' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);
        
        if ($request->jenis == 'SPP') {
            $validator->addRules(['bulan' => 'required|string', 'tahun' => 'required|numeric']);
        }
        
        if ($request->jenis == 'Tabungan') {
            $validator->addRules(['jenis_tabungan' => 'required|in:Setor,Tarik']);
        }
        
        if ($request->jenis == 'Lainnya') {
            $validator->addRules(['kategori' => 'required|string']);
        }
        
        if ($validator->fails()) {
            return redirect()->route('transaksi.index')
                ->with('error', $validator->errors()->first())
                ->withInput();
        }
        
        try {
            $data = [
                'id_user' => $request->id_user,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
                'tanggal_rekap' => now(),
                'total' => $request->total,
                'keterangan' => $request->keterangan,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            if ($request->jenis == 'SPP') {
                $data['periode'] = $request->bulan . '-' . $request->tahun;
                DB::table('transaksi_spp')->insert($data);
            } elseif ($request->jenis == 'Tabungan') {
                $data['jenis'] = $request->jenis_tabungan;
                DB::table('transaksi_tabungan')->insert($data);
            } else {
                $data['kategori'] = $request->kategori;
                DB::table('transaksi_lainnya')->insert($data);
            }
            
            return redirect()->route('transaksi.index')
                ->with('success', '✅ Transaksi berhasil disimpan');
                
        } catch (\Exception $e) {
            return redirect()->route('transaksi.index')
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified transaction.
     */
    public function destroy($sumber, $id)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('transaksi.index')->with('error', 'Akses ditolak');
        }
        
        try {
            DB::table($sumber)->where('id', $id)->delete();
            return redirect()->route('transaksi.index')
                ->with('success', '🗑️ Transaksi berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('transaksi.index')
                ->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
    
    /**
     * Search users for AJAX.
     */
    public function searchUser(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $users = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->where('p.nama_lengkap', 'like', "%{$query}%")
            ->orWhere('u.kode_barcode', 'like', "%{$query}%")
            ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
            ->limit(10)
            ->get();
        
        return response()->json($users);
    }
    
    /**
     * Get transaction statistics for dashboard.
     */
    public function getStats()
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }
        
        $tahunIni = date('Y');
        $bulanIni = date('m');
        
        // Total SPP tahun ini
        $totalSpp = DB::table('transaksi_spp')
            ->whereYear('tanggal_pembayaran', $tahunIni)
            ->sum('total');
        
        // Total Tabungan tahun ini
        $totalTabungan = DB::table('transaksi_tabungan')
            ->whereYear('tanggal_pembayaran', $tahunIni)
            ->sum('total');
        
        // Total Lainnya tahun ini
        $totalLainnya = DB::table('transaksi_lainnya')
            ->whereYear('tanggal_pembayaran', $tahunIni)
            ->sum('total');
        
        // Transaksi bulan ini
        $transaksiBulanIni = DB::table('transaksi_spp')
            ->whereYear('tanggal_pembayaran', $tahunIni)
            ->whereMonth('tanggal_pembayaran', $bulanIni)
            ->count()
            + DB::table('transaksi_tabungan')
            ->whereYear('tanggal_pembayaran', $tahunIni)
            ->whereMonth('tanggal_pembayaran', $bulanIni)
            ->count()
            + DB::table('transaksi_lainnya')
            ->whereYear('tanggal_pembayaran', $tahunIni)
            ->whereMonth('tanggal_pembayaran', $bulanIni)
            ->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_spp' => $totalSpp,
                'total_tabungan' => $totalTabungan,
                'total_lainnya' => $totalLainnya,
                'total_semua' => $totalSpp + $totalTabungan + $totalLainnya,
                'transaksi_bulan_ini' => $transaksiBulanIni,
            ]
        ]);
    }
    /**  
     * LAPORAN TRANSAKSI KEUANGAN
    */
public function laporanKeuangan(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $bulan = $request->get('bulan', date('Y-m'));
        $tahun = $request->get('tahun', date('Y'));
        $view = $request->get('view', 'global'); // global, per_siswa, per_pelatih
        $search = $request->get('search', '');
        $id_user = $request->get('id_user', null);
        
        if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $bulan = date('Y-m');
        }
        
        $startDate = $bulan . '-01';
        $endDate = Carbon::parse($bulan . '-01')->endOfMonth()->format('Y-m-d');
        
        // ======================================================
        // 1. PENDAPATAN SPP BULAN INI
        // ======================================================
        $pendapatanSpp = DB::table('transaksi_spp')
            ->whereBetween('tanggal_pembayaran', [$startDate, $endDate])
            ->sum('total');
        
        $detailSpp = DB::table('transaksi_spp as t')
            ->leftJoin('profil_anggota as p', 't.id_user', '=', 'p.id_user')
            ->whereBetween('t.tanggal_pembayaran', [$startDate, $endDate])
            ->select('t.*', 'p.nama_lengkap', 'p.foto_profil')
            ->orderBy('t.tanggal_pembayaran', 'desc')
            ->get();
        
        // ======================================================
        // 2. TABUNGAN BULAN INI
        // ======================================================
        $tabunganSetor = DB::table('transaksi_tabungan')
            ->whereBetween('tanggal_pembayaran', [$startDate, $endDate])
            ->where('jenis', 'Setor')
            ->sum('total');
        
        $tabunganTarik = DB::table('transaksi_tabungan')
            ->whereBetween('tanggal_pembayaran', [$startDate, $endDate])
            ->where('jenis', 'Tarik')
            ->sum('total');
        
        $detailTabungan = DB::table('transaksi_tabungan as t')
            ->leftJoin('profil_anggota as p', 't.id_user', '=', 'p.id_user')
            ->whereBetween('t.tanggal_pembayaran', [$startDate, $endDate])
            ->select('t.*', 'p.nama_lengkap', 'p.foto_profil')
            ->orderBy('t.tanggal_pembayaran', 'desc')
            ->get();
        
        // ======================================================
        // 3. HONOR PELATIH BULAN INI
        // ======================================================
        $accountingSetting = DB::table('accounting_setting')
            ->where('tahun_bulan', $bulan)
            ->first();
        
        $omset = $accountingSetting->omset_manual ?? 0;
        $operasional = $accountingSetting->operasional_manual ?? 0;
        $netIncome = $omset - $operasional;
        
        $honorData = [];
        if ($accountingSetting) {
            $honorData = [
                'pelatih' => $netIncome * ($accountingSetting->pelatih_percent / 100),
                'admin' => $netIncome * ($accountingSetting->admin_percent / 100),
                'manajemen_keuangan' => $netIncome * ($accountingSetting->manajemen_keuangan_percent / 100),
                'manajemen_sapras' => $netIncome * ($accountingSetting->manajemen_sapras_percent / 100),
                'koreografi' => $netIncome * ($accountingSetting->koreo_default_percent / 100),
                'transport' => $accountingSetting->transport_nominal * $accountingSetting->max_pertemuan,
            ];
            $honorData['total_honor'] = array_sum($honorData);
            $honorData['sisa_sanggar'] = $netIncome - $honorData['total_honor'];
        }
        
        // Detail honor per pelatih (dari koreografi)
        $detailHonorPelatih = DB::table('accounting_koreografi as k')
            ->leftJoin('lagu as l', 'k.id_lagu', '=', 'l.id_lagu')
            ->leftJoin('profil_anggota as p', 'k.id_pelatih', '=', 'p.id_user')
            ->where('k.tahun_bulan', $bulan)
            ->select('k.*', 'l.judul_lagu', 'p.nama_lengkap', 'p.id_user as pelatih_id')
            ->get();
        
        foreach ($detailHonorPelatih as $h) {
            $h->honor = $netIncome * ($h->percent_koreo / 100);
        }
        
        // ======================================================
        // 4. REKAP PER SISWA (jika view per_siswa)
        // ======================================================
        $rekapPerSiswa = [];
        if ($view == 'per_siswa') {
            $siswa = DB::table('users as u')
                ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
                ->where('r.nama_role', 'siswa')
                ->when($search, function($q) use ($search) {
                    $q->where('p.nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('u.kode_barcode', 'like', "%{$search}%");
                })
                ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
                ->orderBy('p.nama_lengkap')
                ->get();
            
            foreach ($siswa as $s) {
                $spp = DB::table('transaksi_spp')
                    ->where('id_user', $s->id_user)
                    ->whereBetween('tanggal_pembayaran', [$startDate, $endDate])
                    ->sum('total');
                
                $tabungan = DB::table('transaksi_tabungan')
                    ->where('id_user', $s->id_user)
                    ->whereBetween('tanggal_pembayaran', [$startDate, $endDate])
                    ->where('jenis', 'Setor')
                    ->sum('total');
                
                $tabunganTarikSiswa = DB::table('transaksi_tabungan')
                    ->where('id_user', $s->id_user)
                    ->whereBetween('tanggal_pembayaran', [$startDate, $endDate])
                    ->where('jenis', 'Tarik')
                    ->sum('total');
                
                $rekapPerSiswa[] = [
                    'id_user' => $s->id_user,
                    'nama_lengkap' => $s->nama_lengkap ?? 'Tanpa Nama',
                    'kode_barcode' => $s->kode_barcode ?? '-',
                    'total_spp' => $spp,
                    'total_tabungan' => $tabungan,
                    'total_tarik' => $tabunganTarikSiswa,
                    'saldo_tabungan' => $tabungan - $tabunganTarikSiswa,
                ];
            }
        }
        
        // ======================================================
        // 5. REKAP PER PELATIH (jika view per_pelatih)
        // ======================================================
        $rekapPerPelatih = [];
        if ($view == 'per_pelatih') {
            $pelatih = DB::table('users as u')
                ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
                ->where('r.nama_role', 'pelatih')
                ->when($search, function($q) use ($search) {
                    $q->where('p.nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('u.kode_barcode', 'like', "%{$search}%");
                })
                ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
                ->orderBy('p.nama_lengkap')
                ->get();
            
            foreach ($pelatih as $p) {
                $koreografi = DB::table('accounting_koreografi')
                    ->where('id_pelatih', $p->id_user)
                    ->where('tahun_bulan', $bulan)
                    ->get();
                
                $totalKoreoPercent = $koreografi->sum('percent_koreo');
                $honorKoreografi = $netIncome * ($totalKoreoPercent / 100);
                
                $rekapPerPelatih[] = [
                    'id_user' => $p->id_user,
                    'nama_lengkap' => $p->nama_lengkap ?? 'Tanpa Nama',
                    'kode_barcode' => $p->kode_barcode ?? '-',
                    'total_koreografi' => $koreografi->count(),
                    'total_persen' => $totalKoreoPercent,
                    'honor' => $honorKoreografi,
                ];
            }
        }
        
        $bulanAngka = [
            "01" => "Januari", "02" => "Februari", "03" => "Maret",
            "04" => "April", "05" => "Mei", "06" => "Juni",
            "07" => "Juli", "08" => "Agustus", "09" => "September",
            "10" => "Oktober", "11" => "November", "12" => "Desember"
        ];
        
        return view('transaksi.laporan-keuangan', compact(
            'bulan', 'view', 'search', 'startDate', 'endDate',
            'pendapatanSpp', 'detailSpp',
            'tabunganSetor', 'tabunganTarik', 'detailTabungan',
            'honorData', 'detailHonorPelatih', 'netIncome', 'accountingSetting',
            'rekapPerSiswa', 'rekapPerPelatih', 'bulanAngka'
        ));
    }
    
    /**
     * Export financial report to PDF
     */
    public function exportLaporanKeuangan(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $bulan = $request->get('bulan', date('Y-m'));
        $view = $request->get('view', 'global');
        
        // Sama seperti method laporanKeuangan, tapi untuk PDF
        // ... (copy data preparation)
        
        $pdf = PDF::loadView('transaksi.laporan-keuangan-pdf', compact('bulan', 'view'));
        return $pdf->download('Laporan_Keuangan_' . $bulan . '.pdf');
    }
    
     /**
     * update rekap
     */

     public function laporan(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $tab = $request->get('tab', 'spp'); // spp, tabungan, detail
        $bulan = $request->get('bulan', date('Y-m'));
        $id_user = $request->get('id_user', null);
        
        // Daftar bulan untuk filter
        $bulanList = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $bulanList[] = [
                'value' => $date->format('Y-m'),
                'nama' => $date->format('F Y')
            ];
        }
        
        // ======================================================
        // 1. REKAP SPP PER BULAN
        // ======================================================
        $rekapSpp = [];
        if ($tab == 'spp') {
            // Ambil semua siswa
            $siswa = DB::table('users as u')
                ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
                ->where('r.nama_role', 'siswa')
                ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
                ->orderBy('p.nama_lengkap')
                ->get();
            
            // Ambil transaksi SPP bulan ini
            $sppTransaksi = DB::table('transaksi_spp')
                ->whereRaw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') = ?", [$bulan])
                ->get()
                ->keyBy('id_user');
            
            foreach ($siswa as $s) {
                $rekapSpp[] = [
                    'id_user' => $s->id_user,
                    'nama_lengkap' => $s->nama_lengkap ?? 'Tanpa Nama',
                    'kode_barcode' => $s->kode_barcode ?? '-',
                    'status' => isset($sppTransaksi[$s->id_user]) ? 'Sudah Bayar' : 'Belum Bayar',
                    'total' => isset($sppTransaksi[$s->id_user]) ? $sppTransaksi[$s->id_user]->total : 0,
                    'tanggal' => isset($sppTransaksi[$s->id_user]) ? $sppTransaksi[$s->id_user]->tanggal_pembayaran : null,
                ];
            }
        }
        
        // ======================================================
        // 2. REKAP TABUNGAN PER SISWA
        // ======================================================
        $rekapTabungan = [];
        if ($tab == 'tabungan') {
            $siswa = DB::table('users as u')
                ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
                ->where('r.nama_role', 'siswa')
                ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
                ->orderBy('p.nama_lengkap')
                ->get();
            
            foreach ($siswa as $s) {
                // Total setoran
                $totalSetor = DB::table('transaksi_tabungan')
                    ->where('id_user', $s->id_user)
                    ->where('jenis', 'Setor')
                    ->sum('total');
                
                // Total tarikan
                $totalTarik = DB::table('transaksi_tabungan')
                    ->where('id_user', $s->id_user)
                    ->where('jenis', 'Tarik')
                    ->sum('total');
                
                $saldo = $totalSetor - $totalTarik;
                
                // Transaksi terakhir
                $lastTransaksi = DB::table('transaksi_tabungan')
                    ->where('id_user', $s->id_user)
                    ->orderBy('tanggal_pembayaran', 'desc')
                    ->first();
                
                $rekapTabungan[] = [
                    'id_user' => $s->id_user,
                    'nama_lengkap' => $s->nama_lengkap ?? 'Tanpa Nama',
                    'kode_barcode' => $s->kode_barcode ?? '-',
                    'total_setor' => $totalSetor,
                    'total_tarik' => $totalTarik,
                    'saldo' => $saldo,
                    'last_transaksi' => $lastTransaksi ? $lastTransaksi->tanggal_pembayaran : null,
                ];
            }
            
            // Urutkan berdasarkan saldo tertinggi
            usort($rekapTabungan, function($a, $b) {
                return $b['saldo'] <=> $a['saldo'];
            });
        }
        
        // ======================================================
        // 3. DETAIL TRANSAKSI PER SISWA
        // ======================================================
        $detailTransaksi = [];
        $selectedSiswa = null;
        
        if ($tab == 'detail') {
            // Ambil daftar siswa untuk dropdown
            $daftarSiswa = DB::table('users as u')
                ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
                ->where('r.nama_role', 'siswa')
                ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
                ->orderBy('p.nama_lengkap')
                ->get();
            
            if ($id_user) {
                // Ambil data siswa
                $selectedSiswa = DB::table('users as u')
                    ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                    ->where('u.id_user', $id_user)
                    ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
                    ->first();
                
                // Ambil semua transaksi siswa
                $spp = DB::table('transaksi_spp')
                    ->where('id_user', $id_user)
                    ->select(
                        DB::raw("'SPP' as jenis"),
                        'periode as detail',
                        'tanggal_pembayaran',
                        'total',
                        'keterangan'
                    )
                    ->get();
                
                $tabungan = DB::table('transaksi_tabungan')
                    ->where('id_user', $id_user)
                    ->select(
                        DB::raw("'Tabungan' as jenis"),
                        'jenis as detail',
                        'tanggal_pembayaran',
                        'total',
                        'keterangan'
                    )
                    ->get();
                
                $lainnya = DB::table('transaksi_lainnya')
                    ->where('id_user', $id_user)
                    ->select(
                        DB::raw("'Lainnya' as jenis"),
                        'kategori as detail',
                        'tanggal_pembayaran',
                        'total',
                        'keterangan'
                    )
                    ->get();
                
                $detailTransaksi = $spp->concat($tabungan)->concat($lainnya)
                    ->sortByDesc('tanggal_pembayaran');
                
                // Hitung statistik
                $statistik = [
                    'total_spp' => $spp->sum('total'),
                    'total_tabungan_setor' => $tabungan->where('detail', 'Setor')->sum('total'),
                    'total_tabungan_tarik' => $tabungan->where('detail', 'Tarik')->sum('total'),
                    'total_lainnya' => $lainnya->sum('total'),
                    'saldo_tabungan' => $tabungan->where('detail', 'Setor')->sum('total') - $tabungan->where('detail', 'Tarik')->sum('total'),
                ];
            } else {
                $statistik = null;
            }
        } else {
            $daftarSiswa = [];
            $statistik = null;
        }
        
        $bulanAngka = [
            "01" => "Januari", "02" => "Februari", "03" => "Maret",
            "04" => "April", "05" => "Mei", "06" => "Juni",
            "07" => "Juli", "08" => "Agustus", "09" => "September",
            "10" => "Oktober", "11" => "November", "12" => "Desember"
        ];
        
        return view('transaksi.laporan', compact(
            'tab', 'bulan', 'id_user', 'bulanList', 'bulanAngka',
            'rekapSpp', 'rekapTabungan', 'detailTransaksi', 'selectedSiswa',
            'daftarSiswa', 'statistik'
        ));
    }
    /**
     * Get monthly report.
     */
    public function getMonthlyReport(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }
        
        $tahun = $request->get('tahun', date('Y'));
        
        $monthlyReport = [];
        
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $bulanStr = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            
            $spp = DB::table('transaksi_spp')
                ->whereYear('tanggal_pembayaran', $tahun)
                ->whereMonth('tanggal_pembayaran', $bulan)
                ->sum('total');
            
            $tabungan = DB::table('transaksi_tabungan')
                ->whereYear('tanggal_pembayaran', $tahun)
                ->whereMonth('tanggal_pembayaran', $bulan)
                ->sum('total');
            
            $lainnya = DB::table('transaksi_lainnya')
                ->whereYear('tanggal_pembayaran', $tahun)
                ->whereMonth('tanggal_pembayaran', $bulan)
                ->sum('total');
            
            $monthlyReport[] = [
                'bulan' => $bulanStr,
                'nama_bulan' => $this->getNamaBulan($bulan),
                'spp' => $spp,
                'tabungan' => $tabungan,
                'lainnya' => $lainnya,
                'total' => $spp + $tabungan + $lainnya
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $monthlyReport,
            'tahun' => $tahun
        ]);
    }
    
    /**
     * Get bulan name in Indonesian.
     */
    private function getNamaBulan($bulan)
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $namaBulan[$bulan] ?? '';
    }
}