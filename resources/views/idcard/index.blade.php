@extends('layouts.admin_layout')

@section('title', 'ID Card - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-id-card fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">ID Card Anggota</h1>
                    <p class="text-muted small mb-0 mt-1">Cetak kartu identitas anggota Sanggar Tari</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" action="{{ route('idcard.index') }}" class="filter-form">
            <div class="filter-group">
                <label>Cari Anggota</label>
                <input type="text" name="cari" placeholder="Cari nama atau kode barcode..." value="{{ $cari }}">
            </div>
            <div class="filter-group">
                <label>Filter Role</label>
                <select name="role">
                    <option value="">Semua Peran</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->nama_role }}" {{ $role == $r->nama_role ? 'selected' : '' }}>
                            {{ ucfirst($r->nama_role) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" class="btn-filter">Terapkan</button>
            </div>
            <div class="filter-group">
                <a href="{{ route('idcard.index') }}" class="btn-reset">Reset</a>
            </div>
            <div class="filter-group">
                <a href="{{ route('idcard.print-all') }}?cari={{ $cari }}&role={{ $role }}" target="_blank" class="btn-print-all">
                    <i class="fa fa-print"></i> Cetak Semua
                </a>
            </div>
        </form>
    </div>

    <!-- Members Table -->
    <div class="table-card">
        <div class="table-header">
            <h5><i class="fa fa-users"></i> Daftar Anggota</h5>
            <span class="total-badge">Total: {{ $members->count() }} Anggota</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-center">Foto</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Kode Barcode</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                    <tr>
                        <td class="text-center">
                            @php
                                $fotoPath = !empty($profile->foto_profil)
        ? $profile->foto_profil
        : asset('assets/img/blank-profile.webp');
                            @endphp
                            <img src="{{ $fotoPath }}" class="profile-thumb" alt="Foto">
                        </td>
                        <td class="fw-semibold">{{ $member->nama_lengkap ?? '-' }}</td>
                        <td>
                            @php
                                $roleColors = [
                                    'admin' => 'danger',
                                    'pelatih' => 'primary',
                                    'siswa' => 'info',
                                    'manajemen' => 'warning',
                                ];
                                $roleColor = $roleColors[strtolower($member->nama_role ?? '')] ?? 'secondary';
                            @endphp
                            <span class="role-badge role-{{ $roleColor }}">
                                {{ ucfirst($member->nama_role ?? '-') }}
                            </span>
                        </td>
                        <td><code>{{ $member->kode_barcode ?? '-' }}</code></td>
                        <td class="text-center">
                            <a href="{{ route('idcard.preview', $member->id_user) }}" target="_blank" class="btn-print">
                                <i class="fa fa-print"></i> Cetak ID Card
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data ditemukan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
    .filter-group input, .filter-group select {
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
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
    }
    .btn-reset {
        background: #e2e8f0;
        color: #334155;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: block;
        text-align: center;
    }
    .btn-print-all {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: block;
        text-align: center;
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
    .table-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
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
    .profile-thumb {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #1a5d45;
    }
    .role-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .role-danger { background: #fee2e2; color: #991b1b; }
    .role-primary { background: #dbeafe; color: #1e40af; }
    .role-info { background: #cffafe; color: #155e75; }
    .role-warning { background: #fef3c7; color: #92400e; }
    .role-secondary { background: #f1f5f9; color: #475569; }
    .btn-print {
        background: #2563eb;
        color: white;
        padding: 6px 12px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn-print:hover {
        background: #1d4ed8;
    }
    .text-center { text-align: center; }
    .fw-semibold { font-weight: 600; }
    code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 6px;
        font-size: 12px;
    }
</style>
@endsection
