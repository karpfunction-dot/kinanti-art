@extends('layouts.admin_layout')

@section('title', 'Scan Barcode Absensi - Kinanti Art')

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
                <p class="text-muted">Arahkan kamera ke barcode anggota untuk absensi otomatis</p>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer">
                @if(session('success'))
                    <div class="alert alert-success border-0 d-flex align-items-center mb-4 alert-dismissible fade show" style="border-left: 5px solid #16a34a; background: #dcfce7; border-radius: 12px;">
                        <i class="fa fa-check-circle fa-2x me-3 text-success"></i>
                        <div>
                            <strong class="d-block">Berhasil!</strong>
                            {{ session('success') }}
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger border-0 d-flex align-items-center mb-4 alert-dismissible fade show" style="border-left: 5px solid #dc2626; background: #fee2e2; border-radius: 12px;">
                        <i class="fa fa-exclamation-circle fa-2x me-3 text-danger"></i>
                        <div>
                            <strong class="d-block">Gagal!</strong>
                            {{ session('error') }}
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning border-0 d-flex align-items-center mb-4 alert-dismissible fade show" style="border-left: 5px solid #ca8a04; background: #fef9c3; border-radius: 12px;">
                        <i class="fa fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                        <div>
                            <strong class="d-block">Peringatan!</strong>
                            {{ session('warning') }}
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>

            <!-- Scanner Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
                <div class="card-body p-4">
                    <!-- Camera Preview Container -->
                    <div class="position-relative mb-4">
                        <div id="qr-reader" style="width: 100%; border-radius: 16px; overflow: hidden;"></div>
                        
                        <!-- Loading Overlay -->
                        <div id="scannerLoading" class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-none align-items-center justify-content-center" style="border-radius: 16px; z-index: 10;">
                            <div class="text-center text-white">
                                <div class="spinner-border mb-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mb-0">Menyiapkan kamera...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Camera Controls -->
                    <div class="d-flex gap-2 mb-4">
                        <button type="button" id="toggleCameraBtn" class="btn btn-outline-secondary flex-grow-1 py-2" style="border-radius: 12px;">
                            <i class="fa fa-refresh me-2"></i> Ganti Kamera
                        </button>
                        <button type="button" id="startScannerBtn" class="btn btn-success flex-grow-1 py-2" style="border-radius: 12px; background: #0f3b2c; border: none;">
                            <i class="fa fa-play me-2"></i> Mulai Scanner
                        </button>
                    </div>
                    
                    <!-- Manual Input (Toggle) -->
                    <div class="mt-3">
                        <button type="button" class="btn btn-link text-muted text-decoration-none w-100" onclick="toggleManualInput()" style="font-size: 0.9rem;">
                            <i class="fa fa-keyboard-o me-2"></i> Gunakan Input Manual
                        </button>
                        
                        <div id="manualInputSection" style="display: none;">
                            <hr>
                            <form method="POST" action="{{ route('absensi.proses') }}" id="manualForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted">
                                        <i class="fa fa-barcode me-1"></i> Ketik Kode Barcode
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0" style="border-radius: 12px 0 0 12px;">
                                            <i class="fa fa-qrcode text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               name="kode_barcode" 
                                               id="manualBarcodeInput"
                                               class="form-control form-control-lg border-0 bg-light" 
                                               placeholder="Ketik kode barcode di sini..." 
                                               style="border-radius: 0 12px 12px 0; font-family: monospace;">
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary py-2" style="border-radius: 12px;">
                                        <i class="fa fa-paper-plane me-2"></i> Proses Manual
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scanned User Info -->
            @if(session('scanned_user'))
                @php
                    $user = session('scanned_user');
                    $fotoPath = asset('assets/img/blank-profile.webp'); 
                    
                    if (!empty($user['foto_profil'])) {
                        if (str_starts_with($user['foto_profil'], 'http')) {
                            $fotoPath = $user['foto_profil'];
                        } elseif (file_exists(public_path('storage/foto_users/' . $user['foto_profil']))) {
                            $fotoPath = asset('storage/foto_users/' . $user['foto_profil']);
                        }
                    }
                @endphp
                
                <div id="userInfoCard" class="card border-0 shadow-sm fade-in" style="border-radius: 20px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-4 flex-wrap">
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

