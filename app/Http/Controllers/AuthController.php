<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // 1. Tampilkan Halaman Login
    public function showForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('login');
    }

    // 2. Proses Login
    public function processLogin(Request $request)
    {
        $request->validate([
            'kode_barcode' => 'required',
            'password' => 'required'
        ]);

        $user = User::with('role')->where('kode_barcode', $request->kode_barcode)->first();

        // Cek Password (Plain Text)
        if ($user && $user->password == $request->password) {
            
            if ($user->aktif != 1) {
                return back()->with('error', 'Akun Anda dinonaktifkan.');
            }

            Auth::login($user);

            return $this->redirectBasedOnRole($user);
        }

        return back()->with('error', 'Username atau Password Salah!');
    }

    // 3. Logout
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    // 4. Helper: Mengarahkan User sesuai Jabatan (Role ID)
    protected function redirectBasedOnRole($user)
    {
        $role_id = $user->id_role;

        // Role 1 (Admin) & 2 (Manajemen) -> Masuk Dashboard Utama
        if ($role_id == 1 || $role_id == 2) {
            return redirect('/dashboard');
        } 
        
        // Role 4 (Siswa) -> Masuk Area Siswa
        elseif ($role_id == 4) {
            return redirect('/dashboard');
        } 
        
        // Role 3 (Pelatih) -> Masuk Area Pelatih
        elseif ($role_id == 3) {
            return redirect('/dashboard');
        } 
        
        // Default
        else {
            return redirect('/dashboard');
        }
    }

} // <--- Kurung kurawal PENUTUP CLASS ini yang tadi hilang!