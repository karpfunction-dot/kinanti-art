@extends('layouts.admin_layout')

@section('title', 'Dashboard Pelatih - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 20px; background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);">
                <div class="d-flex align-items-center gap-4">
                    <img src="{{ $foto }}" 
                         class="rounded-circle border border-3 border-white shadow-sm" 
                         style="width: 80px; height: 80px; object-fit: cover;"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($nama) }}&background=random'">
                    
                    <div class="text-white">
                        <h2 class="fw-bold mb-1">Halo, Kak {{ explode(' ', $nama)[0] }}! 👋</h2>
                        <p class="mb-0 opacity-75">Semangat melatih hari ini di Sanggar Kinanti Art.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="p-3 bg-primary bg-opacity-10 rounded-3">
                            <i class="fa fa-calendar-check fa-2x text-primary"></i>
                        </div>
                        <span class="badge bg-success rounded-pill">Aktif</span>
                    </div>
                    <h6 class="text-muted small text-uppercase fw-bold">Total Pertemuan</h6>
                    <h3 class="fw-bold">{{ $total_mengajar }} <span class="fs-6 text-muted fw-normal">Sesi</span></h3>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #fff4e3;">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold text-warning-emphasis mb-1">Siap Mengabsen Siswa?</h5>
                        <p class="text-muted small mb-0">Klik tombol untuk membuka kamera scanner barcode.</p>
                    </div>
                    <a href="{{ route('absensi.scan') }}" class="btn btn-dark px-4 py-2 rounded-pill fw-bold">
                        <i class="fa fa-qrcode me-2"></i> Buka Scanner
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-0"><i class="fa fa-clock text-success me-2"></i>Jadwal Mengajar Hari Ini ({{ \Carbon\Carbon::now()->translatedFormat('l') }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Kelas</th>
                                    <th>Jam</th>
                                    <th>Ruangan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jadwal_hari_ini as $j)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $j->nama_kelas }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}
                                        </span>
                                    </td>
                                    <td><i class="fa fa-map-marker-alt text-muted me-1"></i> Studio Utama</td>
                                    <td class="text-center">
                                        <a href="{{ route('absensi.scan') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">Mulai Absen</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fa fa-mug-hot fa-2x mb-3 opacity-25"></i>
                                        <p>Tidak ada jadwal mengajar untuk hari ini. Waktunya istirahat!</p>
                                    </td>
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
@endsection