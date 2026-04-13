@extends('layouts.admin_layout')

@section('title', 'Naik Kelas - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-arrow-up fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Naik Kelas / Mutasi</h1>
                    <p class="text-muted small mb-0 mt-1">Pindahkan siswa ke kelas yang lebih tinggi</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('kelas.index') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 12px;">
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

    <!-- Filter Kelas Asal -->
    <div class="filter-card">
        <form method="GET" action="{{ route('kelas.naik') }}" class="filter-form">
            <div class="filter-group">
                <label>Kelas Asal</label>
                <select name="kelas_asal" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Pilih Kelas Asal --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id_kelas }}" {{ $kelasAsal == $k->id_kelas ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($kelasAsal > 0 && $siswaList->count() > 0)
        <form method="POST" action="{{ route('kelas.naik.proses') }}" onsubmit="return confirm('Naikkan siswa yang dipilih?')">
            @csrf
            <input type="hidden" name="kelas_asal" value="{{ $kelasAsal }}">
            
            <!-- Kelas Tujuan -->
            <div class="form-card mb-4">
                <div class="form-header">
                    <h5><i class="fa fa-arrow-right"></i> Pilih Kelas Tujuan</h5>
                </div>
                <div class="form-row">
                    <div class="form-group flex-grow-1">
                        <select name="kelas_tujuan" id="kelas_tujuan" class="form-control" required>
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            @foreach($kelasTujuanList as $k)
                                <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Daftar Siswa -->
            <div class="table-card">
                <div class="table-header">
                    <div class="header-left">
                        <i class="fa fa-users"></i>
                        <h5>Daftar Siswa</h5>
                    </div>
                    <div class="header-actions">
                        <label class="checkbox-label">
                            <input type="checkbox" id="checkAll" onclick="toggleAll(this)">
                            <span>Pilih Semua</span>
                        </label>
                        <span class="total-badge">Total: {{ $siswaList->count() }} Siswa</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" id="checkAllHeader" onclick="toggleAll(this)">
                                </th>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Tanggal Gabung</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($siswaList as $no => $s)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="id_siswa[]" value="{{ $s->id_user }}" class="siswa-checkbox">
                                </td>
                                <td>{{ $no + 1 }}</td>
                                <td class="fw-semibold">{{ $s->nama_lengkap }}</td>
                                <td>{{ \Carbon\Carbon::parse($s->tanggal_gabung)->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <button type="submit" class="btn-submit">
                        <i class="fa fa-arrow-up"></i> Naikkan Kelas
                    </button>
                </div>
            </div>
        </form>
    @elseif($kelasAsal > 0)
        <div class="alert-info text-center">
            <i class="fa fa-info-circle fa-2x mb-2"></i>
            <p>Tidak ada siswa di kelas ini</p>
        </div>
    @else
        <div class="alert-info text-center">
            <i class="fa fa-info-circle fa-2x mb-2"></i>
            <p>Silakan pilih kelas asal terlebih dahulu</p>
        </div>
    @endif
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
    .alert-info { background: #e0f2fe; border-left: 4px solid #0284c7; color: #0c4a6e; padding: 30px; border-radius: 20px; margin-top: 20px; }
    
    .filter-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .filter-form { max-width: 300px; }
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
        border-radius: 10px;
        font-size: 14px;
    }
    
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
    .form-row { padding: 20px; }
    .flex-grow-1 { flex: 1; }
    
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
    .header-actions { display: flex; align-items: center; gap: 15px; }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        cursor: pointer;
    }
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
    .table-footer {
        padding: 16px 20px;
        background: #f8fafc;
        text-align: right;
    }
    .btn-submit {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: white;
        border: none;
        padding: 10px 28px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-submit:hover { opacity: 0.9; }
    .text-center { text-align: center; }
    .fw-semibold { font-weight: 600; }
</style>

<script>
    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('.siswa-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
    }
</script>
@endsection