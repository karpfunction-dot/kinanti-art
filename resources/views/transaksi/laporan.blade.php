@extends('layouts.admin_layout')

@section('title', 'Laporan Keuangan - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-chart-pie fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Laporan Transaksi</h1>
                    <p class="text-muted small mb-0 mt-1">Rekap SPP, Tabungan, dan Detail Transaksi Siswa</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('transaksi.index') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 12px;">
                <i class="fa fa-arrow-left me-2"></i> Kembali ke Transaksi
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation mb-4">
        <a href="{{ route('transaksi.laporan', ['tab' => 'spp', 'bulan' => $bulan]) }}" 
           class="tab-btn {{ $tab == 'spp' ? 'active' : '' }}">
            <i class="fa fa-calendar"></i> Rekap SPP per Bulan
        </a>
        <a href="{{ route('transaksi.laporan', ['tab' => 'tabungan']) }}" 
           class="tab-btn {{ $tab == 'tabungan' ? 'active' : '' }}">
            <i class="fa fa-piggy-bank"></i> Rekap Tabungan Siswa
        </a>
        <a href="{{ route('transaksi.laporan', ['tab' => 'detail']) }}" 
           class="tab-btn {{ $tab == 'detail' ? 'active' : '' }}">
            <i class="fa fa-user"></i> Detail Transaksi Siswa
        </a>
    </div>

    <!-- ====================================================== -->
    <!-- TAB 1: REKAP SPP PER BULAN -->
    <!-- ====================================================== -->
    @if($tab == 'spp')
    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('transaksi.laporan') }}" class="filter-form">
            <input type="hidden" name="tab" value="spp">
            <div class="filter-group">
                <label>Pilih Bulan</label>
                <select name="bulan" class="form-control" onchange="this.form.submit()">
                    @foreach($bulanList as $b)
                        <option value="{{ $b['value'] }}" {{ $bulan == $b['value'] ? 'selected' : '' }}>
                            {{ $b['nama'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-calendar-check"></i>
                <h5>Rekap SPP Bulan {{ \Carbon\Carbon::parse($bulan)->format('F Y') }}</h5>
            </div>
            <div class="stats-summary">
                @php
                    $sudahBayar = 0;
                    $belumBayar = 0;
                    foreach($rekapSpp as $s) {
                        if ($s['status'] == 'Sudah Bayar') $sudahBayar++;
                        else $belumBayar++;
                    }
                @endphp
                <span class="stat-badge sudah-bayar">
                    <i class="fa fa-check-circle"></i> Sudah Bayar: {{ $sudahBayar }}
                </span>
                <span class="stat-badge belum-bayar">
                    <i class="fa fa-clock"></i> Belum Bayar: {{ $belumBayar }}
                </span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kode Barcode</th>
                        <th>Status</th>
                        <th>Nominal</th>
                        <th>Tanggal Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapSpp as $no => $s)
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td class="fw-semibold">{{ $s['nama_lengkap'] }}</td>
                        <td><code>{{ $s['kode_barcode'] }}</code></td>
                        <td>
                            @if($s['status'] == 'Sudah Bayar')
                                <span class="badge badge-success">✅ Sudah Bayar</span>
                            @else
                                <span class="badge badge-danger">⏰ Belum Bayar</span>
                            @endif
                        </td>
                        <td>
                            @if($s['status'] == 'Sudah Bayar')
                                Rp {{ number_format($s['total'], 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $s['tanggal'] ? \Carbon\Carbon::parse($s['tanggal'])->format('d/m/Y') : '-' }}</td>
                    </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Tidak ada data siswa</td
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- ====================================================== -->
    <!-- TAB 2: REKAP TABUNGAN PER SISWA -->
    <!-- ====================================================== -->
    @if($tab == 'tabungan')
    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-piggy-bank"></i>
                <h5>Rekap Tabungan Siswa</h5>
            </div>
            <span class="total-badge">Total Siswa: {{ count($rekapTabungan) }}</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kode Barcode</th>
                        <th>Total Setor</th>
                        <th>Total Tarik</th>
                        <th>Saldo</th>
                        <th>Transaksi Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapTabungan as $no => $s)
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td class="fw-semibold">{{ $s['nama_lengkap'] }}</td>
                        <td><code>{{ $s['kode_barcode'] }}</code></td>
                        <td>
                            <span class="amount-setor">Rp {{ number_format($s['total_setor'], 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="amount-tarik">Rp {{ number_format($s['total_tarik'], 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="amount-saldo {{ $s['saldo'] >= 0 ? 'positive' : 'negative' }}">
                                Rp {{ number_format($s['saldo'], 0, ',', '.') }}
                            </span>
                        </td>
                        <td>{{ $s['last_transaksi'] ? \Carbon\Carbon::parse($s['last_transaksi'])->format('d/m/Y') : '-' }}</td>
                    </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Tidak ada data siswa</td
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- ====================================================== -->
    <!-- TAB 3: DETAIL TRANSAKSI PER SISWA -->
    <!-- ====================================================== -->
    @if($tab == 'detail')
    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('transaksi.laporan') }}" class="filter-form">
            <input type="hidden" name="tab" value="detail">
            <div class="filter-group">
                <label>Pilih Siswa</label>
                <select name="id_user" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Pilih Siswa --</option>
                    @foreach($daftarSiswa as $s)
                        <option value="{{ $s->id_user }}" {{ $id_user == $s->id_user ? 'selected' : '' }}>
                            {{ $s->nama_lengkap }} ({{ $s->kode_barcode }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($id_user && $selectedSiswa)
        <!-- Profil Siswa -->
        <div class="profile-card mb-4">
            <div class="profile-icon">
                <i class="fa fa-user-graduate"></i>
            </div>
            <div class="profile-info">
                <h3>{{ $selectedSiswa->nama_lengkap }}</h3>
                <p>Kode Barcode: <code>{{ $selectedSiswa->kode_barcode }}</code> | ID: {{ $selectedSiswa->id_user }}</p>
            </div>
        </div>

        <!-- Statistik Siswa -->
        <div class="stats-row mb-4">
            <div class="stat-box">
                <div class="stat-icon"><i class="fa fa-book"></i></div>
                <div class="stat-number">Rp {{ number_format($statistik['total_spp'], 0, ',', '.') }}</div>
                <div class="stat-label">Total SPP</div>
            </div>
            <div class="stat-box">
                <div class="stat-icon"><i class="fa fa-piggy-bank"></i></div>
                <div class="stat-number">Rp {{ number_format($statistik['total_tabungan_setor'], 0, ',', '.') }}</div>
                <div class="stat-label">Total Setor</div>
            </div>
            <div class="stat-box">
                <div class="stat-icon"><i class="fa fa-money-bill-wave"></i></div>
                <div class="stat-number">Rp {{ number_format($statistik['total_tabungan_tarik'], 0, ',', '.') }}</div>
                <div class="stat-label">Total Tarik</div>
            </div>
            <div class="stat-box">
                <div class="stat-icon"><i class="fa fa-wallet"></i></div>
                <div class="stat-number {{ $statistik['saldo_tabungan'] >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($statistik['saldo_tabungan'], 0, ',', '.') }}
                </div>
                <div class="stat-label">Saldo Tabungan</div>
            </div>
        </div>

        <!-- Tabel Detail Transaksi -->
        <div class="table-card">
            <div class="table-header">
                <div class="header-left">
                    <i class="fa fa-list"></i>
                    <h5>Riwayat Transaksi</h5>
                </div>
                <span class="total-badge">Total: {{ $detailTransaksi->count() }} Transaksi</span>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis</th>
                            <th>Detail</th>
                            <th>Tanggal</th>
                            <th>Nominal</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailTransaksi as $no => $t)
                        <tr>
                            <td>{{ $no + 1 }}</td
                            <td>
                                <span class="badge {{ $t->jenis == 'SPP' ? 'badge-spp' : ($t->jenis == 'Tabungan' ? 'badge-tabungan' : 'badge-lainnya') }}">
                                    {{ $t->jenis }}
                                </span>
                            </td
                            <td>{{ $t->detail }}</td
                            <td>{{ \Carbon\Carbon::parse($t->tanggal_pembayaran)->format('d/m/Y') }}</td
                            <td>
                                <span class="amount {{ $t->jenis == 'Tabungan' && $t->detail == 'Tarik' ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($t->total, 0, ',', '.') }}
                                </span>
                            </td
                            <td>{{ $t->keterangan ?: '-' }}</td
                        </tr
                        @empty
                        <tr
                            <td colspan="6" class="text-center">Belum ada transaksi</td
                        </tr
                        @endforelse
                    </tbody>
                </table
            </div>
        </div>
    @elseif($id_user)
        <div class="alert-info text-center">
            <i class="fa fa-info-circle fa-2x mb-2"></i>
            <p>Silakan pilih siswa terlebih dahulu</p>
        </div>
    @endif
    @endif
</div>

<style>
    .tab-navigation {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0;
    }
    .tab-btn {
        padding: 10px 24px;
        border-radius: 12px 12px 0 0;
        text-decoration: none;
        font-weight: 600;
        color: #64748b;
        transition: all 0.2s;
    }
    .tab-btn:hover {
        background: #f1f5f9;
        color: #0f3b2c;
    }
    .tab-btn.active {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
    }
    
    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 16px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .filter-form {
        display: flex;
        gap: 15px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .filter-group {
        min-width: 200px;
    }
    .filter-group label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
    }
    
    .table-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .table-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .header-left { display: flex; align-items: center; gap: 10px; }
    .header-left i { font-size: 18px; color: #1a5d45; }
    .header-left h5 { margin: 0; font-size: 15px; font-weight: 600; }
    .total-badge {
        background: #dcfce7;
        color: #166534;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    .stats-summary {
        display: flex;
        gap: 12px;
    }
    .stat-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .stat-badge.sudah-bayar { background: #dcfce7; color: #166534; }
    .stat-badge.belum-bayar { background: #fee2e2; color: #991b1b; }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table th, .data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }
    .data-table th {
        background: #f8fafc;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
    }
    
    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
    }
    .badge-success { background: #dcfce7; color: #166534; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-spp { background: #dbeafe; color: #1e40af; }
    .badge-tabungan { background: #fef3c7; color: #92400e; }
    .badge-lainnya { background: #e0e7ff; color: #3730a3; }
    
    .amount-setor { color: #16a34a; font-weight: 600; }
    .amount-tarik { color: #dc2626; font-weight: 600; }
    .amount-saldo.positive { color: #16a34a; font-weight: 700; }
    .amount-saldo.negative { color: #dc2626; font-weight: 700; }
    
    .profile-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-radius: 20px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .profile-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 28px;
    }
    .profile-info h3 {
        margin: 0 0 5px;
        font-size: 18px;
        font-weight: 700;
        color: #0f3b2c;
    }
    .profile-info p {
        margin: 0;
        font-size: 13px;
        color: #166534;
    }
    
    .stats-row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .stat-box {
        flex: 1;
        min-width: 140px;
        background: white;
        border-radius: 16px;
        padding: 16px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .stat-icon {
        font-size: 24px;
        color: #1a5d45;
        margin-bottom: 8px;
    }
    .stat-number {
        font-size: 20px;
        font-weight: 700;
        color: #0f3b2c;
    }
    .stat-label {
        font-size: 11px;
        color: #64748b;
        margin-top: 4px;
    }
    
    .alert-info {
        background: #e0f2fe;
        border-left: 4px solid #0284c7;
        padding: 30px;
        border-radius: 16px;
        text-align: center;
        color: #0c4a6e;
    }
    
    .text-success { color: #16a34a; font-weight: 600; }
    .text-danger { color: #dc2626; font-weight: 600; }
    .fw-semibold { font-weight: 600; }
    code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 6px;
        font-size: 12px;
    }
    
    @media (max-width: 768px) {
        .tab-navigation { flex-wrap: wrap; }
        .tab-btn { flex: 1; text-align: center; font-size: 12px; padding: 8px 12px; }
        .stats-row { flex-direction: column; }
        .table-header { flex-direction: column; align-items: flex-start; }
    }
</style>
@endsection