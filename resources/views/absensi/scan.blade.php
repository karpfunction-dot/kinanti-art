@extends('layouts.admin_layout')

@section('title', 'Pemindaian Barcode Absensi - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            
            <!-- Header -->
            <div class="text-center mb-4">
                <div class="d-inline-block p-3 rounded-circle bg-success bg-opacity-10 mb-3">
                    <i class="fa fa-qrcode fa-3x text-success"></i>
                </div>
                <h2 style="color: #0f3b2c; font-weight: 700;">Pemindaian Barcode Absensi</h2>
                <p class="text-muted">Scan barcode anggota untuk mencatat kehadiran hari ini</p>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success border-0 d-flex align-items-center mb-4" style="border-left: 5px solid #16a34a; background: #dcfce7; border-radius: 12px;">
                    <i class="fa fa-check-circle fa-2x me-3 text-success"></i>
                    <div>
                        <strong class="d-block">Berhasil!</strong>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger border-0 d-flex align-items-center mb-4" style="border-left: 5px solid #dc2626; background: #fee2e2; border-radius: 12px;">
                    <i class="fa fa-exclamation-circle fa-2x me-3 text-danger"></i>
                    <div>
                        <strong class="d-block">Gagal!</strong>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning border-0 d-flex align-items-center mb-4" style="border-left: 5px solid #ca8a04; background: #fef9c3; border-radius: 12px;">
                    <i class="fa fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                    <div>
                        <strong class="d-block">Peringatan!</strong>
                        {{ session('warning') }}
                    </div>
                </div>
            @endif

            <!-- Form Scan Barcode -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('absensi.proses') }}" id="scanForm">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-muted mb-2">
                                <i class="fa fa-barcode me-1"></i> Scan / Ketik Kode Barcode
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0" style="border-radius: 12px 0 0 12px;">
                                    <i class="fa fa-qrcode text-muted"></i>
                                </span>
                                <input type="text" 
                                       name="kode_barcode" 
                                       id="barcodeInput"
                                       class="form-control form-control-lg border-0 bg-light" 
                                       placeholder="Scan atau ketik kode barcode di sini..." 
                                       style="border-radius: 0 12px 12px 0; font-family: monospace; font-size: 1rem;"
                                       autofocus 
                                       autocomplete="off"
                                       required>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fa fa-info-circle me-1"></i> Arahkan scanner ke barcode anggota
                            </small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success py-3 fw-semibold" style="background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%); border: none; border-radius: 12px;">
                                <i class="fa fa-bolt me-2"></i> Proses Absensi
                            </button>
                            <button type="button" class="btn btn-outline-secondary py-2" style="border-radius: 10px;" onclick="resetForm()">
                                <i class="fa fa-eraser me-2"></i> Bersihkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

           <!-- Informasi User yang Discan (Jika Ada) -->
@if(session('scanned_user'))
    @php
        $user = session('scanned_user');
        
        // Tentukan path foto yang benar untuk Laravel
        $fotoPath = asset('assets/img/blank-profile.webp'); // Default
        
        if (!empty($user['foto_profil'])) {
            // Coba berbagai kemungkinan path
            $possiblePaths = [
                'storage/foto_users/' . $user['foto_profil'],
                'storage/' . $user['foto_profil'],
                'foto_users/' . $user['foto_profil'],
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists(public_path($path))) {
                    $fotoPath = asset($path);
                    break;
                }
            }
        }
    @endphp
    
    <div class="card border-0 shadow-sm" style="border-radius: 20px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-4">
                <!-- Foto User -->
                <div class="text-center">
                    <img src="{{ $fotoPath }}" 
                         alt="Foto Profil" 
                         class="rounded-circle border border-success border-3"
                         style="width: 100px; height: 100px; object-fit: cover;"
                         onerror="this.src='{{ asset('assets/img/blank-profile.webp') }}'">
                    <div class="mt-2">
                        <span class="badge bg-success rounded-pill px-3 py-1">
                            <i class="fa fa-check-circle me-1"></i> Terverifikasi
                        </span>
                    </div>
                </div>
                
                <!-- Informasi User -->
                <div class="flex-grow-1">
                    <h3 class="mb-2" style="color: #0f3b2c; font-weight: 700;">
                        {{ $user['nama_lengkap'] ?? $user['kode_barcode'] ?? 'Tidak diketahui' }}
                    </h3>
                    
                    <div class="row mt-3">
                        <div class="col-md-6 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-tag text-success" style="width: 20px;"></i>
                                <span class="text-muted">Peran:</span>
                                <strong class="text-dark">{{ ucfirst($user['role'] ?? 'Member') }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-barcode text-success" style="width: 20px;"></i>
                                <span class="text-muted">Kode:</span>
                                <strong class="text-dark font-monospace">{{ $user['kode_barcode'] ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

            <!-- Tombol Kembali -->
            <div class="text-center mt-4">
                <a href="{{ route('absensi.index') }}" class="btn btn-link text-decoration-none text-muted">
                    <i class="fa fa-arrow-left me-2"></i> Kembali ke Daftar Absensi
                </a>
            </div>

        </div>
    </div>
</div>

<script>
    // Auto submit untuk barcode scanner
    let scanTimeout = null;
    const barcodeInput = document.getElementById('barcodeInput');
    
    if (barcodeInput) {
        barcodeInput.addEventListener('input', function(e) {
            if (scanTimeout) clearTimeout(scanTimeout);
            scanTimeout = setTimeout(() => {
                if (this.value.length > 3) {
                    document.getElementById('scanForm').submit();
                }
            }, 300);
        });
    }
    
    function resetForm() {
        if (barcodeInput) {
            barcodeInput.value = '';
            barcodeInput.focus();
        }
    }
    
    // Focus on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (barcodeInput) {
            barcodeInput.focus();
        }
    });
</script>
@endsection