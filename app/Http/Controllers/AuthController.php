<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        if ($user && $this->isValidPassword($request->password, $user)) {
            
            if ($user->aktif != 1) {
                return back()->with('error', 'Akun Anda dinonaktifkan.');
            }

            Auth::login($user);
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user);
        }

        return back()->with('error', 'Username atau Password Salah!');
    }

    // 3. Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    protected function isValidPassword(string $plainPassword, User $user): bool
    {
        $storedPassword = (string) $user->password;

        $passwordInfo = password_get_info($storedPassword);
        $isHashedPassword = ($passwordInfo['algo'] ?? null) !== null && ($passwordInfo['algo'] ?? 0) !== 0;

        if ($isHashedPassword && Hash::check($plainPassword, $storedPassword)) {
            return true;
        }

        // Kompatibilitas akun lama yang masih tersimpan plain text.
        if (hash_equals($storedPassword, $plainPassword)) {
            $user->password = Hash::make($plainPassword);
            $user->save();
            return true;
        }

        return false;
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
