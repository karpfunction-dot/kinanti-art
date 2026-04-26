<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\TugasWewenangController;
use App\Http\Controllers\IdCardController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Artisan;
// =============================================================
// HALAMAN PUBLIK
// =============================================================
Route::get('/', [AuthController::class, 'showForm'])->name('login');
Route::get('/login', [AuthController::class, 'showForm']);
Route::post('/login-proses', [AuthController::class, 'processLogin']);
Route::get('/logout', [AuthController::class, 'logout']);

// =============================================================
// HALAMAN YANG MEMERLUKAN LOGIN
// =============================================================
Route::middleware('auth')->group(function () {

    // Dashboard
  

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // ... route lainnya
});

    // =============================================================
    // MANAJEMEN KELAS (LENGKAP)
    // =============================================================
    Route::prefix('kelas')->group(function () {
        // Halaman utama manajemen kelas
        Route::get('/', [KelasController::class, 'index'])->name('kelas.index');
        Route::get('/manage', [KelasController::class, 'index'])->name('kelas.manage');
        
        // Entri Anggota Kelas
        Route::get('/entri', [KelasController::class, 'entri'])->name('kelas.entri');
        Route::post('/entri', [KelasController::class, 'storeAnggota'])->name('kelas.entri.store');
        Route::delete('/entri/{id_kelas}/{id_user}', [KelasController::class, 'destroyAnggota'])->name('kelas.entri.destroy');
        Route::get('/get-siswa', [KelasController::class, 'getSiswaByKelas'])->name('kelas.get-siswa');
        
        // Naik Kelas (Mutasi)
        Route::get('/naik', [KelasController::class, 'naik'])->name('kelas.naik');
        Route::post('/naik', [KelasController::class, 'prosesNaikKelas'])->name('kelas.naik.proses');
        
        // Jenjang CRUD (AJAX)
        Route::post('/jenjang', [KelasController::class, 'storeJenjang']);
        Route::put('/jenjang/{id}', [KelasController::class, 'updateJenjang']);
        Route::delete('/jenjang/{id}', [KelasController::class, 'destroyJenjang']);
        Route::get('/jenjang/{id}', [KelasController::class, 'getJenjang']);
        
        // Kelas CRUD (AJAX)
        Route::post('/kelas', [KelasController::class, 'storeKelas']);
        Route::put('/kelas/{id}', [KelasController::class, 'updateKelas']);
        Route::delete('/kelas/{id}', [KelasController::class, 'destroyKelas']);
        Route::get('/kelas/{id}', [KelasController::class, 'getKelas']);
    });
    
    // Alias untuk kompatibilitas dengan link lama
    Route::get('/kelas_manage', function() {
        return redirect()->route('kelas.manage');
    });

    // Pendaftaran Siswa (Belum Implementasi)
    Route::get('/pendaftaran_siswa', function() {
        return view('pages.sedang_dibuat', ['judul' => 'Pendaftaran Siswa']);
    });

// =============================================================
// MANAJEMEN ABSENSI
// =============================================================
Route::prefix('absensi')->group(function () {
    Route::get('/', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('/scan', [AbsensiController::class, 'scan'])->name('absensi.scan');
    Route::post('/proses', [AbsensiController::class, 'proses'])->name('absensi.proses');
    Route::post('/proses-api', [AbsensiController::class, 'prosesApi'])->name('absensi.proses.api');
    Route::delete('/{id}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');
});

// =============================================================
// CLEAR CACHE ROUTE (Emergency)
// =============================================================
Route::get('/clear-cache', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        
        return response()->json([
            'success' => true,
            'message' => 'All cache cleared successfully!',
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});
    // =============================================================
    // LAPORAN ABSENSI
    // =============================================================
    Route::prefix('absensi_report')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
        Route::get('/print', [LaporanController::class, 'print'])->name('laporan.print');
    });

    // =============================================================
    // SETTINGS - MANAJEMEN PENGGUNA
    // =============================================================
    Route::prefix('settings')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('settings.users');
        Route::post('/users', [UserController::class, 'store'])->name('settings.users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('settings.users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('settings.users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('settings.users.destroy');
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('settings.users.reset-password');
        
        // Profil Management
        Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
        Route::get('/profil/{id}/edit', [ProfilController::class, 'edit'])->name('profil.edit');
        Route::put('/profil/{id}', [ProfilController::class, 'update'])->name('profil.update');
        Route::get('/profil/{id}/data', [ProfilController::class, 'getProfile'])->name('profil.data');
    });

    // =============================================================
    // TUGAS & WEWENANG
    // =============================================================
    Route::prefix('tugas-wewenang')->group(function () {
        Route::get('/', [TugasWewenangController::class, 'index'])->name('tugas-wewenang.index');
        
        // Tugas routes
        Route::post('/tugas', [TugasWewenangController::class, 'storeTugas']);
        Route::get('/tugas/{id}/edit', [TugasWewenangController::class, 'editTugas']);
        Route::put('/tugas/{id}', [TugasWewenangController::class, 'updateTugas']);
        Route::delete('/tugas/{id}', [TugasWewenangController::class, 'destroyTugas']);
        
        // Wewenang routes
        Route::post('/wewenang', [TugasWewenangController::class, 'storeWewenang']);
        Route::get('/wewenang/{id}/edit', [TugasWewenangController::class, 'editWewenang']);
        Route::put('/wewenang/{id}', [TugasWewenangController::class, 'updateWewenang']);
        Route::delete('/wewenang/{id}', [TugasWewenangController::class, 'destroyWewenang']);
        
        // Search users
        Route::get('/search-users', [TugasWewenangController::class, 'searchUsers']);
    });
    
    // Redirect dari underscore ke hyphen (kompatibilitas)
    Route::get('/tugas_wewenang', function () {
        return redirect('/tugas-wewenang');
    });
    Route::get('/tugas_wewenang/{any}', function ($any) {
        return redirect('/tugas-wewenang/' . $any);
    })->where('any', '.*');

    // =============================================================
    // ID CARD
    // =============================================================
    Route::prefix('idcard')->group(function () {
        Route::get('/', [IdCardController::class, 'index'])->name('idcard.index');
        Route::get('/preview/{id}', [IdCardController::class, 'preview'])->name('idcard.preview');
        Route::get('/print-all', [IdCardController::class, 'printAll'])->name('idcard.print-all');
    });
});