<!-- Scanner Scripts -->
@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// ============================================
// CAMERA SCANNER IMPLEMENTATION
// ============================================

let html5QrCode = null;
let currentFacingMode = 'environment';
let isScanning = false;
let isProcessing = false;
let scanTimeout = null;

// DOM Elements
const qrReaderDiv = document.getElementById('qr-reader');
const toggleCameraBtn = document.getElementById('toggleCameraBtn');
const startScannerBtn = document.getElementById('startScannerBtn');
const scannerLoading = document.getElementById('scannerLoading');
const manualInputSection = document.getElementById('manualInputSection');

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Auto start scanner if on mobile device
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        startScanner();
    }
    
    // Set focus for manual input if exists
    const manualInput = document.getElementById('manualBarcodeInput');
    if (manualInput) {
        manualInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('manualForm').submit();
            }
        });
    }
});

// Start Scanner Function
async function startScanner() {
    if (html5QrCode && isScanning) {
        console.log('Scanner already running');
        return;
    }
    
    // Clean up existing scanner
    if (html5QrCode && html5QrCode.isScanning) {
        try {
            await html5QrCode.stop();
        } catch (err) {
            console.warn('Stop error:', err);
        }
    }
    
    // Show loading
    if (scannerLoading) {
        scannerLoading.classList.remove('d-none');
        scannerLoading.classList.add('d-flex');
    }
    
    try {
        html5QrCode = new Html5Qrcode("qr-reader");
        
        const config = {
            fps: 15,
            qrbox: { width: 280, height: 280 },
            aspectRatio: 1.0,
            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
        };
        
        await html5QrCode.start(
            { facingMode: currentFacingMode },
            config,
            onScanSuccess,
            onScanFailure
        );
        
        isScanning = true;
        console.log('Scanner started with facing mode:', currentFacingMode);
        
        // Update UI
        if (startScannerBtn) {
            startScannerBtn.innerHTML = '<i class="fa fa-stop me-2"></i> Hentikan Scanner';
            startScannerBtn.classList.remove('btn-success');
            startScannerBtn.classList.add('btn-danger');
        }
        
    } catch (err) {
        console.error('Failed to start scanner:', err);
        
        // Fallback to user camera if environment fails
        if (currentFacingMode === 'environment') {
            console.log('Falling back to front camera');
            currentFacingMode = 'user';
            await startScanner();
        } else {
            showAlert('error', 'Gagal mengakses kamera. Pastikan izin kamera diberikan dan coba refresh halaman.');
        }
    } finally {
        if (scannerLoading) {
            scannerLoading.classList.remove('d-flex');
            scannerLoading.classList.add('d-none');
        }
    }
}

// Stop Scanner Function
async function stopScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
        try {
            await html5QrCode.stop();
            isScanning = false;
            
            if (startScannerBtn) {
                startScannerBtn.innerHTML = '<i class="fa fa-play me-2"></i> Mulai Scanner';
                startScannerBtn.classList.remove('btn-danger');
                startScannerBtn.classList.add('btn-success');
            }
            
            console.log('Scanner stopped');
        } catch (err) {
            console.error('Stop scanner error:', err);
        }
    }
}

// Toggle Camera
async function toggleCamera() {
    const newMode = currentFacingMode === 'user' ? 'environment' : 'user';
    currentFacingMode = newMode;
    
    if (isScanning) {
        await stopScanner();
        await startScanner();
    }
    
    showAlert('info', 'Kamera berpindah ke ' + (currentFacingMode === 'user' ? 'depan' : 'belakang'));
}

