# 32️⃣ REFACTOR: Route Cleanup

**Status:** 🔴 Not Started  
**Priority:** 🟢 LOW  
**Duration:** ~0.5 day  
**Dependency:** Semua issue #21–#31 selesai  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

File `routes/web.php` saat ini 403 baris dengan masalah:
- **8+ auth middleware group** yang terpisah-pisah
- **20+ redirect** untuk kompatibilitas URL lama
- **Nested auth group** yang redundan (baris 24 + baris 29)
- **Import `use` statement** tersebar di tengah file (bukan di atas)

Kerjakan ini **paling akhir** setelah semua controller sudah direfactor.

**File yang diubah:**
- `routes/web.php` (403 baris → target < 200 baris)

---

## 📝 Implementation Checklist

### Langkah 1 — Pindahkan semua `use` statement ke atas file

**Saat ini** import tersebar di baris 223, 234, 245, 255, 267, 275, 291, 300, 312, 328, 337, 348, 361.

**Pindahkan semua** ke bawah `<?php` sebelum route pertama:
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\TugasWewenangController;
use App\Http\Controllers\IdCardController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\LaguController;
use App\Http\Controllers\KoreografiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\MenuManagerController;
use App\Http\Controllers\MusicPlayerController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoInventarisController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\AccountingSettingController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\RegisterController;
```

---

### Langkah 2 — Hapus nested auth group yang redundan

**Saat ini (baris 24–32):**
```php
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::middleware('auth')->group(function () {  // ← REDUNDANT!
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
    // ...
});
```

**Ganti menjadi:**
```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // ...
});
```

---

### Langkah 3 — Hapus semua redirect kompatibilitas lama

**Hapus semua blok ini** (total ±20 route, ±50 baris):

```
Route::get('/kelas_manage', ...)        → HAPUS
Route::get('/kelas_entri', ...)         → HAPUS
Route::get('/kelas_naik', ...)          → HAPUS
Route::get('/jadwal_info', ...)         → HAPUS
Route::get('/jadwal-info', ...)         → HAPUS
Route::get('/jadwal_manage', ...)       → HAPUS
Route::get('/idcard_info', ...)         → HAPUS
Route::get('/idcard-info', ...)         → HAPUS
Route::get('/info/jadwal_kelas', ...)   → HAPUS
Route::get('/info/idcard_info', ...)    → HAPUS
Route::get('/tugas_wewenang', ...)      → HAPUS
Route::get('/tugas_wewenang/{any}', ...)→ HAPUS
Route::get('/settings/menur', ...)      → HAPUS
```

> **⚠️ PERHATIAN:** Sebelum menghapus, pastikan tidak ada menu/link di blade template yang masih pakai URL lama. Cari di view dengan: `grep -r "kelas_manage\|kelas_entri\|jadwal_info\|idcard_info\|tugas_wewenang\|menur" resources/views/`

---

### Langkah 4 — Gabungkan semua auth middleware group

**Saat ini** ada 8+ group terpisah. **Gabungkan** menjadi 1 group besar:

```php
// =============================================
// HALAMAN PUBLIK (tanpa login)
// =============================================
Route::get('/', [AuthController::class, 'showForm'])->name('login');
Route::get('/login', [AuthController::class, 'showForm']);
Route::post('/login-proses', [AuthController::class, 'processLogin'])->name('login.process');
Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.proses');

// =============================================
// HALAMAN YANG MEMERLUKAN LOGIN
// =============================================
Route::middleware('auth')->group(function () {

    // --- Logout ---
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Kelas ---
    Route::prefix('kelas')->group(function () { ... });

    // --- Absensi ---
    Route::prefix('absensi')->group(function () { ... });

    // --- Jadwal ---
    Route::get('/jadwal', ...);
    Route::post('/jadwal', ...);
    // dst...

    // --- Lagu ---
    // --- Koreografi ---
    // --- Video ---
    // --- Transaksi ---
    // --- Settings (Users, Profil, Roles, Company, Menu) ---
    // --- Tugas & Wewenang ---
    // --- ID Card ---
    // --- WhatsApp ---
    // --- Accounting ---
    // --- Admin Pendaftar ---
    // --- Laporan ---
});

// =============================================
// FALLBACK
// =============================================
Route::fallback(function () { ... });
```

---

### Langkah 5 — Validasi akhir

Jalankan:
```bash
php artisan route:list
```

Pastikan:
- [ ] Semua named route masih ada
- [ ] Tidak ada route duplikat
- [ ] Tidak ada error

---

## 🧪 Testing

```
1. php artisan route:list → tidak ada error
2. Test setiap halaman utama masih bisa diakses:
   - /dashboard, /kelas, /absensi, /jadwal, /lagu
   - /transaksi, /settings/users, /settings/profil
   - /accounting/setting, /whatsapp, /video
3. Test URL lama yang dihapus → harus return 404 (bukan error 500)
```

---

## ✅ Definition of Done

- [ ] Semua `use` statement di atas file
- [ ] Nested auth group dihapus
- [ ] Redirect kompatibilitas lama dihapus
- [ ] Hanya 1 auth middleware group
- [ ] `routes/web.php` < 200 baris
- [ ] `php artisan route:list` tanpa error
- [ ] Semua halaman tetap bisa diakses

---

**Created:** May 4, 2026
