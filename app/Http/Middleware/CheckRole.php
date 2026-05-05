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
        $userRoleName = strtolower($user->role->nama_role ?? '');
        $userRoleId = (string) $user->id_role;
        
        $allowedRoles = array_map('strtolower', $roles);

        // Check if the role matches by name or by ID
        if (empty($allowedRoles) || in_array($userRoleName, $allowedRoles, true) || in_array($userRoleId, $allowedRoles, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin.',
            ], 403);
        }

        return redirect()->back()->with('error', 'Akses ditolak. Anda tidak memiliki izin.');
    }
}
