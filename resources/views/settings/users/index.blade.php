@extends('layouts.admin_layout')

@section('title', 'Manajemen Pengguna - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-users-gear fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Manajemen Pengguna</h1>
                    <p class="text-muted small mb-0 mt-1">Kelola data pengguna, role, dan akses sistem</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn-tambah" onclick="showAddForm()">
                <i class="fa fa-plus me-2"></i> Tambah Pengguna
            </button>
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
        <form method="GET" action="{{ route('settings.users') }}" class="filter-form">
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
                <button type="submit" class="btn-filter">Filter</button>
            </div>
        </form>
    </div>

    <!-- Form Tambah/Edit (Hidden by default) -->
    <div id="formContainer" class="form-container" style="display: none;">
        <div class="form-header">
            <h3 id="formTitle"><i class="fa fa-user-plus"></i> Tambah Pengguna</h3>
            <button type="button" class="btn-close-form" onclick="closeForm()">&times;</button>
        </div>
        <form id="userForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="id_user" id="id_user">
            <div class="form-row">
                <div class="form-group">
                    <label>Kode Barcode <span class="required">*</span></label>
                    <input type="text" name="kode_barcode" id="kode_barcode" placeholder="Contoh: MJ-001" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Nama lengkap" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email" placeholder="email@example.com">
                </div>
                <div class="form-group">
                    <label>Role <span class="required">*</span></label>
                    <select name="id_role" id="id_role" required>
                        <option value="">Pilih Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id_role }}">{{ ucfirst($r->nama_role) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Status</label>
                    <select name="aktif" id="aktif">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-save">Simpan</button>
                    <button type="button" class="btn-cancel" onclick="closeForm()">Batal</button>
                </div>
            </div>
            <div class="info-note">
                <i class="fa fa-info-circle"></i> Password sementara dibuat otomatis saat pengguna ditambahkan.
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="table-card">
        <div class="table-header">
            <h5><i class="fa fa-users"></i> Daftar Pengguna</h5>
            <span class="total-badge">Total: {{ $users->count() }} Pengguna</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pengguna</th>
                        <th>Kode Barcode</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $no => $user)
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div>
                                    <div class="user-name">{{ $user->nama_lengkap ?? '-' }}</div>
                                    <div class="user-id">ID: {{ $user->id_user }}</div>
                                </div>
                            </div>
                        </td>
                        <td><code>{{ $user->kode_barcode ?? '-' }}</code></td>
                        <td>
                            @php
                                $roleColors = [
                                    'admin' => 'danger',
                                    'pelatih' => 'primary',
                                    'siswa' => 'info',
                                    'manajemen' => 'warning',
                                ];
                                $roleColor = $roleColors[strtolower($user->nama_role ?? '')] ?? 'secondary';
                            @endphp
                            <span class="role-badge role-{{ $roleColor }}">
                                {{ ucfirst($user->nama_role ?? '-') }}
                            </span>
                        </td>
                        <td>{{ $user->email ?? '-' }}</td>
                        <td>
                            @if($user->aktif)
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="showEditForm({{ $user->id_user }})" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn-reset" onclick="resetPassword({{ $user->id_user }})" title="Reset Password">
                                    <i class="fa fa-key"></i>
                                </button>
                                <button class="btn-delete" onclick="deleteUser({{ $user->id_user }}, '{{ $user->nama_lengkap }}')" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data pengguna</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* CSS Tanpa Bootstrap */
    .btn-tambah {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-tambah:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 93, 69, 0.3);
    }
    
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
        min-width: 180px;
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
    .filter-group input:focus, .filter-group select:focus {
        outline: none;
        border-color: #1a5d45;
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
    
    .form-container {
        background: white;
        border-radius: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .form-header {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .form-header h3 {
        margin: 0;
        font-size: 16px;
    }
    .btn-close-form {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
    }
    .form-row {
        display: flex;
        gap: 15px;
        padding: 15px 20px 0 20px;
        flex-wrap: wrap;
    }
    .form-group {
        flex: 1;
        min-width: 200px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }
    .required { color: #dc2626; }
    .form-group input, .form-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
    }
    .btn-save {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-cancel {
        background: #e2e8f0;
        color: #334155;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        margin-left: 8px;
    }
    .info-note {
        padding: 12px 20px 20px 20px;
        font-size: 12px;
        color: #64748b;
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
    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .user-avatar {
        width: 35px;
        height: 35px;
        background: #dcfce7;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #166534;
    }
    .user-name {
        font-weight: 600;
        font-size: 14px;
    }
    .user-id {
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
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    .btn-edit, .btn-reset, .btn-delete {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-edit { background: #dbeafe; color: #1e40af; }
    .btn-edit:hover { background: #bfdbfe; transform: scale(1.05); }
    .btn-reset { background: #fef3c7; color: #92400e; }
    .btn-reset:hover { background: #fde68a; transform: scale(1.05); }
    .btn-delete { background: #fee2e2; color: #991b1b; }
    .btn-delete:hover { background: #fecaca; transform: scale(1.05); }
    .text-center { text-align: center; }
    code { background: #f1f5f9; padding: 2px 6px; border-radius: 6px; font-size: 12px; }
</style>

<script>
    // CSRF Token
    const csrfToken = '{{ csrf_token() }}';
    
    function showAddForm() {
        document.getElementById('formTitle').innerHTML = '<i class="fa fa-user-plus"></i> Tambah Pengguna';
        document.getElementById('userForm').action = '{{ route("settings.users.store") }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('id_user').value = '';
        document.getElementById('kode_barcode').value = '';
        document.getElementById('nama_lengkap').value = '';
        document.getElementById('email').value = '';
        document.getElementById('id_role').value = '';
        document.getElementById('aktif').value = '1';
        document.getElementById('formContainer').style.display = 'block';
        document.getElementById('formContainer').scrollIntoView({ behavior: 'smooth' });
    }
    
    function showEditForm(id) {
        document.getElementById('formTitle').innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memuat data...';
        document.getElementById('formContainer').style.display = 'block';
        
        fetch('/settings/users/' + id + '/edit', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.data;
                document.getElementById('formTitle').innerHTML = '<i class="fa fa-user-edit"></i> Edit Pengguna - ' + user.nama_lengkap;
                document.getElementById('userForm').action = '/settings/users/' + id;
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('id_user').value = user.id_user;
                document.getElementById('kode_barcode').value = user.kode_barcode;
                document.getElementById('nama_lengkap').value = user.nama_lengkap;
                document.getElementById('email').value = user.email || '';
                document.getElementById('id_role').value = user.id_role;
                document.getElementById('aktif').value = user.aktif;
                document.getElementById('formContainer').scrollIntoView({ behavior: 'smooth' });
            } else {
                alert('Gagal mengambil data: ' + data.message);
                document.getElementById('formContainer').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
            document.getElementById('formContainer').style.display = 'none';
        });
    }
    
    function closeForm() {
        document.getElementById('formContainer').style.display = 'none';
    }
    
    function resetPassword(id) {
        if (confirm('Reset password pengguna ini? Password sementara baru akan dibuat otomatis.')) {
            fetch('/settings/users/' + id + '/reset-password', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }
    }
    
    function deleteUser(id, nama) {
        if (confirm('Hapus pengguna "' + nama + '" ?\n\nTindakan ini tidak dapat dibatalkan!')) {
            fetch('/settings/users/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }
    }
</script>
@endsection
