<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class WhatsAppController extends Controller
{
    private $apiKey = 'YOUR_API_KEY_HERE';
    private $baseUrl = 'https://api.fonnte.com/send';
  
    public function index()
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        // Ambil data siswa
        $siswa = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('r.nama_role', 'siswa')
            ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
            ->orderBy('p.nama_lengkap')
            ->get();
        
        $pelatih = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('r.nama_role', 'pelatih')
            ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
            ->orderBy('p.nama_lengkap')
            ->get();
        
        // Data tunggakan SPP
        $bulanIni = date('Y-m');
        $tunggakan = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->leftJoin('transaksi_spp as ts', function($join) use ($bulanIni) {
                $join->on('u.id_user', '=', 'ts.id_user')
                     ->whereRaw("DATE_FORMAT(ts.tanggal_pembayaran, '%Y-%m') = ?", [$bulanIni]);
            })
            ->where('r.nama_role', 'siswa')
            ->whereNull('ts.id_user')
            ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
            ->get();
        
        $accountingSetting = DB::table('accounting_setting')->where('tahun_bulan', $bulanIni)->first();
        $koreografi = DB::table('accounting_koreografi as k')
            ->leftJoin('profil_anggota as p', 'k.id_pelatih', '=', 'p.id_user')
            ->where('k.tahun_bulan', $bulanIni)
            ->select('k.*', 'p.nama_lengkap')
            ->get();
        
        return view('whatsapp.index', compact('siswa', 'pelatih', 'tunggakan', 'koreografi', 'accountingSetting'));
    }
    
    public function send(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak']);
        }
        
        $validator = Validator::make($request->all(), [
            'nomor' => 'required|string',
            'pesan' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        
        $nomor = $this->formatNumber($request->nomor);
        $pesan = $request->pesan;
        
        try {
            $result = $this->sendWhatsApp($nomor, $pesan);
            return response()->json(['success' => true, 'message' => 'Pesan berhasil dikirim ke ' . $nomor]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim: ' . $e->getMessage()]);
        }
    }
    
    public function sendBulk(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak']);
        }
        
        $validator = Validator::make($request->all(), [
            'nomor_list' => 'required|array',
            'pesan' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($request->nomor_list as $nomor) {
            $nomorFormatted = $this->formatNumber($nomor);
            try {
                $this->sendWhatsApp($nomorFormatted, $request->pesan);
                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Pesan terkirim: {$successCount} berhasil, {$failCount} gagal"
        ]);
    }
    
    private function formatNumber($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        
        if (substr($number, 0, 1) === '0') {
            $number = '62' . substr($number, 1);
        }
        
        if (substr($number, 0, 1) === '8') {
            $number = '62' . $number;
        }
        
        return $number;
    }
    
    private function sendWhatsApp($nomor, $pesan)
    {
        \Log::info("WhatsApp Message to {$nomor}: {$pesan}");
        return true;
    }
    
    public function getTemplate($type, Request $request)
    {
        $params = $request->all();
        
        switch ($type) {
            case 'akun':
                $template = "*KINANTI ART PRODUCTIONS*\n\n";
                $template .= "Yth. *{{nama}}*\n\n";
                $template .= "Berikut adalah informasi akun Anda:\n";
                $template .= "━━━━━━━━━━━━━━━━━━━━━\n";
                $template .= "📌 *Kode Barcode*: {{kode_barcode}}\n";
                $template .= "━━━━━━━━━━━━━━━━━━━━━\n\n";
                $template .= "Silakan login menggunakan kode barcode di atas.\n";
                $template .= "Untuk password sementara, hubungi admin sanggar.\n\n";
                $template .= "_Pesan ini dikirim otomatis oleh sistem._";
                break;
                
            case 'tunggakan':
                $template = "*KINANTI ART PRODUCTIONS*\n\n";
                $template .= "Yth. *{{nama}}*\n\n";
                $template .= "Kami ingin mengingatkan bahwa terdapat tunggakan SPP untuk periode:\n";
                $template .= "━━━━━━━━━━━━━━━━━━━━━\n";
                $template .= "📅 *Periode*: {{periode}}\n";
                $template .= "💰 *Total*: Rp {{total}}\n";
                $template .= "━━━━━━━━━━━━━━━━━━━━━\n\n";
                $template .= "Mohon segera melakukan pembayaran.\n\n";
                $template .= "Terima kasih atas perhatiannya.\n\n";
                $template .= "_Pesan ini dikirim otomatis oleh sistem._";
                break;
                
            case 'gaji':
                $template = "*KINANTI ART PRODUCTIONS*\n\n";
                $template .= "Yth. *{{nama}}*\n\n";
                $template .= "Berikut adalah rincian honor Anda untuk periode:\n";
                $template .= "━━━━━━━━━━━━━━━━━━━━━\n";
                $template .= "📅 *Periode*: {{periode}}\n";
                $template .= "━━━━━━━━━━━━━━━━━━━━━\n\n";
                $template .= "*Rincian Honor:*\n";
                $template .= "├ Koreografi: {{koreografi}} lagu\n";
                $template .= "├ Total Persen: {{persen}}%\n";
                $template .= "└ *Total Honor*: Rp {{total}}\n\n";
                $template .= "Terima kasih atas dedikasi Anda.\n\n";
                $template .= "_Pesan ini dikirim otomatis oleh sistem._";
                break;
                
            case 'kegiatan':
                $template = "*KINANTI ART PRODUCTIONS*\n\n";
                $template .= "Yth. *{{nama}}*\n\n";
                $template .= "Informasi kegiatan:\n";
                $template .= "━━━━━━━━━━━━━━━━━━━━━\n";
                $template .= "📌 *Kegiatan*: {{nama_kegiatan}}\n";
                $template .= "📅 *Tanggal*: {{tanggal}}\n";
                $template .= "⏰ *Waktu*: {{waktu}}\n";
                $template .= "📍 *Tempat*: {{tempat}}\n";
                $template .= "━━━━━━━━━━━━━━━━━━━━━\n\n";
                $template .= "Mohon hadir tepat waktu.\n\n";
                $template .= "_Pesan ini dikirim otomatis oleh sistem._";
                break;
                
            default:
                $template = $params['pesan'] ?? '';
        }
        
        return response()->json(['template' => $template]);
    }
    
    public function getRecipients(Request $request)
    {
        $role = $request->get('role', 'siswa');
        
        $query = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role');
        
        if ($role == 'siswa') {
            $query->where('r.nama_role', 'siswa');
        } elseif ($role == 'pelatih') {
            $query->where('r.nama_role', 'pelatih');
        } elseif ($role == 'tunggakan') {
            $bulanIni = date('Y-m');
            $query->where('r.nama_role', 'siswa')
                  ->whereNotExists(function($q) use ($bulanIni) {
                      $q->select(DB::raw(1))
                        ->from('transaksi_spp as ts')
                        ->whereRaw("ts.id_user = u.id_user")
                        ->whereRaw("DATE_FORMAT(ts.tanggal_pembayaran, '%Y-%m') = ?", [$bulanIni]);
                  });
        }
        
        $recipients = $query->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
            ->orderBy('p.nama_lengkap')
            ->get();
        
        return response()->json(['recipients' => $recipients]);
    }
}
