@extends('layouts.admin_layout')

@section('content')
<div class="container-fluid">

    <h4 class="mb-4">💳 Transaksi Saya</h4>

    <!-- SUMMARY -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <small>SPP</small>
                <h5 class="text-success">Rp {{ number_format($summary['total_spp']) }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <small>Tabungan Masuk</small>
                <h5 class="text-primary">Rp {{ number_format($summary['setor']) }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <small>Tabungan Keluar</small>
                <h5 class="text-danger">Rp {{ number_format($summary['tarik']) }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3">
                <small>Saldo</small>
                <h5 class="text-warning">Rp {{ number_format($summary['saldo']) }}</h5>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold">
            Riwayat Transaksi
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Detail</th>
                        <th>Total</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $t)
                    <tr>
                        <td>{{ date('d-m-Y', strtotime($t->tanggal_pembayaran)) }}</td>
                        <td>
                            @if($t->jenis == 'SPP')
                                <span class="badge bg-success">SPP</span>
                            @elseif($t->jenis == 'Tabungan')
                                <span class="badge bg-primary">{{ $t->detail }}</span>
                            @else
                                <span class="badge bg-secondary">Lainnya</span>
                            @endif
                        </td>
                        <td>{{ $t->detail }}</td>
                        <td>Rp {{ number_format($t->total) }}</td>
                        <td>{{ $t->keterangan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Belum ada transaksi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
