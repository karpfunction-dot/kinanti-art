@extends('layouts.admin_layout')

@section('title', 'Payroll - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-file-invoice-dollar fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Preview Payroll</h1>
                    <p class="text-muted small mb-0 mt-1">Periode: {{ date('F Y', strtotime($bulan . '-01')) }}</p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="stats-row mb-4">
                <div class="stat-card">
                    <div class="stat-value">Rp {{ number_format($omset, 0, ',', '.') }}</div>
                    <div class="stat-label">Omset</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp {{ number_format($operasional, 0, ',', '.') }}</div>
                    <div class="stat-label">Operasional</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value text-success">Rp {{ number_format($netIncome, 0, ',', '.') }}</div>
                    <div class="stat-label">Net Income</div>
                </div>
            </div>

            <!-- Honor Breakdown -->
            <div class="form-card mb-4">
                <div class="form-header">
                    <h5><i class="fa fa-chart-pie"></i> Rincian Honor</h5>
                </div>
                <div class="honor-list">
                    <div class="honor-item">
                        <div class="honor-label">
                            <i class="fa fa-chalkboard-user"></i>
                            <span>Pelatih ({{ $setting->pelatih_percent }}%)</span>
                        </div>
                        <div class="honor-value">Rp {{ number_format($honor['pelatih'], 0, ',', '.') }}</div>
                    </div>
                    <div class="honor-item">
                        <div class="honor-label">
                            <i class="fa fa-user-tie"></i>
                            <span>Admin ({{ $setting->admin_percent }}%)</span>
                        </div>
                        <div class="honor-value">Rp {{ number_format($honor['admin'], 0, ',', '.') }}</div>
                    </div>
                    <div class="honor-item">
                        <div class="honor-label">
                            <i class="fa fa-chart-line"></i>
                            <span>Manajemen Keuangan ({{ $setting->manajemen_keuangan_percent }}%)</span>
                        </div>
                        <div class="honor-value">Rp {{ number_format($honor['manajemen_keuangan'], 0, ',', '.') }}</div>
                    </div>
                    <div class="honor-item">
                        <div class="honor-label">
                            <i class="fa fa-tools"></i>
                            <span>Manajemen Sapras ({{ $setting->manajemen_sapras_percent }}%)</span>
                        </div>
                        <div class="honor-value">Rp {{ number_format($honor['manajemen_sapras'], 0, ',', '.') }}</div>
                    </div>
                    <div class="honor-item">
                        <div class="honor-label">
                            <i class="fa fa-music"></i>
                            <span>Koreografi ({{ $setting->koreo_default_percent }}%)</span>
                        </div>
                        <div class="honor-value">Rp {{ number_format($honor['koreografi'], 0, ',', '.') }}</div>
                    </div>
                    <div class="honor-item">
                        <div class="honor-label">
                            <i class="fa fa-truck"></i>
                            <span>Transport ({{ $setting->max_pertemuan }} pertemuan x Rp {{ number_format($setting->transport_nominal, 0, ',', '.') }})</span>
                        </div>
                        <div class="honor-value">Rp {{ number_format($transport, 0, ',', '.') }}</div>
                    </div>
                </div>
                
                <div class="honor-total">
                    <div class="total-label">Total Honor</div>
                    <div class="total-value">Rp {{ number_format($totalHonor, 0, ',', '.') }}</div>
                </div>
                
                <div class="honor-sisa">
                    <div class="sisa-label">Sisa untuk Sanggar</div>
                    <div class="sisa-value {{ $sisa >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($sisa, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Koreografi Details -->
            @if($koreografi->count() > 0)
            <div class="table-card">
                <div class="table-header">
                    <div class="header-left">
                        <i class="fa fa-music"></i>
                        <h5>Detail Koreografi</h5>
                    </div>
                    <span class="total-badge">Total: {{ $koreografi->count() }} Koreografi</span>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Lagu</th>
                                <th>Pelatih</th>
                                <th>Persentase</th>
                                <th>Honor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($koreografi as $k)
                            <tr>
                                <td>{{ $k->judul_lagu ?? '-' }}</td
                                <td>{{ $k->nama_lengkap ?? '-' }}</td
                                <td>{{ number_format($k->percent_koreo, 2) }}%</td
                                <td>Rp {{ number_format($netIncome * ($k->percent_koreo / 100), 0, ',', '.') }}</td
                            </tr
                            @endforeach
                        </tbody>
                    </table
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="action-buttons">
                <a href="{{ route('accounting.setting', ['bulan' => $bulan]) }}" class="btn-back">
                    <i class="fa fa-arrow-left"></i> Kembali ke Setting
                </a>
                <button type="button" class="btn-save-payroll" onclick="window.print()">
                    <i class="fa fa-print"></i> Cetak Payroll
                </button>
            </div>
            
        </div>
    </div>
</div>

<style>
    .stats-row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .stat-card {
        flex: 1;
        background: white;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #0f3b2c;
    }
    .stat-label {
        font-size: 12px;
        color: #64748b;
        margin-top: 4px;
    }
    .text-success { color: #16a34a; }
    .text-danger { color: #dc2626; }
    
    .form-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
    }
    .form-header {
        padding: 18px 24px;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .form-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    
    .honor-list {
        padding: 20px 24px;
    }
    .honor-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .honor-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 500;
        color: #334155;
    }
    .honor-label i {
        width: 24px;
        color: #1a5d45;
    }
    .honor-value {
        font-size: 14px;
        font-weight: 600;
        color: #0f3b2c;
    }
    
    .honor-total, .honor-sisa {
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #e2e8f0;
    }
    .total-label, .sisa-label {
        font-size: 16px;
        font-weight: 700;
        color: #0f3b2c;
    }
    .total-value {
        font-size: 20px;
        font-weight: 700;
        color: #1a5d45;
    }
    .sisa-value {
        font-size: 20px;
        font-weight: 700;
    }
    
    .table-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-top: 24px;
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
    
    .action-buttons {
        display: flex;
        gap: 16px;
        justify-content: center;
        margin-top: 24px;
    }
    .btn-back {
        background: #e2e8f0;
        color: #334155;
        padding: 12px 28px;
        border-radius: 40px;
        text-decoration: none;
        font-weight: 600;
    }
    .btn-save-payroll {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
    }
    
    @media (max-width: 768px) {
        .stats-row { flex-direction: column; }
        .action-buttons { flex-direction: column; }
        .btn-back, .btn-save-payroll { text-align: center; }
    }
</style>
@endsection