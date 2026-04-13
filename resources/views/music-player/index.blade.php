@extends('layouts.admin_layout')

@section('title', 'Materi Lagu - Kinanti Art')

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
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Materi Lagu</h1>
                    <p class="text-muted small mb-0 mt-1">Koleksi lagu dan materi audio/video untuk latihan</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                <i class="fa fa-user-shield me-1"></i> Akses: {{ ucfirst($role) }}
            </span>
        </div>
    </div>

    <!-- Info Card -->
    <div class="info-card mb-4">
        <div class="info-icon">
            <i class="fa fa-info-circle"></i>
        </div>
        <div class="info-content">
            <strong>Tips:</strong> Layar tidak akan mati saat audio/video diputar. Gunakan kontrol play/pause untuk mengatur.
        </div>
    </div>

    @if(empty($kelasList))
        <div class="empty-state">
            <i class="fa fa-folder-open fa-4x mb-3"></i>
            <p>Tidak ada folder musik ditemukan.</p>
            <small class="text-muted">Silakan tambahkan file musik ke folder <code>public/assets/musik/[nama_kelas]/</code></small>
        </div>
    @else
        @foreach($kelasList as $kelas => $files)
            <div class="music-card">
                <div class="music-card-header">
                    <div class="header-icon">
                        <i class="fa fa-folder-open"></i>
                    </div>
                    <div>
                        <h3 class="music-title">Kelas: {{ $kelas }}</h3>
                        <span class="music-count">{{ count($files) }} file materi</span>
                    </div>
                </div>
                
                <div class="music-card-body">
                    @foreach($files as $file)
                        <div class="file-item">
                            <div class="file-info">
                                <i class="fa {{ $file['extension'] == 'mp4' ? 'fa-video' : 'fa-music' }} file-icon"></i>
                                <strong>{{ $file['name'] }}</strong>
                            </div>
                            
                            @if(in_array($file['extension'], ['mp3', 'wav', 'ogg', 'm4a']))
                                <audio controls class="media-player" preload="metadata">
                                    <source src="{{ asset('assets/musik/' . $kelas . '/' . $file['name']) }}" type="audio/mpeg">
                                    Browser Anda tidak mendukung audio tag.
                                </audio>
                            @endif
                            
                            @if($file['extension'] == 'mp4')
                                <video controls class="media-player" preload="metadata">
                                    <source src="{{ asset('assets/musik/' . $kelas . '/' . $file['name']) }}" type="video/mp4">
                                    Browser Anda tidak mendukung video tag.
                                </video>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>

<style>
    .info-card {
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        border-radius: 16px;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        border-left: 4px solid #0284c7;
    }
    .info-icon {
        width: 40px;
        height: 40px;
        background: #0284c7;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }
    .info-content {
        flex: 1;
        color: #0c4a6e;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 20px;
        color: #64748b;
    }
    .empty-state code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 6px;
    }
    
    .music-card {
        background: white;
        border-radius: 20px;
        margin-bottom: 24px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .music-card-header {
        padding: 20px 24px;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .header-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 22px;
    }
    .music-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #0f3b2c;
    }
    .music-count {
        font-size: 12px;
        color: #64748b;
    }
    
    .music-card-body {
        padding: 20px 24px;
    }
    
    .file-item {
        margin-bottom: 20px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 12px;
    }
    .file-item:hover {
        background: #f1f5f9;
    }
    .file-info {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .file-icon {
        font-size: 18px;
        color: #1a5d45;
    }
    
    .media-player {
        width: 100%;
        border-radius: 10px;
        margin-top: 8px;
    }
    
    @media (max-width: 768px) {
        .music-card-header {
            padding: 16px 20px;
        }
        .music-card-body {
            padding: 16px 20px;
        }
        .header-icon {
            width: 40px;
            height: 40px;
            font-size: 18px;
        }
        .music-title {
            font-size: 16px;
        }
    }
</style>

<script>
    let wakeLock = null;
    
    async function enableWakeLock() {
        try {
            if ("wakeLock" in navigator) {
                wakeLock = await navigator.wakeLock.request("screen");
                wakeLock.addEventListener("release", () => {
                    console.log("Wake Lock dilepas");
                });
                console.log("Wake Lock aktif");
            }
        } catch (err) {
            console.warn("Wake Lock gagal:", err);
        }
    }
    
    document.addEventListener("visibilitychange", () => {
        if (wakeLock && document.visibilityState === "visible") {
            enableWakeLock();
        }
    });
    
    document.querySelectorAll('.media-player').forEach(player => {
        player.addEventListener('play', () => {
            enableWakeLock();
        });
        
        player.addEventListener('pause', () => {
            if (wakeLock) {
                wakeLock.release();
                wakeLock = null;
                console.log("Wake Lock dilepas karena pause");
            }
        });
        
        player.addEventListener('ended', () => {
            if (wakeLock) {
                wakeLock.release();
                wakeLock = null;
                console.log("Wake Lock dilepas karena selesai");
            }
        });
    });
</script>
@endsection