// On Scan Success
async function onScanSuccess(decodedText) {
    // Prevent duplicate processing
    if (isProcessing) {
        console.log('Already processing, ignoring duplicate scan');
        return;
    }
    
    console.log('Scanned:', decodedText);
    
    // Stop scanner while processing
    isProcessing = true;
    
    if (html5QrCode && html5QrCode.isScanning) {
        try {
            await html5QrCode.stop();
            isScanning = false;
        } catch (err) {
            console.warn('Stop during processing error:', err);
        }
    }
    
    // Process the barcode
    await processBarcode(decodedText);
}

// On Scan Failure
function onScanFailure(error) {
    // Silent ignore - this is normal for non-QR frames
    // Only log occasional errors to avoid console spam
    if (Math.random() < 0.01) {
        console.debug('Scan frame error:', error);
    }
}

// Process Barcode via AJAX (Better UX)
async function processBarcode(barcode) {
    // Show processing indicator
    showProcessingIndicator(true);
    
    try {
        const response = await fetch('{{ route("absensi.proses.api") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ kode_barcode: barcode })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            
            // Update user info card if user data returned
            if (data.user) {
                updateUserInfoCard(data.user);
            }
            
            // Auto reset scanner after 2 seconds
            setTimeout(() => {
                resetAndRestartScanner();
            }, 2000);
        } else if (response.status === 409) {
            // Already attended
            showAlert('warning', data.message);
            if (data.user) {
                updateUserInfoCard(data.user);
            }
            setTimeout(() => {
                resetAndRestartScanner();
            }, 2000);
        } else {
            showAlert('error', data.message || 'Barcode tidak valid');
            setTimeout(() => {
                resetAndRestartScanner();
            }, 1500);
        }
        
    } catch (error) {
        console.error('Process error:', error);
        showAlert('error', 'Terjadi kesalahan: ' + error.message);
        setTimeout(() => {
            resetAndRestartScanner();
        }, 1500);
    } finally {
        showProcessingIndicator(false);
        isProcessing = false;
    }
}

// Reset and restart scanner
async function resetAndRestartScanner() {
    isProcessing = false;
    
    // Clear scanner instance
    if (html5QrCode) {
        try {
            if (html5QrCode.isScanning) {
                await html5QrCode.stop();
            }
        } catch (err) {}
        html5QrCode = null;
    }
    
    // Restart scanner after delay
    setTimeout(() => {
        startScanner();
    }, 500);
}

// Update user info card dynamically
function updateUserInfoCard(user) {
    const userInfoContainer = document.querySelector('#userInfoCard') || 
                             document.createElement('div');
    
    if (!document.querySelector('#userInfoCard')) {
        // Create card if doesn't exist
        const container = document.querySelector('.card.border-0.shadow-sm.mb-4');
        if (container && container.parentNode) {
            const newCard = document.createElement('div');
            newCard.id = 'userInfoCard';
            newCard.className = 'card border-0 shadow-sm fade-in mt-4';
            newCard.style.borderRadius = '20px';
            newCard.style.background = 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)';
            container.parentNode.insertBefore(newCard, container.nextSibling);
        }
    }
    
    const card = document.querySelector('#userInfoCard');
    if (card && user) {
        const fotoUrl = user.foto_profil && user.foto_profil.startsWith('http') 
            ? user.foto_profil 
            : '{{ asset("assets/img/blank-profile.webp") }}';
        
        card.innerHTML = `
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div class="text-center">
                        <img src="${fotoUrl}" 
                             alt="Foto Profil" 
                             class="rounded-circle border border-success border-3"
                             style="width: 100px; height: 100px; object-fit: cover;"
                             onerror="this.src='{{ asset("assets/img/blank-profile.webp") }}'">
                        <div class="mt-2">
                            <span class="badge bg-success rounded-pill px-3 py-1">
                                <i class="fa fa-check-circle me-1"></i> ${user.sudah_absen ? 'Sudah Absen' : 'Terverifikasi'}
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-2" style="color: #0f3b2c; font-weight: 700;">
                            ${user.nama_lengkap || user.kode_barcode || 'Tidak diketahui'}
                        </h3>
                        <div class="row mt-3">
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa fa-tag text-success" style="width: 20px;"></i>
                                    <span class="text-muted">Peran:</span>
                                    <strong class="text-dark">${user.role || 'Member'}</strong>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa fa-barcode text-success" style="width: 20px;"></i>
                                    <span class="text-muted">Kode:</span>
                                    <strong class="text-dark font-monospace">${user.kode_barcode || '-'}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

// Show alert message
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success' : 
                      (type === 'error' ? 'alert-danger' : 
                      (type === 'warning' ? 'alert-warning' : 'alert-info'));
    const icon = type === 'success' ? 'fa-check-circle' :
                (type === 'error' ? 'fa-exclamation-circle' :
                (type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'));
    
    const alertHtml = `
        <div class="alert ${alertClass} border-0 d-flex align-items-center mb-4 alert-dismissible fade show" style="border-left: 5px solid ${type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#ca8a04'}; border-radius: 12px;">
            <i class="fa ${icon} fa-2x me-3 ${type === 'success' ? 'text-success' : type === 'error' ? 'text-danger' : 'text-warning'}"></i>
            <div>
                <strong class="d-block">${type === 'success' ? 'Berhasil!' : type === 'error' ? 'Gagal!' : 'Peringatan!'}</strong>
                ${message}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml + (alertContainer.innerHTML || '');
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alerts = alertContainer.querySelectorAll('.alert');
        if (alerts.length > 0) {
            alerts[0].remove();
        }
    }, 5000);
}

