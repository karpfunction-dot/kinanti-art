<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\RoleType;
use Carbon\Carbon;

class AbsensiService
{
    /**
     * Get attendance history with filters.
     */
    public function getHistory(array $filters)
    {
        $bulan = $filters['bulan'] ?? date('Y-m');
        $filter_role = $filters['role'] ?? '';
        $search = $filters['search'] ?? '';

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
            // Performance Fix: Use date ranges instead of DATE_FORMAT on column
            $startOfMonth = Carbon::parse($bulan)->startOfMonth()->toDateString();
            $endOfMonth = Carbon::parse($bulan)->endOfMonth()->toDateString();
            $query->whereBetween('a.tanggal', [$startOfMonth, $endOfMonth]);
        }

        if ($filter_role) {
            $query->where('r.nama_role', $filter_role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('u.kode_barcode', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('a.tanggal', 'desc')
            ->orderBy('a.waktu', 'desc')
            ->paginate(20);
    }

    /**
     * Process attendance from barcode.
     */
    public function processBarcode(string $barcode, $currentUser)
    {
        // 1. Authorization Check (Admin, Pelatih, Manajemen)
        $userRole = RoleType::from($currentUser->id_role);
        if (!$userRole->isAdministrative() && $userRole !== RoleType::PELATIH) {
            throw new \Exception('Akses ditolak!');
        }

        // 2. Find User by Barcode
        $targetUser = DB::table('users as u')
            ->leftJoin('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
            ->where('u.kode_barcode', $barcode)
            ->select('u.id_user', 'u.kode_barcode', 'u.id_role', 'p.nama_lengkap', 'p.foto_profil')
            ->first();

        if (!$targetUser) {
            throw new \Exception('Barcode tidak terdaftar.');
        }

        // 3. Prevent Double Attendance
        $today = date('Y-m-d');
        $sudahAbsen = DB::table('absensi')
            ->where('id_user', $targetUser->id_user)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($sudahAbsen) {
            throw new \Exception($targetUser->nama_lengkap . ' sudah absen hari ini.');
        }

        // 4. Determine Category
        $targetRole = RoleType::from($targetUser->id_role);
        $kategori = ($targetRole === RoleType::SISWA) ? 'Siswa' : 'Pelatih';

        // 5. Insert Record
        DB::table('absensi')->insert([
            'id_user' => $targetUser->id_user,
            'kode_barcode' => $barcode,
            'tanggal' => $today,
            'waktu' => date('H:i:s'),
            'status' => 'Hadir',
            'kategori' => $kategori,
            'lokasi' => 'Studio',
            'keterangan' => "Absen tercatat oleh: " . ($currentUser->profil->nama_lengkap ?? $currentUser->name),
            'status_absen' => 'tercatat',
            'created_at' => now(),
        ]);

        return $targetUser;
    }

    /**
     * Store mass attendance for a class.
     */
    public function storeMassal(int $id_kelas, array $statuses, $currentUser)
    {
        DB::beginTransaction();
        try {
            foreach ($statuses as $id_user => $status) {
                if (empty($status)) continue;

                $user = DB::table('users')->where('id_user', $id_user)->first();
                if (!$user) continue;

                $roleType = RoleType::from($user->id_role);
                $kategori = ($roleType === RoleType::SISWA) ? 'Siswa' : 'Pelatih';

                DB::table('absensi')->updateOrInsert(
                    ['id_user' => $id_user, 'id_kelas' => $id_kelas, 'tanggal' => date('Y-m-d')],
                    [
                        'kode_barcode' => $user->kode_barcode,
                        'waktu' => date('H:i:s'),
                        'status' => $status,
                        'kategori' => $kategori,
                        'lokasi' => 'Studio',
                        'keterangan' => "Input manual oleh: " . ($currentUser->profil->nama_lengkap ?? $currentUser->name),
                        'status_absen' => 'tercatat',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
