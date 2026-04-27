@extends('layouts.admin_layout')

@section('title', 'Manajemen Profil - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-address-card fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Manajemen Profil</h1>
                    <p class="text-muted small mb-0 mt-1">Kelola data profil anggota Sanggar Tari</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert-success-custom">
            <i class="fa fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error-custom">
            <i class="fa fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" action="{{ route('profil.index') }}" class="filter-form">
            <div class="filter-group">
                <label>Cari Nama</label>
                <input type="text" name="search" placeholder="Cari berdasarkan nama..." value="{{ $search }}">
            </div>
            <div class="filter-group">
                <label>Filter Role</label>
                <select name="role">
                    <option value="">Semua Role</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id_role }}" {{ $filter_role == $r->id_role ? 'selected' : '' }}>
                            {{ ucfirst($r->nama_role) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" class="btn-filter">Tampilkan</button>
            </div>
            <div class="filter-group">
                <a href="{{ route('profil.index') }}" class="btn-reset">Reset</a>
            </div>
        </form>
    </div>

    <!-- Profiles Table -->
    <div class="table-card">
        <div class="table-header">
            <h5><i class="fa fa-users"></i> Daftar Profil Anggota</h5>
            <span class="total-badge">Total: {{ $profiles->count() }} Profil</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $profile)
                    <tr>
                        <td>
                           <img src="{{ $profile->foto_url ?? \App\Support\PhotoUrl::resolve($profile->foto_profil ?? null) }}" 
     class="profile-thumb" 
     alt="foto">
                        </td>
                        <td>
                            <div class="profile-name">{{ $profile->nama_lengkap ?? '-' }}</div>
                            <div class="profile-id">ID: {{ $profile->id_user }}</div>
                        </td>
                        <td>{{ $profile->email ?? '-' }}</td>
                        <td>
                            @php
                                $roleColors = [
                                    'admin' => 'danger',
                                    'pelatih' => 'primary',
                                    'siswa' => 'info',
                                    'manajemen' => 'warning',
                                ];
                                $roleColor = $roleColors[strtolower($profile->nama_role ?? '')] ?? 'secondary';
                            @endphp
                            <span class="role-badge role-{{ $roleColor }}">
                                {{ ucfirst($profile->nama_role ?? '-') }}
                            </span>
                        </td>
                        <td>
                            @if($profile->aktif)
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('profil.edit', $profile->id_user) }}" class="btn-edit">
                                <i class="fa fa-pencil"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data profil</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
    .alert-success-custom {
        background: #dcfce7;
        border-left: 4px solid #16a34a;
        color: #166534;
    }
    .alert-error-custom {
        background: #fee2e2;
        border-left: 4px solid #dc2626;
        color: #991b1b;
    }
    
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
        margin-top: 22px;
    }
    .btn-reset {
        background: #e2e8f0;
        color: #334155;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        margin-top: 22px;
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
        border-radius: 10px;
        object-fit: cover;
    }
    .profile-name {
        font-weight: 600;
        font-size: 14px;
    }
    .profile-id {
        font-size: 11px;
        color: #94a3b8;
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
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    .status-active { background: #dcfce7; color: #166534; }
    .status-inactive { background: #fee2e2; color: #991b1b; }
    .btn-edit {
        background: #dbeafe;
        color: #1e40af;
        padding: 6px 12px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn-edit:hover {
        background: #bfdbfe;
    }
    .text-center { text-align: center; }
</style>
@endsection
