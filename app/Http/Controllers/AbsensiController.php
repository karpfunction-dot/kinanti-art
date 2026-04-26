/**
 * Menampilkan daftar siswa per kelas untuk absen manual (Hybrid).
 */
public function inputKelas($id_kelas)
{
    // Ambil info kelas
    $kelas = DB::table('kelas')->where('id_kelas', $id_kelas)->first();
    
    if (!$kelas) {
        return redirect()->back()->with('error', 'Kelas tidak ditemukan.');
    }

    // Ambil siswa yang terdaftar di kelas ini
    $siswas = DB::table('pendaftaran_kelas as pk')
        ->join('users as u', 'pk.id_user', '=', 'u.id_user')
        ->join('profil_anggota as p', 'u.id_user', '=', 'p.id_user')
        ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
        ->where('pk.id_kelas', $id_kelas)
        ->select('u.id_user', 'u.kode_barcode', 'p.nama_lengkap', 'p.foto_profil', 'r.nama_role')
        ->get();

    // Cek siapa saja yang sudah absen hari ini melalui scanner/manual sebelumnya
    $absensi_hari_ini = DB::table('absensi')
        ->where('id_kelas', $id_kelas)
        ->whereDate('tanggal', date('Y-m-d'))
        ->pluck('status', 'id_user')
        ->toArray();

    return view('absensi.input_massal', compact('siswas', 'kelas', 'absensi_hari_ini'));
}

/**
 * Menyimpan data absensi massal (Hadir, Izin, Sakit, Alfa).
 */
public function storeMassal(Request $request)
{
    $id_kelas = $request->id_kelas;
    $statuses = $request->status; // Array [id_user => status]
    $currentUser = auth()->user();

    if (!$statuses) {
        return redirect()->back()->with('error', 'Tidak ada data kehadiran yang dipilih.');
    }

    DB::beginTransaction();
    try {
        foreach ($statuses as $id_user => $status) {
            // Ambil data user untuk barcode dan kategori
            $user = DB::table('users as u')
                ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
                ->where('u.id_user', $id_user)
                ->select('u.kode_barcode', 'r.nama_role')
                ->first();

            $kategori = (strtolower($user->nama_role ?? '') == 'siswa') ? 'Siswa' : 'Pelatih';

            DB::table('absensi')->updateOrInsert(
                [
                    'id_user' => $id_user,
                    'id_kelas' => $id_kelas,
                    'tanggal' => date('Y-m-d')
                ],
                [
                    'kode_barcode' => $user->kode_barcode,
                    'waktu' => date('H:i:s'),
                    'status' => $status,
                    'kategori' => $kategori,
                    'lokasi' => 'Studio',
                    'keterangan' => "Diinput manual oleh: " . ($currentUser->profil->nama_lengkap ?? $currentUser->name),
                    'status_absen' => 'tercatat',
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );
        }
        DB::commit();
        return redirect()->route('absensi.index')->with('success', '✅ Data kehadiran kelas berhasil disimpan!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', '❌ Gagal: ' . $e->getMessage());
    }
}