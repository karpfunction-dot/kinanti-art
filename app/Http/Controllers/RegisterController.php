<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Pendaftar;
use App\Models\User;
use App\Models\ProfilAnggota;
use App\Http\Requests\Auth\RegisterRequest;
use App\Enums\RoleType;

class RegisterController extends Controller
{
    // ================================
    // 1. FORM PENDAFTARAN (PUBLIC)
    // ================================
    public function showForm()
    {
        return view('auth.register');
    }

    // ================================
    // 2. SIMPAN PENDAFTAR
    // ================================
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'pending';

        Pendaftar::create($data);

        return redirect()->route('login')
            ->with('success', 'Pendaftaran berhasil! Silakan tunggu persetujuan admin.');
    }

    // ================================
    // 3. HALAMAN ADMIN (FIX UTAMA)
    // ================================
    public function index()
    {
        // hanya tampilkan yang pending
        $pendaftar = Pendaftar::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pendaftar.index', compact('pendaftar'));
    }

    // ================================
    // 4. API (OPTIONAL - AJAX)
    // ================================
    public function listPendaftar()
    {
        return response()->json(
            Pendaftar::orderBy('created_at', 'desc')->get()
        );
    }

    // ================================
    // 5. APPROVE PENDAFTAR
    // ================================
    public function approve($id)
    {
        $pendaftar = Pendaftar::find($id);

        if (!$pendaftar) {
            return back()->with('error', 'Data tidak ditemukan');
        }

        if ($pendaftar->status === 'approved') {
            return back()->with('info', 'Pendaftar sudah disetujui sebelumnya.');
        }

        DB::beginTransaction();

        try {

            // 🔥 Barcode lebih aman (5 digit)
            $barcode = 'KAP-' . str_pad($pendaftar->id, 5, '0', STR_PAD_LEFT);

            $user = User::create([
                'kode_barcode' => $barcode,
                'password' => $pendaftar->password,
                'id_role' => RoleType::SISWA->value,
                'aktif' => 1,
            ]);

            ProfilAnggota::create([
                'id_user' => $user->id_user,
                'nama_lengkap' => $pendaftar->nama_lengkap,
                'email' => $pendaftar->email,
                'no_hp' => $pendaftar->telepon,
                'alamat_lengkap' => $pendaftar->alamat,
                'tanggal_lahir' => $pendaftar->tanggal_lahir,
                'jenis_kelamin' => $pendaftar->jenis_kelamin,
            ]);

            $pendaftar->update(['status' => 'approved']);

            DB::commit();

            return back()->with('success', 'Pendaftar disetujui. Kode: ' . $barcode);

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ================================
    // 6. REJECT PENDAFTAR
    // ================================
    public function reject($id)
    {
        $pendaftar = Pendaftar::find($id);

        if (!$pendaftar) {
            return back()->with('error', 'Data tidak ditemukan');
        }

        $pendaftar->update(['status' => 'rejected']);

        return back()->with('success', 'Pendaftar ditolak.');
    }
}