// Show/hide processing indicator
function showProcessingIndicator(show) {
    const indicator = document.getElementById('processingIndicator') || createProcessingIndicator();
    if (show) {
        indicator.classList.remove('d-none');
        indicator.classList.add('d-flex');
    } else {
        indicator.classList.add('d-none');
        indicator.classList.remove('d-flex');
    }
}

function createProcessingIndicator() {
    const div = document.createElement('div');
    div.id = 'processingIndicator';
    div.className = 'position-fixed top-50 start-50 translate-middle bg-dark bg-opacity-75 text-white px-4 py-3 rounded-3 d-none align-items-center gap-3';
    div.style.zIndex = '9999';
    div.style.borderRadius = '50px';
    div.innerHTML = `
        <div class="spinner-border spinner-border-sm" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <span>Memproses absensi...</span>
    `;
    document.body.appendChild(div);
    return div;
}

// Toggle manual input section
function toggleManualInput() {
    if (manualInputSection) {
        const isVisible = manualInputSection.style.display === 'block';
        manualInputSection.style.display = isVisible ? 'none' : 'block';
        
        if (!isVisible) {
            // Stop scanner when showing manual input
            stopScanner();
        } else {
            // Restart scanner when hiding manual input
            startScanner();
        }
    }
}

// Reset form function
function resetForm() {
    const manualInput = document.getElementById('manualBarcodeInput');
    if (manualInput) {
        manualInput.value = '';
        manualInput.focus();
    }
}

// Event Listeners
if (toggleCameraBtn) {
    toggleCameraBtn.addEventListener('click', toggleCamera);
}

if (startScannerBtn) {
    startScannerBtn.addEventListener('click', function() {
        if (isScanning) {
            stopScanner();
        } else {
            startScanner();
        }
    });
}

// Auto focus on page load
window.addEventListener('load', function() {
    // Check camera permission
    if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
        navigator.mediaDevices.enumerateDevices().then(devices => {
            const hasCamera = devices.some(device => device.kind === 'videoinput');
            if (!hasCamera) {
                showAlert('warning', 'Tidak ditemukan kamera di perangkat ini. Gunakan input manual.');
                toggleManualInput();
            }
        });
    }
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().catch(console.error);
    }
});
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.5s ease-out;
}

#qr-reader video {
    width: 100%;
    height: auto;
    border-radius: 16px;
}

#qr-reader {
    border: none !important;
    background: #f0f0f0;
}

#qr-reader__scan_region {
    background: #000 !important;
    border-radius: 16px;
}

#qr-reader__dashboard {
    padding: 10px !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 16px;
        padding-right: 16px;
    }
    
    #qr-reader {
        min-height: 300px;
    }
}
</style>
@endpush

@endsection
