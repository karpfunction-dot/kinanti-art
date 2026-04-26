@extends('layouts.admin_layout')

@section('title', 'Dashboard Admin - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 20px; background: linear-gradient(135deg, #1a5d45 0%, #0f3b2c 100%);">
                <div class="text-white">
                    <h2 class="fw-bold mb-1">Panel Kendali Admin 👋</h2>
                    <p class="mb-0 opacity-75">Halo, {{ $nama }}. Pantau statistik dan aktivitas sanggar hari ini di sini.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 bg-success bg-opacity-10 rounded-3">
                            <i class="fa fa-users fa-2x text-success"></i>
                        </div>
                        <span class="badge bg-light text-dark rounded-pill border">Total Siswa</span>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $total_siswa ?? 0 }} <span class="fs-6 text-muted fw-normal">Orang</span></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 bg-info bg-opacity-10 rounded-3">
                            <i class="fa fa-user-tie fa-2x text-info"></i>
                        </div>
                        <span class="badge bg-light text-dark rounded-pill border">Total Pelatih</span>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $total_pelatih ?? 0 }} <span class="fs-6 text-muted fw-normal">Orang</span></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 bg-warning bg-opacity-10 rounded-3">
                            <i class="fa fa-clipboard-list fa-2x text-warning"></i>
                        </div>
                        <span class="badge bg-light text-dark rounded-pill border">Absen Hari Ini</span>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $absensi_hari_ini ?? 0 }} <span class="fs-6 text-muted fw-normal">Tercatat</span></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fa fa-list text-primary me-2"></i>Daftar Kelas Sanggar</h5>
                        <a href="{{ route('kelas.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase fs-7 text-muted">
                                <tr>
                                    <th class="ps-4">Nama Kelas</th>
                                    <th>Kategori</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data_kelas ?? [] as $k)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $k->nama_kelas }}</div>
                                    </td>
                                    <td>{{ $k->kategori ?? 'Umum' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('kelas.edit', $k->id_kelas) }}" class="btn btn-sm btn-light rounded-circle">
                                            <i class="fa fa-edit text-primary"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">Belum ada data kelas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fs-7 { font-size: 0.8rem; letter-spacing: 0.5px; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
</style>
@endsection