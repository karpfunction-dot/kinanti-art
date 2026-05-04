# 30️⃣ REFACTOR: Modul Role, Tugas & Wewenang

**Status:** 🔴 Not Started  
**Priority:** 🟠 MEDIUM  
**Duration:** ~0.5 day  
**Dependency:** Phase 2 selesai  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

Model `Role` sudah ada tapi perlu perbaikan. Perlu 2 model baru: `Tugas` dan `Wewenang`.

**File yang diubah:**
- `app/Http/Controllers/RoleController.php` (183 baris)
- `app/Http/Controllers/TugasWewenangController.php` (355 baris)
- `app/Models/Role.php` (perbaiki timestamps)

**File yang dibuat:**
- `app/Models/Tugas.php`
- `app/Models/Wewenang.php`

---

## 📝 Implementation Checklist

### Langkah 1 — Perbaiki Model `Role`

**Buka:** `app/Models/Role.php`
- Ubah `$timestamps = false` → sesuaikan (tabel punya `created_at`). Jika tidak ada `updated_at`, set `const UPDATED_AT = null;`
- Tambahkan relationship: `hasMany(User::class, 'id_role', 'id_role')`

### Langkah 2 — Buat Model `Tugas`

**Buat file:** `app/Models/Tugas.php`
- Table: `tugas`, PK: `id_tugas`
- Fillable: `nama_tugas`, `kategori`, `deskripsi`, `aktif`
- Relationship: `hasMany(Wewenang::class, 'id_tugas', 'id_tugas')`

### Langkah 3 — Buat Model `Wewenang`

**Buat file:** `app/Models/Wewenang.php`
- Table: `wewenang`, PK: `id_wewenang`
- Fillable: `id_user`, `id_tugas`, `aktif`, `periode_mulai`, `periode_selesai`, `catatan`
- Relationships:
  - `belongsTo(User::class, 'id_user', 'id_user')`
  - `belongsTo(Tugas::class, 'id_tugas', 'id_tugas')`

### Langkah 4 — Refactor `RoleController`

1. Ganti semua `DB::table('roles')` → `Role::`
2. Ganti `DB::table('users')` → `User::`
3. Hapus `Validator::make(...)` → buat Form Request jika mau, atau minimal ganti ke Model-based query

### Langkah 5 — Refactor `TugasWewenangController`

1. Ganti semua `DB::table('tugas')` → `Tugas::`
2. Ganti semua `DB::table('wewenang')` → `Wewenang::`
3. Ganti `DB::table('users as u')->leftJoin(...)` → bisa pakai `User::with('profil')` atau tetap join tapi via Model
4. Hapus semua `Validator::make(...)`

---

## 🧪 Testing

```
1. /settings/roles → list role tampil
2. Tambah/edit role → berhasil
3. Nonaktifkan/aktifkan role → berhasil
4. /tugas-wewenang → list tugas dan wewenang tampil
5. Tambah/edit/hapus tugas → berhasil
6. Tambah/edit/hapus wewenang → berhasil
7. Search user di form wewenang → AJAX berfungsi
```

---

## ✅ Definition of Done

- [ ] Model `Role` diperbaiki
- [ ] Model `Tugas` dan `Wewenang` dibuat
- [ ] Kedua controller refactored ke Eloquent
- [ ] CRUD role, tugas, dan wewenang tetap berfungsi

---

**Created:** May 4, 2026
