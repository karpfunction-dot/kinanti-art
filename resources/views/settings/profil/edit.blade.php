@extends('layouts.admin_layout')

@section('title', 'Edit Profil - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-user-edit fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Edit Profil</h1>
                    <p class="text-muted small mb-0 mt-1">Perbarui data profil anggota</p>
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

            <!-- Form Edit Profil -->
            <div class="form-card">
                <form method="POST" action="{{ route('profil.update', $profile->id_user) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-section">
                        <h3><i class="fa fa-user-circle"></i> Informasi Pribadi</h3>
                        
                        <div class="form-row">
                            <div class="form-group text-center">
                                <label>Foto Profil</label>
                                <div class="foto-wrapper">
                                   @php
    $fotoPath = $profile->foto_url ?? \App\Support\PhotoUrl::resolve($profile->foto_profil ?? null);
@endphp
                                    <img src="{{ $fotoPath }}" id="fotoPreview" class="foto-preview" alt="Foto Profil">
                                    <input type="file" name="foto_profil" id="foto_profil" accept="image/*" onchange="previewImage(event)">
                                    <small class="form-text">Format: JPG, PNG, GIF (Max 2MB)</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Kode Barcode</label>
                                <input type="text" value="{{ $profile->kode_barcode ?? '-' }}" class="form-control" disabled>
                                <small class="form-text text-muted">Kode barcode tidak dapat diubah</small>
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap <span class="required">*</span></label>
                                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $profile->nama_lengkap) }}" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="{{ old('email', $profile->email) }}" class="form-control" placeholder="email@example.com">
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fa fa-shield-alt"></i> Informasi Akun</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Role</label>
                                <select name="id_role" class="form-control" {{ auth()->user()->role->nama_role !== 'admin' ? 'disabled' : '' }}>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id_role }}" {{ old('id_role', $profile->role_id) == $role->id_role ? 'selected' : '' }}>
                                            {{ ucfirst($role->nama_role) }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(auth()->user()->role->nama_role !== 'admin')
                                    <small class="form-text text-muted">Hanya admin yang dapat mengubah role</small>
                                @endif
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="aktif" class="form-control" {{ auth()->user()->role->nama_role !== 'admin' ? 'disabled' : '' }}>
                                    <option value="1" {{ old('aktif', $profile->aktif) == '1' ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('aktif', $profile->aktif) == '0' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                                @if(auth()->user()->role->nama_role !== 'admin')
                                    <small class="form-text text-muted">Hanya admin yang dapat mengubah status</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-save">
                            <i class="fa fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ auth()->user()->role->nama_role === 'admin' ? route('profil.index') : route('dashboard') }}" class="btn-cancel">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
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
    
    .form-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    
    .form-section {
        padding: 24px;
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
    }
    
    .form-row {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 16px;
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
    
    .required {
        color: #dc2626;
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
    
    .form-text {
        font-size: 11px;
        color: #64748b;
        margin-top: 4px;
        display: block;
    }
    
    .foto-wrapper {
        text-align: center;
    }
    
    .foto-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #1a5d45;
        margin-bottom: 12px;
    }
    
    .form-actions {
        padding: 20px 24px;
        background: #f8fafc;
        display: flex;
        gap: 12px;
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
        text-decoration: none;
    }
    
    input:disabled, select:disabled {
        background: #f1f5f9;
        cursor: not-allowed;
    }
    
    .text-center {
        text-align: center;
    }
</style>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('fotoPreview');
            output.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection
