@extends('layouts.admin_layout')

@section('title', 'Manajemen Absensi - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-clipboard-list fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Manajemen Absensi</h1>
                    <p class="text-muted small mb-0 mt-1">Kelola kehadiran harian anggota Sanggar Tari Kinanti Art Productions</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('absensi.scan') }}" class="btn btn-primary px-4 py-2 shadow-sm" style="background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%); border-radius: 12px; border: none; font-weight: 600;">
                <i class="fa fa-qrcode me-2"></i> Mulai Scan Barcode
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">
        <div class="card-body p-4">
            <form method="get" action="{{ route('absensi.index') }}" class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-uppercase text-secondary mb-2">
                        <i class="fa fa-calendar me-1"></i> Periode Bulan
                    </label>
                    <input type="month" name="bulan" value="{{ $bulan ?? date('Y-m') }}" class="form-control form-control-lg" style="border-radius: 12px; border: 1.5px solid #e2e8f0; background: white; font-size: 0.95rem;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-uppercase text-secondary mb-2">
                        <i class="fa fa-filter me-1"></i> Filter Peran
                    </label>
                    <select name="role" class="form-select form-select-lg" style="border-radius: 12px; border: 1.5px solid #e2e8f0; background: white; font-size: 0.95rem;">
                        <option value="">Semua Peran</option>
                        @foreach(['manajemen','pelatih','siswa','admin'] as $r)
                            <option value="{{ $r }}" {{ ($filter_role ?? '') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn w-100 py-2 fw-semibold shadow-sm" style="background: #0f3b2c; border-radius: 12px; border: none; color: white;">
                        <i class="fa fa-search me-2"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-0 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fa fa-users text-success me-2"></i> Data Kehadiran
                </h5>
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                    <i class="fa fa-check-circle me-1"></i> Total: {{ $rows->count() }} Anggota
                </span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 py-3 text-secondary fw-semibold small text-uppercase" style="width: 70px;">#</th>
                        <th class="py-3 text-secondary fw-semibold small text-uppercase">Anggota</th>
                        <th class="py-3 text-secondary fw-semibold small text-uppercase">Peran</th>
                        <th class="py-3 text-secondary fw-semibold small text-uppercase">Waktu Presensi</th>
                        <th class="py-3 text-secondary fw-semibold small text-uppercase">Status</th>
                        <th class="py-3 text-secondary fw-semibold small text-uppercase text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $no => $row)
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td class="ps-4 fw-bold text-muted">{{ $no + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-success bg-opacity-10 p-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-user text-success small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $row->nama_lengkap ?? $row->nama ?? '-' }}</div>
                                    <div class="small text-muted" style="font-size: 0.7rem;">{{ $row->kode_barcode ?? $row->kode_anggota ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleColors = [
                                    'pelatih' => ['bg' => 'primary', 'text' => 'white'],
                                    'siswa' => ['bg' => 'info', 'text' => 'white'],
                                    'manajemen' => ['bg' => 'warning', 'text' => 'dark'],
                                    'admin' => ['bg' => 'danger', 'text' => 'white'],
                                ];
                                $role = strtolower($row->role_name ?? $row->peran ?? '-');
                                $color = $roleColors[$role] ?? ['bg' => 'secondary', 'text' => 'white'];
                            @endphp
                            <span class="badge bg-{{ $color['bg'] }} bg-opacity-10 text-{{ $color['text'] }} px-3 py-2 rounded-pill" style="font-weight: 500;">
                                <i class="fa fa-tag me-1 small"></i> {{ ucfirst($role) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">
                                {{ isset($row->tanggal) ? \Carbon\Carbon::parse($row->tanggal)->format('d M Y') : '-' }}
                            </div>
                            <div class="small text-muted">{{ $row->waktu ?? '-' }} WIB</div>
                        </td>
                        <td>
                            <span class="badge {{ isset($row->status) && strtolower($row->status) == 'hadir' ? 'bg-success' : 'bg-secondary' }} px-3 py-2 rounded-pill" style="font-weight: 500;">
                                <i class="fa {{ (isset($row->status) && strtolower($row->status) == 'hadir') ? 'fa-check-circle' : 'fa-clock' }} me-1 small"></i>
                                {{ ucfirst($row->status ?? 'Tidak Hadir') }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                // Gunakan id_absensi atau id sesuai dengan struktur database
                                $rowId = $row->id_absensi ?? $row->id_absen ?? $row->id ?? null;
                            @endphp
                            @if($rowId)
                                <button class="btn btn-sm btn-outline-danger rounded-circle" style="width: 32px; height: 32px; border: none; background: rgba(220, 38, 38, 0.1);" onclick="confirmDelete('{{ $rowId }}')">
                                    <i class="fa fa-trash-alt text-danger"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px; border: none; background: rgba(108, 117, 125, 0.1);" disabled>
                                    <i class="fa fa-trash-alt text-secondary"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa fa-inbox fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada data absensi periode ini.</p>
                                <small class="text-muted">Silakan scan barcode anggota untuk mencatat kehadiran.</small>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data absensi ini?')) {
        // Ganti dengan route delete yang sesuai
        // window.location.href = '/absensi/' + id + '/delete';
        alert('Fitur hapus untuk ID: ' + id + ' sedang dalam pengembangan');
    }
}
</script>
@endsection