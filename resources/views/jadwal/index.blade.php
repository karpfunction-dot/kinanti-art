@extends('layouts.admin_layout')

@section('title', 'Jadwal Sanggar - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-calendar-alt fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Jadwal Sanggar</h1>
                    <p class="text-muted small mb-0 mt-1">
                        @if($canManage)
                            Kelola jadwal latihan dan kegiatan sanggar
                        @elseif($role === 'pelatih')
                            Menampilkan semua jadwal aktif sanggar
                        @else
                            Menampilkan jadwal aktif sesuai kelas Anda
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 12px;">
                <i class="fa fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Tabel Jadwal -->
    <div class="table-card">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-table-list"></i>
                <h5>Daftar Jadwal</h5>
            </div>
            @if($canManage)
            <button class="btn-tambah" onclick="openJadwalModal()">
                <i class="fa fa-plus"></i> Tambah Jadwal
            </button>
            @endif
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hari</th>
                        <th>Waktu</th>
                        <th>Kelas</th>
                        <th>Pelatih</th>
                        <th>Lokasi</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        @if($canManage)
                        <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwal as $j)
                    <tr>
                        <td>{{ $j->id_jadwal }}</td>
                        <td class="fw-semibold">{{ $j->hari }}</td>
                        <td>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</td>
                        <td>{{ $j->nama_kelas ?? '-' }}</td>
                        <td>{{ $j->nama_pelatih ?? '-' }}</td>
                        <td>{{ $j->lokasi ?? '-' }}</td>
                        <td>{{ $j->kategori }}</td>
                        <td>
                            @if($j->status == 'aktif')
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>
                        @if($canManage)
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="editJadwal({{ $j->id_jadwal }})" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn-delete" onclick="deleteJadwal({{ $j->id_jadwal }})" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canManage ? 9 : 8 }}" class="text-center">Belum ada data jadwal</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($canManage)
<!-- Modal Jadwal -->
<div id="modalJadwal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalJadwalTitle"><i class="fa fa-plus"></i> Tambah Jadwal</h3>
            <span class="modal-close" onclick="closeModal('modalJadwal')">&times;</span>
        </div>
        <form id="formJadwal">
            @csrf
            <input type="hidden" name="id_jadwal" id="editJadwalId">
            <div class="form-row">
                <div class="form-group">
                    <label>Hari <span class="required">*</span></label>
                    <select name="hari" id="hari" class="form-control" required>
                        <option value="">Pilih Hari</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam Mulai <span class="required">*</span></label>
                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Jam Selesai <span class="required">*</span></label>
                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" required>
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
                    <label>Pelatih</label>
                    <select name="id_pelatih" id="id_pelatih" class="form-control">
                        <option value="">Pilih Pelatih</option>
                        @foreach($pelatih as $p)
                            <option value="{{ $p->id_user }}">{{ $p->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" name="lokasi" id="lokasi" class="form-control" placeholder="Contoh: Studio 1">
                </div>
                <div class="form-group">
                    <label>Kategori <span class="required">*</span></label>
                    <select name="kategori" id="kategori" class="form-control" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Latihan">Latihan</option>
                        <option value="UTS">UTS</option>
                        <option value="UAS">UAS</option>
                        <option value="Pasanggiri">Pasanggiri</option>
                        <option value="Festival">Festival</option>
                        <option value="Study Banding">Study Banding</option>
                        <option value="Kelas Umum">Kelas Umum</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" id="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('modalJadwal')">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endif

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
        max-width: 600px;
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
    .form-row {
        display: flex;
        gap: 15px;
        padding: 0 20px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .form-group {
        flex: 1;
        min-width: 150px;
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
    textarea.form-control { resize: vertical; }
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
        .form-row { flex-direction: column; gap: 10px; }
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
    
    @if($canManage)
    function openJadwalModal() {
        document.getElementById('formJadwal').reset();
        document.getElementById('editJadwalId').value = '';
        document.getElementById('modalJadwalTitle').innerHTML = '<i class="fa fa-plus"></i> Tambah Jadwal';
        document.getElementById('modalJadwal').style.display = 'flex';
    }
    
    function editJadwal(id) {
        fetch('/jadwal/' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const j = data.data;
                    document.getElementById('editJadwalId').value = j.id_jadwal;
                    document.getElementById('hari').value = j.hari;
                    document.getElementById('jam_mulai').value = j.jam_mulai;
                    document.getElementById('jam_selesai').value = j.jam_selesai;
                    document.getElementById('id_kelas').value = j.id_kelas || '';
                    document.getElementById('id_pelatih').value = j.id_pelatih || '';
                    document.getElementById('lokasi').value = j.lokasi || '';
                    document.getElementById('kategori').value = j.kategori;
                    document.getElementById('keterangan').value = j.keterangan || '';
                    document.getElementById('status').value = j.status;
                    document.getElementById('modalJadwalTitle').innerHTML = '<i class="fa fa-pencil"></i> Edit Jadwal';
                    document.getElementById('modalJadwal').style.display = 'flex';
                } else {
                    showAlert(data.message, 'error');
                }
            });
    }
    
    document.getElementById('formJadwal').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('editJadwalId').value;
        const url = id ? '/jadwal/' + id : '/jadwal';
        const method = id ? 'PUT' : 'POST';
        
        const formData = new FormData();
        formData.append('_method', method);
        formData.append('hari', document.getElementById('hari').value);
        formData.append('jam_mulai', document.getElementById('jam_mulai').value);
        formData.append('jam_selesai', document.getElementById('jam_selesai').value);
        formData.append('id_kelas', document.getElementById('id_kelas').value);
        formData.append('id_pelatih', document.getElementById('id_pelatih').value);
        formData.append('lokasi', document.getElementById('lokasi').value);
        formData.append('kategori', document.getElementById('kategori').value);
        formData.append('keterangan', document.getElementById('keterangan').value);
        formData.append('status', document.getElementById('status').value);
        
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
    
    function deleteJadwal(id) {
        if (confirm('Hapus jadwal ini?')) {
            fetch('/jadwal/' + id, {
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
    @endif
</script>
@endsection
