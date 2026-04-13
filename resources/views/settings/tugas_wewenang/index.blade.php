@extends('layouts.admin_layout')

@section('title', 'Tugas & Wewenang - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-tasks fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Tugas & Wewenang</h1>
                    <p class="text-muted small mb-0 mt-1">Kelola tugas dan wewenang anggota Sanggar Tari</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Tugas -->
    <div class="table-card mb-4">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-clipboard-list"></i>
                <h5>Daftar Tugas</h5>
            </div>
            <button class="btn-tambah" onclick="openTugasModal()">
                <i class="fa fa-plus"></i> Tambah Tugas
            </button>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Tugas</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tugas as $t)
                    <tr>
                        <td>{{ $t->id_tugas }}</td>
                        <td class="fw-semibold">{{ $t->nama_tugas }}</td>
                        <td>{{ $t->kategori ?? '-' }}</td>
                        <td>{{ Str::limit($t->deskripsi ?? '-', 50) }}</td>
                        <td>
                            @if($t->aktif)
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="editTugas({{ $t->id_tugas }})" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn-delete" onclick="deleteTugas({{ $t->id_tugas }}, '{{ $t->nama_tugas }}')" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data tugas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabel Wewenang -->
    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-user-shield"></i>
                <h5>Daftar Wewenang</h5>
            </div>
            <button class="btn-tambah" onclick="openWewenangModal()">
                <i class="fa fa-plus"></i> Tambah Wewenang
            </button>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Pengguna</th>
                        <th>Tugas</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wewenang as $w)
                    <tr>
                        <td>{{ $w->id_wewenang }}</td>
                        <td class="fw-semibold">{{ $w->nama_lengkap ?? '-' }}</td>
                        <td>{{ $w->nama_tugas ?? '-' }}</td>
                        <td>
                            @if($w->periode_mulai || $w->periode_selesai)
                                {{ $w->periode_mulai ?? '-' }} s/d {{ $w->periode_selesai ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($w->aktif)
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="editWewenang({{ $w->id_wewenang }})" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn-delete" onclick="deleteWewenang({{ $w->id_wewenang }})" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data wewenang</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tugas -->
<div id="modalTugas" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTugasTitle"><i class="fa fa-plus"></i> Tambah Tugas</h3>
            <span class="modal-close" onclick="closeModal('modalTugas')">&times;</span>
        </div>
        <form id="formTugas">
            @csrf
            <input type="hidden" name="id_tugas" id="editTugasId">
            <div class="form-group">
                <label>Nama Tugas <span class="required">*</span></label>
                <input type="text" name="nama_tugas" id="nama_tugas" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori" id="kategori" class="form-control">
                    <option value="">Pilih Kategori</option>
                    <option value="Pelatih">Pelatih</option>
                    <option value="Koordinator">Koordinator</option>
                    <option value="Staf">Staf</option>
                    <option value="Admin">Admin</option>
                    <option value="Manajemen">Manajemen</option>
                </select>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" placeholder="Deskripsi tugas..."></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="aktif" id="aktif_tugas" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('modalTugas')">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Wewenang -->
<div id="modalWewenang" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalWewenangTitle"><i class="fa fa-plus"></i> Tambah Wewenang</h3>
            <span class="modal-close" onclick="closeModal('modalWewenang')">&times;</span>
        </div>
        <form id="formWewenang">
            @csrf
            <input type="hidden" name="id_wewenang" id="editWewenangId">
            <div class="form-group">
                <label>Cari Pengguna <span class="required">*</span></label>
                <div style="position: relative;">
                    <input type="text" id="searchUser" class="form-control" placeholder="Ketik nama pengguna atau kode barcode..." autocomplete="off">
                    <div id="userResults" class="search-results"></div>
                </div>
                <input type="hidden" name="id_user" id="id_user">
                <small class="form-text text-muted">Ketik minimal 2 karakter untuk mencari</small>
            </div>
            <div class="form-group">
                <label>Tugas <span class="required">*</span></label>
                <select name="id_tugas" id="id_tugas" class="form-control" required>
                    <option value="">Pilih Tugas</option>
                    @foreach($tugas as $t)
                        <option value="{{ $t->id_tugas }}">{{ $t->nama_tugas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Periode Mulai</label>
                <input type="date" name="periode_mulai" id="periode_mulai" class="form-control">
            </div>
            <div class="form-group">
                <label>Periode Selesai</label>
                <input type="date" name="periode_selesai" id="periode_selesai" class="form-control">
            </div>
            <div class="form-group">
                <label>Catatan</label>
                <textarea name="catatan" id="catatan" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="aktif" id="aktif_wewenang" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('modalWewenang')">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<style>
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
    .header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .header-left i {
        font-size: 18px;
        color: #1a5d45;
    }
    .header-left h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
    }
    .btn-tambah {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .btn-tambah:hover {
        transform: translateY(-1px);
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
    .data-table tr:hover {
        background: #f8fafc;
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-active { background: #dcfce7; color: #166534; }
    .status-inactive { background: #fee2e2; color: #991b1b; }
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    .btn-edit, .btn-delete {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .btn-edit { background: #dbeafe; color: #1e40af; }
    .btn-delete { background: #fee2e2; color: #991b1b; }
    .btn-edit:hover, .btn-delete:hover {
        transform: scale(1.05);
    }
    
    /* Modal Styles */
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
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
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
    .modal-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    .modal-close {
        font-size: 24px;
        cursor: pointer;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    .modal-close:hover {
        opacity: 1;
    }
    .form-group {
        padding: 0 20px;
        margin-bottom: 16px;
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
        transition: all 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #1a5d45;
        box-shadow: 0 0 0 3px rgba(26, 93, 69, 0.1);
    }
    textarea.form-control {
        resize: vertical;
    }
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
    .btn-save:hover, .btn-cancel:hover {
        opacity: 0.9;
    }
    .required { color: #dc2626; }
    .search-results {
        position: absolute;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        max-height: 200px;
        overflow-y: auto;
        width: 100%;
        z-index: 1001;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .search-results div {
        padding: 10px 12px;
        cursor: pointer;
        font-size: 13px;
        border-bottom: 1px solid #f0f0f0;
    }
    .search-results div:hover {
        background: #dcfce7;
    }
    .text-center { text-align: center; }
    .fw-semibold { font-weight: 600; }
    
    @media (max-width: 768px) {
        .data-table th, .data-table td {
            padding: 8px 10px;
            font-size: 12px;
        }
        .modal-content {
            max-width: 95%;
        }
    }
</style>

<script>
    const csrfToken = '{{ csrf_token() }}';
    
    // ==================== TUGAS ====================
    function openTugasModal() {
        document.getElementById('formTugas').reset();
        document.getElementById('editTugasId').value = '';
        document.getElementById('modalTugasTitle').innerHTML = '<i class="fa fa-plus"></i> Tambah Tugas';
        document.getElementById('modalTugas').style.display = 'flex';
    }
    
    function editTugas(id) {
        fetch('/tugas-wewenang/tugas/' + id + '/edit', {
            headers: { 
                'Accept': 'application/json', 
                'X-Requested-With': 'XMLHttpRequest' 
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tugas = data.data;
                document.getElementById('editTugasId').value = tugas.id_tugas;
                document.getElementById('nama_tugas').value = tugas.nama_tugas;
                document.getElementById('kategori').value = tugas.kategori || '';
                document.getElementById('deskripsi').value = tugas.deskripsi || '';
                document.getElementById('aktif_tugas').value = tugas.aktif;
                document.getElementById('modalTugasTitle').innerHTML = '<i class="fa fa-pencil"></i> Edit Tugas';
                document.getElementById('modalTugas').style.display = 'flex';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengambil data tugas');
        });
    }
    
    document.getElementById('formTugas').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('editTugasId').value;
        const url = id ? '/tugas-wewenang/tugas/' + id : '/tugas-wewenang/tugas';
        const method = id ? 'PUT' : 'POST';
        
        const formData = new FormData();
        formData.append('_method', method);
        formData.append('nama_tugas', document.getElementById('nama_tugas').value);
        formData.append('kategori', document.getElementById('kategori').value);
        formData.append('deskripsi', document.getElementById('deskripsi').value);
        formData.append('aktif', document.getElementById('aktif_tugas').value);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
    
    function deleteTugas(id, nama) {
        if (confirm('Hapus tugas "' + nama + '" ?\n\nData wewenang yang terkait juga akan terpengaruh.')) {
            fetch('/tugas-wewenang/tugas/' + id, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': csrfToken, 
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus tugas');
            });
        }
    }
    
    // ==================== WEWENANG ====================
    function openWewenangModal() {
        document.getElementById('formWewenang').reset();
        document.getElementById('editWewenangId').value = '';
        document.getElementById('searchUser').value = '';
        document.getElementById('id_user').value = '';
        document.getElementById('periode_mulai').value = '';
        document.getElementById('periode_selesai').value = '';
        document.getElementById('catatan').value = '';
        document.getElementById('modalWewenangTitle').innerHTML = '<i class="fa fa-plus"></i> Tambah Wewenang';
        document.getElementById('modalWewenang').style.display = 'flex';
    }
    
    function editWewenang(id) {
        fetch('/tugas-wewenang/wewenang/' + id + '/edit', {
            headers: { 
                'Accept': 'application/json', 
                'X-Requested-With': 'XMLHttpRequest' 
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const w = data.data;
                document.getElementById('editWewenangId').value = w.id_wewenang;
                document.getElementById('id_user').value = w.id_user;
                document.getElementById('searchUser').value = w.nama_lengkap;
                document.getElementById('id_tugas').value = w.id_tugas;
                document.getElementById('periode_mulai').value = w.periode_mulai || '';
                document.getElementById('periode_selesai').value = w.periode_selesai || '';
                document.getElementById('catatan').value = w.catatan || '';
                document.getElementById('aktif_wewenang').value = w.aktif;
                document.getElementById('modalWewenangTitle').innerHTML = '<i class="fa fa-pencil"></i> Edit Wewenang';
                document.getElementById('modalWewenang').style.display = 'flex';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengambil data wewenang');
        });
    }
    
    // Search User
    let searchTimeout;
    const searchInput = document.getElementById('searchUser');
    const resultsDiv = document.getElementById('userResults');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                resultsDiv.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                fetch('/tugas-wewenang/search-users?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            resultsDiv.innerHTML = data.map(u => 
                                `<div onclick="selectUser(${u.id_user}, '${escapeHtml(u.nama_lengkap)}', '${escapeHtml(u.kode_barcode)}')">
                                    <strong>${escapeHtml(u.nama_lengkap)}</strong><br>
                                    <small>Kode: ${escapeHtml(u.kode_barcode)}</small>
                                </div>`
                            ).join('');
                            resultsDiv.style.display = 'block';
                        } else {
                            resultsDiv.innerHTML = '<div style="color:#999;">Tidak ditemukan</div>';
                            resultsDiv.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultsDiv.style.display = 'none';
                    });
            }, 300);
        });
        
        // Close results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                resultsDiv.style.display = 'none';
            }
        });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    function selectUser(id, nama, kode) {
        document.getElementById('searchUser').value = nama + ' (' + kode + ')';
        document.getElementById('id_user').value = id;
        resultsDiv.style.display = 'none';
    }
    
    document.getElementById('formWewenang').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const idUser = document.getElementById('id_user').value;
        if (!idUser) {
            alert('Silakan pilih pengguna terlebih dahulu!');
            return;
        }
        
        const id = document.getElementById('editWewenangId').value;
        const url = id ? '/tugas-wewenang/wewenang/' + id : '/tugas-wewenang/wewenang';
        const method = id ? 'PUT' : 'POST';
        
        const formData = new FormData();
        formData.append('_method', method);
        formData.append('id_user', idUser);
        formData.append('id_tugas', document.getElementById('id_tugas').value);
        formData.append('periode_mulai', document.getElementById('periode_mulai').value);
        formData.append('periode_selesai', document.getElementById('periode_selesai').value);
        formData.append('catatan', document.getElementById('catatan').value);
        formData.append('aktif', document.getElementById('aktif_wewenang').value);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
    
    function deleteWewenang(id) {
        if (confirm('Hapus wewenang ini?')) {
            fetch('/tugas-wewenang/wewenang/' + id, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': csrfToken, 
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus wewenang');
            });
        }
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
</script>
@endsection