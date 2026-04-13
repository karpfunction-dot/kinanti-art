@extends('layouts.admin_layout')

@section('title', 'Manajemen Role - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-shield-alt fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Manajemen Role</h1>
                    <p class="text-muted small mb-0 mt-1">Kelola role / hak akses pengguna sistem</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn-tambah" onclick="openRoleModal()">
                <i class="fa fa-plus me-2"></i> Tambah Role
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Tabel Role -->
    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-table-list"></i>
                <h5>Daftar Role</h5>
            </div>
            <span class="total-badge">Total: {{ $roles->count() }} Role</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Nama Role</th>
                        <th>Deskripsi</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <th style="width: 130px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td class="text-center">{{ $role->id_role }}</td>
                        <td class="fw-semibold">
                            <i class="fa fa-tag text-success me-2"></i>
                            {{ ucfirst($role->nama_role) }}
                        </td>
                        <td>{{ $role->deskripsi ?? '-' }}</td>
                        <td class="text-center">
                            @if($role->aktif)
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="editRole({{ $role->id_role }})" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                @if($role->aktif)
                                    <button class="btn-delete" onclick="deactivateRole({{ $role->id_role }}, '{{ $role->nama_role }}')" title="Nonaktifkan">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                @else
                                    <button class="btn-activate" onclick="activateRole({{ $role->id_role }}, '{{ $role->nama_role }}')" title="Aktifkan">
                                        <i class="fa fa-check-circle"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data role</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Role -->
<div id="roleModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalRoleTitle"><i class="fa fa-plus"></i> Tambah Role</h3>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <form id="roleForm">
            @csrf
            <input type="hidden" name="id_role" id="editRoleId">
            <div class="form-group">
                <label>Nama Role <span class="required">*</span></label>
                <input type="text" name="nama_role" id="nama_role" class="form-control" placeholder="Contoh: pelatih, manajemen, siswa" required>
                <small class="form-text">Gunakan huruf kecil, tanpa spasi</small>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" placeholder="Deskripsi role..."></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="aktif" id="aktif" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<style>
    .btn-tambah {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .btn-tambah:hover {
        transform: translateY(-2px);
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
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table th, .data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    .data-table th {
        background: #f8fafc;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
    }
    .data-table tbody tr:hover {
        background: #f8fafc;
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        display: inline-block;
    }
    .status-active { background: #dcfce7; color: #166534; }
    .status-inactive { background: #fee2e2; color: #991b1b; }
    .action-buttons { display: flex; gap: 8px; justify-content: center; }
    .btn-edit, .btn-delete, .btn-activate {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .btn-edit { background: #dbeafe; color: #1e40af; }
    .btn-delete { background: #fee2e2; color: #991b1b; }
    .btn-activate { background: #dcfce7; color: #166534; }
    .btn-edit:hover, .btn-delete:hover, .btn-activate:hover {
        transform: scale(1.05);
    }
    
    /* Modal */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal-content {
        background: white;
        border-radius: 20px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        animation: fadeIn 0.2s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .modal-header {
        padding: 18px 20px;
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
    }
    .modal-header h3 { margin: 0; font-size: 16px; font-weight: 600; }
    .modal-close { font-size: 24px; cursor: pointer; opacity: 0.8; }
    .modal-close:hover { opacity: 1; }
    .form-group { padding: 0 20px; margin-bottom: 16px; }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #334155;
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #1a5d45;
        box-shadow: 0 0 0 3px rgba(26, 93, 69, 0.1);
    }
    textarea.form-control { resize: vertical; }
    .form-text {
        font-size: 11px;
        color: #64748b;
        margin-top: 4px;
        display: block;
    }
    .modal-footer {
        padding: 16px 20px;
        background: #f8fafc;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        position: sticky;
        bottom: 0;
    }
    .btn-save {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: opacity 0.2s;
    }
    .btn-save:hover { opacity: 0.9; }
    .btn-cancel {
        background: #e2e8f0;
        color: #334155;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: opacity 0.2s;
    }
    .btn-cancel:hover { opacity: 0.8; }
    .required { color: #dc2626; }
    .text-center { text-align: center; }
    .fw-semibold { font-weight: 600; }
    
    /* Responsive */
    @media (max-width: 768px) {
        .data-table th, .data-table td {
            padding: 10px 12px;
            font-size: 12px;
        }
        .action-buttons { gap: 4px; }
        .btn-edit, .btn-delete, .btn-activate {
            width: 28px;
            height: 28px;
        }
        .btn-edit i, .btn-delete i, .btn-activate i {
            font-size: 12px;
        }
        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<script>
    const csrfToken = '{{ csrf_token() }}';
    
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        const alertClass = type === 'success' ? 'alert-success-custom' : 'alert-error-custom';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        alertContainer.innerHTML = `
            <div class="${alertClass}">
                <i class="fa ${icon}"></i>
                <div>${message}</div>
            </div>
        `;
        setTimeout(() => { 
            alertContainer.innerHTML = ''; 
        }, 3000);
    }
    
    function openRoleModal() {
        document.getElementById('roleForm').reset();
        document.getElementById('editRoleId').value = '';
        document.getElementById('modalRoleTitle').innerHTML = '<i class="fa fa-plus"></i> Tambah Role';
        document.getElementById('roleModal').style.display = 'flex';
    }
    
    function editRole(id) {
        fetch('/settings/roles/' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const role = data.data;
                    document.getElementById('editRoleId').value = role.id_role;
                    document.getElementById('nama_role').value = role.nama_role;
                    document.getElementById('deskripsi').value = role.deskripsi || '';
                    document.getElementById('aktif').value = role.aktif;
                    document.getElementById('modalRoleTitle').innerHTML = '<i class="fa fa-pencil"></i> Edit Role';
                    document.getElementById('roleModal').style.display = 'flex';
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan', 'error');
            });
    }
    
    document.getElementById('roleForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('editRoleId').value;
        const url = id ? '/settings/roles/' + id : '/settings/roles';
        const method = id ? 'PUT' : 'POST';
        
        const formData = new FormData();
        formData.append('_method', method);
        formData.append('nama_role', document.getElementById('nama_role').value);
        formData.append('deskripsi', document.getElementById('deskripsi').value);
        formData.append('aktif', document.getElementById('aktif').value);
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Terjadi kesalahan', 'error');
        }
    });
    
    function deactivateRole(id, nama) {
        if (confirm('Nonaktifkan role "' + nama + '" ?\n\nRole yang dinonaktifkan tidak dapat digunakan untuk pengguna baru.')) {
            fetch('/settings/roles/' + id + '/deactivate', {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': csrfToken, 
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                showAlert(data.message, data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => location.reload(), 1500);
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan', 'error');
            });
        }
    }
    
    function activateRole(id, nama) {
        if (confirm('Aktifkan role "' + nama + '" kembali?')) {
            fetch('/settings/roles/' + id + '/activate', {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': csrfToken, 
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                showAlert(data.message, data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => location.reload(), 1500);
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan', 'error');
            });
        }
    }
    
    function closeModal() {
        document.getElementById('roleModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
</script>
@endsection