// Fallback route untuk halaman yang belum tersedia
Route::fallback(function () {
    if (auth()->check()) {
        return view('pages.sedang_dibuat', ['judul' => 'Fitur Belum Tersedia']);
    }
    return redirect()->route('login');
});

// Redirect dari underscore ke slash untuk kelas entri dan naik
Route::get('/kelas_entri', function () {
    return redirect('/kelas/entri');
})->middleware(['auth']);

Route::get('/kelas_naik', function () {
    return redirect('/kelas/naik');
})->middleware(['auth']);

use App\Http\Controllers\JadwalController;

// Route untuk Jadwal
Route::middleware(['auth'])->group(function () {
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::post('/jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
    Route::put('/jadwal/{id}', [JadwalController::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{id}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');
    Route::get('/jadwal/{id}', [JadwalController::class, 'getJadwal']);
});

use App\Http\Controllers\LaguController;

// Route untuk Manajemen Lagu
Route::middleware(['auth'])->group(function () {
    Route::get('/lagu', [LaguController::class, 'index'])->name('lagu.index');
    Route::post('/lagu', [LaguController::class, 'store'])->name('lagu.store');
    Route::put('/lagu/{id}', [LaguController::class, 'update'])->name('lagu.update');
    Route::delete('/lagu/{id}', [LaguController::class, 'destroy'])->name('lagu.destroy');
    Route::get('/lagu/{id}', [LaguController::class, 'getLagu']);
});

use App\Http\Controllers\KoreografiController;

// Route untuk Koreografi
Route::middleware(['auth'])->group(function () {
    Route::get('/accounting_koreografi', [KoreografiController::class, 'index'])->name('koreografi.index');
    Route::post('/accounting_koreografi', [KoreografiController::class, 'store'])->name('koreografi.store');
    Route::put('/accounting_koreografi/{id}', [KoreografiController::class, 'update'])->name('koreografi.update');
    Route::delete('/accounting_koreografi/{id}', [KoreografiController::class, 'destroy'])->name('koreografi.destroy');
});

use App\Http\Controllers\RoleController;

// Route untuk Manajemen Role
Route::prefix('settings')->middleware(['auth'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])->name('settings.roles');
    Route::post('/roles', [RoleController::class, 'store'])->name('settings.roles.store');
    Route::get('/roles/{id}', [RoleController::class, 'getRole']);
    Route::put('/roles/{id}', [RoleController::class, 'update'])->name('settings.roles.update');
    Route::post('/roles/{id}/deactivate', [RoleController::class, 'destroy'])->name('settings.roles.deactivate');
    Route::post('/roles/{id}/activate', [RoleController::class, 'activate'])->name('settings.roles.activate');
});

use App\Http\Controllers\CompanyProfileController;

// Route untuk Profil Sanggar
Route::prefix('settings')->middleware(['auth'])->group(function () {
    Route::get('/company_profile', [CompanyProfileController::class, 'index'])->name('settings.company');
    Route::put('/company_profile', [CompanyProfileController::class, 'update'])->name('settings.company.update');
});

use App\Http\Controllers\MenuManagerController;

// Route untuk Menu Manager
Route::prefix('settings')->middleware(['auth'])->group(function () {
    Route::get('/menu', [MenuManagerController::class, 'index'])->name('settings.menu');
    Route::post('/menu', [MenuManagerController::class, 'store']);
    Route::get('/menu/{id}', [MenuManagerController::class, 'getMenu']);
    Route::put('/menu/{id}', [MenuManagerController::class, 'update']);
    Route::delete('/menu/{id}', [MenuManagerController::class, 'destroy']);
});

// Redirect dari menur ke menu
Route::get('/settings/menur', function () {
    return redirect('/settings/menu');
})->middleware(['auth']);

use App\Http\Controllers\MusicPlayerController;

// Route untuk Music Player
Route::middleware(['auth'])->group(function () {
    Route::get('/music-player', [MusicPlayerController::class, 'index'])->name('music-player.index');
});



use App\Http\Controllers\VideoController;

// Route untuk Video
Route::middleware(['auth'])->group(function () {
    Route::get('/video', [VideoController::class, 'index'])->name('video.index');
    Route::get('/video/upload', [VideoController::class, 'uploadForm'])->name('video.upload');
    Route::post('/video/upload', [VideoController::class, 'upload'])->name('video.upload.process');
    Route::get('/video/stream', [VideoController::class, 'stream'])->name('video.stream');
    Route::get('/video/delete', [VideoController::class, 'delete'])->name('video.delete');
});


use App\Http\Controllers\TransaksiController;

// Route untuk Transaksi
Route::middleware(['auth'])->group(function () {
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
    Route::delete('/transaksi/{sumber}/{id}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');
    Route::get('/transaksi/search-user', [TransaksiController::class, 'searchUser'])->name('transaksi.search-user');
    Route::get('/transaksi/laporan', [TransaksiController::class, 'laporan'])->name('transaksi.laporan');
});
// Route untuk Laporan Keuangan
Route::middleware(['auth'])->group(function () {
    Route::get('/transaksi/laporan-keuangan', [TransaksiController::class, 'laporanKeuangan'])->name('transaksi.laporan-keuangan');
    Route::get('/transaksi/laporan-keuangan/export', [TransaksiController::class, 'exportLaporanKeuangan'])->name('transaksi.laporan-keuangan.export');
});

use App\Http\Controllers\AccountingSettingController;

// Route untuk Accounting Setting
Route::middleware(['auth'])->group(function () {
    Route::get('/accounting/setting', [AccountingSettingController::class, 'index'])->name('accounting.setting');
    Route::post('/accounting/setting', [AccountingSettingController::class, 'save'])->name('accounting.setting.save');
    Route::get('/accounting/payroll', [AccountingSettingController::class, 'payroll'])->name('accounting.payroll');
});

use App\Http\Controllers\WhatsAppController;

// Route untuk WhatsApp
Route::middleware(['auth'])->group(function () {
    Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::post('/whatsapp/send', [WhatsAppController::class, 'send'])->name('whatsapp.send');
    Route::post('/whatsapp/send-bulk', [WhatsAppController::class, 'sendBulk'])->name('whatsapp.send-bulk');
    Route::post('/whatsapp/template/{type}', [WhatsAppController::class, 'getTemplate'])->name('whatsapp.template');
    Route::get('/whatsapp/recipients', [WhatsAppController::class, 'getRecipients'])->name('whatsapp.recipients');
});

use App\Http\Controllers\RegisterController;

// Rute pendaftaran
Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.proses');

// Rute admin untuk kelola pendaftar (harus login & admin)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/pendaftar', [RegisterController::class, 'listPendaftar'])->name('admin.pendaftar');
    Route::post('/admin/pendaftar/approve/{id}', [RegisterController::class, 'approve'])->name('admin.pendaftar.approve');
    Route::post('/admin/pendaftar/reject/{id}', [RegisterController::class, 'reject'])->name('admin.pendaftar.reject');
});

