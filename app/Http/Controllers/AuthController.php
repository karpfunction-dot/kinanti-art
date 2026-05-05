<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Enums\RoleType;

class AuthController extends Controller
{
    // 1. Tampilkan halaman login
    public function showForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('login');
    }

    // 2. Proses login
    public function processLogin(LoginRequest $request)
    {
        $credentials = $request->only('kode_barcode', 'password');

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();
            $user = Auth::user();

            // Cek status aktif
            if ($user->aktif != 1) {
                Auth::logout();
                return back()->with('error', 'Akun Anda dinonaktifkan.');
            }

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

    // 4. Redirect berdasarkan role (STANDAR FINAL)
    protected function redirectBasedOnRole($user)
    {
        $role = RoleType::from($user->id_role);

        return match ($role) {
            RoleType::ADMIN, RoleType::MANAJEMEN => redirect('/dashboard'),
            RoleType::PELATIH => redirect('/dashboard/pelatih'),
            RoleType::SISWA => redirect('/dashboard/siswa'),
            default => redirect('/dashboard'),
        };
    }
}