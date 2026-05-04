# 31️⃣ REFACTOR: Modul Dashboard

**Status:** 🔴 Not Started  
**Priority:** 🟢 LOW  
**Duration:** ~0.5 day  
**Dependency:** Semua Phase 2 & 3 selesai (butuh semua Model)  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

Dashboard hanya membaca data — tidak ada Form Request yang diperlukan. Cukup ganti raw query ke Model Eloquent.

**File yang diubah:**
- `app/Http/Controllers/DashboardController.php` (153 baris, 10x `DB::table()`)

**File yang dibuat:**
- Tidak ada — semua Model sudah dibuat di issue sebelumnya

---

## 📝 Implementation Checklist

### Langkah 1 — Tambahkan import Model

**Buka file:** `app/Http/Controllers/DashboardController.php`

Tambahkan:
```php
use App\Models\User;
use App\Models\ProfilAnggota;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\Jadwal;
```

### Langkah 2 — Refactor bagian Admin & Manajemen (baris 31–67)

1. `DB::table('profil_anggota')->where(...)` → `ProfilAnggota::where(...)` atau pakai relasi `$user->profil`
2. `DB::table('users')->where('id_role', ...)->count()` → `User::where('id_role', ...)->count()`
3. `DB::table('absensi')->whereDate(...)` → `Absensi::whereDate(...)`
4. `DB::table('kelas')->get()` → `Kelas::all()`

### Langkah 3 — Refactor bagian Pelatih (baris 72–95)

1. `DB::table('jadwal_dev as j')->join(...)` → `Jadwal::with('kelas')->where('id_pelatih', ...)->where('hari', ...)->get()`
2. `DB::table('absensi')->where(...)` → `Absensi::where(...)`

### Langkah 4 — Refactor bagian Siswa (baris 100–131)

1. `DB::table('kelas_siswa as ks')->join(...)` → `KelasSiswa::with('kelas')->where('id_user', ...)->get()`
   > **Catatan:** View mungkin akses `$item->nama_kelas`. Dengan Eloquent jadi `$item->kelas->nama_kelas`. Cek view blade-nya.
2. `DB::table('absensi')->where(...)` → `Absensi::where(...)`

### Langkah 5 — Hapus import DB jika tidak lagi dipakai

Hapus: `use Illuminate\Support\Facades\DB;`

---

## 🧪 Testing

```
1. Login admin → dashboard admin tampil dengan statistik benar
2. Login pelatih → dashboard pelatih tampil dengan jadwal hari ini
3. Login siswa → dashboard siswa tampil dengan kelas dan riwayat absensi
```

---

## ✅ Definition of Done

- [ ] Semua `DB::table()` di DashboardController diganti ke Model
- [ ] Dashboard admin, pelatih, dan siswa tetap tampil benar
- [ ] Statistik (jumlah siswa, absensi hari ini, dll) tetap akurat

---

**Created:** May 4, 2026
