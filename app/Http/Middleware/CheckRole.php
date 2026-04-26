<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        // Sesuaikan dengan field role di tabel users Anda
        $userRole = $user->role ?? $user->nama_role ?? 'user';
        
        // Jika tidak ada role yang direquired atau user punya role yang diizinkan
        if (empty($roles) || in_array($userRole, $roles)) {
            return $next($request);
        }
        
        // Untuk AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin.'
            ], 403);
        }
        
        // Untuk web request
        return redirect()->back()->with('error', '❌ Akses ditolak! Hanya admin dan pelatih yang dapat melakukan absensi.');
    }
}
