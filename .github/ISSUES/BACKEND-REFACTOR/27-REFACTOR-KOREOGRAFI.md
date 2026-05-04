# 27️⃣ REFACTOR: Modul Koreografi

**Status:** 🔴 Not Started  
**Priority:** 🟠 MEDIUM  
**Duration:** ~0.5 day  
**Dependency:** [#22 Lagu](./22-REFACTOR-LAGU.md) (butuh Model Lagu)  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

Manajemen progress koreografi per lagu per pelatih per bulan.

**File yang diubah:**
- `app/Http/Controllers/KoreografiController.php` (240 baris)

**File yang dibuat:**
- `app/Models/Koreografi.php`
- `app/Http/Requests/Koreografi/KoreografiRequest.php`

---

## 📝 Implementation Checklist

### Langkah 1 — Buat Model `Koreografi`

**Buat file:** `app/Models/Koreografi.php`
- Table: `accounting_koreografi`, PK: `id_koreografi`
- Fillable: `tahun_bulan`, `id_lagu`, `id_pelatih`, `percent_koreo`, `created_by`, `updated_by`
- Relationships:
  - `belongsTo(Lagu::class, 'id_lagu', 'id_lagu')`
  - `belongsTo(User::class, 'id_pelatih', 'id_user')`

### Langkah 2 — Buat Form Request `KoreografiRequest`

**Buat file:** `app/Http/Requests/Koreografi/KoreografiRequest.php`
- Rules: `tahun_bulan` required|date_format:Y-m, `id_lagu` required|exists:lagu,id_lagu, `id_pelatih` required|exists:users,id_user, `percent_koreo` required|numeric|min:0|max:100

### Langkah 3 — Refactor `KoreografiController`

1. Ganti semua `DB::table('accounting_koreografi')` → `Koreografi::`
2. Ganti `DB::table('lagu')` → `Lagu::`
3. Ganti `DB::table('wewenang')` dan `DB::table('users')` → `DB::table()` dulu boleh (atau pakai Model jika sudah ada)
4. Inject `KoreografiRequest` di `store()` dan `update()`
5. Hapus semua `Validator::make(...)`

---

## 🧪 Testing

```
1. /accounting_koreografi → list koreografi tampil per bulan
2. Tambah koreografi baru → berhasil
3. Edit koreografi → berhasil
4. Hapus koreografi → berhasil
5. Filter bulan → data berubah sesuai
```

---

## ✅ Definition of Done

- [ ] Model `Koreografi` dibuat
- [ ] Form Request dibuat
- [ ] Controller refactored
- [ ] CRUD koreografi tetap berfungsi

---

**Created:** May 4, 2026
