<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        $filter_role = $request->get('role', '');
        $search = $request->get('search', '');
        
        $query = DB::table('absensi as a')
            ->leftJoin('users as u', 'a.id_user', '=', 'u.id_user')
            ->leftJoin('profil_anggota as p', 'p.id_user', '=', 'a.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->select(
                'a.id_absensi',
                'a.tanggal',
                'a.waktu',
                'a.status',
                'a.kategori',
                'p.nama_lengkap',
                'p.foto_profil',
                'u.kode_barcode',
                DB::raw('COALESCE(r.nama_role, "Member") AS role_name')
            );
        
        if ($bulan) {
            $query->whereRaw("DATE_FORMAT(a.tanggal, '%Y-%m') = ?", [$bulan]);
        }
        
        if ($filter_role) {
            $query->where('r.nama_role', $filter_role);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('p.nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('u.kode_barcode', 'like', "%{$search}%");
            });
        }
        
        $rows = $query->orderBy('a.tanggal', 'desc')
            ->orderBy('a.waktu', 'desc')
            ->paginate(20);
        
        // Data untuk filter
        $roles = DB::table('roles')->select('nama_role')->get();
        $months = $this->getMonths();
        $years = range(date('Y') - 2, date('Y') + 1);
        
        return view('absensi.index', compact('rows', 'bulan', 'filter_role', 'search', 'roles', 'months', 'years'));
    }

    /**
     * Show the scan barcode form with camera scanner.
     */
    public function scan()
    {
        return view('absensi.scan');
    }

    /**
     * Process barcode scan from camera or manual input.
     */
    public function proses(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode_barcode' => 'required|string|min:3|max:50'
        ]);
        
        $barcode = trim($request->kode_barcode);
        $currentUser = auth()->user();
        
        // Log untuk debugging
        Log::info('Scan attempt', ['barcode' => $barcode, 'user_id' => $currentUser->id_user ?? 'unknown']);
        
        // Cek akses role (hanya admin/pelatih yang bisa scan)
        if (!in_array($currentUser->role ?? '', ['admin', 'pelatih', 'manajemen'])) {
            return redirect()->route('absensi.scan')
                ->with('error', '❌ Akses ditolak! Hanya admin dan pelatih yang dapat melakukan absensi.')
                ->withInput();
        }
        
        // Cari user berdasarkan barcode
        $user = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('u.kode_barcode', $barcode)
            ->select(
                'u.id_user', 
                'u.kode_barcode', 
                'u.email',
                'r.nama_role as role', 
                'p.nama_lengkap', 
                'p.foto_profil'
            )
            ->first();
        
        if (!$user) {
            Log::warning('Barcode not found', ['barcode' => $barcode]);
            return redirect()->route('absensi.scan')
                ->with('error', '❌ Barcode "' . htmlspecialchars($barcode) . '" tidak ditemukan di sistem.')
                ->withInput();
        }
        
        // Gunakan database transaction untuk mencegah race condition
        DB::beginTransaction();
        
        try {
            // Cek apakah sudah absen hari ini dengan lock untuk mencegah duplicate
            $sudahAbsen = DB::table('absensi')
                ->where('id_user', $user->id_user)
                ->whereDate('tanggal', date('Y-m-d'))
                ->lockForUpdate()
                ->exists();
            
            // Siapkan data user untuk ditampilkan
            $userData = [
                'nama_lengkap' => $user->nama_lengkap ?? 'Tidak ada nama',
                'role' => $user->role ?? 'Member',
                'kode_barcode' => $user->kode_barcode,
                'foto_profil' => $user->foto_profil,
                'email' => $user->email ?? '-',
            ];
            
            if ($sudahAbsen) {
                DB::rollBack();
                Log::info('Duplicate attendance prevented', ['user_id' => $user->id_user]);
                return redirect()->route('absensi.scan')
                    ->with('warning', '⚠️ Pengguna ' . ($user->nama_lengkap ?? $user->kode_barcode) . ' sudah absen hari ini.')
                    ->with('scanned_user', $userData);
            }
            
            // Tentukan kategori
            $kategori = in_array($user->role, ['pelatih', 'manajemen', 'admin']) ? 'Pelatih' : 'Siswa';
            $keterangan = "Absen otomatis via camera scanner: {$user->nama_lengkap} ({$user->role})";
            $waktuNow = date('H:i:s');
            $tanggalNow = date('Y-m-d');
            
            // Simpan absensi
            $idAbsensi = DB::table('absensi')->insertGetId([
                'id_user' => $user->id_user,
                'kode_barcode' => $barcode,
                'tanggal' => $tanggalNow,
                'waktu' => $waktuNow,
                'status' => 'Hadir',
                'kategori' => $kategori,
                'lokasi' => 'Studio',
                'keterangan' => $keterangan,
                'status_absen' => 'tercatat',
                'created_at' => now(),
                'scanned_by' => $currentUser->id_user ?? null, // Tambahkan kolom ini di migration
            ]);
            
            DB::commit();
            
            Log::info('Attendance recorded', [
                'attendance_id' => $idAbsensi,
                'user_id' => $user->id_user,
                'scanned_by' => $currentUser->id_user
            ]);
            
            return redirect()->route('absensi.scan')
                ->with('success', '✅ Absensi berhasil dicatat untuk ' . htmlspecialchars($user->nama_lengkap))
                ->with('scanned_user', $userData)
                ->with('auto_reset', true); // Flag untuk reset scanner
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance failed: ' . $e->getMessage(), [
                'barcode' => $barcode,
                'user_id' => $user->id_user ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('absensi.scan')
                ->with('error', '❌ Terjadi kesalahan sistem: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * API endpoint for AJAX scan (for better UX)
     */
public function prosesApi(Request $request)
{
    // Debug: Log semua request
    \Log::info('API Request received', [
        'method' => $request->method(),
        'path' => $request->path(),
        'headers' => $request->headers->all(),
        'content' => $request->getContent()
    ]);
    
    try {
        // Validasi input
        $validator = validator($request->all(), [
            'kode_barcode' => 'required|string|min:1|max:50'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }
        
        $barcode = trim($request->kode_barcode);
        
        // Cek autentikasi
        if (!auth()->check()) {
            \Log::warning('Unauthorized access to prosesApi');
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login terlebih dahulu.'
            ], 401);
        }
        
        $currentUser = auth()->user();
        
        // Ambil role user dengan aman
        $userRole = null;
        
        // Method 1: Langsung dari property
        if (isset($currentUser->role)) {
            $userRole = $currentUser->role;
        }
        
        // Method 2: Dari atribut model
        if (empty($userRole) && method_exists($currentUser, 'getAttributes')) {
            $attrs = $currentUser->getAttributes();
            $userRole = $attrs['role'] ?? null;
        }
        
        // Method 3: Dari tabel roles
        if (empty($userRole)) {
            try {
                $roleName = DB::table('users as u')
                    ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
                    ->where('u.id_user', $currentUser->id_user)
                    ->value('r.nama_role');
                
                if ($roleName) {
                    $userRole = $roleName;
                }
            } catch (\Exception $e) {
                \Log::warning('Could not fetch role from roles table: ' . $e->getMessage());
            }
        }
        
        // Cleanup: Jika role masih object, ekstrak string
        if (is_object($userRole)) {
            $userRole = $userRole->nama_role ?? json_encode($userRole);
        }
        if (is_array($userRole)) {
            $userRole = $userRole['nama_role'] ?? implode(',', $userRole);
        }
        
        // Convert ke string dan lowercase
        $userRole = trim(strtolower((string)$userRole));
        
        \Log::info('User role detected', [
            'user_id' => $currentUser->id_user,
            'email' => $currentUser->email ?? 'unknown',
            'role' => $userRole
        ]);
        
        // Cek role yang diizinkan
        $allowedRoles = ['admin', 'pelatih', 'manajemen', 'owner', 'superadmin'];
        
        if (!in_array($userRole, $allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak! Role Anda: "' . $userRole . '". Diperlukan: admin/pelatih/manajemen.'
            ], 403);
        }
        
        // Cari user berdasarkan barcode
        $user = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('u.kode_barcode', $barcode)
            ->select(
                'u.id_user', 
                'u.kode_barcode',
                DB::raw('COALESCE(r.nama_role, u.role, "Member") as role'),
                DB::raw('COALESCE(p.nama_lengkap, u.name, "Tidak diketahui") as nama_lengkap'),
                'p.foto_profil'
            )
            ->first();
        
        if (!$user) {
            \Log::warning('Barcode not found', ['barcode' => $barcode]);
            return response()->json([
                'success' => false,
                'message' => "Barcode '{$barcode}' tidak ditemukan."
            ], 404);
        }
        
        // Cek absen hari ini
        $sudahAbsen = DB::table('absensi')
            ->where('id_user', $user->id_user)
            ->whereDate('tanggal', date('Y-m-d'))
            ->exists();
        
        if ($sudahAbsen) {
            return response()->json([
                'success' => false,
                'message' => $user->nama_lengkap . ' sudah absen hari ini.',
                'user' => [
                    'nama_lengkap' => $user->nama_lengkap,
                    'role' => $user->role,
                    'kode_barcode' => $user->kode_barcode,
                    'foto_profil' => $user->foto_profil,
                    'sudah_absen' => true
                ]
            ], 409);
        }
        
        // Simpan absensi
        $userRoleForCategory = is_object($user->role) ? ($user->role->nama_role ?? 'siswa') : $user->role;
        $kategori = in_array(strtolower($userRoleForCategory), ['pelatih', 'manajemen', 'admin', 'owner', 'superadmin']) 
            ? 'Pelatih' 
            : 'Siswa';
        
        DB::table('absensi')->insert([
            'id_user' => $user->id_user,
            'kode_barcode' => $barcode,
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
            'status' => 'Hadir',
            'kategori' => $kategori,
            'lokasi' => 'Studio',
            'keterangan' => "Absen via scanner: {$user->nama_lengkap}",
            'status_absen' => 'tercatat',
            'created_at' => now(),
            'scanned_by' => $currentUser->id_user,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '✅ Absensi berhasil untuk ' . $user->nama_lengkap,
            'user' => [
                'nama_lengkap' => $user->nama_lengkap,
                'role' => is_object($user->role) ? ($user->role->nama_role ?? 'Member') : $user->role,
                'kode_barcode' => $user->kode_barcode,
                'foto_profil' => $user->foto_profil,
                'sudah_absen' => false
            ]
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal: ' . implode(', ', $e->errors())
        ], 422);
    } catch (\Exception $e) {
        // Log error detail
        \Log::error('API Scan critical error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Remove the specified attendance record.
     */
    public function destroy($id)
    {
        try {
            $currentUser = auth()->user();
            
            // Cek akses
            if (!in_array($currentUser->role ?? '', ['admin'])) {
                return redirect()->route('absensi.index')
                    ->with('error', '❌ Hanya admin yang dapat menghapus data absensi.');
            }
            
            $deleted = DB::table('absensi')->where('id_absensi', $id)->delete();
            
            if ($deleted) {
                Log::info('Attendance deleted', ['id' => $id, 'deleted_by' => $currentUser->id_user]);
                return redirect()->route('absensi.index')
                    ->with('success', '✅ Data absensi berhasil dihapus.');
            } else {
                return redirect()->route('absensi.index')
                    ->with('error', '❌ Data absensi tidak ditemukan.');
            }
        } catch (\Exception $e) {
            Log::error('Delete failed: ' . $e->getMessage());
            return redirect()->route('absensi.index')
                ->with('error', '❌ Gagal menghapus data absensi: ' . $e->getMessage());
        }
    }
    
    /**
     * Export attendance report
     */
    public function export(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        $filter_role = $request->get('role', '');
        
        $query = DB::table('absensi as a')
            ->leftJoin('users as u', 'a.id_user', '=', 'u.id_user')
            ->leftJoin('profil_anggota as p', 'p.id_user', '=', 'a.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->select(
                'p.nama_lengkap',
                'u.kode_barcode',
                'a.tanggal',
                'a.waktu',
                'a.status',
                'a.kategori',
                'a.lokasi',
                'a.keterangan',
                DB::raw('COALESCE(r.nama_role, "Member") AS role_name')
            );
        
        if ($bulan) {
            $query->whereRaw("DATE_FORMAT(a.tanggal, '%Y-%m') = ?", [$bulan]);
        }
        
        if ($filter_role) {
            $query->where('r.nama_role', $filter_role);
        }
        
        $records = $query->orderBy('a.tanggal', 'desc')
            ->orderBy('a.waktu', 'desc')
            ->get();
        
        // Export to CSV
        $filename = "absensi_{$bulan}.csv";
        $handle = fopen('php://temp', 'w+');
        
        // Add headers
        fputcsv($handle, ['Nama Lengkap', 'Kode Barcode', 'Tanggal', 'Waktu', 'Status', 'Kategori', 'Lokasi', 'Keterangan', 'Role']);
        
        // Add data
        foreach ($records as $record) {
            fputcsv($handle, [
                $record->nama_lengkap,
                $record->kode_barcode,
                $record->tanggal,
                $record->waktu,
                $record->status,
                $record->kategori,
                $record->lokasi,
                $record->keterangan,
                $record->role_name
            ]);
        }
        
        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);
        
        return response($csvContent, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
    
    /**
     * Helper: Get months list
     */
    private function getMonths()
    {
        return [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
    }
}
