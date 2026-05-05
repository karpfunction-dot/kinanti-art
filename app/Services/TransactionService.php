<?php

namespace App\Services;

use App\Models\TransaksiSpp;
use App\Models\TransaksiTabungan;
use App\Models\TransaksiLainnya;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionService
{
    /**
     * Get all transactions combined and ordered.
     */
    public function getAllTransactions(?string $filterBulan = null)
    {
        // Use Query Builder for Union (Eloquent doesn't support Union well for mixed models)
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

        if ($filterBulan) {
            // Fix performance: Use date range
            $start = Carbon::parse($filterBulan)->startOfMonth()->toDateString();
            $end = Carbon::parse($filterBulan)->endOfMonth()->toDateString();
            $query->whereBetween('tanggal_pembayaran', [$start, $end]);
        }

        $results = $query->orderByRaw('COALESCE(tanggal_rekap, tanggal_pembayaran) DESC')->get();

        // Hydrate with User info (Eager loading equivalent for union)
        foreach ($results as $item) {
            $user = User::with('profil')->find($item->id_user);
            $item->nama_lengkap = $user->profil->nama_lengkap ?? 'User ' . $item->id_user;
            $item->kode_barcode = $user->kode_barcode ?? '-';
        }

        return $results;
    }

    /**
     * Get monthly statistics.
     */
    public function getMonthlyStats()
    {
        // ... Logic for stats ...
        // Simplification: Return summary for the current month
        $now = Carbon::now();
        $start = $now->copy()->startOfMonth()->toDateString();
        $end = $now->copy()->endOfMonth()->toDateString();

        return [
            'total_spp' => TransaksiSpp::whereBetween('tanggal_pembayaran', [$start, $end])->sum('total'),
            'total_tabungan' => TransaksiTabungan::whereBetween('tanggal_pembayaran', [$start, $end])->sum('total'),
            'total_lainnya' => TransaksiLainnya::whereBetween('tanggal_pembayaran', [$start, $end])->sum('total'),
        ];
    }

    /**
     * Store a transaction using Eloquent.
     */
    public function storeTransaction(array $data)
    {
        $commonData = [
            'id_user' => $data['id_user'],
            'tanggal_pembayaran' => $data['tanggal_pembayaran'],
            'tanggal_rekap' => now(),
            'total' => $data['total'],
            'keterangan' => $data['keterangan'] ?? null,
        ];

        return DB::transaction(function () use ($data, $commonData) {
            switch ($data['jenis']) {
                case 'SPP':
                    return TransaksiSpp::create(array_merge($commonData, [
                        'periode' => $data['bulan'] . '-' . $data['tahun']
                    ]));
                case 'Tabungan':
                    return TransaksiTabungan::create(array_merge($commonData, [
                        'jenis' => $data['jenis_tabungan']
                    ]));
                case 'Lainnya':
                    return TransaksiLainnya::create(array_merge($commonData, [
                        'kategori' => $data['kategori']
                    ]));
            }
        });
    }

    /**
     * Delete a transaction safely.
     */
    public function deleteTransaction(string $sumber, $id)
    {
        $allowedSources = [
            'transaksi_spp' => TransaksiSpp::class,
            'transaksi_tabungan' => TransaksiTabungan::class,
            'transaksi_lainnya' => TransaksiLainnya::class
        ];

        if (!isset($allowedSources[$sumber])) {
            throw new \InvalidArgumentException('Sumber transaksi tidak valid.');
        }

        $modelClass = $allowedSources[$sumber];
        $record = $modelClass::find($id);

        if (!$record) {
            throw new \Exception('Data tidak ditemukan.');
        }

        return $record->delete();
    }
}
