@extends('layouts.admin_layout')

@section('title', 'Menu Manager - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-bars fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Menu Manager</h1>
                    <p class="text-muted small mb-0 mt-1">Kelola struktur menu dan akses berdasarkan role</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn-tambah" onclick="openMenuModal()">
                <i class="fa fa-plus me-2"></i> Tambah Menu
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Tabel Menu -->
    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-table-list"></i>
                <h5>Daftar Menu</h5>
            </div>
            <span class="total-badge">Total: {{ $menus->count() }} Menu</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Label</th>
                        <th>Roles</th>
                        <th>Parent</th>
                        <th>Icon</th>
                        <th>Page</th>
                        <th style="width: 80px;" class="text-center">Order</th>
                        <th style="width: 80px;" class="text-center">Status</th>
                        <th style="width: 130px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $menu)
                    <tr>
                        <td class="text-center">{{ $menu->id_menu }}</td>
                        <td class="fw-semibold">
                            @if($menu->id_parent)
                                <span class="submenu-indent">↳ </span>
                            @endif
                            <i class="fa {{ $menu->icon ?? 'fa-circle' }} text-success me-2"></i>
                            {{ $menu->label }}
                        </td>
                        <td>
                            @if($menu->role_names)
                                @foreach(explode(',', $menu->role_names) as $role)
                                    <span class="role-badge">{{ trim($role) }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $parentLabel = '-';
                                if ($menu->id_parent) {
                                    $parent = $menus->firstWhere('id_menu', $menu->id_parent);
                                    $parentLabel = $parent ? $parent->label : '-';
                                }
                            @endphp
                            {{ $parentLabel }}
                        </td>
                        <td><code>{{ $menu->icon ?? '-' }}</code></td>
                        <td><code>{{ $menu->page ?? '-' }}</code></td>
                        <td class="text-center">{{ $menu->order_index }}</td>
                        <td class="text-center">
                            @if($menu->aktif)
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="editMenu({{ $menu->id_menu }})" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn-delete" onclick="deleteMenu({{ $menu->id_menu }}, '{{ $menu->label }}')" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Belum ada data menu</td
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Menu -->
<div id="menuModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalMenuTitle"><i class="fa fa-plus"></i> Tambah Menu</h3>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <form id="menuForm">
            @csrf
            <input type="hidden" name="id_menu" id="editMenuId">
            <div class="form-row">
                <div class="form-group">
                    <label>Label <span class="required">*</span></label>
                    <input type="text" name="label" id="label" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Icon (FontAwesome)</label>
                    <input type="text" name="icon" id="icon" class="form-control" placeholder="fa-home">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Page / URL</label>
                    <input type="text" name="page" id="page" class="form-control" placeholder="dashboard / kelas/entri">
                    <small class="form-text">Path halaman sesuai route Laravel</small>
                </div>
                <div class="form-group">
                    <label>Parent Menu</label>
                    <select name="id_parent" id="id_parent" class="form-control">
                        <option value="">(Menu Utama)</option>
                        @foreach($parentMenus as $p)
                            <option value="{{ $p->id_menu }}">{{ $p->label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Order Index</label>
                    <input type="number" name="order_index" id="order_index" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="aktif" id="aktif" class="form-control">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Akses Role</label>
                <div class="roles-checkbox" id="rolesList">
                    @foreach($roles as $role)
                        <label class="role-checkbox">
                            <input type="checkbox" name="roles[]" value="{{ $role->id_role }}">
                            <span class="role-label">{{ ucfirst($role->nama_role) }}</span>
                        </label>
                    @endforeach
                </div>
                <small class="form-text">Centang role yang diizinkan mengakses menu ini</small>
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
    .btn-tambah:hover { transform: translateY(-2px); }
    
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
    .submenu-indent {
        color: #94a3b8;
        font-weight: normal;
    }
    .role-badge {
        display: inline-block;
        background: #eef2ff;
        color: #1e40af;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        margin: 2px 3px;
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
    .btn-edit, .btn-delete {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-edit { background: #dbeafe; color: #1e40af; }
    .btn-delete { background: #fee2e2; color: #991b1b; }
    
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
        max-width: 650px;
        max-height: 90vh;
        overflow-y: auto;
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
    .modal-header h3 { margin: 0; font-size: 16px; }
    .modal-close { font-size: 24px; cursor: pointer; opacity: 0.8; }
    .modal-close:hover { opacity: 1; }
    .form-row {
        display: flex;
        gap: 15px;
        padding: 0 20px;
        margin-bottom: 16px;
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
        margin-bottom: 6px;
        color: #334155;
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
    .roles-checkbox {
        padding: 0 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .role-checkbox {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #f8fafc;
        border-radius: 20px;
        cursor: pointer;
        font-size: 13px;
    }
    .role-checkbox input {
        margin: 0;
    }
    .role-label {
        font-weight: 500;
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
    }
    .btn-cancel {
        background: #e2e8f0;
        color: #334155;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }
    .required { color: #dc2626; }
    .text-center { text-align: center; }
    .fw-semibold { font-weight: 600; }
    code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 6px;
        font-size: 12px;
    }
    
    @media (max-width: 768px) {
        .form-row { flex-direction: column; gap: 10px; }
        .data-table th, .data-table td { padding: 8px 10px; font-size: 12px; }
        .action-buttons { gap: 4px; }
        .btn-edit, .btn-delete { width: 28px; height: 28px; }
        .roles-checkbox { gap: 8px; }
    }
</style>

<script>
    const csrfToken = '{{ csrf_token() }}';
    
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        const alertClass = type === 'success' ? 'alert-success-custom' : 'alert-error-custom';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        alertContainer.innerHTML = `
            <div class="${alertClass}" style="padding:12px;margin-bottom:20px;border-radius:12px;background:${type === 'success' ? '#dcfce7' : '#fee2e2'};color:${type === 'success' ? '#166534' : '#991b1b'}">
                <i class="fa ${icon}"></i> ${message}
            </div>
        `;
        setTimeout(() => { alertContainer.innerHTML = ''; }, 3000);
    }
    
    function openMenuModal() {
        document.getElementById('menuForm').reset();
        document.getElementById('editMenuId').value = '';
        document.querySelectorAll('#rolesList input[type=checkbox]').forEach(cb => cb.checked = false);
        document.getElementById('modalMenuTitle').innerHTML = '<i class="fa fa-plus"></i> Tambah Menu';
        document.getElementById('menuModal').style.display = 'flex';
    }
    
    function editMenu(id) {
        fetch('/settings/menu/' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const menu = data.data;
                    document.getElementById('editMenuId').value = menu.id_menu;
                    document.getElementById('label').value = menu.label;
                    document.getElementById('icon').value = menu.icon || '';
                    document.getElementById('page').value = menu.page || '';
                    document.getElementById('id_parent').value = menu.id_parent || '';
                    document.getElementById('order_index').value = menu.order_index || 0;
                    document.getElementById('aktif').value = menu.aktif;
                    
                    document.querySelectorAll('#rolesList input[type=checkbox]').forEach(cb => cb.checked = false);
                    if (menu.role_ids && menu.role_ids.length) {
                        menu.role_ids.forEach(roleId => {
                            const cb = document.querySelector(`#rolesList input[value="${roleId}"]`);
                            if (cb) cb.checked = true;
                        });
                    }
                    
                    document.getElementById('modalMenuTitle').innerHTML = '<i class="fa fa-pencil"></i> Edit Menu';
                    document.getElementById('menuModal').style.display = 'flex';
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan saat mengambil data', 'error');
            });
    }
    
    document.getElementById('menuForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('editMenuId').value;
        let url = '/settings/menu';
        let method = 'POST';
        
        if (id) {
            url = '/settings/menu/' + id;
            method = 'PUT';
        }
        
        const formData = new FormData();
        formData.append('_method', method);
        formData.append('label', document.getElementById('label').value);
        formData.append('icon', document.getElementById('icon').value);
        formData.append('page', document.getElementById('page').value);
        formData.append('id_parent', document.getElementById('id_parent').value || '');
        formData.append('order_index', document.getElementById('order_index').value);
        formData.append('aktif', document.getElementById('aktif').value);
        
        const selectedRoles = [];
        document.querySelectorAll('#rolesList input[type=checkbox]:checked').forEach(cb => {
            selectedRoles.push(cb.value);
        });
        selectedRoles.forEach(roleId => {
            formData.append('roles[]', roleId);
        });
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message || 'Terjadi kesalahan', 'error');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            showAlert('Terjadi kesalahan: ' + error.message, 'error');
        });
    });
    
    function deleteMenu(id, label) {
        if (confirm('Hapus menu "' + label + '" ?')) {
            fetch('/settings/menu/' + id, {
                method: 'DELETE',
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
        document.getElementById('menuModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
</script>

@endsection