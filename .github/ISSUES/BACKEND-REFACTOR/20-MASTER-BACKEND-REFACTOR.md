# 🎯 MASTER ISSUE: Backend Refactoring — Kinanti Art Management System

**Status:** 🔴 Not Started  
**Target Completion:** 2 weeks  
**Assignee:** @backend-team  
**Dependency:** Tidak ada — ini independent dari Landing Page CMS issues (#00–#10)

---

## 📌 Deskripsi Singkat

Refactoring seluruh backend operasional Kinanti Art agar mengikuti standar Laravel modern:
- Mengganti 200+ raw `DB::table()` query dengan **Eloquent Model**
- Memindahkan validasi inline ke **Form Request** classes
- Menghapus legacy **plain text password** compatibility
- Membersihkan **route bloat** (400+ baris, 20+ redirect legacy)

**CRITICAL:** Tidak mengubah behavior/fitur yang sudah berjalan. Hanya refactor internal.

---

## 🔴 Aturan Global (WAJIB Dibaca Sebelum Mulai)

1. Setiap tabel di database **wajib punya Model Eloquent** di `app/Models/`
2. Semua `DB::table(...)` di controller **wajib diganti** dengan Model Eloquent
3. Semua validasi di controller **wajib dipindah** ke `app/Http/Requests/`
4. Semua model **wajib pakai `$timestamps = true`** jika tabel punya `created_at`/`updated_at`. Kalau hanya `created_at`, set `const UPDATED_AT = null;`
5. **Hapus** semua helper `withTimestamps()` buatan manual — Eloquent sudah handle
6. Pengecekan role di controller tetap boleh inline, tapi **pakai constant** dari `RoleConstant`

---

## 🗂️ Sub-Issues Per Modul

### Phase 1: Security Fix (URGENT)
| # | Modul | Durasi | Priority | Status |
|---|-------|--------|----------|--------|
| **21** | [Auth & Register](./21-REFACTOR-AUTH.md) | 0.5d | 🔴 CRITICAL | 🔴 Not Started |

### Phase 2: Core Modules (Paralel)
| # | Modul | Durasi | Priority | Status |
|---|-------|--------|----------|--------|
| **22** | [Lagu](./22-REFACTOR-LAGU.md) | 0.5d | 🟡 HIGH | 🔴 Not Started |
| **23** | [Absensi](./23-REFACTOR-ABSENSI.md) | 0.5d | 🟡 HIGH | 🔴 Not Started |
| **24** | [Jadwal](./24-REFACTOR-JADWAL.md) | 0.5d | 🟡 HIGH | 🔴 Not Started |
| **25** | [Kelas & Jenjang](./25-REFACTOR-KELAS.md) | 1d | 🟡 HIGH | 🔴 Not Started |
| **26** | [User & Profil](./26-REFACTOR-USER-PROFIL.md) | 0.5d | 🟡 HIGH | 🔴 Not Started |

### Phase 3: Feature Modules (Paralel, depends on Phase 2 Models)
| # | Modul | Durasi | Priority | Status |
|---|-------|--------|----------|--------|
| **27** | [Koreografi](./27-REFACTOR-KOREOGRAFI.md) | 0.5d | 🟠 MEDIUM | 🔴 Not Started |
| **28** | [Video & Inventaris](./28-REFACTOR-VIDEO.md) | 0.5d | 🟠 MEDIUM | 🔴 Not Started |
| **29** | [Transaksi & Keuangan](./29-REFACTOR-TRANSAKSI.md) | 1.5d | 🟠 MEDIUM | 🔴 Not Started |
| **30** | [Role, Tugas & Wewenang](./30-REFACTOR-ROLE-TUGAS.md) | 0.5d | 🟠 MEDIUM | 🔴 Not Started |

### Phase 4: Cleanup
| # | Modul | Durasi | Priority | Status |
|---|-------|--------|----------|--------|
| **31** | [Dashboard](./31-REFACTOR-DASHBOARD.md) | 0.5d | 🟢 LOW | 🔴 Not Started |
| **32** | [Route Cleanup](./32-REFACTOR-ROUTES.md) | 0.5d | 🟢 LOW | 🔴 Not Started |

---

## 📊 Daftar Tabel & Status Model

| Tabel | Model Ada? | Perlu Dibuat di Issue # |
|---|---|---|
| `users` | ✅ User.php (perlu perbaikan) | #26 |
| `roles` | ✅ Role.php (perlu perbaikan) | #30 |
| `kelas` | ✅ Kelas.php (perlu perbaikan) | #25 |
| `profil_anggota` | ✅ ProfilAnggota.php (perlu perbaikan) | #26 |
| `menu_registry` | ✅ MenuRegistry.php | — |
| `lagu` | ❌ | #22 |
| `absensi` | ❌ | #23 |
| `jadwal_dev` | ❌ | #24 |
| `jenjang` | ❌ | #25 |
| `kelas_siswa` | ❌ | #25 |
| `tugas` | ❌ | #30 |
| `wewenang` | ❌ | #30 |
| `accounting_koreografi` | ❌ | #27 |
| `accounting_setting` | ❌ | #29 |
| `transaksi_spp` | ❌ | #29 |
| `transaksi_tabungan` | ❌ | #29 |
| `transaksi_lainnya` | ❌ | #29 |
| `video_inventaris` | ❌ | #28 |
| `pendaftar` | ❌ | #21 |

---

## 📌 Dependency Graph

```
#21 AUTH (Day 1) — security fix, harus duluan
    ↓
    ├─→ #22 LAGU (0.5d)
    ├─→ #23 ABSENSI (0.5d)
    ├─→ #24 JADWAL (0.5d)
    ├─→ #25 KELAS (1d)
    └─→ #26 USER & PROFIL (0.5d)
            ↓
            All Phase 2 models exist
            ↓
    ├─→ #27 KOREOGRAFI (0.5d)
    ├─→ #28 VIDEO (0.5d)
    ├─→ #29 TRANSAKSI (1.5d)
    └─→ #30 ROLE/TUGAS (0.5d)
            ↓
    #31 DASHBOARD (0.5d)
    #32 ROUTES (0.5d)
            ↓
    ✅ Refactoring Complete
```

---

## ✅ Definition of Done

- [ ] Semua 12 sub-issue merged
- [ ] Zero `DB::table()` di semua controller
- [ ] Semua Model punya relationships yang benar
- [ ] Semua validasi di Form Request classes
- [ ] Plain text password compatibility dihapus
- [ ] Route file < 200 baris
- [ ] Tidak ada regression (fitur lama tetap berfungsi)
- [ ] `php artisan test` passed (jika ada test)

---

**Created:** May 4, 2026  
**Last Updated:** May 4, 2026
