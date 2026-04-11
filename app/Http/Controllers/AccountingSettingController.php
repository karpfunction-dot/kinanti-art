<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AccountingSettingController extends Controller
{
    public function index(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $bulan = $request->get('bulan', date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $bulan = date('Y-m');
        }
        
        // Default settings
        $defaultSetting = [
            'omset_manual' => 0,
            'operasional_manual' => 0,
            'pelatih_percent' => 10.0,
            'admin_percent' => 10.0,
            'manajemen_keuangan_percent' => 10.0,
            'manajemen_sapras_percent' => 10.0,
            'koreo_default_percent' => 2.5,
            'transport_nominal' => 0,
            'max_pertemuan' => 9,
        ];
        
        // Load saved setting
        $setting = DB::table('accounting_setting')
            ->where('tahun_bulan', $bulan)
            ->first();
        
        if ($setting) {
            $settingData = (array) $setting;
        } else {
            $settingData = $defaultSetting;
            $settingData['tahun_bulan'] = $bulan;
        }
        
        // Get SPP omset for current month
        $startDate = $bulan . '-01';
        $endDate = Carbon::parse($bulan . '-01')->endOfMonth()->format('Y-m-d');
        
        $omsetSpp = DB::table('transaksi_spp')
            ->whereBetween('tanggal_pembayaran', [$startDate, $endDate])
            ->sum('total');
        
        // Simple piutang calculation (total siswa * 150000 - collected)
        $totalSiswa = DB::table('users as u')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('r.nama_role', 'siswa')
            ->count();
        
        $expectedSpp = $totalSiswa * 150000; // Assuming SPP is 150k per month
        $piutang = max(0, $expectedSpp - $omsetSpp);
        
        // Get available months for filter
        $availableMonths = DB::table('accounting_setting')
            ->select('tahun_bulan')
            ->distinct()
            ->orderBy('tahun_bulan', 'desc')
            ->get();
        
        $bulanAngka = [
            "01" => "Januari", "02" => "Februari", "03" => "Maret",
            "04" => "April", "05" => "Mei", "06" => "Juni",
            "07" => "Juli", "08" => "Agustus", "09" => "September",
            "10" => "Oktober", "11" => "November", "12" => "Desember"
        ];
        
        return view('accounting.setting', compact(
            'settingData', 'bulan', 'omsetSpp', 'piutang', 
            'availableMonths', 'bulanAngka'
        ));
    }
    
    public function save(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('accounting.setting')->with('error', 'Akses ditolak');
        }
        
        $validator = Validator::make($request->all(), [
            'tahun_bulan' => 'required|date_format:Y-m',
            'omset_manual' => 'required|numeric|min:0',
            'operasional_manual' => 'nullable|numeric|min:0',
            'pelatih_percent' => 'nullable|numeric|min:0|max:100',
            'admin_percent' => 'nullable|numeric|min:0|max:100',
            'manajemen_keuangan_percent' => 'nullable|numeric|min:0|max:100',
            'manajemen_sapras_percent' => 'nullable|numeric|min:0|max:100',
            'koreo_default_percent' => 'nullable|numeric|min:0|max:100',
            'transport_nominal' => 'nullable|numeric|min:0',
            'max_pertemuan' => 'nullable|integer|min:1|max:31',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('accounting.setting', ['bulan' => $request->tahun_bulan])
                ->with('error', $validator->errors()->first());
        }
        
        try {
            $data = [
                'tahun_bulan' => $request->tahun_bulan,
                'omset_manual' => $request->omset_manual,
                'operasional_manual' => $request->operasional_manual ?? 0,
                'pelatih_percent' => $request->pelatih_percent ?? 10,
                'admin_percent' => $request->admin_percent ?? 10,
                'manajemen_keuangan_percent' => $request->manajemen_keuangan_percent ?? 10,
                'manajemen_sapras_percent' => $request->manajemen_sapras_percent ?? 10,
                'koreo_default_percent' => $request->koreo_default_percent ?? 2.5,
                'transport_nominal' => $request->transport_nominal ?? 0,
                'max_pertemuan' => $request->max_pertemuan ?? 9,
                'updated_at' => now(),
            ];
            
            $existing = DB::table('accounting_setting')
                ->where('tahun_bulan', $request->tahun_bulan)
                ->first();
            
            if ($existing) {
                DB::table('accounting_setting')
                    ->where('tahun_bulan', $request->tahun_bulan)
                    ->update($data);
            } else {
                $data['created_at'] = now();
                DB::table('accounting_setting')->insert($data);
            }
            
            return redirect()->route('accounting.setting', ['bulan' => $request->tahun_bulan])
                ->with('success', '✅ Pengaturan accounting berhasil disimpan');
                
        } catch (\Exception $e) {
            return redirect()->route('accounting.setting', ['bulan' => $request->tahun_bulan])
                ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
    
    public function payroll(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $bulan = $request->get('bulan', date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $bulan = date('Y-m');
        }
        
        // Get accounting setting
        $setting = DB::table('accounting_setting')
            ->where('tahun_bulan', $bulan)
            ->first();
        
        if (!$setting) {
            return redirect()->route('accounting.setting', ['bulan' => $bulan])
                ->with('error', 'Silakan atur pengaturan accounting terlebih dahulu untuk bulan ' . $bulan);
        }
        
        // Get omset (priority: manual omset)
        $omset = $setting->omset_manual;
        
        // Get operasional
        $operasional = $setting->operasional_manual;
        
        // Calculate net income after operasional
        $netIncome = $omset - $operasional;
        
        // Calculate honor berdasarkan persentase
        $honor = [
            'pelatih' => $netIncome * ($setting->pelatih_percent / 100),
            'admin' => $netIncome * ($setting->admin_percent / 100),
            'manajemen_keuangan' => $netIncome * ($setting->manajemen_keuangan_percent / 100),
            'manajemen_sapras' => $netIncome * ($setting->manajemen_sapras_percent / 100),
            'koreografi' => $netIncome * ($setting->koreo_default_percent / 100),
        ];
        
        // Get koreografi data
        $koreografi = DB::table('accounting_koreografi as k')
            ->leftJoin('lagu as l', 'k.id_lagu', '=', 'l.id_lagu')
            ->leftJoin('profil_anggota as p', 'k.id_pelatih', '=', 'p.id_user')
            ->where('k.tahun_bulan', $bulan)
            ->select('k.*', 'l.judul_lagu', 'p.nama_lengkap')
            ->get();
        
        // Calculate transport
        $transport = $setting->transport_nominal * $setting->max_pertemuan;
        
        // Calculate total honor
        $totalHonor = array_sum($honor) + $transport;
        $sisa = $netIncome - $totalHonor;
        
        return view('accounting.payroll', compact(
            'setting', 'bulan', 'omset', 'operasional', 'netIncome',
            'honor', 'koreografi', 'transport', 'totalHonor', 'sisa'
        ));
    }
}