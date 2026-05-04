# 🎭 Kinanti Art Productions — Management System

**Tech Stack:** Laravel 12 · PHP 8.2+ · MySQL · Cloudinary · DomPDF  
**Status:** Active Development  
**Last Updated:** May 4, 2026

---

## 📋 Deskripsi Project

Sistem manajemen sanggar tari **Kinanti Art Productions** yang mencakup:
- Manajemen kelas, jenjang, dan siswa
- Absensi via barcode scanner
- Jadwal latihan
- Transaksi keuangan (SPP, tabungan, dll)
- Payroll & accounting
- Inventaris video dan musik
- Manajemen user, role, tugas & wewenang
- ID Card generator
- WhatsApp notification
- Landing page CMS *(planned)*

---

## 🚀 Quick Start

```bash
# 1. Clone & install
git clone https://github.com/karpfunction-dot/kinanti-art.git
cd kinanti-art
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate
# Edit .env → set DB_DATABASE, CLOUDINARY_URL

# 3. Database
php artisan migrate

# 4. Run
php artisan serve
```

---

## 📂 Dokumentasi Project

Semua dokumentasi teknis dan planning disimpan di folder `.github/ISSUES/`:

```
.github/ISSUES/
├── README.md                          ← Navigasi utama
├── BACKEND-REFACTOR/                  ← Issues refactoring backend
│   ├── README.md                      ← Index backend (13 modul)
│   ├── 20-MASTER-BACKEND-REFACTOR.md  ← Master issue + aturan global
│   ├── 21-REFACTOR-AUTH.md            ← Security fix (plain text password)
│   ├── 22-REFACTOR-LAGU.md            ← Model Lagu + Form Request
│   ├── 23-REFACTOR-ABSENSI.md         ← Model Absensi + Form Request
│   ├── 24-REFACTOR-JADWAL.md          ← Model Jadwal + Form Request
│   ├── 25-REFACTOR-KELAS.md           ← Model Jenjang, KelasSiswa + perbaiki Kelas
│   ├── 26-REFACTOR-USER-PROFIL.md     ← Perbaiki User & ProfilAnggota
│   ├── 27-REFACTOR-KOREOGRAFI.md      ← Model Koreografi
│   ├── 28-REFACTOR-VIDEO.md           ← Model VideoInventaris
│   ├── 29-REFACTOR-TRANSAKSI.md       ← Model SPP, Tabungan, Lainnya, Setting
│   ├── 30-REFACTOR-ROLE-TUGAS.md      ← Model Tugas, Wewenang
│   ├── 31-REFACTOR-DASHBOARD.md       ← Ganti raw query ke Model
│   └── 32-REFACTOR-ROUTES.md          ← Cleanup routes/web.php
│
└── FRONTEND-LANDING/                  ← Issues landing page CMS (fitur baru)
    ├── README.md                      ← Index frontend (14 file)
    ├── 00-MASTER-ISSUE.md             ← Master issue landing page
    ├── 00-SETUP-AND-PREPARATION.md    ← Database migration & model setup
    ├── 01-MODULE-ARTICLE.md           ← CRUD Artikel/Berita
    ├── 02-MODULE-EVENT.md             ← CRUD Event
    ├── 03-MODULE-ACHIEVEMENT.md       ← CRUD Prestasi
    ├── 04-MODULE-TRAINER.md           ← CRUD Profil Pelatih
    ├── 05-MODULE-ACTIVITY.md          ← CRUD Kegiatan
    ├── 06-MODULE-HERO-SLIDER.md       ← CRUD Hero Slider
    ├── 07-MODULE-SITE-SETTINGS.md     ← CRUD Pengaturan Situs
    ├── 08-LANDING-PAGE-FRONTEND.md    ← Frontend blade templates
    ├── 09-VALIDATION-AND-SECURITY-CHECKLIST.md
    ├── 10-DOCUMENTATION-AND-TRAINING.md
    └── AUTHORIZATION_AND_PERMISSIONS.md
```

---

## 🎯 Prioritas Pengerjaan Global

Berikut urutan pengerjaan yang **wajib diikuti** untuk menghindari conflict dan regresi:

### 🔴 PRIORITAS 1 — Backend Refactoring (Fondasi)

> **Alasan:** Semua controller saat ini masih pakai `DB::table()` mentah.
> Landing page CMS **tidak boleh dimulai** sebelum fondasi ini beres, karena akan memakai model dan pattern yang sama.

