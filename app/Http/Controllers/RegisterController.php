<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    // Tampilkan form pendaftaran
    public function showForm()
    {
        return view('auth.register');
    }

    // Proses pendaftaran
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|unique:pendaftar,email',
            'telepon' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::table('pendaftar')->insert([
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
                'telepon' => $request->telepon,
                'password' => Hash::make($request->password),
                'alamat' => $request->alamat,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            return redirect()->route('login')
                ->with('success', 'Pendaftaran berhasil! Silakan tunggu persetujuan admin.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mendaftar: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Admin: Lihat daftar pendaftar
    public function listPendaftar()
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }

        $pendaftar = DB::table('pendaftar')->orderBy('created_at', 'desc')->get();
        return view('admin.pendaftar', compact('pendaftar'));
    }

    // Admin: Approve pendaftar
    public function approve($id)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }

        $pendaftar = DB::table('pendaftar')->where('id', $id)->first();
        if (!$pendaftar) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        try {
            DB::beginTransaction();

            // Generate kode barcode otomatis
            $lastUser = DB::table('users')->orderBy('id_user', 'desc')->first();
            $lastId = $lastUser ? $lastUser->id_user : 2510000;
            $newId = $lastId + 1;
            $kodeBarcode = 'KAP-' . str_pad($newId, 3, '0', STR_PAD_LEFT);

            // Insert ke users
            $id_user = DB::table('users')->insertGetId([
                'kode_barcode' => $kodeBarcode,
                'password' => $pendaftar->password,
                'id_role' => 4, // role siswa
                'aktif' => 1,
                'created_at' => now(),
            ]);

            // Insert ke profil_anggota
            DB::table('profil_anggota')->insert([
                'id_user' => $id_user,
                'nama_lengkap' => $pendaftar->nama_lengkap,
                'email' => $pendaftar->email,
                'telepon' => $pendaftar->telepon,
                'alamat' => $pendaftar->alamat,
                'tanggal_lahir' => $pendaftar->tanggal_lahir,
                'jenis_kelamin' => $pendaftar->jenis_kelamin,
                'created_at' => now(),
            ]);

            // Update status pendaftar
            DB::table('pendaftar')->where('id', $id)->update([
                'status' => 'approved',
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Pendaftar berhasil disetujui. Kode barcode: ' . $kodeBarcode);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    // Admin: Reject pendaftar
    public function reject($id)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }

        DB::table('pendaftar')->where('id', $id)->update([
            'status' => 'rejected',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pendaftar ditolak.');
    }
}