@extends('layouts.admin_layout')

@section('title', 'Entri Anggota Kelas - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-user-plus fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Entri Anggota Kelas</h1>
                    <p class="text-muted small mb-0 mt-1">Kelola anggota di setiap kelas</p>
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

    <!-- Pilih Kelas -->
    <div class="filter-card">
        <form method="GET" action="{{ route('kelas.entri') }}" class="filter-form">
            <div class="filter-group">
                <label>Pilih Kelas</label>
                <select name="id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id_kelas }}" {{ $id_kelas == $k->id_kelas ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($id_kelas > 0)
        <!-- Info Kelas -->
        <div class="info-card">
            <div class="info-icon">
                <i class="fa fa-chalkboard"></i>
            </div>
            <div class="info-content">
                <h3>{{ $nama_kelas }}</h3>
                <p>Manajemen anggota kelas</p>
            </div>
        </div>

        <!-- Form Tambah Anggota -->
        <div class="form-card mb-4">
            <div class="form-header">
                <h5><i class="fa fa-plus-circle"></i> Tambah Anggota</h5>
            </div>
            <form method="POST" action="{{ route('kelas.entri.store') }}">
                @csrf
                <input type="hidden" name="id_kelas" value="{{ $id_kelas }}">
                <div class="form-row">
                    <div class="form-group flex-grow-1">
                        <select name="id_user" class="form-control" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswaAll as $s)
                                <option value="{{ $s->id_user }}">{{ $s->nama_lengkap }} ({{ $s->kode_barcode }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-add">
                            <i class="fa fa-plus"></i> Tambahkan
                        </button>
                    </div>
                </div>
            </form>
            @if($siswaAll->isEmpty())
                <div class="alert-info">
                    <i class="fa fa-info-circle"></i> Semua siswa sudah memiliki kelas aktif.
                </div>
            @endif
        </div>

        <!-- Daftar Anggota Kelas -->
        <div class="table-card">
            <div class="table-header">
                <div class="header-left">
                    <i class="fa fa-users"></i>
                    <h5>Daftar Anggota Kelas</h5>
                </div>
                <span class="total-badge">Total: {{ $anggotaList->count() }} Anggota</span>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Tanggal Gabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($anggotaList as $no => $a)
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td class="fw-semibold">{{ $a->nama_lengkap }}</td>
                            <td>{{ \Carbon\Carbon::parse($a->tanggal_gabung)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('kelas.entri.destroy', [$id_kelas, $a->id_user]) }}" 
                                   class="btn-delete"
                                   onclick="return confirm('Hapus {{ $a->nama_lengkap }} dari kelas ini?')">
                                    <i class="fa fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">Belum ada anggota di kelas ini</td>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert-info text-center">
            <i class="fa fa-info-circle fa-2x mb-2"></i>
            <p>Silakan pilih kelas terlebih dahulu</p>
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
    .alert-info { background: #e0f2fe; border-left: 4px solid #0284c7; color: #0c4a6e; padding: 12px 16px; border-radius: 12px; margin-top: 15px; }
    
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
    
    .info-card {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        color: white;
    }
    .info-icon {
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .info-content h3 { margin: 0 0 5px; font-size: 18px; }
    .info-content p { margin: 0; font-size: 12px; opacity: 0.8; }
    
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
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .form-group { margin-bottom: 0; }
    .flex-grow-1 { flex: 1; min-width: 200px; }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
    }
    .btn-add {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
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
    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .btn-delete:hover { background: #fecaca; }
    .text-center { text-align: center; }
    .fw-semibold { font-weight: 600; }
</style>
@endsection