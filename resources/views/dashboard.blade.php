@extends('layouts.admin_layout')

@section('title', 'Dashboard - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-tachometer-alt fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Dashboard</h1>
                    <p class="text-muted small mb-0 mt-1">Selamat datang, {{ Auth::user()->profil->nama_lengkap ?? Auth::user()->kode_barcode }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%); color: white;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Kelas</small>
                            <h2 class="mb-0 fw-bold">{{ $data_kelas->count() }}</h2>
                        </div>
                        <i class="fa fa-building fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #dcfce7; color: #166534;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Siswa</small>
                            <h2 class="mb-0 fw-bold">{{ $total_siswa }}</h2>
                        </div>
                        <i class="fa fa-user-graduate fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #dbeafe; color: #1e40af;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Pelatih</small>
                            <h2 class="mb-0 fw-bold">{{ $total_pelatih }}</h2>
                        </div>
                        <i class="fa fa-chalkboard-user fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #fef3c7; color: #92400e;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Absensi Hari Ini</small>
                            <h2 class="mb-0 fw-bold">{{ $absensi_hari_ini }}</h2>
                        </div>
                        <i class="fa fa-calendar-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Kehadiran Bulan Ini -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Statistik Kehadiran Bulan Ini</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-success bg-opacity-10">
                                <h3 class="text-success mb-0">{{ $statistik['hadir'] }}</h3>
                                <small class="text-muted">Hadir</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-warning bg-opacity-10">
                                <h3 class="text-warning mb-0">{{ $statistik['izin'] }}</h3>
                                <small class="text-muted">Izin</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-danger bg-opacity-10">
                                <h3 class="text-danger mb-0">{{ $statistik['alfa'] }}</h3>
                                <small class="text-muted">Alfa</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Info Sistem</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%">Laravel Version</td>
                            <td><strong>{{ Illuminate\Foundation\Application::VERSION }}</strong></td>
                        </tr>
                        <tr>
                            <td>PHP Version</td>
                            <td><strong>{{ phpversion() }}</strong></td>
                        </tr>
                        <tr>
                            <td>Server Status</td>
                            <td><span class="badge bg-success">ONLINE</span></td>
                        </tr>
                        <tr>
                            <td>Database</td>
                            <td><span class="badge bg-info">MySQL</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection