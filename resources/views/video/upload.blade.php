@extends('layouts.admin_layout')

@section('title', 'Upload Video - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            
            <!-- Header dengan Tombol Kembali -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="fa fa-upload fa-2x text-success"></i>
                    </div>
                    <div>
                        <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Upload Video</h1>
                        <p class="text-muted small mb-0 mt-1">Upload video materi ke folder lagu</p>
                    </div>
                </div>
                <a href="{{ route('video.index') }}" class="btn-back">
                    <i class="fa fa-arrow-left me-2"></i> Kembali
                </a>
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

            <!-- Upload Form -->
            <div class="upload-card">
                <form method="POST" action="{{ route('video.upload.process') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-section">
                        <h3><i class="fa fa-music"></i> Pilih Lagu</h3>
                        <div class="form-group">
                            <select name="id_lagu" id="id_lagu" class="form-control" required>
                                <option value="">-- Pilih Lagu --</option>
                                @foreach($lagu as $l)
                                    <option value="{{ $l->id_lagu }}" {{ request('id_lagu') == $l->id_lagu ? 'selected' : '' }}>
                                        {{ $l->judul_lagu }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-hint">
                                <i class="fa fa-folder"></i> Video akan disimpan di: 
                                <code>public/assets/video/{id_lagu}/</code>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fa fa-video"></i> File Video</h3>
                        <div class="form-group">
                            <div class="upload-area" id="uploadArea">
                                <div class="upload-icon">
                                    <i class="fa fa-cloud-upload-alt"></i>
                                </div>
                                <p class="upload-text">Drag & drop video di sini</p>
                                <p class="upload-or">atau</p>
                                <button type="button" class="btn-browse">Pilih File</button>
                                <input type="file" name="video" id="video" accept="video/mp4" required>
                            </div>
                            <div id="fileInfo" class="file-info" style="display: none;">
                                <div class="file-info-content">
                                    <i class="fa fa-file-video"></i>
                                    <div class="file-details">
                                        <span id="fileName"></span>
                                        <span id="fileSize" class="file-size"></span>
                                    </div>
                                    <button type="button" class="btn-clear" onclick="clearFile()">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-hint">
                                <i class="fa fa-info-circle"></i> Format: MP4, Maksimal 200MB
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-upload">
                            <i class="fa fa-upload"></i> Upload Sekarang
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Tips Card -->
            <div class="tips-card">
                <div class="tips-header">
                    <i class="fa fa-lightbulb"></i>
                    <h3>Tips Upload</h3>
                </div>
                <ul>
                    <li>Pastikan video dalam format <strong>MP4</strong> untuk hasil terbaik</li>
                    <li>Ukuran video maksimal <strong>200MB</strong></li>
                    <li>Nama file akan otomatis diberi timestamp</li>
                    <li>Video bisa dihapus melalui halaman daftar video</li>
                </ul>
            </div>
            
        </div>
    </div>
</div>

<style>
    .btn-back {
        background: #e2e8f0;
        color: #334155;
        border: none;
        padding: 8px 20px;
        border-radius: 40px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s;
    }
    .btn-back:hover {
        background: #cbd5e1;
        color: #1e293b;
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
    
    .upload-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
    }
    
    .form-section {
        padding: 28px 32px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .form-section h3 {
        font-size: 16px;
        font-weight: 600;
        color: #0f3b2c;
        margin-bottom: 20px;
    }
    
    .form-section h3 i {
        margin-right: 8px;
        color: #1a5d45;
    }
    
    .form-group {
        margin-bottom: 0;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #1a5d45;
        box-shadow: 0 0 0 3px rgba(26, 93, 69, 0.1);
    }
    
    .form-hint {
        font-size: 12px;
        color: #64748b;
        margin-top: 8px;
    }
    .form-hint code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 6px;
    }
    
    .upload-area {
        border: 2px dashed #cbd5e1;
        border-radius: 20px;
        padding: 48px 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #fafcff;
    }
    
    .upload-area:hover {
        border-color: #1a5d45;
        background: #f0fdf4;
    }
    
    .upload-area.drag-over {
        border-color: #1a5d45;
        background: #dcfce7;
    }
    
    .upload-icon i {
        font-size: 48px;
        color: #94a3b8;
        margin-bottom: 16px;
    }
    
    .upload-text {
        font-size: 14px;
        color: #64748b;
        margin: 0;
    }
    
    .upload-or {
        font-size: 12px;
        color: #94a3b8;
        margin: 8px 0;
    }
    
    .btn-browse {
        background: #0f3b2c;
        color: white;
        border: none;
        padding: 8px 24px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        margin-top: 8px;
    }
    
    .upload-area input {
        display: none;
    }
    
    .file-info {
        margin-top: 16px;
        padding: 14px 18px;
        background: #dcfce7;
        border-radius: 14px;
        border-left: 4px solid #16a34a;
    }
    
    .file-info-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .file-info-content i {
        font-size: 24px;
        color: #16a34a;
    }
    
    .file-details {
        flex: 1;
    }
    
    .file-details span:first-child {
        font-weight: 600;
        color: #166534;
        display: block;
        font-size: 14px;
    }
    
    .file-size {
        font-size: 11px;
        color: #166534;
        opacity: 0.8;
    }
    
    .btn-clear {
        background: none;
        border: none;
        color: #166534;
        cursor: pointer;
        font-size: 14px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .btn-clear:hover {
        background: rgba(0,0,0,0.1);
    }
    
    .form-actions {
        padding: 20px 32px;
        background: #f8fafc;
        text-align: center;
    }
    
    .btn-upload {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 14px 32px;
        border-radius: 40px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        max-width: 300px;
    }
    
    .btn-upload:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 93, 69, 0.3);
    }
    
    .tips-card {
        background: white;
        border-radius: 20px;
        padding: 20px 24px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .tips-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }
    
    .tips-header i {
        font-size: 20px;
        color: #f59e0b;
    }
    
    .tips-header h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #0f3b2c;
    }
    
    .tips-card ul {
        margin: 0;
        padding-left: 20px;
    }
    
    .tips-card li {
        font-size: 13px;
        color: #475569;
        margin-bottom: 8px;
    }
    
    @media (max-width: 768px) {
        .form-section {
            padding: 20px;
        }
        .upload-area {
            padding: 32px 16px;
        }
    }
</style>

<script>
    const uploadArea = document.getElementById('uploadArea');
    const videoInput = document.getElementById('video');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    // Click to upload
    document.querySelector('.btn-browse').addEventListener('click', () => {
        videoInput.click();
    });
    
    uploadArea.addEventListener('click', (e) => {
        if (e.target === uploadArea || e.target.classList.contains('upload-text') || e.target.classList.contains('upload-icon')) {
            videoInput.click();
        }
    });
    
    // Drag & drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('drag-over');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('drag-over');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            videoInput.files = files;
            updateFileInfo(files[0]);
        }
    });
    
    videoInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            updateFileInfo(e.target.files[0]);
        }
    });
    
    function updateFileInfo(file) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        fileName.textContent = file.name;
        fileSize.textContent = `${sizeMB} MB`;
        uploadArea.style.display = 'none';
        fileInfo.style.display = 'block';
    }
    
    function clearFile() {
        videoInput.value = '';
        uploadArea.style.display = 'block';
        fileInfo.style.display = 'none';
    }
</script>
@endsection