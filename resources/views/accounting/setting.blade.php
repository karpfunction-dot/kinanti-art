@extends('layouts.admin_layout')

@section('title', 'Accounting Setting - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-calculator fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Accounting Setting</h1>
                    <p class="text-muted small mb-0 mt-1">Pengaturan honor pelatih, koreografi, dan omset per bulan</p>
                </div>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert-success-custom">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert-error-custom">
                    <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            <!-- Filter Bulan -->
            <div class="filter-card mb-4">
                <form method="GET" action="{{ route('accounting.setting') }}" class="filter-form">
                    <div class="filter-group">
                        <label>Periode Bulan</label>
                        <select name="bulan" class="form-control" onchange="this.form.submit()">
                            @foreach($availableMonths as $m)
                                <option value="{{ $m->tahun_bulan }}" {{ $bulan == $m->tahun_bulan ? 'selected' : '' }}>
                                    {{ date('F Y', strtotime($m->tahun_bulan . '-01')) }}
                                </option>
                            @endforeach
                            <option value="{{ date('Y-m') }}" {{ $bulan == date('Y-m') ? 'selected' : '' }}>
                                {{ date('F Y') }} (Bulan Ini)
                            </option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-filter">Tampilkan</button>
                    </div>
                </form>
            </div>

            <!-- Info SPP -->
            <div class="info-card mb-4">
                <div class="info-icon">
                    <i class="fa fa-money-bill-wave"></i>
                </div>
                <div class="info-content">
                    <strong>SPP Real-time:</strong> Rp {{ number_format($omsetSpp, 0, ',', '.') }}
                    <span class="separator">|</span>
                    <strong>Piutang:</strong> Rp {{ number_format($piutang, 0, ',', '.') }}
                </div>
            </div>

            <!-- Form Setting -->
            <div class="form-card">
                <div class="form-header">
                    <h5><i class="fa fa-sliders-h"></i> Pengaturan Accounting</h5>
                    <p class="text-muted small mb-0">Periode: {{ date('F Y', strtotime($bulan . '-01')) }}</p>
                </div>
                
                <form method="POST" action="{{ route('accounting.setting.save') }}">
                    @csrf
                    <input type="hidden" name="tahun_bulan" value="{{ $bulan }}">
                    
                    <div class="form-section">
                        <h3><i class="fa fa-chart-line"></i> Omset & Operasional</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Omset Manual <span class="required">*</span></label>
                                <div class="input-icon">
                                    <i class="fa fa-rupiah-sign"></i>
                                    <input type="number" step="0.01" name="omset_manual" class="form-control" 
                                           value="{{ $settingData['omset_manual'] }}" required>
                                </div>
                                <small class="form-text">Pendapatan kotor bulan ini (wajib diisi)</small>
                            </div>
                            <div class="form-group">
                                <label>Operasional Manual</label>
                                <div class="input-icon">
                                    <i class="fa fa-building"></i>
                                    <input type="number" step="0.01" name="operasional_manual" class="form-control" 
                                           value="{{ $settingData['operasional_manual'] }}">
                                </div>
                                <small class="form-text">Biaya operasional bulan ini</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fa fa-percent"></i> Persentase Honor</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Pelatih (%)</label>
                                <input type="number" step="0.1" name="pelatih_percent" class="form-control" 
                                       value="{{ $settingData['pelatih_percent'] }}">
                            </div>
                            <div class="form-group">
                                <label>Admin (%)</label>
                                <input type="number" step="0.1" name="admin_percent" class="form-control" 
                                       value="{{ $settingData['admin_percent'] }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Manajemen - Keuangan (%)</label>
                                <input type="number" step="0.1" name="manajemen_keuangan_percent" class="form-control" 
                                       value="{{ $settingData['manajemen_keuangan_percent'] }}">
                            </div>
                            <div class="form-group">
                                <label>Manajemen - Sapras (%)</label>
                                <input type="number" step="0.1" name="manajemen_sapras_percent" class="form-control" 
                                       value="{{ $settingData['manajemen_sapras_percent'] }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fa fa-music"></i> Koreografi</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Persen Koreo Default (%)</label>
                                <input type="number" step="0.1" name="koreo_default_percent" class="form-control" 
                                       value="{{ $settingData['koreo_default_percent'] }}">
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <a href="{{ route('koreografi.index', ['bulan' => $bulan]) }}" class="btn-link">
                                    <i class="fa fa-external-link-alt"></i> Buka Koreografi
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fa fa-truck"></i> Transport & Pertemuan</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nominal Transport per Hadir</label>
                                <div class="input-icon">
                                    <i class="fa fa-rupiah-sign"></i>
                                    <input type="number" step="0.01" name="transport_nominal" class="form-control" 
                                           value="{{ $settingData['transport_nominal'] }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Maksimal Pertemuan (per bulan)</label>
                                <input type="number" name="max_pertemuan" class="form-control" 
                                       value="{{ $settingData['max_pertemuan'] }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-save">
                            <i class="fa fa-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Preview Payroll Link -->
            <div class="preview-card">
                <div class="preview-icon">
                    <i class="fa fa-file-invoice-dollar"></i>
                </div>
                <div class="preview-content">
                    <h3>Preview Payroll</h3>
                    <p>Setelah menyimpan setting, buka halaman payroll untuk melihat preview dan menyimpan payroll.</p>
                    <a href="{{ route('accounting.payroll', ['bulan' => $bulan]) }}" class="btn-preview">
                        <i class="fa fa-eye"></i> Lihat Payroll
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
    .alert-success-custom, .alert-error-custom {
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .alert-success-custom { background: #dcfce7; border-left: 4px solid #16a34a; color: #166534; }
    .alert-error-custom { background: #fee2e2; border-left: 4px solid #dc2626; color: #991b1b; }
    
    .filter-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .filter-form {
        display: flex;
        gap: 15px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    .filter-group label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 6px;
    }
    .btn-filter {
        background: #0f3b2c;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .info-card {
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        border-radius: 16px;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        border-left: 4px solid #0284c7;
    }
    .info-icon {
        width: 40px;
        height: 40px;
        background: #0284c7;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }
    .info-content {
        flex: 1;
        color: #0c4a6e;
        font-size: 14px;
    }
    .separator {
        margin: 0 10px;
        opacity: 0.5;
    }
    
    .form-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
    }
    .form-header {
        padding: 18px 24px;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .form-header h5 {
        margin: 0 0 4px;
        font-size: 16px;
        font-weight: 600;
    }
    .form-section {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
    }
    .form-section h3 {
        font-size: 15px;
        font-weight: 600;
        color: #0f3b2c;
        margin-bottom: 16px;
    }
    .form-section h3 i {
        margin-right: 8px;
        color: #1a5d45;
    }
    .form-row {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .form-group {
        flex: 1;
        min-width: 180px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #334155;
    }
    .input-icon {
        position: relative;
    }
    .input-icon i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }
    .input-icon .form-control {
        padding-left: 35px;
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
    }
    .form-text {
        font-size: 11px;
        color: #64748b;
        margin-top: 4px;
        display: block;
    }
    .required { color: #dc2626; }
    .btn-link {
        display: inline-block;
        margin-top: 28px;
        color: #1a5d45;
        text-decoration: none;
    }
    .btn-link:hover {
        text-decoration: underline;
    }
    .form-actions {
        padding: 20px 24px;
        background: #f8fafc;
        text-align: right;
    }
    .btn-save {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 93, 69, 0.3);
    }
    
    .preview-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .preview-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
    }
    .preview-content {
        flex: 1;
    }
    .preview-content h3 {
        margin: 0 0 4px;
        font-size: 16px;
        font-weight: 600;
        color: #0f3b2c;
    }
    .preview-content p {
        margin: 0 0 12px;
        font-size: 13px;
        color: #64748b;
    }
    .btn-preview {
        background: #e2e8f0;
        color: #334155;
        padding: 8px 20px;
        border-radius: 30px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-preview:hover {
        background: #cbd5e1;
    }
    
    @media (max-width: 768px) {
        .form-section {
            padding: 16px 20px;
        }
        .form-row {
            flex-direction: column;
            gap: 12px;
        }
        .preview-card {
            flex-direction: column;
            text-align: center;
        }
    }
</style>
@endsection