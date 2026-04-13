@extends('layouts.admin_layout')

@section('title', 'Laporan Evaluasi Absensi - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-chart-simple fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Evaluasi Kehadiran</h1>
                    <p class="text-muted small mb-0 mt-1">Rekapitulasi dan analisis kehadiran anggota Sanggar Tari</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-primary px-4 py-2 shadow-sm" style="background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%); border-radius: 12px; border: none;" onclick="window.location.href='{{ route('laporan.pdf') }}?bulan={{ $bulan }}&kelas={{ $kelas }}'">
                <i class="fa fa-file-pdf me-2"></i> Export PDF
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('laporan.index') }}" class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-uppercase text-secondary mb-2">
                        <i class="fa fa-calendar me-1"></i> Periode Bulan
                    </label>
                    <input type="month" name="bulan" value="{{ $bulan }}" class="form-control form-control-lg" style="border-radius: 12px; border: 1.5px solid #e2e8f0;">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-uppercase text-secondary mb-2">
                        <i class="fa fa-users me-1"></i> Filter Kelas
                    </label>
                    <select name="kelas" class="form-select form-select-lg" style="border-radius: 12px; border: 1.5px solid #e2e8f0;">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id_kelas }}" {{ $kelas == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn w-100 py-2 fw-semibold shadow-sm" style="background: #0f3b2c; border-radius: 12px; border: none; color: white;">
                        <i class="fa fa-search me-2"></i> Tampilkan Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Filter -->
    @if($selectedKelas)
    <div class="alert alert-success border-0 mb-4" style="background: #dcfce7; border-radius: 16px; color: #166534;">
        <i class="fa fa-info-circle me-2"></i>
        Menampilkan laporan untuk Kelas: <strong>{{ $selectedKelas->nama_kelas }}</strong> - Periode {{ $bulanText }}
    </div>
    @endif

    <!-- Ringkasan Statistik -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%); color: white;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Kehadiran</small>
                            <h2 class="mb-0 fw-bold">{{ $statistik['total_hadir'] }}</h2>
                        </div>
                        <i class="fa fa-calendar-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #dcfce7; color: #166534;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Izin</small>
                            <h2 class="mb-0 fw-bold">{{ $statistik['total_izin'] }}</h2>
                        </div>
                        <i class="fa fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #fee2e2; color: #991b1b;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Alfa</small>
                            <h2 class="mb-0 fw-bold">{{ $statistik['total_alfa'] }}</h2>
                        </div>
                        <i class="fa fa-times-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #e0e7ff; color: #1e40af;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Siswa Aktif</small>
                            <h2 class="mb-0 fw-bold">{{ $statistik['total_siswa_aktif'] }}</h2>
                        </div>
                        <i class="fa fa-user-graduate fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- REKAP PER KELAS -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-0 px-4" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fa fa-building me-2 text-success"></i> Rekapitulasi Kehadiran Per Kelas
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 py-3 text-secondary fw-semibold">#</th>
                        <th class="py-3 text-secondary fw-semibold">Nama Kelas</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Jumlah Siswa</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Total Kehadiran</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Rata-rata per Siswa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapPerKelas as $no => $kelasItem)
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $no + 1 }}</td>
                        <td class="fw-semibold">{{ $kelasItem->nama_kelas }}</td>
                        <td class="text-center">{{ $kelasItem->total_siswa }} Siswa</td>
                        <td class="text-center">{{ $kelasItem->total_kehadiran }} Kali</td>
                        <td class="text-center">
                            @php
                                $rata = $kelasItem->total_siswa > 0 ? round($kelasItem->total_kehadiran / $kelasItem->total_siswa, 1) : 0;
                            @endphp
                            {{ $rata }} Kali/Siswa
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data kelas</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- REKAP PER SISWA (EVALUASI) -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-0 px-4" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fa fa-users me-2 text-success"></i> Rekapitulasi Kehadiran Per Siswa
                @if($selectedKelas)
                    <span class="badge bg-success ms-2">Kelas: {{ $selectedKelas->nama_kelas }}</span>
                @endif
            </h5>
            <p class="text-muted small mb-0 mt-1">Evaluasi kehadiran individu siswa periode {{ $bulanText }}</p>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 py-3 text-secondary fw-semibold" style="width: 60px;">#</th>
                        <th class="py-3 text-secondary fw-semibold">Nama Siswa</th>
                        <th class="py-3 text-secondary fw-semibold">Kelas</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Hadir</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Izin</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Alfa</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Total</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Persentase</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapSiswa as $no => $siswa)
                    @php
                        $total = $siswa->hadir + $siswa->izin + $siswa->alfa;
                        $persen = $total > 0 ? round(($siswa->hadir / $total) * 100, 1) : 0;
                        $keterangan = $persen >= 90 ? 'Sangat Baik' : ($persen >= 75 ? 'Baik' : ($persen >= 60 ? 'Cukup' : 'Perlu Bimbingan'));
                        $badgeColor = $persen >= 90 ? 'success' : ($persen >= 75 ? 'info' : ($persen >= 60 ? 'warning' : 'danger'));
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $no + 1 }}</td>
                        <td class="fw-semibold">{{ $siswa->nama_lengkap ?? '-' }}</td>
                        <td>{{ $siswa->nama_kelas ?? '-' }}</td>
                        <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success px-3 py-2">{{ $siswa->hadir }}</span></td>
                        <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">{{ $siswa->izin }}</span></td>
                        <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">{{ $siswa->alfa }}</span></td>
                        <td class="text-center fw-bold">{{ $total }}</td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <div class="progress" style="width: 60px; height: 6px;">
                                    <div class="progress-bar bg-{{ $badgeColor }}" style="width: {{ $persen }}%"></div>
                                </div>
                                <span>{{ $persen }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }} px-3 py-2">
                                {{ $keterangan }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-5 text-muted">Belum ada data siswa untuk periode ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- REKAP PER PELATIH -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-0 px-4" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fa fa-chalkboard-user me-2 text-success"></i> Rekapitulasi Kehadiran Pelatih
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 py-3 text-secondary fw-semibold">#</th>
                        <th class="py-3 text-secondary fw-semibold">Nama Pelatih</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Hadir</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Izin</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Alfa</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Total</th>
                        <th class="py-3 text-secondary fw-semibold text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapPelatih as $no => $pelatih)
                    @php
                        $total = $pelatih->hadir + $pelatih->izin + $pelatih->alfa;
                        $persen = $total > 0 ? round(($pelatih->hadir / $total) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $no + 1 }}</td>
                        <td class="fw-semibold">{{ $pelatih->nama_lengkap ?? '-' }}</td>
                        <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success px-3 py-2">{{ $pelatih->hadir }}</span></td>
                        <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">{{ $pelatih->izin }}</span></td>
                        <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">{{ $pelatih->alfa }}</span></td>
                        <td class="text-center fw-bold">{{ $total }}</td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <div class="progress" style="width: 60px; height: 6px;">
                                    <div class="progress-bar bg-success" style="width: {{ $persen }}%"></div>
                                </div>
                                <span>{{ $persen }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data pelatih</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- DETAIL ABSENSI HARIAN (Collapsible) -->
    <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-0 px-4" style="cursor: pointer;" onclick="toggleDetail()">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fa fa-list me-2 text-success"></i> Detail Absensi Harian
                </h5>
                <i class="fa fa-chevron-down" id="detailIcon"></i>
            </div>
        </div>
        <div id="detailTable" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4 py-3 text-secondary fw-semibold">#</th>
                            <th class="py-3 text-secondary fw-semibold">Nama</th>
                            <th class="py-3 text-secondary fw-semibold">Peran</th>
                            <th class="py-3 text-secondary fw-semibold">Kelas</th>
                            <th class="py-3 text-secondary fw-semibold">Tanggal</th>
                            <th class="py-3 text-secondary fw-semibold">Waktu</th>
                            <th class="py-3 text-secondary fw-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailAbsensi as $no => $item)
                        <tr>
                            <td class="ps-4">{{ $no + 1 }}</td>
                            <td>{{ $item->nama_lengkap ?? '-' }}</td>
                            <td>{{ ucfirst($item->role_name ?? '-') }}</td>
                            <td>{{ $item->nama_kelas ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $item->waktu ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $item->status == 'Hadir' ? 'success' : ($item->status == 'Izin' ? 'warning' : 'danger') }} px-3 py-2">
                                    {{ $item->status ?? '-' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data absensi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDetail() {
        const detail = document.getElementById('detailTable');
        const icon = document.getElementById('detailIcon');
        if (detail.style.display === 'none') {
            detail.style.display = 'block';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        } else {
            detail.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }
</script>

<style>
    .progress {
        background-color: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar {
        border-radius: 10px;
    }
    .card-header:hover {
        background-color: #f1f5f9;
    }
</style>
@endsection