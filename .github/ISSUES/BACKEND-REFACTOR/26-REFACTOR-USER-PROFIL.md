# 26️⃣ REFACTOR: Modul User & Profil

**Status:** 🔴 Not Started  
**Priority:** 🟡 HIGH  
**Duration:** ~0.5 day  
**Dependency:** [#21 Auth](./21-REFACTOR-AUTH.md) selesai  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

Model `User` dan `ProfilAnggota` sudah ada tapi perlu perbaikan (timestamps, fillable). Controller masih pakai raw query.

**File yang diubah:**
- `app/Http/Controllers/UserController.php` (218 baris)
- `app/Http/Controllers/ProfilController.php` (118 baris)
- `app/Models/User.php` (perbaiki timestamps + fillable)
- `app/Models/ProfilAnggota.php` (perbaiki timestamps + fillable)

**File yang dibuat:**
- `app/Http/Requests/User/StoreUserRequest.php`
- `app/Http/Requests/User/UpdateUserRequest.php`
- `app/Http/Requests/User/UpdateProfilRequest.php`

---

## 📝 Implementation Checklist

### Langkah 1 — Perbaiki Model `User`

**Buka:** `app/Models/User.php`
- Cek tabel: jika ada `created_at` tapi tidak `updated_at` → set `const UPDATED_AT = null;` dan hapus `$timestamps = false;`
- Jika ada kedua kolom → ubah `$timestamps = false` → `$timestamps = true`
- Tambah ke `$fillable`: `created_at` jika perlu

### Langkah 2 — Perbaiki Model `ProfilAnggota`

**Buka:** `app/Models/ProfilAnggota.php`
- Sama: sesuaikan timestamps dengan kolom tabel
- Tambah ke `$fillable`: `email`, `telepon`, `alamat`, `tanggal_lahir`, `jenis_kelamin`

### Langkah 3 — Buat Form Requests

**`app/Http/Requests/User/StoreUserRequest.php`:**
- Rules dari `UserController::store()`
- `kode_barcode` required|string|unique:users, `nama_lengkap` required|string|max:100, `email` nullable|email|unique:profil_anggota, `id_role` required|exists:roles,id_role, `aktif` boolean

**`app/Http/Requests/User/UpdateUserRequest.php`:**
- Sama tapi unique rule pakai exclude: `unique:users,kode_barcode,' . $this->route('id') . ',id_user'`

**`app/Http/Requests/User/UpdateProfilRequest.php`:**
- Rules dari `ProfilController::update()`

### Langkah 4 — Refactor `UserController`

1. Ganti semua `DB::table('users')` → `User::`
2. Ganti semua `DB::table('profil_anggota')` → `ProfilAnggota::`
3. Ganti semua `DB::table('roles')` → `Role::`
4. Inject `StoreUserRequest` di `store()`, `UpdateUserRequest` di `update()`
5. Hapus semua `Validator::make(...)`

### Langkah 5 — Refactor `ProfilController`

1. Ganti semua `DB::table('profil_anggota')` → `ProfilAnggota::`
2. Ganti semua `DB::table('users')` → `User::`
3. Ganti semua `DB::table('roles')` → `Role::`
4. Inject `UpdateProfilRequest` di `update()`

---

## 🧪 Testing

```
1. /settings/users → list user tampil
2. Tambah user baru → password sementara tampil
3. Edit user → data berubah
4. Reset password → password baru tampil
5. /settings/profil → list profil tampil
6. Edit profil + upload foto → berhasil
```

---

## ✅ Definition of Done

- [ ] Model `User` dan `ProfilAnggota` timestamps diperbaiki
- [ ] Form Requests dibuat
- [ ] Kedua controller refactored ke Eloquent
- [ ] CRUD user dan profil tetap berfungsi

---

**Created:** May 4, 2026
