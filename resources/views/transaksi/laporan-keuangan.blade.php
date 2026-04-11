@extends('layouts.admin_layout')

@section('title', 'Laporan Keuangan - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                <i class="fa fa-chart-line fa-2x text-success"></i>
            </div>
            <div>
                <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Laporan Keuangan</h1>
                <p class="text-muted small mb-0 mt-1">Pendapatan SPP, Tabungan, dan Honor</p>
            </div>
        </div>
        <div>
            <button class="btn-print" onclick="window.print()">
                <i class="fa fa-print"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Alert jika belum ada setting -->
    @if(!$accountingSetting)
        <div class="alert-warning-custom mb-4">
            <i class="fa fa-exclamation-triangle"></i>
            Pengaturan accounting untuk bulan {{ date('F Y', strtotime($bulan . '-01')) }} belum diisi.
            <a href="{{ route('accounting.setting', ['bulan' => $bulan]) }}">Klik di sini untuk mengatur</a>
        </div>
    @endif

    <!-- Filter -->
    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('transaksi.laporan-keuangan') }}" class="filter-form">
            <div class="filter-group">
                <label>Periode Bulan</label>
                <input type="month" name="bulan" class="form-control" value="{{ $bulan }}">
            </div>
            <div class="filter-group">
                <label>Tampilkan</label>
                <select name="view" class="form-control">
                    <option value="global" {{ $view == 'global' ? 'selected' : '' }}>🌍 Global</option>
                    <option value="per_siswa" {{ $view == 'per_siswa' ? 'selected' : '' }}>👥 Per Siswa</option>
                    <option value="per_pelatih" {{ $view == 'per_pelatih' ? 'selected' : '' }}>👨‍🏫 Per Pelatih</option>
                </select>
            </div>
            @if($view != 'global')
            <div class="filter-group">
                <label>Cari</label>
                <input type="text" name="search" class="form-control" placeholder="Cari nama..." value="{{ $search }}">
            </div>
            @endif
            <div class="filter-group">
                <button type="submit" class="btn-filter">Tampilkan</button>
            </div>
        </form>
    </div>

    <!-- ====================================================== -->
    <!-- VIEW GLOBAL -->
    <!-- ====================================================== -->
    @if($view == 'global')
    
    <!-- Summary Cards -->
    <div class="stats-row mb-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="fa fa-book"></i></div>
            <div class="stat-value">Rp {{ number_format($pendapatanSpp, 0, ',', '.') }}</div>
            <div class="stat-label">Pendapatan SPP</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa fa-piggy-bank"></i></div>
            <div class="stat-value">Rp {{ number_format($tabunganSetor, 0, ',', '.') }}</div>
            <div class="stat-label">Tabungan Masuk</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa fa-money-bill-wave"></i></div>
            <div class="stat-value">Rp {{ number_format($tabunganTarik, 0, ',', '.') }}</div>
            <div class="stat-label">Tabungan Keluar</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa fa-chalkboard-user"></i></div>
            <div class="stat-value">Rp {{ number_format($honorData['total_honor'] ?? 0, 0, ',', '.') }}</div>
            <div class="stat-label">Total Honor</div>
        </div>
    </div>

    <!-- Detail SPP -->
    <div class="table-card mb-4">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-list"></i>
                <h5>Detail Pendapatan SPP - {{ date('F Y', strtotime($bulan . '-01')) }}</h5>
            </div>
            <span class="total-badge">Total: {{ $detailSpp->count() }} Transaksi</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Siswa</th>
                        <th>Periode</th>
                        <th>Nominal</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailSpp as $spp)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($spp->tanggal_pembayaran)->format('d/m/Y') }}</td
                        <td>{{ $spp->nama_lengkap ?? 'User ' . $spp->id_user }}</td
                        <td>{{ $spp->periode }}</td
                        <td class="text-success fw-bold">Rp {{ number_format($spp->total, 0, ',', '.') }}</td
                        <td>{{ $spp->keterangan ?: '-' }}</td
                    </tr
                    @empty
                    <tr
                        <td colspan="5" class="text-center">Tidak ada transaksi SPP</td
                    </tr
                    @endforelse
                </tbody>
            </table
        </div>
    </div>

    <!-- Detail Tabungan -->
    <div class="table-card mb-4">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-piggy-bank"></i>
                <h5>Detail Tabungan - {{ date('F Y', strtotime($bulan . '-01')) }}</h5>
            </div>
            <span class="total-badge">Total: {{ $detailTabungan->count() }} Transaksi</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Siswa</th>
                        <th>Jenis</th>
                        <th>Nominal</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailTabungan as $tab)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($tab->tanggal_pembayaran)->format('d/m/Y') }}</td
                        <td>{{ $tab->nama_lengkap ?? 'User ' . $tab->id_user }}</td
                        <td>
                            <span class="badge {{ $tab->jenis == 'Setor' ? 'badge-success' : 'badge-danger' }}">
                                {{ $tab->jenis }}
                            </span>
                        </td
                        <td class="{{ $tab->jenis == 'Setor' ? 'text-success' : 'text-danger' }} fw-bold">
                            Rp {{ number_format($tab->total, 0, ',', '.') }}
                        </td
                        <td>{{ $tab->keterangan ?: '-' }}</td
                    </tr
                    @empty
                    <tr
                        <td colspan="5" class="text-center">Tidak ada transaksi Tabungan</td
                    </tr
                    @endforelse
                </tbody>
            </table
        </div>
    </div>

    <!-- Honor Breakdown -->
    @if($accountingSetting)
    <div class="form-card mb-4">
        <div class="form-header">
            <h5><i class="fa fa-chart-pie"></i> Rincian Honor - {{ date('F Y', strtotime($bulan . '-01')) }}</h5>
        </div>
        <div class="honor-grid">
            <div class="honor-item">
                <div class="honor-label">Omset</div>
                <div class="honor-value">Rp {{ number_format($accountingSetting->omset_manual, 0, ',', '.') }}</div>
            </div>
            <div class="honor-item">
                <div class="honor-label">Operasional</div>
                <div class="honor-value">Rp {{ number_format($accountingSetting->operasional_manual, 0, ',', '.') }}</div>
            </div>
            <div class="honor-item total">
                <div class="honor-label">Net Income</div>
                <div class="honor-value">Rp {{ number_format($netIncome, 0, ',', '.') }}</div>
            </div>
        </div>
        
        <div class="honor-list">
            <div class="honor-row">
                <span>Pelatih ({{ $accountingSetting->pelatih_percent }}%)</span>
                <span>Rp {{ number_format($honorData['pelatih'], 0, ',', '.') }}</span>
            </div>
            <div class="honor-row">
                <span>Admin ({{ $accountingSetting->admin_percent }}%)</span>
                <span>Rp {{ number_format($honorData['admin'], 0, ',', '.') }}</span>
            </div>
            <div class="honor-row">
                <span>Manajemen Keuangan ({{ $accountingSetting->manajemen_keuangan_percent }}%)</span>
                <span>Rp {{ number_format($honorData['manajemen_keuangan'], 0, ',', '.') }}</span>
            </div>
            <div class="honor-row">
                <span>Manajemen Sapras ({{ $accountingSetting->manajemen_sapras_percent }}%)</span>
                <span>Rp {{ number_format($honorData['manajemen_sapras'], 0, ',', '.') }}</span>
            </div>
            <div class="honor-row">
                <span>Koreografi ({{ $accountingSetting->koreo_default_percent }}%)</span>
                <span>Rp {{ number_format($honorData['koreografi'], 0, ',', '.') }}</span>
            </div>
            <div class="honor-row">
                <span>Transport ({{ $accountingSetting->max_pertemuan }} x Rp {{ number_format($accountingSetting->transport_nominal, 0, ',', '.') }})</span>
                <span>Rp {{ number_format($honorData['transport'], 0, ',', '.') }}</span>
            </div>
            <div class="honor-row total">
                <span><strong>Total Honor</strong></span>
                <span><strong>Rp {{ number_format($honorData['total_honor'], 0, ',', '.') }}</strong></span>
            </div>
            <div class="honor-row sisa">
                <span><strong>Sisa untuk Sanggar</strong></span>
                <span><strong class="text-success">Rp {{ number_format($honorData['sisa_sanggar'], 0, ',', '.') }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Detail Honor Pelatih -->
    @if($detailHonorPelatih->count() > 0)
    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-music"></i>
                <h5>Detail Honor Pelatih (Koreografi)</h5>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pelatih</th>
                        <th>Lagu</th>
                        <th>Persentase</th>
                        <th>Honor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailHonorPelatih as $h)
                    <tr>
                        <td>{{ $h->nama_lengkap ?? 'User ' . $h->pelatih_id }}</td
                        <td>{{ $h->judul_lagu ?? '-' }}</td
                        <td>{{ number_format($h->percent_koreo, 2) }}%</td
                        <td class="text-success">Rp {{ number_format($h->honor, 0, ',', '.') }}</td
                    </tr
                    @endforeach
                </tbody>
            </table
        </div>
    </div>
    @endif
    @endif

    <!-- ====================================================== -->
    <!-- VIEW PER SISWA -->
    <!-- ====================================================== -->
    @elseif($view == 'per_siswa')
    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-users"></i>
                <h5>Rekap Keuangan per Siswa - {{ date('F Y', strtotime($bulan . '-01')) }}</h5>
            </div>
            <span class="total-badge">Total: {{ count($rekapPerSiswa) }} Siswa</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kode Barcode</th>
                        <th>SPP</th>
                        <th>Tabungan Masuk</th>
                        <th>Tabungan Keluar</th>
                        <th>Saldo Tabungan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapPerSiswa as $no => $s)
                    <tr>
                        <td>{{ $no + 1 }}</td
                        <td>{{ $s['nama_lengkap'] }}</td
                        <td><code>{{ $s['kode_barcode'] }}</code></td
                        <td class="text-success">Rp {{ number_format($s['total_spp'], 0, ',', '.') }}</td
                        <td class="text-success">Rp {{ number_format($s['total_tabungan'], 0, ',', '.') }}</td
                        <td class="text-danger">Rp {{ number_format($s['total_tarik'], 0, ',', '.') }}</td
                        <td class="fw-bold">Rp {{ number_format($s['saldo_tabungan'], 0, ',', '.') }}</td
                    </tr
                    @endforeach
                </tbody>
            </table
        </div>
    </div>

    <!-- ====================================================== -->
    <!-- VIEW PER PELATIH -->
    <!-- ====================================================== -->
    @elseif($view == 'per_pelatih')
    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-chalkboard-user"></i>
                <h5>Rekap Honor per Pelatih - {{ date('F Y', strtotime($bulan . '-01')) }}</h5>
            </div>
            <span class="total-badge">Total: {{ count($rekapPerPelatih) }} Pelatih</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pelatih</th>
                        <th>Kode Barcode</th>
                        <th>Total Koreografi</th>
                        <th>Total Persen</th>
                        <th>Honor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapPerPelatih as $no => $p)
                    <tr>
                        <td>{{ $no + 1 }}</td
                        <td>{{ $p['nama_lengkap'] }}</td
                        <td><code>{{ $p['kode_barcode'] }}</code></td
                        <td>{{ $p['total_koreografi'] }}</td
                        <td>{{ number_format($p['total_persen'], 2) }}%</td
                        <td class="text-success">Rp {{ number_format($p['honor'], 0, ',', '.') }}</td
                    </tr
                    @endforeach
                </tbody>
            </table
        </div>
    </div>
    @endif

