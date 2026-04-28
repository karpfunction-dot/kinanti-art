<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class JadwalController extends Controller
{
    private function currentRole(): string
    {
        return strtolower(auth()->user()->role->nama_role ?? '');
    }

    private function canManage(): bool
    {
        // kalau mau admin + manajemen, ubah ke:
        // return in_array($this->currentRole(), ['admin', 'manajemen'], true);
        return $this->currentRole() === 'admin';
    }

    private function withTimestamps(string $table, array $payload, bool $isUpdate = false): array
    {
        if (!$isUpdate && Schema::hasColumn($table, 'created_at')) {
            $payload['created_at'] = now();
        }

        if ($isUpdate && Schema::hasColumn($table, 'updated_at')) {
            $payload['updated_at'] = now();
        }

        return $payload;
    }

    public function index()
    {
        $role = $this->currentRole();
        $user = auth()->user();
        $canManage = $this->canManage();

        if (!in_array($role, ['admin', 'pelatih', 'siswa'], true)) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }

        $kelas = collect();
        $pelatih = collect();

        if ($canManage) {
            $kelas = DB::table('kelas')
                ->where('aktif', 1)
                ->orderBy('id_jenjang')
                ->orderBy('nama_kelas')
                ->get();

            $pelatih = DB::table('wewenang as w')
                ->join('profil_anggota as p', 'w.id_user', '=', 'p.id_user')
                ->where('w.aktif', 1)
                ->select('p.id_user', 'p.nama_lengkap')
                ->distinct()
                ->orderBy('p.nama_lengkap')
                ->get();
        }

        $jadwal = DB::table('jadwal_dev as jd')
            ->leftJoin('kelas as k', 'jd.id_kelas', '=', 'k.id_kelas')
            ->leftJoin('profil_anggota as p', 'jd.id_pelatih', '=', 'p.id_user')
            ->select(
                'jd.id_jadwal',
                'jd.hari',
                'jd.jam_mulai',
                'jd.jam_selesai',
                'jd.lokasi',
                'jd.kategori',
                'jd.keterangan',
                'jd.status',
                'k.nama_kelas',
                'p.nama_lengkap as nama_pelatih'
            );

        if (!$canManage) {
            $jadwal->where('jd.status', 'aktif');
        }

        if ($role === 'siswa') {
            $activeClassIds = DB::table('kelas_siswa')
                ->where('id_user', $user->id_user)
                ->where('aktif', 1)
                ->pluck('id_kelas');

            if ($activeClassIds->isEmpty()) {
                $jadwal = collect();
            } else {
                $jadwal = $jadwal
                    ->whereIn('jd.id_kelas', $activeClassIds)
                    ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
                    ->orderBy('jd.jam_mulai')
                    ->get();
            }
        } else {
            $jadwal = $jadwal
                ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
                ->orderBy('jd.jam_mulai')
                ->get();
        }

        return view('jadwal.index', compact('kelas', 'pelatih', 'jadwal', 'canManage', 'role'));
    }

    public function store(Request $request)
    {
        if (!$this->canManage()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validator = Validator::make($request->all(), [
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'id_kelas' => 'nullable|exists:kelas,id_kelas',
            'id_pelatih' => 'nullable|exists:users,id_user',
            'lokasi' => 'nullable|string|max:100',
            'kategori' => 'required|string|max:50',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $payload = [
                'hari' => $request->hari,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'id_kelas' => $request->id_kelas ?: null,
                'id_pelatih' => $request->id_pelatih ?: null,
                'lokasi' => $request->lokasi,
                'kategori' => $request->kategori,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
            ];
            $payload = $this->withTimestamps('jadwal_dev', $payload, false);

            DB::table('jadwal_dev')->insert($payload);

            return response()->json(['success' => true, 'message' => '✅ Jadwal baru berhasil ditambahkan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if (!$this->canManage()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validator = Validator::make($request->all(), [
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'id_kelas' => 'nullable|exists:kelas,id_kelas',
            'id_pelatih' => 'nullable|exists:users,id_user',
            'lokasi' => 'nullable|string|max:100',
            'kategori' => 'required|string|max:50',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $payload = [
                'hari' => $request->hari,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'id_kelas' => $request->id_kelas ?: null,
                'id_pelatih' => $request->id_pelatih ?: null,
                'lokasi' => $request->lokasi,
                'kategori' => $request->kategori,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
            ];
            $payload = $this->withTimestamps('jadwal_dev', $payload, true);

            DB::table('jadwal_dev')->where('id_jadwal', $id)->update($payload);

            return response()->json(['success' => true, 'message' => '✏️ Jadwal berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!$this->canManage()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            DB::table('jadwal_dev')->where('id_jadwal', $id)->delete();
            return response()->json(['success' => true, 'message' => '🗑️ Jadwal berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    public function getJadwal($id)
    {
        if (!$this->canManage()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $jadwal = DB::table('jadwal_dev')->where('id_jadwal', $id)->first();
        if ($jadwal) {
            return response()->json(['success' => true, 'data' => $jadwal]);
        }

        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
    }
}