@extends('layouts.admin_layout')

@section('title', 'Profil Sanggar - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <!-- Header -->
            <div class="text-center mb-5">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                    <i class="fa fa-building fa-3x text-success"></i>
                </div>
                <h1 style="color: #0f3b2c; font-weight: 700; font-size: 2rem; margin: 0;">Profil Sanggar</h1>
                <p class="text-muted mt-2">Kelola informasi dan identitas sanggar tari</p>
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

            <!-- Form Profil Sanggar -->
            <div class="profile-card">
                <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <!-- Logo Section -->
                    <div class="logo-section">
                        <div class="logo-preview">
                            @php
                                $logoPath = asset('assets/img/logo-placeholder.png');
                                if (!empty($profile->logo) && file_exists(public_path('storage/company/' . $profile->logo))) {
                                    $logoPath = asset('storage/company/' . $profile->logo);
                                }
                            @endphp
                            <img src="{{ $logoPath }}" id="logoPreview" class="logo-img" alt="Logo Sanggar">
                            <div class="logo-upload">
                                <label for="logo" class="btn-upload">
                                    <i class="fa fa-camera"></i> Ganti Logo
                                </label>
                                <input type="file" name="logo" id="logo" accept="image/*" style="display: none;" onchange="previewLogo(event)">
                                <small class="form-text">Format: JPG, PNG (Max 2MB)</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Fields -->
                    <div class="form-section">
                        <h3><i class="fa fa-info-circle"></i> Informasi Umum</h3>
                        
                        <div class="form-group">
                            <label>Nama Lembaga <span class="required">*</span></label>
                            <div class="input-icon">
                                <i class="fa fa-building"></i>
                                <input type="text" name="nama_lembaga" class="form-control" value="{{ old('nama_lembaga', $profile->nama_lembaga) }}" placeholder="Nama Sanggar" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Alamat</label>
                            <div class="input-icon">
                                <i class="fa fa-location-dot"></i>
                                <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap sanggar">{{ old('alamat', $profile->alamat) }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fa fa-address-card"></i> Kontak</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Telepon</label>
                                <div class="input-icon">
                                    <i class="fa fa-phone"></i>
                                    <input type="text" name="telp" class="form-control" value="{{ old('telp', $profile->telp) }}" placeholder="Nomor telepon">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <div class="input-icon">
                                    <i class="fa fa-envelope"></i>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $profile->email) }}" placeholder="Email sanggar">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Website</label>
                            <div class="input-icon">
                                <i class="fa fa-globe"></i>
                                <input type="text" name="website" class="form-control" value="{{ old('website', $profile->website) }}" placeholder="kinanti-artpro.com">
                            </div>
                            <small class="form-text">Masukkan domain tanpa https:// (contoh: kinanti-artpro.com)</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-save">
                            <i class="fa fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn-cancel">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Preview Card -->
            <div class="preview-card">
                <h3><i class="fa fa-eye"></i> Preview</h3>
                <div class="preview-content" id="previewContent">
                    <div class="preview-logo">
                        <img src="{{ $logoPath }}" alt="Logo" id="previewLogo">
                    </div>
                    <div class="preview-info">
                        <h4 id="previewNama">{{ $profile->nama_lembaga }}</h4>
                        <p id="previewAlamat">{{ $profile->alamat ?: 'Alamat belum diisi' }}</p>
                        <div class="preview-contact">
                            @if($profile->telp)
                                <span><i class="fa fa-phone"></i> {{ $profile->telp }}</span>
                            @endif
                            @if($profile->email)
                                <span><i class="fa fa-envelope"></i> {{ $profile->email }}</span>
                            @endif
                            @if($profile->website)
                                <span><i class="fa fa-globe"></i> {{ $profile->website }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
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
    .alert-success-custom { background: #dcfce7; border-left: 4px solid #16a34a; color: #166534; }
    .alert-error-custom { background: #fee2e2; border-left: 4px solid #dc2626; color: #991b1b; }
    
    .profile-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }
    
    .logo-section {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        padding: 30px;
        text-align: center;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .logo-preview {
        display: inline-block;
        text-align: center;
    }
    
    .logo-img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        margin-bottom: 15px;
    }
    
    .btn-upload {
        display: inline-block;
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        padding: 8px 20px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-upload:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 93, 69, 0.3);
    }
    
    .form-section {
        padding: 24px 30px;
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
    
    .form-row {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .form-group {
        flex: 1;
        margin-bottom: 18px;
    }
    
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #334155;
    }
    
    .input-icon {
        position: relative;
    }
    
    .input-icon i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
    }
    
    .input-icon textarea ~ i {
        top: 18px;
        transform: none;
    }
    
    .input-icon .form-control {
        padding-left: 42px;
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
    
    textarea.form-control {
        resize: vertical;
    }
    
    .form-text {
        font-size: 11px;
        color: #64748b;
        margin-top: 6px;
        display: block;
    }
    
    .required {
        color: #dc2626;
    }
    
    .form-actions {
        padding: 20px 30px;
        background: #f8fafc;
        display: flex;
        gap: 12px;
    }
    
    .btn-save {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 93, 69, 0.3);
    }
    
    .btn-cancel {
        background: #e2e8f0;
        color: #334155;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .btn-cancel:hover {
        background: #cbd5e1;
    }
    
    /* Preview Card */
    .preview-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
    }
    
    .preview-card h3 {
        padding: 18px 24px;
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .preview-card h3 i {
        margin-right: 8px;
        color: #1a5d45;
    }
    
    .preview-content {
        padding: 24px;
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .preview-logo img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #1a5d45;
    }
    
    .preview-info h4 {
        margin: 0 0 8px;
        font-size: 18px;
        font-weight: 700;
        color: #0f3b2c;
    }
    
    .preview-info p {
        margin: 0 0 8px;
        font-size: 13px;
        color: #64748b;
    }
    
    .preview-contact {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        font-size: 12px;
        color: #64748b;
    }
    
    .preview-contact span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    @media (max-width: 768px) {
        .form-section {
            padding: 20px;
        }
        .form-actions {
            padding: 16px 20px;
            flex-direction: column;
        }
        .btn-save, .btn-cancel {
            text-align: center;
        }
        .preview-content {
            flex-direction: column;
            text-align: center;
        }
        .preview-contact {
            justify-content: center;
        }
    }
</style>

<script>
    function previewLogo(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('logoPreview');
            const previewLogo = document.getElementById('previewLogo');
            output.src = reader.result;
            if (previewLogo) previewLogo.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
    
    // Live preview saat input berubah
    document.addEventListener('DOMContentLoaded', function() {
        const namaInput = document.querySelector('input[name="nama_lembaga"]');
        const alamatInput = document.querySelector('textarea[name="alamat"]');
        const telpInput = document.querySelector('input[name="telp"]');
        const emailInput = document.querySelector('input[name="email"]');
        const websiteInput = document.querySelector('input[name="website"]');
        
        if (namaInput) {
            namaInput.addEventListener('input', function() {
                document.getElementById('previewNama').textContent = this.value || 'Nama Sanggar';
            });
        }
        
        if (alamatInput) {
            alamatInput.addEventListener('input', function() {
                document.getElementById('previewAlamat').textContent = this.value || 'Alamat belum diisi';
            });
        }
        
        function updatePreview() {
            const container = document.querySelector('.preview-contact');
            if (!container) return;
            
            // Clear existing preview spans
            const existingSpans = container.querySelectorAll('.dynamic-preview');
            existingSpans.forEach(span => span.remove());
            
            // Add telp preview
            if (telpInput && telpInput.value) {
                const span = document.createElement('span');
                span.className = 'dynamic-preview';
                span.innerHTML = '<i class="fa fa-phone"></i> ' + telpInput.value;
                container.appendChild(span);
            }
            
            // Add email preview
            if (emailInput && emailInput.value) {
                const span = document.createElement('span');
                span.className = 'dynamic-preview';
                span.innerHTML = '<i class="fa fa-envelope"></i> ' + emailInput.value;
                container.appendChild(span);
            }
            
            // Add website preview
            if (websiteInput && websiteInput.value) {
                const span = document.createElement('span');
                span.className = 'dynamic-preview';
                let displayUrl = websiteInput.value;
                if (displayUrl.startsWith('https://')) {
                    displayUrl = displayUrl.replace('https://', '');
                }
                if (displayUrl.startsWith('http://')) {
                    displayUrl = displayUrl.replace('http://', '');
                }
                span.innerHTML = '<i class="fa fa-globe"></i> ' + displayUrl;
                container.appendChild(span);
            }
        }
        
        if (telpInput) telpInput.addEventListener('input', updatePreview);
        if (emailInput) emailInput.addEventListener('input', updatePreview);
        if (websiteInput) websiteInput.addEventListener('input', updatePreview);
        
        // Initial update
        updatePreview();
    });
</script>
@endsection