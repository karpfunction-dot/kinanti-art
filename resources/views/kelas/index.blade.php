@extends('layouts.admin_layout')

@section('title', 'Manajemen Kelas - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-building fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Manajemen Kelas</h1>
                    <p class="text-muted small mb-0 mt-1">Kelola jenjang, kelas, dan entri anggota kelas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="btn-group">
                <a href="{{ route('kelas.entri') }}" class="btn btn-info px-3 py-2" style="background: #0891b2; border-radius: 10px; color: white;">
                    <i class="fa fa-users me-2"></i> Entri Anggota
                </a>
                <a href="{{ route('kelas.naik') }}" class="btn btn-warning px-3 py-2" style="background: #d97706; border-radius: 10px; color: white; margin-left: 8px;">
                    <i class="fa fa-arrow-up me-2"></i> Naik Kelas
                </a>
            </div>
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

    <!-- Tabel Jenjang -->
    <div class="table-card mb-4">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-layer-group"></i>
                <h5>Daftar Jenjang</h5>
            </div>
            <button class="btn-tambah" onclick="openJenjangModal()">
                <i class="fa fa-plus"></i> Tambah Jenjang
            </button>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Jenjang</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jenjang as $j)
                    <tr>
                        <td>{{ $j->id_jenjang }}</td>
                        <td class="fw-semibold">{{ $j->nama_jenjang }}</td>
                        <td>{{ Str::limit($j->deskripsi ?? '-', 50) }}</td>
                        <td>
                            @if($j->aktif)
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="editJenjang({{ $j->id_jenjang }})" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn-delete" onclick="deleteJenjang({{ $j->id_jenjang }}, '{{ $j->nama_jenjang }}')" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center">Belum ada data jenjang</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabel Kelas -->
    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-chalkboard"></i>
                <h5>Daftar Kelas</h5>
            </div>
            <button class="btn-tambah" onclick="openKelasModal()">
                <i class="fa fa-plus"></i> Tambah Kelas
            </button>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kelas</th>
                        <th>Jenjang</th>
                        <th>Pelatih</th>
                        <th>Lagu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas as $k)
                    <tr>
                        <td>{{ $k->id_kelas }}</td>
                        <td class="fw-semibold">{{ $k->nama_kelas }}</td>
                        <td>{{ $k->nama_jenjang ?? '-' }}</td>
                        <td>{{ $k->nama_pelatih ?? '-' }}</td>
                        <td>{{ $k->judul_lagu ?? '-' }}</td>
                        <td>
                            @if($k->aktif)
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="editKelas({{ $k->id_kelas }})" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn-delete" onclick="deleteKelas({{ $k->id_kelas }}, '{{ $k->nama_kelas }}')" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center">Belum ada data kelas</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Jenjang -->
<div id="modalJenjang" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalJenjangTitle"><i class="fa fa-plus"></i> Tambah Jenjang</h3>
            <span class="modal-close" onclick="closeModal('modalJenjang')">&times;</span>
        </div>
        <form id="formJenjang">
            @csrf
            <input type="hidden" name="id_jenjang" id="editJenjangId">
            <div class="form-group">
                <label>Nama Jenjang <span class="required">*</span></label>
                <input type="text" name="nama_jenjang" id="nama_jenjang" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi_jenjang" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="aktif" id="aktif_jenjang" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('modalJenjang')">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kelas -->
<div id="modalKelas" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalKelasTitle"><i class="fa fa-plus"></i> Tambah Kelas</h3>
            <span class="modal-close" onclick="closeModal('modalKelas')">&times;</span>
        </div>
        <form id="formKelas">
            @csrf
            <input type="hidden" name="id_kelas" id="editKelasId">
            <div class="form-group">
                <label>Nama Kelas <span class="required">*</span></label>
                <input type="text" name="nama_kelas" id="nama_kelas" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Jenjang <span class="required">*</span></label>
                <select name="id_jenjang" id="id_jenjang" class="form-control" required>
                    <option value="">Pilih Jenjang</option>
                    @foreach($jenjang as $j)
                        <option value="{{ $j->id_jenjang }}">{{ $j->nama_jenjang }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Pelatih</label>
                <select name="pelatih" id="pelatih" class="form-control">
                    <option value="">Pilih Pelatih</option>
                    @foreach($pelatih as $p)
                        <option value="{{ $p->id_user }}">{{ $p->nama_lengkap }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Lagu Utama</label>
                <select name="id_lagu" id="id_lagu" class="form-control">
                    <option value="">Pilih Lagu</option>
                    @foreach($lagu as $l)
                        <option value="{{ $l->id_lagu }}">{{ $l->judul_lagu }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi_kelas" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="aktif" id="aktif_kelas" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('modalKelas')">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
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
        max-width: 500px;
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
    }
    .modal-header h3 { margin: 0; font-size: 16px; }
    .modal-close { font-size: 24px; cursor: pointer; }
    .form-group { padding: 0 20px; margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #334155; }
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
</style>

<script>
    const csrfToken = '{{ csrf_token() }}';
    
    // ==================== JENJANG ====================
    function openJenjangModal() {
        document.getElementById('formJenjang').reset();
        document.getElementById('editJenjangId').value = '';
        document.getElementById('modalJenjangTitle').innerHTML = '<i class="fa fa-plus"></i> Tambah Jenjang';
        document.getElementById('modalJenjang').style.display = 'flex';
    }
    
    function editJenjang(id) {
        fetch('/kelas/jenjang/' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const j = data.data;
                    document.getElementById('editJenjangId').value = j.id_jenjang;
                    document.getElementById('nama_jenjang').value = j.nama_jenjang;
                    document.getElementById('deskripsi_jenjang').value = j.deskripsi || '';
                    document.getElementById('aktif_jenjang').value = j.aktif;
                    document.getElementById('modalJenjangTitle').innerHTML = '<i class="fa fa-pencil"></i> Edit Jenjang';
                    document.getElementById('modalJenjang').style.display = 'flex';
                }
            });
    }
    
    document.getElementById('formJenjang').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('editJenjangId').value;
        const url = id ? '/kelas/jenjang/' + id : '/kelas/jenjang';
        const method = id ? 'PUT' : 'POST';
        
        const formData = new FormData();
        formData.append('_method', method);
        formData.append('nama_jenjang', document.getElementById('nama_jenjang').value);
        formData.append('deskripsi', document.getElementById('deskripsi_jenjang').value);
        formData.append('aktif', document.getElementById('aktif_jenjang').value);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        const data = await response.json();
        alert(data.message);
        if (data.success) location.reload();
    });
    
    function deleteJenjang(id, nama) {
        if (confirm('Hapus jenjang "' + nama + '" ?')) {
            fetch('/kelas/jenjang/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }
    }
    
    // ==================== KELAS ====================
    function openKelasModal() {
        document.getElementById('formKelas').reset();
        document.getElementById('editKelasId').value = '';
        document.getElementById('modalKelasTitle').innerHTML = '<i class="fa fa-plus"></i> Tambah Kelas';
        document.getElementById('modalKelas').style.display = 'flex';
    }
    
    function editKelas(id) {
        fetch('/kelas/kelas/' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const k = data.data;
                    document.getElementById('editKelasId').value = k.id_kelas;
                    document.getElementById('nama_kelas').value = k.nama_kelas;
                    document.getElementById('id_jenjang').value = k.id_jenjang;
                    document.getElementById('pelatih').value = k.pelatih || '';
                    document.getElementById('id_lagu').value = k.id_lagu || '';
                    document.getElementById('deskripsi_kelas').value = k.deskripsi || '';
                    document.getElementById('aktif_kelas').value = k.aktif;
                    document.getElementById('modalKelasTitle').innerHTML = '<i class="fa fa-pencil"></i> Edit Kelas';
                    document.getElementById('modalKelas').style.display = 'flex';
                }
            });
    }
    
    document.getElementById('formKelas').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('editKelasId').value;
        const url = id ? '/kelas/kelas/' + id : '/kelas/kelas';
        const method = id ? 'PUT' : 'POST';
        
        const formData = new FormData();
        formData.append('_method', method);
        formData.append('nama_kelas', document.getElementById('nama_kelas').value);
        formData.append('id_jenjang', document.getElementById('id_jenjang').value);
        formData.append('pelatih', document.getElementById('pelatih').value);
        formData.append('id_lagu', document.getElementById('id_lagu').value);
        formData.append('deskripsi', document.getElementById('deskripsi_kelas').value);
        formData.append('aktif', document.getElementById('aktif_kelas').value);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        const data = await response.json();
        alert(data.message);
        if (data.success) location.reload();
    });
    
    function deleteKelas(id, nama) {
        if (confirm('Hapus kelas "' + nama + '" ?')) {
            fetch('/kelas/kelas/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
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