<!-- DEBUG: Cek data -->
@php
    \Log::info('Video List di View: ' . $videoList->count());
@endphp

@if($videoList->count() == 0)
    <div class="alert alert-warning">
        <strong>Debug:</strong> Tidak ada data video. Total: {{ $videoList->count() }}
    </div>
@endif

@extends('layouts.admin_layout')

@section('title', 'Inventaris Video - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                <i class="fa fa-video fa-2x text-success"></i>
            </div>
            <div>
                <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Inventaris Video</h1>
                <p class="text-muted small mb-0 mt-1">Kelola video pembelajaran (Upload/YouTube/Embed)</p>
            </div>
        </div>
        <button class="btn-tambah" onclick="openVideoModal()">
            <i class="fa fa-plus me-2"></i> Tambah Video
        </button>
    </div>

    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Info Card -->
    <div class="info-card mb-4">
        <div class="info-icon"><i class="fa fa-info-circle"></i></div>
        <div class="info-content">
            <strong>💡 Tips Menghemat Bandwidth:</strong> Gunakan opsi "YouTube" atau "Embed" untuk video berukuran besar. 
            Video akan diputar langsung dari sumber aslinya, tidak membebani server.
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <i class="fa fa-spinner fa-spin fa-3x"></i>
            <p>Memproses...</p>
        </div>
    </div>

    <!-- Tabel Video -->
<div class="table-card">
    <div class="table-header">
        <div class="header-left">
            <i class="fa fa-table-list"></i>
            <h5>Daftar Video</h5>
        </div>
        <span class="total-badge">Total: {{ $videoList->count() }} Video</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th style="width: 80px;">Urutan</th>
                    <th>Judul Video</th>
                    <th style="width: 120px;">Tipe</th>
                    <th style="width: 150px;">Lagu</th>
                    <th style="width: 90px;">Status</th>
                    <th style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($videoList as $index => $v)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $v->urutan }}</td>
                    <td>
                        <strong>{{ $v->judul }}</strong>
                        @if($v->deskripsi)
                            <br><small class="text-muted">{{ Str::limit($v->deskripsi, 60) }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($v->tipe == 'upload')
                            <span class="badge-upload"><i class="fa fa-upload"></i> Upload</span>
                        @elseif($v->tipe == 'youtube')
                            <span class="badge-youtube"><i class="fab fa-youtube"></i> YouTube</span>
                        @elseif($v->tipe == 'vimeo')
                            <span class="badge-vimeo"><i class="fab fa-vimeo"></i> Vimeo</span>
                        @elseif($v->tipe == 'googledrive')
                            <span class="badge-drive"><i class="fab fa-google-drive"></i> Google Drive</span>
                        @else
                            <span class="badge-other"><i class="fa fa-link"></i> {{ ucfirst($v->tipe) }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($v->judul_lagu)
                            <i class="fa fa-music text-success"></i> {{ $v->judul_lagu }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($v->status == 'aktif')
                            <span class="status-badge status-active">Aktif</span>
                        @else
                            <span class="status-badge status-inactive">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="action-buttons">
                            <a href="{{ route('video.player', $v->id_video) }}" class="btn-play" target="_blank">
                                <i class="fa fa-play"></i>
                            </a>
                            <button class="btn-edit" onclick="editVideo({{ $v->id_video }})">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button class="btn-delete" onclick="deleteVideo({{ $v->id_video }}, '{{ addslashes($v->judul) }}')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fa fa-video fa-2x text-muted mb-2"></i>
                        <p>Belum ada data video</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
</div>

<!-- Modal Video -->
<div id="videoModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalVideoTitle"><i class="fa fa-plus"></i> Tambah Video</h3>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <form id="videoForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_video" id="editVideoId">
            <div class="form-group">
                <label>Judul <span class="required">*</span></label>
                <input type="text" name="judul" id="judul" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Tipe Video <span class="required">*</span></label>
                <select name="tipe" id="tipe" class="form-control" required>
                    <option value="upload">📁 Upload ke Server</option>
                    <option value="youtube">🎬 YouTube (Embed)</option>
                    <option value="vimeo">🎥 Vimeo (Embed)</option>
                    <option value="googledrive">📀 Google Drive (Embed)</option>
                    <option value="other">🔗 Link Embed Lainnya</option>
                </select>
            </div>
            
            <!-- Field Upload -->
            <div id="field_upload" class="dynamic-field">
                <div class="form-group">
                    <label>File Video (MP4)</label>
                    <input type="file" name="file_video" id="file_video" class="form-control" accept="video/mp4">
                    <small class="form-text">Maksimal 200MB</small>
                </div>
            </div>
            
            <!-- Field Embed -->
            <div id="field_embed" class="dynamic-field" style="display: none;">
                <div class="form-group">
                    <label>URL Embed</label>
                    <input type="text" name="url_embed" id="url_embed" class="form-control" placeholder="https://www.youtube.com/embed/...">
                    <small class="form-text">URL embed dari YouTube, Vimeo, atau Google Drive</small>
                </div>
                <div class="form-group">
                    <label>YouTube ID (opsional)</label>
                    <input type="text" name="youtube_id" id="youtube_id" class="form-control" placeholder="dQw4w9WgXcQ">
                </div>
            </div>
            
            <div class="form-group">
                <label>Lagu (opsional)</label>
                <select name="id_lagu" id="id_lagu" class="form-control">
                    <option value="">-- Pilih Lagu --</option>
                    @foreach($laguList as $l)
                        <option value="{{ $l->id_lagu }}">{{ $l->judul_lagu }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="urutan" id="urutan" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-save" id="submitBtn">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* tambahan styling khusus untuk halaman inventaris video */
    /* Tambahan CSS untuk memperjelas kolom */
.video-row td {
    vertical-align: middle;
}

.lagu-info {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.lagu-info i {
    font-size: 14px;
}

.lagu-info span {
    font-size: 13px;
    color: #334155;
}

/* Badge styles */
.badge-upload, .badge-youtube, .badge-vimeo, .badge-drive, .badge-other {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

/* Status badge */
.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
    white-space: nowrap;
}

/* Action buttons */
.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-play, .btn-edit, .btn-delete {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-play {
    background: #dcfce7;
    color: #166534;
    text-decoration: none;
}

.btn-play:hover {
    background: #16a34a;
    color: white;
    transform: scale(1.05);
}

.btn-edit {
    background: #dbeafe;
    color: #1e40af;
    border: none;
    cursor: pointer;
}

.btn-delete {
    background: #fee2e2;
    color: #991b1b;
    border: none;
    cursor: pointer;
}

.btn-edit:hover, .btn-delete:hover {
    transform: scale(1.05);
}

/* Table header */
.data-table th {
    background: #f8fafc;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    color: #64748b;
    padding: 14px 16px;
}

.data-table td {
    padding: 14px 16px;
    vertical-align: middle;
}

/* Row hover */
.data-table tbody tr:hover {
    background: #f8fafc;
}

/* Status colors */
.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

/* Responsive */
@media (max-width: 768px) {
    .data-table th, .data-table td {
        padding: 10px 12px;
        font-size: 12px;
    }
    
    .badge-upload, .badge-youtube, .badge-vimeo, .badge-drive, .badge-other,
    .status-badge {
        padding: 4px 10px;
        font-size: 11px;
    }
    
    .action-buttons {
        gap: 6px;
    }
    
    .btn-play, .btn-edit, .btn-delete {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
}
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
    
    .info-card {
        background: #e0f2fe;
        border-left: 4px solid #0284c7;
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .info-icon {
        width: 36px;
        height: 36px;
        background: #0284c7;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .loading-spinner {
        background: white;
        padding: 30px 40px;
        border-radius: 20px;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    .loading-spinner i {
        color: #1a5d45;
        margin-bottom: 15px;
    }
    .loading-spinner p {
        margin: 0;
        color: #334155;
        font-weight: 500;
    }
    
    .badge-upload, .badge-youtube, .badge-vimeo, .badge-drive, .badge-other {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-upload { background: #e0e7ff; color: #3730a3; }
    .badge-youtube { background: #fee2e2; color: #991b1b; }
    .badge-vimeo { background: #cffafe; color: #155e75; }
    .badge-drive { background: #fef3c7; color: #92400e; }
    .badge-other { background: #f1f5f9; color: #475569; }
    
    .lagu-name {
        font-size: 13px;
        color: #334155;
    }
    
    .btn-play {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #dcfce7;
        color: #166534;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-play:hover {
        background: #16a34a;
        color: white;
        transform: scale(1.05);
    }
    
    .alert-success-custom, .alert-error-custom {
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .alert-success-custom { background: #dcfce7; border-left: 4px solid #16a34a; color: #166534; }
    .alert-error-custom { background: #fee2e2; border-left: 4px solid #dc2626; color: #991b1b; }
    
    .table-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .table-header {
        padding: 18px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .header-left i {
        font-size: 20px;
        color: #1a5d45;
    }
    .header-left h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    .total-badge {
        background: #dcfce7;
        color: #166534;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table th, .data-table td {
        padding: 16px 18px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    .data-table th {
        background: #f8fafc;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
    }
    .data-table tbody tr:hover {
        background: #f8fafc;
    }
    
    .video-title strong {
        font-size: 15px;
    }
    
    .status-badge {
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
    }
    .status-active { background: #dcfce7; color: #166534; }
    .status-inactive { background: #fee2e2; color: #991b1b; }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
    }
    .btn-edit, .btn-delete {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
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
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal-content {
        background: white;
        border-radius: 24px;
        width: 90%;
        max-width: 580px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 35px -10px rgba(0,0,0,0.2);
    }
    .modal-header {
        padding: 20px 24px;
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
        font-size: 18px;
        font-weight: 600;
    }
    .modal-close {
        font-size: 28px;
        cursor: pointer;
        opacity: 0.8;
    }
    .modal-close:hover {
        opacity: 1;
    }
    
    .form-group {
        padding: 0 24px;
        margin-bottom: 18px;
    }
    .form-group label {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 6px;
        display: block;
        color: #334155;
    }
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #1a5d45;
        box-shadow: 0 0 0 3px rgba(26,93,69,0.1);
    }
    .form-row {
        display: flex;
        gap: 16px;
        padding: 0 24px;
        margin-bottom: 18px;
    }
    .form-row .form-group {
        flex: 1;
        padding: 0;
        margin-bottom: 0;
    }
    .form-text {
        font-size: 11px;
        color: #64748b;
        margin-top: 5px;
        display: block;
    }
    
    .modal-footer {
        padding: 18px 24px;
        background: #f8fafc;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        position: sticky;
        bottom: 0;
    }
    .btn-save {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26,93,69,0.3);
    }
    .btn-cancel {
        background: #e2e8f0;
        color: #334155;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
    }
    .required { color: #dc2626; }
    .dynamic-field { transition: all 0.2s; }
    .text-center { text-align: center; }
    .text-muted { color: #64748b; }
    
    @media (max-width: 768px) {
        .data-table th, .data-table td {
            padding: 12px 12px;
            font-size: 13px;
        }
        .action-buttons { gap: 6px; }
        .btn-edit, .btn-delete, .btn-play { width: 30px; height: 30px; }
        .form-row { flex-direction: column; gap: 12px; }
    }
</style>

<script>
    const csrfToken = '{{ csrf_token() }}';
    
    function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success-custom' : 'alert-error-custom';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    if (alertContainer.innerHTML !== '') {
        alertContainer.innerHTML = '';
    }
    
    alertContainer.innerHTML = `
        <div class="${alertClass}">
            <i class="fa ${icon}"></i>
            <div>${message}</div>
        </div>
    `;
    
    // Hanya hilangkan alert setelah 4 detik, TIDAK refresh halaman
    setTimeout(() => {
        if (alertContainer.innerHTML !== '') {
            alertContainer.innerHTML = '';
        }
    }, 4000);
}
    
    function showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        if (show) {
            overlay.style.display = 'flex';
        } else {
            overlay.style.display = 'none';
        }
    }
    
    document.getElementById('tipe').addEventListener('change', function() {
        const uploadField = document.getElementById('field_upload');
        const embedField = document.getElementById('field_embed');
        
        if (this.value === 'upload') {
            uploadField.style.display = 'block';
            embedField.style.display = 'none';
        } else {
            uploadField.style.display = 'none';
            embedField.style.display = 'block';
        }
    });
    
    function openVideoModal() {
    // Reset form
    document.getElementById('videoForm').reset();
    document.getElementById('editVideoId').value = '';
    document.getElementById('modalVideoTitle').innerHTML = '<i class="fa fa-plus"></i> Tambah Video';
    document.getElementById('field_upload').style.display = 'block';
    document.getElementById('field_embed').style.display = 'none';
    document.getElementById('tipe').value = 'upload';
    
    // Hapus alert yang mungkin masih ada
    const alertContainer = document.getElementById('alertContainer');
    if (alertContainer.innerHTML !== '') {
        alertContainer.innerHTML = '';
    }
    
    // Tampilkan modal
    document.getElementById('videoModal').style.display = 'flex';
}
    
    function editVideo(id) {
    showLoading(true);
    fetch('/video/inventaris/' + id)
        .then(res => res.json())
        .then(data => {
            showLoading(false);
            if (data.success) {
                const v = data.data;
                document.getElementById('editVideoId').value = v.id_video;
                document.getElementById('judul').value = v.judul;
                document.getElementById('deskripsi').value = v.deskripsi || '';
                document.getElementById('tipe').value = v.tipe;
                document.getElementById('id_lagu').value = v.id_lagu || '';
                document.getElementById('urutan').value = v.urutan;
                document.getElementById('status').value = v.status;
                
                // Trigger change event untuk menampilkan field yang sesuai
                const tipeSelect = document.getElementById('tipe');
                const event = new Event('change');
                tipeSelect.dispatchEvent(event);
                
                if (v.tipe !== 'upload') {
                    document.getElementById('url_embed').value = v.url_embed || '';
                    document.getElementById('youtube_id').value = v.youtube_id || '';
                }
                
                document.getElementById('modalVideoTitle').innerHTML = '<i class="fa fa-pencil"></i> Edit Video';
                document.getElementById('videoModal').style.display = 'flex';
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            showLoading(false);
            showAlert('Terjadi kesalahan: ' + error, 'error');
        });
}
    
    document.getElementById('videoForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const id = document.getElementById('editVideoId').value;
    let url = id ? '/video/inventaris/' + id : '/video/inventaris';
    let method = id ? 'PUT' : 'POST';
    
    // Validasi sebelum submit
    const judul = document.getElementById('judul').value.trim();
    if (!judul) {
        showAlert('Judul video wajib diisi', 'error');
        return;
    }
    
    const tipe = document.getElementById('tipe').value;
    if (tipe === 'upload') {
        const fileInput = document.getElementById('file_video');
        if (fileInput.files.length === 0 && !id) {
            showAlert('Pilih file video untuk diupload', 'error');
            return;
        }
    } else {
        const urlEmbed = document.getElementById('url_embed').value.trim();
        if (!urlEmbed) {
            showAlert('Masukkan URL embed', 'error');
            return;
        }
    }
    
    const formData = new FormData();
    formData.append('_method', method);
    formData.append('judul', judul);
    formData.append('deskripsi', document.getElementById('deskripsi').value);
    formData.append('tipe', tipe);
    formData.append('id_lagu', document.getElementById('id_lagu').value);
    formData.append('urutan', document.getElementById('urutan').value);
    formData.append('status', document.getElementById('status').value);
    
    if (tipe === 'upload') {
        const fileInput = document.getElementById('file_video');
        if (fileInput.files.length > 0) {
            formData.append('file_video', fileInput.files[0]);
        }
    } else {
        formData.append('url_embed', document.getElementById('url_embed').value);
        formData.append('youtube_id', document.getElementById('youtube_id').value);
    }
    
    // Show loading
    showLoading(true);
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        });
        
        const data = await response.json();
        showLoading(false);
        
        if (data.success) {
            showAlert(data.message, 'success');
            
            // Tutup modal
            closeModal();
            
            // Reset form
            document.getElementById('videoForm').reset();
            document.getElementById('editVideoId').value = '';
            
            // Refresh halaman setelah 1 detik
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert(data.message || 'Terjadi kesalahan', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    } catch (error) {
        showLoading(false);
        showAlert('Terjadi kesalahan: ' + error.message, 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});
    
    function deleteVideo(id, judul) {
    if (confirm(`Hapus video "${judul}" ?\n\nTindakan ini tidak dapat dibatalkan!`)) {
        showLoading(true);
        fetch('/video/inventaris/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            showLoading(false);
            showAlert(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                // Refresh halaman setelah 1 detik
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            showLoading(false);
            showAlert('Terjadi kesalahan: ' + error, 'error');
        });
    }
}
    
    function closeModal() {
    // Sembunyikan modal
    document.getElementById('videoModal').style.display = 'none';
    
    // Reset form
    document.getElementById('videoForm').reset();
    document.getElementById('editVideoId').value = '';
    
    // Reset button state
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fa fa-save"></i> Simpan';
        submitBtn.disabled = false;
    }
    
    // Reset field visibility ke default (upload)
    const tipeSelect = document.getElementById('tipe');
    if (tipeSelect) {
        tipeSelect.value = 'upload';
        const uploadField = document.getElementById('field_upload');
        const embedField = document.getElementById('field_embed');
        if (uploadField) uploadField.style.display = 'block';
        if (embedField) embedField.style.display = 'none';
    }
    
    // HANYA menutup modal, TIDAK refresh halaman
    // Tidak ada location.reload() di sini
}

</script>
@endsection