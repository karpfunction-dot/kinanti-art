# 📅 SCHEDULE-TASK: Roadmap Eksekusi Kinanti Art

**Versi:** 1.0  
**Target Durasi:** 3 Minggu  
**Status:** 🔴 Ready for Implementation

---

## 🏢 Alokasi Tim & Profil AI

Untuk mendapatkan hasil terbaik, gunakan pembagian peran berikut:

| Peran | Deskripsi | Pelaksana Ideal |
|:--- |:--- |:--- |
| **[SR-BE]** | Senior Backend / AI Thinking | Fokus pada logika rumit, keamanan, transaksi uang, dan arsitektur data. |
| **[JR-BE]** | Junior Backend / AI Flash | Fokus pada penggantian pola kode repetitif, CRUD standar, dan pembuatan Model. |
| **[FE-DEV]** | Frontend Dev / AI Creative | Fokus pada UI/UX, CSS/Styling, dan konversi desain ke Blade Template. |

---

## 🗓️ MINGGU 1: Backend Foundations & Security
*Fokus: Mengamankan sistem dan merapikan data dasar.*

| Hari | Issue | Deskripsi | Pelaksana | Status |
|:---:|:--- |:--- |:---:|:---:|
| 1 | [#21 Auth](./BACKEND-REFACTOR/21-REFACTOR-AUTH.md) | 🔴 **Critical.** Hapus plain-text password & setup Model Pendaftar. | **[SR-BE]** | 🔴 |
| 2 | [#22 Lagu](./BACKEND-REFACTOR/22-REFACTOR-LAGU.md) | Modul Lagu (Latihan pola refactor pertama). | **[JR-BE]** | 🔴 |
| 2 | [#26 User](./BACKEND-REFACTOR/26-REFACTOR-USER-PROFIL.md) | Perbaikan Model User & ProfilAnggota. | **[JR-BE]** | 🔴 |
| 3 | [#25 Kelas](./BACKEND-REFACTOR/25-REFACTOR-KELAS.md) | Merancang 3 sub-entity (Jenjang, Kelas, KelasSiswa). | **[SR-BE]** | 🔴 |
| 4 | [#23 Absensi](./BACKEND-REFACTOR/23-REFACTOR-ABSENSI.md) | Refactor scanner & histori absensi. | **[JR-BE]** | 🔴 |
| 5 | [#24 Jadwal](./BACKEND-REFACTOR/24-REFACTOR-JADWAL.md) | Refactor manajemen jadwal latihan harian. | **[JR-BE]** | 🔴 |

---

## 🗓️ MINGGU 2: Complex Logic & Cleanup
*Fokus: Menyelesaikan modul berat dan merapikan sisa backend.*

| Hari | Issue | Deskripsi | Pelaksana | Status |
|:---:|:--- |:--- |:---:|:---:|
| 1 | [#27 Koreo](./BACKEND-REFACTOR/27-REFACTOR-KOREOGRAFI.md) | Modul progres koreografi per lagu. | **[JR-BE]** | 🔴 |
| 1 | [#28 Video](./BACKEND-REFACTOR/28-REFACTOR-VIDEO.md) | Inventaris video & integrasi streaming. | **[JR-BE]** | 🔴 |
| 2 | [#30 Role](./BACKEND-REFACTOR/30-REFACTOR-ROLE-TUGAS.md) | Manajemen Tugas & Wewenang (RBAC). | **[SR-BE]** | 🔴 |
| 3-4 | [#29 Transaksi](./BACKEND-REFACTOR/29-REFACTOR-TRANSAKSI.md) | ⚠️ **Heavy.** SPP, Tabungan, Payroll & Accounting. | **[SR-BE]** | 🔴 |
| 5 | [#31 Dash](./BACKEND-REFACTOR/31-REFACTOR-DASHBOARD.md) | Ganti raw query dashboard ke Model Eloquent. | **[JR-BE]** | 🔴 |
| 5 | [#32 Route](./BACKEND-REFACTOR/32-REFACTOR-ROUTES.md) | **Pembersihan Akhir.** Cleanup routes/web.php. | **[JR-BE]** | 🔴 |

---

## 🗓️ MINGGU 3: Frontend Landing Page & CMS
*Fokus: Membangun wajah baru dan CMS pendaftar.*

| Hari | Issue | Deskripsi | Pelaksana | Status |
|:---:|:--- |:--- |:---:|:---:|
| 1 | [#00 Setup CMS](./FRONTEND-LANDING/00-SETUP-AND-PREPARATION.md) | Migration, Model & Cloudinary Service setup. | **[SR-BE]** | 🔴 |
| 2 | [#01 - #04](./FRONTEND-LANDING/README.md) | CRUD Artikel, Event, Prestasi, dan Pelatih. | **[JR-BE]** | 🔴 |
| 3 | [#05 - #07](./FRONTEND-LANDING/README.md) | CRUD Kegiatan, Slider, dan Site Settings. | **[JR-BE]** | 🔴 |
| 4 | [#08 Frontend](./FRONTEND-LANDING/08-LANDING-PAGE-FRONTEND.md) | Blade Templates & Styling Landing Page. | **[FE-DEV]** | 🔴 |
| 5 | [#09 Validation](./FRONTEND-LANDING/09-VALIDATION-AND-SECURITY-CHECKLIST.md) | Security testing & Performance audit. | **[SR-BE]** | 🔴 |

---

## 🚦 Aturan Pengerjaan (SOP)

1. **Isolation**: Setiap pengerjaan issue harus dilakukan dalam branch Git terpisah (contoh: `feature/refactor-auth`).
2. **Sequential Stage**: Jangan memulai pengerjaan **MINGGU 3** jika **MINGGU 1 & 2** belum selesai (merged ke main/develop).
3. **Daily Testing**: Selesai satu modul, jalankan fitur tersebut secara manual untuk memastikan fungsionalitas CRUD tetap berjalan (Zero Regression).
4. **AI Instruction**: Berikan file Issue (.md) secara spesifik kepada AI. Jangan menumpuk banyak instruksi dalam satu waktu.

---

**Last Updated:** May 4, 2026
