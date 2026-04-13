@extends('layouts.admin_layout')

@section('title', 'Manajemen Lagu - Kinanti Art')

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
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Manajemen Lagu</h1>
                    <p class="text-muted small mb-0 mt-1">Kelola daftar lagu untuk materi pembelajaran</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 12px;">
                <i class="fa fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Tabel Lagu -->
    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-table-list"></i>
                <h5>Daftar Lagu</h5>
            </div>
            <button class="btn-tambah" onclick="openLaguModal()">
                <i class="fa fa-plus"></i> Tambah Lagu
            </button>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul Lagu</th>
                        <th>Pencipta</th>
                        <th>Lisensi</th>
                        <th>Status Lisensi</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lagu as $l)
                    <tr>
                        <td>{{ $l->id_lagu }}</td>
                        <td class="fw-semibold">{{ $l->judul_lagu }}</td>
                        <td>{{ $l->pencipta ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $l->lisensi == 'gratis' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($l->lisensi) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-info">
                                {{ ucfirst($l->status_lisensi) }}
                            </span>
                        </td>
                        <td>{{ $l->nama_kelas ?? '-' }}</td>
                        <td>
                            @if($l->status == 'aktif')
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="editLagu({{ $l->id_lagu }})" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn-delete" onclick="deleteLagu({{ $l->id_lagu }})" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Belum ada data lagu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Lagu -->
<div id="modalLagu" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalLaguTitle"><i class="fa fa-plus"></i> Tambah Lagu</h3>
            <span class="modal-close" onclick="closeModal('modalLagu')">&times;</span>
        </div>
        <form id="formLagu">
            @csrf
            <input type="hidden" name="id_lagu" id="editLaguId">
            <div class="form-group">
                <label>Judul Lagu <span class="required">*</span></label>
                <input type="text" name="judul_lagu" id="judul_lagu" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Pencipta</label>
                <input type="text" name="pencipta" id="pencipta" class="form-control">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Lisensi</label>
                    <select name="lisensi" id="lisensi" class="form-control">
                        <option value="gratis">Gratis</option>
                        <option value="berbayar">Berbayar</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Lisensi</label>
                    <select name="status_lisensi" id="status_lisensi" class="form-control">
                        <option value="bebas">Bebas</option>
                        <option value="izin">Izin</option>
                        <option value="internal">Internal</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Kelas</label>
                    <select name="id_kelas" id="id_kelas" class="form-control">
                        <option value="">Pilih Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Link Lisensi</label>
                <input type="url" name="link_lisensi" id="link_lisensi" class="form-control" placeholder="https://...">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('modalLagu')">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<style>
    .badge-success { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 20px; font-size: 11px; }
    .badge-warning { background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 20px; font-size: 11px; }
    .badge-info { background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 20px; font-size: 11px; }
    
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
    .btn-tambah {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
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
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    .status-active { background: #dcfce7; color: #166534; }
    .status-inactive { background: #fee2e2; color: #991b1b; }
    .action-buttons { display: flex; gap: 8px; }
    .btn-edit, .btn-delete {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
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
        max-width: 550px;
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
    .modal-close { font-size: 24px; cursor: pointer; }
    .form-group {
        padding: 0 20px;
        margin-bottom: 16px;
    }
    .form-row {
        display: flex;
        gap: 15px;
        padding: 0 20px;
        margin-bottom: 16px;
        flex-wrap: wrap;
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
    }
    .btn-cancel {
        background: #e2e8f0;
        color: #334155;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
    }
    .required { color: #dc2626; }
    .text-center { text-align: center; }
    .fw-semibold { font-weight: 600; }
    
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
    
    @media (max-width: 768px) {
        .form-row { flex-direction: column; gap: 10px; padding: 0 20px; }
        .data-table th, .data-table td { padding: 8px 10px; font-size: 12px; }
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
        setTimeout(() => { alertContainer.innerHTML = ''; }, 3000);
    }
    
    function openLaguModal() {
        document.getElementById('formLagu').reset();
        document.getElementById('editLaguId').value = '';
        document.getElementById('modalLaguTitle').innerHTML = '<i class="fa fa-plus"></i> Tambah Lagu';
        document.getElementById('modalLagu').style.display = 'flex';
    }
    
    function editLagu(id) {
        fetch('/lagu/' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const l = data.data;
                    document.getElementById('editLaguId').value = l.id_lagu;
                    document.getElementById('judul_lagu').value = l.judul_lagu;
                    document.getElementById('pencipta').value = l.pencipta || '';
                    document.getElementById('lisensi').value = l.lisensi;
                    document.getElementById('status_lisensi').value = l.status_lisensi;
                    document.getElementById('id_kelas').value = l.id_kelas || '';
                    document.getElementById('status').value = l.status;
                    document.getElementById('link_lisensi').value = l.link_lisensi || '';
                    document.getElementById('modalLaguTitle').innerHTML = '<i class="fa fa-pencil"></i> Edit Lagu';
                    document.getElementById('modalLagu').style.display = 'flex';
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => showAlert('Error: ' + error, 'error'));
    }
    
    document.getElementById('formLagu').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('editLaguId').value;
        const url = id ? '/lagu/' + id : '/lagu';
        const method = id ? 'PUT' : 'POST';
        
        const formData = new FormData();
        formData.append('_method', method);
        formData.append('judul_lagu', document.getElementById('judul_lagu').value);
        formData.append('pencipta', document.getElementById('pencipta').value);
        formData.append('lisensi', document.getElementById('lisensi').value);
        formData.append('status_lisensi', document.getElementById('status_lisensi').value);
        formData.append('id_kelas', document.getElementById('id_kelas').value);
        formData.append('status', document.getElementById('status').value);
        formData.append('link_lisensi', document.getElementById('link_lisensi').value);
        
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
            showAlert(data.message, 'error');
        }
    });
    
    function deleteLagu(id) {
        if (confirm('Hapus lagu ini?')) {
            fetch('/lagu/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                showAlert(data.message, data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => location.reload(), 1500);
            });
        }
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
</script>
@endsection