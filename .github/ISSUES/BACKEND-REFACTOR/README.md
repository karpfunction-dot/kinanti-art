# 📑 INDEX: Backend Refactoring Issues

**Folder:** `.github/ISSUES/BACKEND-REFACTOR/`  
**Last Updated:** May 4, 2026  
**Total Issues:** 13 files (1 master + 12 modul)  
**Status:** 🔴 Ready for Assignment  
**Scope:** Refactoring internal — tidak mengubah fitur/behavior

---

## ⚠️ Baca Ini Dulu Sebelum Mulai

1. **Tidak ada fitur baru** — ini murni refactoring
2. **Jangan merge ke `main`** sebelum semua issue Phase 2 selesai dan di-test
3. **Test setiap modul** sebelum lanjut ke modul berikutnya
4. **Aturan global** ada di [20-MASTER-BACKEND-REFACTOR.md](./20-MASTER-BACKEND-REFACTOR.md) — **wajib dibaca**

---

## 🗂️ Daftar Issue

### 📌 Master
| File | Keterangan |
|------|------------|
| [20-MASTER-BACKEND-REFACTOR.md](./20-MASTER-BACKEND-REFACTOR.md) | Overview, dependency graph, aturan global |

---

### 🔴 Phase 1 — Security Fix (Kerjakan Pertama!)

| # | File | Durasi | Highlight |
|---|------|--------|-----------|
| 21 | [21-REFACTOR-AUTH.md](./21-REFACTOR-AUTH.md) | 0.5 day | **Hapus plain text password** + Model Pendaftar + Form Request |

---

### 🟡 Phase 2 — Core Modules (Boleh Paralel Setelah #21 Selesai)

| # | File | Durasi | Yang Dibuat |
|---|------|--------|-------------|
| 22 | [22-REFACTOR-LAGU.md](./22-REFACTOR-LAGU.md) | 0.5 day | Model Lagu, LaguRequest |
| 23 | [23-REFACTOR-ABSENSI.md](./23-REFACTOR-ABSENSI.md) | 0.5 day | Model Absensi, AbsensiScanRequest |
| 24 | [24-REFACTOR-JADWAL.md](./24-REFACTOR-JADWAL.md) | 0.5 day | Model Jadwal, JadwalRequest |
| 25 | [25-REFACTOR-KELAS.md](./25-REFACTOR-KELAS.md) | 1 day | Model Jenjang, KelasSiswa + perbaiki Kelas |
| 26 | [26-REFACTOR-USER-PROFIL.md](./26-REFACTOR-USER-PROFIL.md) | 0.5 day | Perbaiki User & ProfilAnggota + Form Requests |

---

### 🟠 Phase 3 — Feature Modules (Butuh Model dari Phase 2)

| # | File | Durasi | Dependency |
|---|------|--------|------------|
| 27 | [27-REFACTOR-KOREOGRAFI.md](./27-REFACTOR-KOREOGRAFI.md) | 0.5 day | Butuh Model Lagu (#22) |
| 28 | [28-REFACTOR-VIDEO.md](./28-REFACTOR-VIDEO.md) | 0.5 day | Butuh Model Lagu (#22) |
| 29 | [29-REFACTOR-TRANSAKSI.md](./29-REFACTOR-TRANSAKSI.md) | 1.5 day | Butuh semua Phase 2 + Model Koreografi (#27) |
| 30 | [30-REFACTOR-ROLE-TUGAS.md](./30-REFACTOR-ROLE-TUGAS.md) | 0.5 day | Butuh semua Phase 2 |

---

### 🟢 Phase 4 — Cleanup (Kerjakan Paling Akhir)

| # | File | Durasi | Catatan |
|---|------|--------|---------|
| 31 | [31-REFACTOR-DASHBOARD.md](./31-REFACTOR-DASHBOARD.md) | 0.5 day | Butuh semua Model dari Phase 2 & 3 |
| 32 | [32-REFACTOR-ROUTES.md](./32-REFACTOR-ROUTES.md) | 0.5 day | **Terakhir!** Setelah semua controller beres |

---

## 📌 Dependency Graph Singkat

```
#21 AUTH
  └──► #22 LAGU ──────────────────────────┐
  └──► #23 ABSENSI                         │
  └──► #24 JADWAL                          ▼
  └──► #25 KELAS ──► #27 KOREOGRAFI ──► #29 TRANSAKSI
  └──► #26 USER  ──► #28 VIDEO
                 └──► #30 ROLE/TUGAS
                           │
                           ▼
                    #31 DASHBOARD
                    #32 ROUTES (terakhir)
```

---

## 📊 Progress Tracker

| Issue | Status | Assignee | Selesai |
|-------|--------|----------|---------|
| #21 Auth | 🔴 Not Started | — | — |
| #22 Lagu | 🔴 Not Started | — | — |
| #23 Absensi | 🔴 Not Started | — | — |
| #24 Jadwal | 🔴 Not Started | — | — |
| #25 Kelas | 🔴 Not Started | — | — |
| #26 User & Profil | 🔴 Not Started | — | — |
| #27 Koreografi | 🔴 Not Started | — | — |
| #28 Video | 🔴 Not Started | — | — |
| #29 Transaksi | 🔴 Not Started | — | — |
| #30 Role & Tugas | 🔴 Not Started | — | — |
| #31 Dashboard | 🔴 Not Started | — | — |
| #32 Routes | 🔴 Not Started | — | — |

---

## 🧠 Tips untuk Junior Engineer / AI Agent

- **Kerjakan satu issue** — jangan paralel di mesin yang sama
- **Pola selalu sama:** Buat Model → Buat Form Request → Refactor Controller → Test
- Kalau ada tabel yang belum ada Model-nya dan **belum ada di issue ini**, tetap boleh pakai `DB::table()` dulu — tandai dengan komentar `// TODO: ganti ke Model`
- Jika ada **error saat test**, rollback ke `DB::table()` dulu, jangan blokir issue lain
- Commit message format: `refactor(#22): Lagu - replace DB::table with Eloquent`

---

**Created:** May 4, 2026  
**Folder:** `.github/ISSUES/BACKEND-REFACTOR/`
