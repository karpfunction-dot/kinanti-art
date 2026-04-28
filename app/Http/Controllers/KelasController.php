<?php

namespace App\Http\Controllers;

use App\Constants\RoleConstant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class KelasController extends Controller
{
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

    /**
     * Display kelas management page.
     */
    public function index()
    {
        $user = auth()->user();

        if (!RoleConstant::isAdminOrManagement($user->id_role)) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }

        $jenjang = DB::table('jenjang')->orderBy('id_jenjang')->get();
        $lagu = DB::table('lagu')->where('status', 'aktif')->orderBy('judul_lagu')->get();

        $pelatih = DB::table('wewenang as w')
            ->join('profil_anggota as p', 'w.id_user', '=', 'p.id_user')
            ->where('w.aktif', 1)
            ->select('p.id_user', 'p.nama_lengkap')
            ->distinct()
            ->orderBy('p.nama_lengkap')
            ->get();

        $kelas = DB::table('kelas as k')
            ->leftJoin('jenjang as j', 'k.id_jenjang', '=', 'j.id_jenjang')
            ->leftJoin('lagu as l', 'k.id_lagu', '=', 'l.id_lagu')
            ->leftJoin('profil_anggota as p', 'k.pelatih', '=', 'p.id_user')
            ->select(
                'k.id_kelas',
                'k.nama_kelas',
                'k.deskripsi',
                'k.aktif',
                'j.nama_jenjang',
                'j.id_jenjang',
                'l.judul_lagu',
                'l.id_lagu',
                'p.nama_lengkap as nama_pelatih',
                'k.pelatih'
            )
            ->orderBy('j.id_jenjang')
            ->orderBy('k.nama_kelas')
            ->get();

        return view('kelas.index', compact('jenjang', 'lagu', 'pelatih', 'kelas'));
    }

    /**
     * Display entri anggota kelas page.
     */
    public function entri(Request $request)
    {
        if (auth()->user()->role->nama_role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }

        $kelasList = DB::table('kelas')
            ->where('aktif', 1)
            ->orderBy('nama_kelas')
            ->get();

        $id_kelas = (int) $request->get('id', 0);
        $nama_kelas = '';
        $siswaAll = collect();
        $anggotaList = collect();

        if ($id_kelas > 0) {
            $kelas = DB::table('kelas')->where('id_kelas', $id_kelas)->first();
            if ($kelas) {
                $nama_kelas = $kelas->nama_kelas;
            }

            $siswaAll = DB::table('users as u')
                ->join('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
                ->join('roles as r', 'u.id_role', '=', 'r.id_role')
                ->leftJoin('kelas_siswa as ks', function ($join) {
                    $join->on('u.id_user', '=', 'ks.id_user')
                         ->where('ks.aktif', 1);
                })
                ->where('r.nama_role', 'siswa')
                ->whereNull('ks.id_kelas')
                ->select('u.id_user', 'p.nama_lengkap', 'u.kode_barcode')
                ->orderBy('p.nama_lengkap')
                ->get();

            $anggotaList = DB::table('kelas_siswa as ks')
                ->join('profil_anggota as p', 'ks.id_user', '=', 'p.id_user')
                ->where('ks.id_kelas', $id_kelas)
                ->where('ks.aktif', 1)
                ->select('ks.id_user', 'p.nama_lengkap', 'ks.tanggal_gabung')
                ->orderBy('p.nama_lengkap')
                ->get();
        }

        return view('kelas.entri', compact('kelasList', 'id_kelas', 'nama_kelas', 'siswaAll', 'anggotaList'));
    }

    /**
     * Store anggota to kelas.
     */
    public function storeAnggota(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kelas' => 'required|exists:kelas,id_kelas',
            'id_user' => 'required|exists:users,id_user',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            $exists = DB::table('kelas_siswa')
                ->where('id_user', $request->id_user)
                ->where('aktif', 1)
                ->exists();

            if ($exists) {
                return redirect()->back()->with('error', 'Siswa ini sudah memiliki kelas aktif.');
            }

            $payload = [
                'id_kelas' => $request->id_kelas,
                'id_user' => $request->id_user,
                'tanggal_gabung' => date('Y-m-d'),
                'aktif' => 1,
            ];
            $payload = $this->withTimestamps('kelas_siswa', $payload, false);

            DB::table('kelas_siswa')->insert($payload);

            return redirect()->route('kelas.entri', ['id' => $request->id_kelas])
                ->with('success', 'Anggota berhasil ditambahkan ke kelas.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan: ' . $e->getMessage());
        }
    }

    /**
     * Destroy anggota from kelas.
     */
    public function destroyAnggota($id_kelas, $id_user)
    {
        try {
            $payload = ['aktif' => 0];
            $payload = $this->withTimestamps('kelas_siswa', $payload, true);

            DB::table('kelas_siswa')
                ->where('id_kelas', $id_kelas)
                ->where('id_user', $id_user)
                ->update($payload);

            return redirect()->route('kelas.entri', ['id' => $id_kelas])
                ->with('success', 'Anggota berhasil dihapus dari kelas.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function getSiswaByKelas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kelas' => 'required|exists:kelas,id_kelas',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $siswa = DB::table('kelas_siswa as ks')
            ->join('profil_anggota as p', 'ks.id_user', '=', 'p.id_user')
            ->where('ks.id_kelas', $request->id_kelas)
            ->where('ks.aktif', 1)
            ->select('ks.id_user', 'p.nama_lengkap', 'ks.tanggal_gabung')
            ->orderBy('p.nama_lengkap')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $siswa,
        ]);
    }

    /**
     * Display naik kelas page.
     */
    public function naik(Request $request)
    {
        if (auth()->user()->role->nama_role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }

        $kelasList = DB::table('kelas')
            ->where('aktif', 1)
            ->orderBy('nama_kelas')
            ->get();

        $kelasAsal = (int) $request->get('kelas_asal', 0);
        $siswaList = collect();
        $kelasTujuanList = collect();

        if ($kelasAsal > 0) {
            $siswaList = DB::table('kelas_siswa as ks')
                ->join('profil_anggota as p', 'ks.id_user', '=', 'p.id_user')
                ->where('ks.id_kelas', $kelasAsal)
                ->where('ks.aktif', 1)
                ->select('ks.id_user', 'p.nama_lengkap', 'ks.tanggal_gabung')
                ->orderBy('p.nama_lengkap')
                ->get();

            $kelasTujuanList = DB::table('kelas')
                ->where('aktif', 1)
                ->where('id_kelas', '!=', $kelasAsal)
                ->orderBy('nama_kelas')
                ->get();
        }

        return view('kelas.naik', compact('kelasList', 'kelasAsal', 'siswaList', 'kelasTujuanList'));
    }

    /**
     * Process naik kelas (mutasi siswa).
     */
    public function prosesNaikKelas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kelas_asal' => 'required|exists:kelas,id_kelas',
            'kelas_tujuan' => 'required|exists:kelas,id_kelas|different:kelas_asal',
            'id_siswa' => 'required|array|min:1',
            'id_siswa.*' => 'exists:users,id_user',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            foreach ($request->id_siswa as $id_siswa) {
                $oldPayload = ['aktif' => 0];
                $oldPayload = $this->withTimestamps('kelas_siswa', $oldPayload, true);

                DB::table('kelas_siswa')
                    ->where('id_user', $id_siswa)
                    ->where('id_kelas', $request->kelas_asal)
                    ->where('aktif', 1)
                    ->update($oldPayload);

                $newPayload = [
                    'id_kelas' => $request->kelas_tujuan,
                    'id_user' => $id_siswa,
                    'tanggal_gabung' => date('Y-m-d'),
                    'aktif' => 1,
                ];
                $newPayload = $this->withTimestamps('kelas_siswa', $newPayload, false);

                DB::table('kelas_siswa')->insert($newPayload);
            }

            DB::commit();

            return redirect()->route('kelas.naik')
                ->with('success', count($request->id_siswa) . ' siswa berhasil dinaikkan kelasnya.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    // ==================== CRUD JENJANG ====================

    public function storeJenjang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_jenjang' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'aktif' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $payload = [
                'nama_jenjang' => $request->nama_jenjang,
                'deskripsi' => $request->deskripsi,
                'aktif' => (int) $request->aktif,
            ];
            $payload = $this->withTimestamps('jenjang', $payload, false);

            DB::table('jenjang')->insert($payload);

            return response()->json([
                'success' => true,
                'message' => '✅ Jenjang berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan jenjang: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateJenjang(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_jenjang' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'aktif' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        if (!DB::table('jenjang')->where('id_jenjang', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data jenjang tidak ditemukan',
            ], 404);
        }

        try {
            $payload = [
                'nama_jenjang' => $request->nama_jenjang,
                'deskripsi' => $request->deskripsi,
                'aktif' => (int) $request->aktif,
            ];
            $payload = $this->withTimestamps('jenjang', $payload, true);

            DB::table('jenjang')
                ->where('id_jenjang', $id)
                ->update($payload);

            return response()->json([
                'success' => true,
                'message' => '✏️ Jenjang berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jenjang: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyJenjang($id)
    {
        if (!DB::table('jenjang')->where('id_jenjang', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data jenjang tidak ditemukan',
            ], 404);
        }

        $dipakai = DB::table('kelas')->where('id_jenjang', $id)->exists();
        if ($dipakai) {
            return response()->json([
                'success' => false,
                'message' => 'Jenjang tidak bisa dihapus karena masih dipakai oleh data kelas',
            ], 409);
        }

        try {
            DB::table('jenjang')->where('id_jenjang', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => '🗑️ Jenjang berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jenjang: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getJenjang($id)
    {
        $jenjang = DB::table('jenjang')->where('id_jenjang', $id)->first();

        if (!$jenjang) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $jenjang,
        ]);
    }

    // ==================== CRUD KELAS ====================

    public function storeKelas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kelas' => 'required|string|max:100',
            'id_jenjang' => 'required|exists:jenjang,id_jenjang',
            'pelatih' => 'nullable|exists:users,id_user',
            'id_lagu' => 'nullable|exists:lagu,id_lagu',
            'deskripsi' => 'nullable|string',
            'aktif' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $payload = [
                'nama_kelas' => $request->nama_kelas,
                'id_jenjang' => $request->id_jenjang,
                'pelatih' => $request->pelatih ?: null,
                'id_lagu' => $request->id_lagu ?: null,
                'deskripsi' => $request->deskripsi,
                'aktif' => (int) $request->aktif,
            ];
            $payload = $this->withTimestamps('kelas', $payload, false);

            DB::table('kelas')->insert($payload);

            return response()->json([
                'success' => true,
                'message' => '✅ Kelas berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kelas: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateKelas(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_kelas' => 'required|string|max:100',
            'id_jenjang' => 'required|exists:jenjang,id_jenjang',
            'pelatih' => 'nullable|exists:users,id_user',
            'id_lagu' => 'nullable|exists:lagu,id_lagu',
            'deskripsi' => 'nullable|string',
            'aktif' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        if (!DB::table('kelas')->where('id_kelas', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data kelas tidak ditemukan',
            ], 404);
        }

        try {
            $payload = [
                'nama_kelas' => $request->nama_kelas,
                'id_jenjang' => $request->id_jenjang,
                'pelatih' => $request->pelatih ?: null,
                'id_lagu' => $request->id_lagu ?: null,
                'deskripsi' => $request->deskripsi,
                'aktif' => (int) $request->aktif,
            ];
            $payload = $this->withTimestamps('kelas', $payload, true);

            DB::table('kelas')
                ->where('id_kelas', $id)
                ->update($payload);

            return response()->json([
                'success' => true,
                'message' => '✏️ Kelas berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kelas: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyKelas($id)
    {
        if (!DB::table('kelas')->where('id_kelas', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data kelas tidak ditemukan',
            ], 404);
        }

        try {
            $payload = ['aktif' => 0];
            $payload = $this->withTimestamps('kelas', $payload, true);

            DB::table('kelas')
                ->where('id_kelas', $id)
                ->update($payload);

            return response()->json([
                'success' => true,
                'message' => '🗑️ Kelas berhasil dinonaktifkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kelas: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getKelas($id)
    {
        $kelas = DB::table('kelas')->where('id_kelas', $id)->first();

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $kelas,
        ]);
    }
}