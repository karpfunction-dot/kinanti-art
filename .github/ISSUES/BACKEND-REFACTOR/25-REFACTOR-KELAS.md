# 25️⃣ REFACTOR: Modul Kelas & Jenjang

**Status:** 🔴 Not Started  
**Priority:** 🟡 HIGH  
**Duration:** ~1 day  
**Dependency:** [#21 Auth](./21-REFACTOR-AUTH.md) selesai  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

Controller terbesar kedua (573 baris). Punya 3 sub-entity: Kelas, Jenjang, KelasSiswa. Model `Kelas` sudah ada tapi perlu diperbaiki.

**File yang diubah:**
- `app/Http/Controllers/KelasController.php` (573 baris, 20+ `DB::table()`)
- `app/Models/Kelas.php` (perlu update fillable + relationships)

**File yang dibuat:**
- `app/Models/Jenjang.php`
- `app/Models/KelasSiswa.php`
- `app/Http/Requests/Kelas/StoreKelasRequest.php`
- `app/Http/Requests/Kelas/StoreJenjangRequest.php`

---

## 📝 Implementation Checklist

### Langkah 1 — Buat Model `Jenjang`

**Buat file:** `app/Models/Jenjang.php`
- Table: `jenjang`, PK: `id_jenjang`
- Fillable: `nama_jenjang`, `deskripsi`, `aktif`
- Relationship: `hasMany(Kelas::class, 'id_jenjang', 'id_jenjang')`

### Langkah 2 — Buat Model `KelasSiswa`

**Buat file:** `app/Models/KelasSiswa.php`
- Table: `kelas_siswa`
- Fillable: `id_kelas`, `id_user`, `tanggal_gabung`, `aktif`
- Relationships: `belongsTo(Kelas)`, `belongsTo(User)`, `belongsTo(ProfilAnggota)`

### Langkah 3 — Perbaiki Model `Kelas`

**Buka file:** `app/Models/Kelas.php`
- Tambah ke fillable: `id_jenjang`, `pelatih`, `id_lagu`
- Tambah relationships:
  - `belongsTo(Jenjang::class, 'id_jenjang', 'id_jenjang')`
  - `belongsTo(User::class, 'pelatih', 'id_user')` → method name: `pelatihUser()`
  - `belongsTo(Lagu::class, 'id_lagu', 'id_lagu')`
  - `hasMany(KelasSiswa::class, 'id_kelas', 'id_kelas')`

### Langkah 4 — Buat Form Requests

**`app/Http/Requests/Kelas/StoreKelasRequest.php`:**
- Rules: `nama_kelas` required|string|max:100, `id_jenjang` required|exists, `pelatih` nullable|exists, `id_lagu` nullable|exists, `deskripsi` nullable|string, `aktif` required|in:0,1

**`app/Http/Requests/Kelas/StoreJenjangRequest.php`:**
- Rules: `nama_jenjang` required|string|max:100, `deskripsi` nullable|string, `aktif` required|in:0,1

### Langkah 5 — Refactor `KelasController`

1. **Hapus** method `withTimestamps()` — Eloquent handle otomatis
2. Ganti semua `DB::table('jenjang')` → `Jenjang::`
3. Ganti semua `DB::table('kelas')` → `Kelas::`
4. Ganti semua `DB::table('kelas_siswa')` → `KelasSiswa::`
5. Ganti semua `DB::table('lagu')` → `Lagu::`
6. `DB::table('wewenang')` → boleh tetap dulu (Model dibuat di issue #30)
7. Inject `StoreKelasRequest` di `storeKelas()`, `updateKelas()`
8. Inject `StoreJenjangRequest` di `storeJenjang()`, `updateJenjang()`
9. Hapus semua `Validator::make(...)` di method yang sudah pakai Form Request
10. Hapus import `Schema` dan `Validator`

---

## 🧪 Testing

```
1. /kelas → list kelas tampil dengan jenjang dan pelatih
2. Tambah/edit/hapus jenjang → AJAX berhasil
3. Tambah/edit/nonaktifkan kelas → AJAX berhasil
4. /kelas/entri → tambah/hapus siswa ke kelas
5. /kelas/naik → mutasi siswa antar kelas
```

---

## ✅ Definition of Done

- [ ] Model `Jenjang` dan `KelasSiswa` dibuat
- [ ] Model `Kelas` diperbarui
- [ ] `withTimestamps()` dihapus
- [ ] Form Requests dibuat dan di-inject
- [ ] Semua fitur kelas tetap berfungsi

---

**Created:** May 4, 2026
