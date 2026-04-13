<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\TugasWewenangController;
use App\Http\Controllers\IdCardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\LaguController;
use App\Http\Controllers\KoreografiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\MenuManagerController;
use App\Http\Controllers\MusicPlayerController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\AccountingSettingController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\VideoInventarisController;

///////////////////////////////////////////////////////////////
// PUBLIK
///////////////////////////////////////////////////////////////
Route::get('/', [AuthController::class, 'showForm'])->name('login');
Route::get('/login', [AuthController::class, 'showForm']);
Route::post('/login-proses', [AuthController::class, 'processLogin']);
Route::get('/logout', [AuthController::class, 'logout']);

///////////////////////////////////////////////////////////////
// AUTH
///////////////////////////////////////////////////////////////
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    ///////////////////////////////////////////////////////////
    // KELAS
    ///////////////////////////////////////////////////////////
    Route::prefix('kelas')->group(function () {
        Route::get('/', [KelasController::class, 'index'])->name('kelas.index');
        Route::get('/manage', [KelasController::class, 'index'])->name('kelas.manage');

        Route::get('/entri', [KelasController::class, 'entri'])->name('kelas.entri');
        Route::post('/entri', [KelasController::class, 'storeAnggota']);
        Route::delete('/entri/{id_kelas}/{id_user}', [KelasController::class, 'destroyAnggota']);

        Route::get('/naik', [KelasController::class, 'naik']);
        Route::post('/naik', [KelasController::class, 'prosesNaikKelas']);
    });

    ///////////////////////////////////////////////////////////
    // ABSENSI
    ///////////////////////////////////////////////////////////
    Route::prefix('absensi')->group(function () {
        Route::get('/', [AbsensiController::class, 'index']);
        Route::get('/scan', [AbsensiController::class, 'scan']);
        Route::post('/proses', [AbsensiController::class, 'proses']);
    });

    ///////////////////////////////////////////////////////////
    // PROFIL
    ///////////////////////////////////////////////////////////
    Route::prefix('settings')->group(function () {
        Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
        Route::get('/profil/{id}/edit', [ProfilController::class, 'edit']);
        Route::put('/profil/{id}', [ProfilController::class, 'update']);
    });

    ///////////////////////////////////////////////////////////
    // JADWAL
    ///////////////////////////////////////////////////////////
    Route::get('/jadwal', [JadwalController::class, 'index']);

    ///////////////////////////////////////////////////////////
    // LAGU
    ///////////////////////////////////////////////////////////
    Route::get('/lagu', [LaguController::class, 'index']);

    ///////////////////////////////////////////////////////////
    // TRANSAKSI
    ///////////////////////////////////////////////////////////
    Route::get('/transaksi', [TransaksiController::class, 'index']);

    ///////////////////////////////////////////////////////////
    // WHATSAPP
    ///////////////////////////////////////////////////////////
    Route::get('/whatsapp', [WhatsAppController::class, 'index']);

    ///////////////////////////////////////////////////////////
    // TERMINAL (DEBUG ONLY ⚠️)
    ///////////////////////////////////////////////////////////
    Route::get('/terminal/{command}', function ($command) {

        $allowed = ['migrate', 'optimize:clear', 'config:clear'];

        if (in_array($command, $allowed)) {
            Artisan::call($command);
            return "<pre>" . Artisan::output() . "</pre>";
        }

        return "Command tidak diizinkan.";
    });

    ///////////////////////////////////////////////////////////
    // FIX STORAGE
    ///////////////////////////////////////////////////////////
    Route::get('/terminal/fix-storage', function () {
        exec('rm -rf ' . public_path('storage'));
        Artisan::call('storage:link');
        return "Storage fixed: " . Artisan::output();
    });

});

///////////////////////////////////////////////////////////////
// REGISTER
///////////////////////////////////////////////////////////////
Route::get('/register', [RegisterController::class, 'showForm']);
Route::post('/register', [RegisterController::class, 'register']);

///////////////////////////////////////////////////////////////
// FALLBACK
///////////////////////////////////////////////////////////////
Route::fallback(function () {
    return redirect()->route('login');
});
