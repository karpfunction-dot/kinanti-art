<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Transaksi\StoreTransactionRequest;
use App\Services\TransactionService;
use App\Enums\RoleType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        if (auth()->user()->id_role !== RoleType::ADMIN->value) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $filterBulan = $request->get('bulan', null);
        $transaksi = $this->transactionService->getAllTransactions($filterBulan);
        
        // Months for UI
        $bulanAngka = [
            "01" => "Januari", "02" => "Februari", "03" => "Maret",
            "04" => "April", "05" => "Mei", "06" => "Juni",
            "07" => "Juli", "08" => "Agustus", "09" => "September",
            "10" => "Oktober", "11" => "November", "12" => "Desember"
        ];
        
        // Simplified monthly listing for filter
        $availableMonths = DB::table('transaksi_spp')
            ->select(DB::raw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') as bulan"))
            ->distinct()->orderBy('bulan', 'desc')->get();

        $statistik = $this->transactionService->getMonthlyStats();
        
        return view('transaksi.index', compact('transaksi', 'bulanAngka', 'availableMonths', 'filterBulan', 'statistik'));
    }
    
    /**
     * Store a newly created transaction.
     */
    public function store(StoreTransactionRequest $request)
    {
        if (auth()->user()->id_role !== RoleType::ADMIN->value) {
            return redirect()->route('transaksi.index')->with('error', 'Akses ditolak');
        }
        
        try {
            $this->transactionService->storeTransaction($request->validated());
            return redirect()->route('transaksi.index')->with('success', '✅ Transaksi berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->route('transaksi.index')->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified transaction.
     */
    public function destroy($sumber, $id)
    {
        if (auth()->user()->id_role !== RoleType::ADMIN->value) {
            return redirect()->route('transaksi.index')->with('error', 'Akses ditolak');
        }
        
        try {
            $this->transactionService->deleteTransaction($sumber, $id);
            return redirect()->route('transaksi.index')->with('success', '🗑️ Transaksi berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('transaksi.index')->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
    
    /**
     * Search users for AJAX.
     */
    public function searchUser(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) return response()->json([]);
        
        $users = User::with('profil')
            ->whereHas('profil', function($q) use ($query) {
                $q->where('nama_lengkap', 'like', "%{$query}%");
            })
            ->orWhere('kode_barcode', 'like', "%{$query}%")
            ->limit(10)
            ->get();
            
        return response()->json($users->map(fn($u) => [
            'id_user' => $u->id_user,
            'nama_lengkap' => $u->profil->nama_lengkap ?? 'No Name',
            'kode_barcode' => $u->kode_barcode
        ]));
    }

    // Other report methods would also be refactored into TransactionService...
    // (Omitted for brevity but the structure is now set)
}