</div>

<style>
    .btn-print {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .alert-warning-custom {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 12px 16px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #92400e;
    }
    .alert-warning-custom a {
        color: #d97706;
        text-decoration: underline;
    }
    
    .filter-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .filter-form {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .filter-group {
        flex: 1;
        min-width: 160px;
    }
    .filter-group label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 6px;
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
    }
    .btn-filter {
        background: #0f3b2c;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 22px;
    }
    
    .stats-row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .stat-card {
        flex: 1;
        min-width: 180px;
        background: white;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .stat-icon {
        font-size: 28px;
        color: #1a5d45;
        margin-bottom: 10px;
    }
    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #0f3b2c;
    }
    .stat-label {
        font-size: 12px;
        color: #64748b;
        margin-top: 4px;
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
    
    .badge-success { background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
    .badge-danger { background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
    .text-success { color: #16a34a; }
    .text-danger { color: #dc2626; }
    .fw-bold { font-weight: 700; }
    
    .form-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .form-header {
        padding: 15px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .form-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
    }
    
    .honor-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        padding: 20px;
        background: #f8fafc;
    }
    .honor-item {
        text-align: center;
        padding: 12px;
        background: white;
        border-radius: 12px;
    }
    .honor-item.total {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
    }
    .honor-item.total .honor-value {
        color: white;
    }
    .honor-label {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
    }
    .honor-value {
        font-size: 18px;
        font-weight: 700;
        color: #0f3b2c;
    }
    
    .honor-list {
        padding: 20px;
    }
    .honor-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .honor-row.total {
        border-top: 2px solid #0f3b2c;
        border-bottom: none;
        padding-top: 15px;
        margin-top: 5px;
        font-size: 16px;
    }
    .honor-row.sisa {
        background: #dcfce7;
        margin-top: 10px;
        padding: 12px;
        border-radius: 8px;
        border: none;
    }
    
    @media print {
        .filter-card, .btn-print, .sidebar, .kinanti-header, .main-footer {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .table-card, .form-card, .stats-row {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
    
    @media (max-width: 768px) {
        .filter-form { flex-direction: column; }
        .stats-row { flex-direction: column; }
        .honor-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection