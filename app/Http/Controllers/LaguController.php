<?php

namespace App\Http\Controllers;

use App\Models\Lagu;
use App\Models\Kelas;
use App\Http\Requests\LaguRequest;

class LaguController extends Controller
{
    public function index()
    {
        if (auth()->user()->id_role !== 1) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }

        $lagu = Lagu::with('kelas')
            ->orderBy('id_lagu', 'desc')
            ->get();

        $kelas = Kelas::where('aktif', 1)
            ->orderBy('nama_kelas')
            ->get();

        return view('lagu.index', compact('lagu', 'kelas'));
    }

    public function store(LaguRequest $request)
    {
        try {
            Lagu::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => '✅ Lagu berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan: ' . $e->getMessage()
            ]);
        }
    }

    public function update(LaguRequest $request, $id)
    {
        try {
            $lagu = Lagu::findOrFail($id);
            $lagu->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => '✏️ Lagu berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $lagu = Lagu::findOrFail($id);

            if ($lagu->jadwal()->exists() || $lagu->koreografi()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lagu tidak bisa dihapus karena masih digunakan'
                ], 400);
            }

            $lagu->delete();

            return response()->json([
                'success' => true,
                'message' => '🗑️ Lagu berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ]);
        }
    }

    public function getLagu($id)
    {
        try {
            $lagu = Lagu::with('kelas')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $lagu
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }
}