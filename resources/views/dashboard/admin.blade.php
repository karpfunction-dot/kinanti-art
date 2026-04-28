@extends('layouts.admin_layout')

@section('title', 'Dashboard - Kinanti Art')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-tachometer-alt fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Dashboard</h1>
                    <p class="text-muted small mb-0 mt-1">Selamat datang, {{ $nama }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- STATISTIK ATAS --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm dashboard-card" style="border-radius: 20px; background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%); color: white;">
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
    </div>

    {{-- BAGIAN UTAMA (FIX PROPORSI DI SINI) --}}
    <div class="row g-4 mb-4 align-items-stretch">

        {{-- Statistik Kehadiran --}}
        <div class="col-lg-5 col-md-6 col-12 d-flex">
            <div class="card border-0 shadow-sm w-100 dashboard-card" style="border-radius: 20px;">
                <div class="card-header bg-white border-0 py-3 text-dark">
                    <h5 class="mb-0 fw-bold">Statistik Kehadiran (Bulan Ini)</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="row text-center">
                        <div class="col">
                            <div class="p-3 rounded-3 bg-success bg-opacity-10 stat-box">
                                <h3 class="text-success mb-0 fw-bold">{{ $statistik['hadir'] }}</h3>
                                <small class="text-muted">Hadir</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 rounded-3 bg-warning bg-opacity-10 stat-box">
                                <h3 class="text-warning mb-0 fw-bold">{{ $statistik['izin'] }}</h3>
                                <small class="text-muted">Izin</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 rounded-3 bg-danger bg-opacity-10 stat-box">
                                <h3 class="text-danger mb-0 fw-bold">{{ $statistik['alfa'] }}</h3>
                                <small class="text-muted">Alfa</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info & Integrasi --}}
        <div class="col-lg-7 col-md-6 col-12 d-flex">
            <div class="card border-0 shadow-sm w-100 dashboard-card" style="border-radius: 20px;">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">Info & Integrasi Sistem</h5>
                    <span class="badge bg-success bg-opacity-10 text-success">Server Online</span>
                </div>

                <div class="card-body pt-0 d-flex flex-column justify-content-between">
                    <table class="table table-sm table-borderless mb-3">
                        <tr class="border-bottom">
                            <td class="text-muted py-2">Versi Framework</td>
                            <td class="text-end py-2">
                                <strong>Laravel {{ Illuminate\Foundation\Application::VERSION }}</strong>
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted py-2">Status GitHub</td>
                            <td class="text-end py-2">
                                <span class="text-success">● Connected (Railway Auto-Deploy)</span>
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted py-2">Environment</td>
                            <td class="text-end py-2">
                                <span class="badge bg-primary">Production</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Cloudinary Media</td>
                            <td class="text-end py-2">
                                @if(config('cloudinary.cloud_url') || env('CLOUDINARY_URL'))
                                    <span class="text-success fw-bold">✔ Linked</span>
                                @else
                                    <span class="text-danger">✘ Not Linked</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    <div class="p-3 rounded-3 bg-light mt-auto">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fab fa-github text-dark"></i>
                            <small class="text-muted">Sync: {{ date('d M Y, H:i') }} WIB</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* 🔥 BIAR PROPORSIONAL TANPA UBAH LOGIC */
.dashboard-card {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.stat-box {
    min-height: 90px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Hover tetap */
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
}

/* Table kecil tetap rapi */
.table td {
    font-size: 0.9rem;
}

/* Responsive spacing */
@media (max-width: 768px) {
    .stat-box {
        min-height: 70px;
    }
}
</style>

@endsection