use App\Http\Controllers\VideoInventarisController;

// Route untuk Inventaris Video
Route::middleware(['auth'])->group(function () {
    Route::get('/video/inventaris', [VideoInventarisController::class, 'index'])->name('video.inventaris');
    Route::post('/video/inventaris', [VideoInventarisController::class, 'store']);
    Route::get('/video/inventaris/{id}', [VideoInventarisController::class, 'getVideo']);
    Route::put('/video/inventaris/{id}', [VideoInventarisController::class, 'update']);
    Route::delete('/video/inventaris/{id}', [VideoInventarisController::class, 'destroy']);
    Route::get('/video/player/{id}', [VideoInventarisController::class, 'player'])->name('video.player');
});

Route::get('/cek-env', function () {
    return [
        'cloudinary_url_env' => env('CLOUDINARY_URL') ? 'TERDETEKSI (Aman)' : 'KOSONG (Error)',
        // Ganti 'cloudinary_url' menjadi 'cloud_url' sesuai isi file config kamu
        'cloudinary_config' => config('cloudinary.cloud_url') ? 'TERBACA (Aman)' : 'TIDAK TERBACA (Error)',
        'app_env' => app()->environment(),
    ];
});

Route::get('/debug-role', [AbsensiController::class, 'debugRole'])->middleware('auth');