| Tahap | Issue | Fokus | Durasi |
|-------|-------|-------|--------|
| **1.1** | [#21 Auth](/.github/ISSUES/BACKEND-REFACTOR/21-REFACTOR-AUTH.md) | 🔴 **SECURITY** — Hapus plain text password, buat Model Pendaftar | 0.5d |
| **1.2** | [#22 Lagu](/.github/ISSUES/BACKEND-REFACTOR/22-REFACTOR-LAGU.md) | Modul paling simpel — latihan pola refactor | 0.5d |
| **1.3** | [#23 Absensi](/.github/ISSUES/BACKEND-REFACTOR/23-REFACTOR-ABSENSI.md) | Core feature harian | 0.5d |
| **1.4** | [#24 Jadwal](/.github/ISSUES/BACKEND-REFACTOR/24-REFACTOR-JADWAL.md) | Mirip pola Lagu | 0.5d |
| **1.5** | [#25 Kelas](/.github/ISSUES/BACKEND-REFACTOR/25-REFACTOR-KELAS.md) | 3 sub-entity (Kelas, Jenjang, KelasSiswa) | 1d |
| **1.6** | [#26 User & Profil](/.github/ISSUES/BACKEND-REFACTOR/26-REFACTOR-USER-PROFIL.md) | Perbaiki model User & ProfilAnggota | 0.5d |
| **1.7** | [#27 Koreografi](/.github/ISSUES/BACKEND-REFACTOR/27-REFACTOR-KOREOGRAFI.md) | Butuh Model Lagu (#22) | 0.5d |
| **1.8** | [#28 Video](/.github/ISSUES/BACKEND-REFACTOR/28-REFACTOR-VIDEO.md) | Butuh Model Lagu (#22) | 0.5d |
| **1.9** | [#29 Transaksi](/.github/ISSUES/BACKEND-REFACTOR/29-REFACTOR-TRANSAKSI.md) | ⚠️ Controller terbesar (829 baris) | 1.5d |
| **1.10** | [#30 Role & Tugas](/.github/ISSUES/BACKEND-REFACTOR/30-REFACTOR-ROLE-TUGAS.md) | Model Tugas, Wewenang | 0.5d |
| **1.11** | [#31 Dashboard](/.github/ISSUES/BACKEND-REFACTOR/31-REFACTOR-DASHBOARD.md) | Read-only, ganti query ke Model | 0.5d |
| **1.12** | [#32 Routes](/.github/ISSUES/BACKEND-REFACTOR/32-REFACTOR-ROUTES.md) | Cleanup terakhir (403 → <200 baris) | 0.5d |

**📖 Detail lengkap:** [BACKEND-REFACTOR/README.md](/.github/ISSUES/BACKEND-REFACTOR/README.md)

---

### 🟡 PRIORITAS 2 — Landing Page CMS (Fitur Baru)

> **Alasan:** Baru bisa dimulai setelah fondasi backend beres karena menggunakan model, service, dan pattern yang sama.

| Tahap | Issue | Fokus | Durasi |
|-------|-------|-------|--------|
| **2.0** | [#00 Setup](/.github/ISSUES/FRONTEND-LANDING/00-SETUP-AND-PREPARATION.md) | Migration, model, service layer | 1d |
| **2.1** | [#01 Article](/.github/ISSUES/FRONTEND-LANDING/01-MODULE-ARTICLE.md) | CRUD Artikel + Quill editor | 0.5d |
| **2.2** | [#02 Event](/.github/ISSUES/FRONTEND-LANDING/02-MODULE-EVENT.md) | CRUD Event | 0.5d |
| **2.3** | [#03 Achievement](/.github/ISSUES/FRONTEND-LANDING/03-MODULE-ACHIEVEMENT.md) | CRUD Prestasi | 0.5d |
| **2.4** | [#04 Trainer](/.github/ISSUES/FRONTEND-LANDING/04-MODULE-TRAINER.md) | CRUD Profil Pelatih | 0.5d |
| **2.5** | [#05 Activity](/.github/ISSUES/FRONTEND-LANDING/05-MODULE-ACTIVITY.md) | CRUD Kegiatan | 0.5d |
| **2.6** | [#06 Hero Slider](/.github/ISSUES/FRONTEND-LANDING/06-MODULE-HERO-SLIDER.md) | CRUD Slider | 0.5d |
| **2.7** | [#07 Site Settings](/.github/ISSUES/FRONTEND-LANDING/07-MODULE-SITE-SETTINGS.md) | CRUD Pengaturan Web | 0.5d |
| **2.8** | [#08 Frontend](/.github/ISSUES/FRONTEND-LANDING/08-LANDING-PAGE-FRONTEND.md) | Blade templates landing page | 2d |
| **2.9** | [#09 Validation](/.github/ISSUES/FRONTEND-LANDING/09-VALIDATION-AND-SECURITY-CHECKLIST.md) | Security & performance testing | 1d |
| **2.10** | [#10 Documentation](/.github/ISSUES/FRONTEND-LANDING/10-DOCUMENTATION-AND-TRAINING.md) | User guide & technical docs | 0.5d |

**📖 Detail lengkap:** [FRONTEND-LANDING/README.md](/.github/ISSUES/FRONTEND-LANDING/README.md)

---

## 📊 Timeline Global

```
═══════════════════════════════════════════════════════════════
  PRIORITAS 1: BACKEND REFACTORING          ~8 hari (1.5 minggu)
═══════════════════════════════════════════════════════════════
  Day 1       #21 Auth (security fix)
  Day 2-3     #22 Lagu, #23 Absensi, #24 Jadwal     (paralel)
  Day 3-4     #25 Kelas, #26 User & Profil           (paralel)
  Day 4-5     #27 Koreografi, #28 Video, #30 Role    (paralel)
  Day 5-7     #29 Transaksi (controller terbesar)
  Day 7-8     #31 Dashboard, #32 Routes (cleanup)

═══════════════════════════════════════════════════════════════
  PRIORITAS 2: FRONTEND LANDING PAGE CMS    ~7 hari (1.5 minggu)
═══════════════════════════════════════════════════════════════
  Day 9       #00 Setup & Preparation
  Day 10-11   #01-#07 Module CRUD            (3 dev paralel)
  Day 12-13   #08 Frontend Landing Page
  Day 14      #09 Validation & Security
  Day 14+     #10 Documentation (opsional)

═══════════════════════════════════════════════════════════════
  TOTAL: ~3 minggu
═══════════════════════════════════════════════════════════════
```

---

## 📊 Inventaris Lengkap (29 file .md)

### Root (1 file)
| File | Ukuran | Fungsi |
|------|--------|--------|
| `README.md` | 3.9 KB | Dokumentasi project utama (file ini) |
| `.github/ISSUES/README.md` | 1.1 KB | Navigasi utama ke subfolder |
| `.github/ISSUES/SCHEDULE-TASK.md` | 4.2 KB | Jadwal & Spesifikasi Engineer |

### `.github/ISSUES/BACKEND-REFACTOR/` (14 file)
| File | Ukuran | Fungsi |
|------|--------|--------|
| `README.md` | 4.6 KB | Index backend + progress tracker |
| `20-MASTER-BACKEND-REFACTOR.md` | 4.8 KB | Master issue, aturan global, dependency graph |
| `21-REFACTOR-AUTH.md` | 6.7 KB | Security fix + Model Pendaftar |
| `22-REFACTOR-LAGU.md` | 6.1 KB | Model Lagu + LaguRequest |
| `23-REFACTOR-ABSENSI.md` | 5.8 KB | Model Absensi + AbsensiScanRequest |
| `24-REFACTOR-JADWAL.md` | 6.0 KB | Model Jadwal + JadwalRequest |
| `25-REFACTOR-KELAS.md` | 3.3 KB | Model Jenjang, KelasSiswa |
| `26-REFACTOR-USER-PROFIL.md` | 3.1 KB | Perbaiki User & ProfilAnggota |
| `27-REFACTOR-KOREOGRAFI.md` | 2.1 KB | Model Koreografi |
| `28-REFACTOR-VIDEO.md` | 3.6 KB | Model VideoInventaris |
| `29-REFACTOR-TRANSAKSI.md` | 5.9 KB | 4 model transaksi + accounting |
| `30-REFACTOR-ROLE-TUGAS.md` | 2.6 KB | Model Tugas, Wewenang |
| `31-REFACTOR-DASHBOARD.md` | 2.5 KB | Ganti raw query ke Model |
| `32-REFACTOR-ROUTES.md` | 6.2 KB | Cleanup routes 403→200 baris |

### `.github/ISSUES/FRONTEND-LANDING/` (14 file)
| File | Ukuran | Fungsi |
|------|--------|--------|
| `README.md` | 3.6 KB | Index frontend + timeline |
| `00-MASTER-ISSUE.md` | 6.3 KB | Master issue landing page CMS |
| `00-SETUP-AND-PREPARATION.md` | 22.3 KB | Database migration & model setup |
| `01-MODULE-ARTICLE.md` | 25.7 KB | CRUD Artikel (contoh referensi paling lengkap) |
| `02-MODULE-EVENT.md` | 2.4 KB | CRUD Event |
| `03-MODULE-ACHIEVEMENT.md` | 2.9 KB | CRUD Prestasi |
| `04-MODULE-TRAINER.md` | 2.7 KB | CRUD Profil Pelatih |
| `05-MODULE-ACTIVITY.md` | 2.5 KB | CRUD Kegiatan |
| `06-MODULE-HERO-SLIDER.md` | 3.6 KB | CRUD Hero Slider |
| `07-MODULE-SITE-SETTINGS.md` | 5.5 KB | CRUD Pengaturan Situs |
| `08-LANDING-PAGE-FRONTEND.md` | 24.4 KB | Frontend blade templates |
| `09-VALIDATION-AND-SECURITY-CHECKLIST.md` | 13.7 KB | Security & performance testing |
| `10-DOCUMENTATION-AND-TRAINING.md` | 11.5 KB | User guide & technical docs |
| `AUTHORIZATION_AND_PERMISSIONS.md` | 10.9 KB | Permission matrix |

---

## 📞 Kontak

**Repository:** [github.com/karpfunction-dot/kinanti-art](https://github.com/karpfunction-dot/kinanti-art)
