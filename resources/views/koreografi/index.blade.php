@extends('layouts.admin_layout')

@section('title', 'Koreografi - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-music fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Koreografi</h1>
                    <p class="text-muted small mb-0 mt-1">Manajemen koreografi untuk payroll sanggar</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 12px;">
                <i class="fa fa-arrow-left me-2"></i> Kembali
            </a>
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

    <!-- Filter Periode -->
    <div class="filter-card">
        <form method="GET" action="{{ route('koreografi.index') }}" class="filter-form">
            <div class="filter-group">
                <label>Lihat Koreografi Bulan</label>
                <select name="bulan" class="form-control">
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

    <!-- Form Tambah/Edit -->
    <div class="form-card mb-4">
        <div class="form-header">
            <h5><i class="fa {{ $editing ? 'fa-pencil' : 'fa-plus' }}"></i> {{ $editing ? 'Edit Koreografi' : 'Tambah Koreografi' }}</h5>
        </div>
        <form method="POST" action="{{ $editing ? route('koreografi.update', $editing->id_koreografi) : route('koreografi.store') }}">
            @csrf
            @if($editing)
                @method('PUT')
            @endif
            <input type="hidden" name="tahun_bulan" value="{{ $bulan }}">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Lagu <span class="required">*</span></label>
                    <select name="id_lagu" class="form-control" required>
                        <option value="">-- Pilih Lagu --</option>
                        @foreach($laguList as $l)
                            <option value="{{ $l->id_lagu }}" {{ $editing && $editing->id_lagu == $l->id_lagu ? 'selected' : '' }}>
                                {{ $l->judul_lagu }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Pelatih <span class="required">*</span></label>
                    <select name="id_pelatih" class="form-control" required>
                        <option value="">-- Pilih Pelatih --</option>
                        @foreach($pelatihList as $p)
                            <option value="{{ $p->id_user }}" {{ $editing && $editing->id_pelatih == $p->id_user ? 'selected' : '' }}>
                                {{ $p->nama_lengkap ?? $p->kode_barcode }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Persentase Koreo (%) <span class="required">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" name="percent_koreo" class="form-control" value="{{ $editing ? $editing->percent_koreo : '2.5' }}" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-save">
                    <i class="fa fa-save"></i> {{ $editing ? 'Update' : 'Simpan' }}
                </button>
                @if($editing)
                    <a href="{{ route('koreografi.index', ['bulan' => $bulan]) }}" class="btn-cancel">Batal</a>
                @endif
            </div>
        </form>
    </div>

    <!-- KOREOGRAFI BULAN BERJALAN -->
    <div class="table-card mb-4">
        <div class="table-header" style="background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%); color: white;">
            <div class="header-left">
                <i class="fa fa-calendar-check"></i>
                <h5 style="color: white;">📅 Koreografi Bulan {{ date('F Y', strtotime($bulan . '-01')) }}</h5>
            </div>
            <span class="total-badge" style="background: rgba(255,255,255,0.2); color: white;">
                <i class="fa fa-database me-1"></i> Total: {{ $koreografiBulanIni->count() }} Data
            </span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Lagu</th>
                        <th>Pelatih</th>
                        <th style="width: 120px;" class="text-center">Persentase</th>
                        <th style="width: 100px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($koreografiBulanIni as $k)
                    <tr>
                        <td>{{ $k->id_koreografi }}</td>
                        <td class="fw-semibold">
                            <i class="fa fa-music text-success me-2"></i>
                            {{ $k->judul_lagu ?? 'Lagu ID: '.$k->id_lagu }}
                        </td>
                        <td>
                            <div class="pelatih-info">
                                <strong>{{ $k->nama_pelatih ?? 'User '.$k->id_pelatih }}</strong>
                                @if($k->kode_barcode)
                                    <br><small class="text-muted">{{ $k->kode_barcode }}</small>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">
                                <i class="fa fa-percent me-1"></i>
                                {{ number_format($k->percent_koreo, 2) }}%
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="{{ route('koreografi.index', ['bulan' => $bulan, 'edit' => $k->id_koreografi]) }}" class="btn-edit" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('koreografi.destroy', $k->id_koreografi) }}" style="display:inline;" onsubmit="return confirm('Hapus koreografi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                                    <button type="submit" class="btn-delete" title="Hapus">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="empty-state">
                                <i class="fa fa-music fa-3x text-muted mb-3"></i>
                                <p>Belum ada data koreografi untuk bulan <strong>{{ date('F Y', strtotime($bulan . '-01')) }}</strong></p>
                                <small class="text-muted">Silakan tambah koreografi baru menggunakan form di atas</small>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- HISTORI / RIWAYAT KOREOGRAFI SEMUA BULAN -->
    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-history"></i>
                <h5>📜 Riwayat Koreografi (Semua Bulan)</h5>
            </div>
            <span class="total-badge">
                <i class="fa fa-database me-1"></i> Total Arsip: {{ $riwayatKoreografi->count() }} Data
            </span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 100px;">Periode</th>
                        <th>Lagu</th>
                        <th>Pelatih</th>
                        <th style="width: 120px;" class="text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatKoreografi as $k)
                    <tr>
                        <td>{{ $k->id_koreografi }}</td
                        <td>
                            <span class="badge badge-period">
                                <i class="fa fa-calendar me-1"></i>
                                {{ date('M Y', strtotime($k->tahun_bulan . '-01')) }}
                            </span>
                        </td
                        <td class="fw-semibold">
                            <i class="fa fa-music text-success me-2"></i>
                            {{ $k->judul_lagu ?? 'Lagu ID: '.$k->id_lagu }}
                        </td
                        <td>
                            <div class="pelatih-info">
                                <strong>{{ $k->nama_pelatih ?? 'User '.$k->id_pelatih }}</strong>
                                @if($k->kode_barcode)
                                    <br><small class="text-muted">{{ $k->kode_barcode }}</small>
                                @endif
                            </div>
                        </td
                        <td class="text-center">
                            <span class="badge badge-info">
                                <i class="fa fa-percent me-1"></i>
                                {{ number_format($k->percent_koreo, 2) }}%
                            </span>
                        </td
                    </tr
                    @empty
                    <tr
                        <td colspan="5" class="text-center">
                            <div class="empty-state">
                                <i class="fa fa-archive fa-3x text-muted mb-3"></i>
                                <p>Belum ada riwayat koreografi</p>
                                <small class="text-muted">Koreografi yang sudah dibuat akan muncul di sini sebagai arsip</small>
                            </div>
                        </td
                    </tr
                    @endforelse
                </tbody>
            </table
        </div>
    </div>
</div>

<style>
    .filter-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 20px;
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
    }
    
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
    .form-header h5 { margin: 0; font-size: 15px; font-weight: 600; }
    .form-row {
        padding: 20px;
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
    .required { color: #dc2626; }
    .form-actions {
        padding: 16px 20px;
        background: #f8fafc;
        display: flex;
        gap: 10px;
    }
    .btn-save {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 8px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-cancel {
        background: #e2e8f0;
        color: #334155;
        border: none;
        padding: 8px 20px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
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
    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        display: inline-block;
    }
    .badge-info { background: #cffafe; color: #155e75; }
    .badge-period { background: #e0e7ff; color: #1e40af; }
    .action-buttons { display: flex; gap: 8px; justify-content: center; }
    .btn-edit, .btn-delete {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }
    .btn-edit { background: #dbeafe; color: #1e40af; }
    .btn-delete { background: #fee2e2; color: #991b1b; }
    .text-center { text-align: center; }
    .fw-semibold { font-weight: 600; }
    .pelatih-info strong { color: #0f3b2c; }
    
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #64748b;
    }
    
    @media (max-width: 768px) {
        .form-row { flex-direction: column; gap: 10px; }
        .filter-form { flex-direction: column; }
        .data-table th, .data-table td { padding: 8px 10px; font-size: 12px; }
        .action-buttons { gap: 4px; }
        .btn-edit, .btn-delete { width: 28px; height: 28px; }
    }
</style>
@endsection