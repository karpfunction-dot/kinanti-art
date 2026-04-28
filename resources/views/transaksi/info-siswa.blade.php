@extends('layouts.admin_layout')

@section('title', 'Informasi Transaksi - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="fa fa-receipt fa-2x text-info"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">💰 Informasi Transaksi</h1>
                    <p class="text-muted small mb-0 mt-1">Riwayat SPP, Tabungan, dan Transaksi Lainnya Anda</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Total SPP -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body">
                    <small class="opacity-75">Total SPP</small>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($summary['total_spp'] ?? 0, 0, ',', '.') }}</h3>
                    <small class="opacity-75 mt-2">Sudah dibayarkan</small>
                </div>
            </div>
        </div>

        <!-- Saldo Tabungan -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <div class="card-body">
                    <small class="opacity-75">Saldo Tabungan</small>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($summary['saldo_tabungan'] ?? 0, 0, ',', '.') }}</h3>
                    <small class="opacity-75 mt-2">
                        <i class="fa fa-plus me-1"></i> Rp {{ number_format($summary['total_tabungan_setor'] ?? 0, 0, ',', '.') }}
                        <i class="fa fa-minus ms-2 me-1"></i> Rp {{ number_format($summary['total_tabungan_tarik'] ?? 0, 0, ',', '.') }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Transaksi Lainnya -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                <div class="card-body">
                    <small class="opacity-75">Transaksi Lainnya</small>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($summary['total_lainnya'] ?? 0, 0, ',', '.') }}</h3>
                    <small class="opacity-75 mt-2">Biaya tambahan</small>
                </div>
            </div>
        </div>

        <!-- Total Keseluruhan -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%); color: white;">
                <div class="card-body">
                    <small class="opacity-75">Total Pengeluaran</small>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($summary['total_semua'] ?? 0, 0, ',', '.') }}</h3>
                    <small class="opacity-75 mt-2">SPP + Lainnya</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Transaksi -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 py-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">📋 Riwayat Transaksi</h5>
                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">{{ count($transaksi ?? []) }} Transaksi</span>
                </div>
                <div class="card-body px-4 py-3">
                    @if(count($transaksi ?? []) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr style="border-bottom: 2px solid #e5e7eb;">
                                        <th class="text-muted fw-semibold small">Tanggal</th>
                                        <th class="text-muted fw-semibold small">Jenis</th>
                                        <th class="text-muted fw-semibold small">Detail</th>
                                        <th class="text-muted fw-semibold small">Nominal</th>
                                        <th class="text-muted fw-semibold small">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transaksi ?? [] as $t)
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td class="py-3">
                                            <span class="fw-semibold text-dark">
                                                {{ \Carbon\Carbon::parse($t->tanggal_pembayaran)->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            @if($t->jenis === 'SPP')
                                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                                    <i class="fa fa-graduation-cap me-1"></i> SPP
                                                </span>
                                            @elseif($t->jenis === 'Tabungan')
                                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                                    <i class="fa fa-piggy-bank me-1"></i> Tabungan
                                                </span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
                                                    <i class="fa fa-tag me-1"></i> Lainnya
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-muted small">{{ $t->detail ?? '-' }}</td>
                                        <td class="py-3">
                                            <strong class="text-dark">Rp {{ number_format($t->total ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td class="py-3 text-muted small">{{ $t->keterangan ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-inbox text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3">Belum ada transaksi</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Info Note -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible fade show border-0" role="alert" style="border-radius: 15px;">
                <i class="fa fa-info-circle me-2"></i>
                <strong>Informasi:</strong> Halaman ini hanya menampilkan data transaksi Anda. 
                Untuk perubahan atau pertanyaan, silakan hubungi admin.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12) !important;
        transform: translateY(-2px);
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-weight: 500;
    }
</style>
@endsection
