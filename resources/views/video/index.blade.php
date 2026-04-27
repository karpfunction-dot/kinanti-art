@extends('layouts.admin_layout')

@section('title', 'Semua Video - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header dengan Tombol Upload di Kanan -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                <i class="fa fa-video fa-2x text-success"></i>
            </div>
            <div>
                <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Materi Video</h1>
                <p class="text-muted small mb-0 mt-1">Koleksi video tutorial dan materi latihan</p>
            </div>
        </div>
        
        <!-- Tombol Upload di Kanan Atas -->
        <div class="d-flex gap-2">
            <a href="{{ route('video.upload') }}" class="btn-upload">
                <i class="fa fa-upload me-2"></i> Upload Video
            </a>
            <span class="badge-role">
                <i class="fa fa-user-shield me-1"></i> {{ ucfirst($role) }}
            </span>
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

    <!-- Info Card -->
    <div class="info-card mb-4">
        <div class="info-icon">
            <i class="fa fa-info-circle"></i>
        </div>
        <div class="info-content">
            <strong>Informasi:</strong> Video disimpan di folder <code>public/assets/video/{id_lagu}/</code>. Hanya admin, pelatih, dan manajemen yang dapat menghapus video.
        </div>
        @if(in_array($role, ['admin', 'pelatih', 'manajemen']))
            <a href="{{ route('video.upload') }}" class="info-upload-link">
                <i class="fa fa-plus-circle"></i> Upload
            </a>
        @endif
    </div>

    @if(empty($videoData))
        <div class="empty-state">
            <i class="fa fa-folder-open fa-4x mb-3"></i>
            <p>Tidak ada video ditemukan.</p>
            <small class="text-muted">Silakan upload video melalui tombol <strong>Upload Video</strong> di atas</small>
        </div>
    @else
        @foreach($videoData as $idLagu => $data)
            <div class="video-card">
                <div class="video-card-header">
                    <div class="header-icon">
                        <i class="fa fa-film"></i>
                    </div>
                    <div class="header-title">
                        <h3 class="video-title">{{ $data['judul'] }}</h3>
                        <span class="video-count">{{ count($data['videos']) }} video materi</span>
                    </div>
                    @if(in_array($role, ['admin', 'pelatih', 'manajemen']))
                        <a href="{{ route('video.upload') }}?id_lagu={{ $idLagu }}" class="btn-add-video" title="Tambah video ke lagu ini">
                            <i class="fa fa-plus"></i> Tambah Video
                        </a>
                    @endif
                </div>
                
                <div class="video-card-body">
                    @foreach($data['videos'] as $video)
                        <div class="video-item">
                            <div class="video-info">
                                <i class="fa fa-play-circle file-icon"></i>
                                <div class="video-name">
                                    <strong>{{ $video['name'] }}</strong>
                                    <small>{{ \Illuminate\Support\Str::limit($video['name'], 50) }}</small>
                                </div>
                                @if(in_array($role, ['admin', 'pelatih', 'manajemen']))
                                    <form action="{{ route('video.delete') }}" method="POST" onsubmit="return confirm('Hapus video {{ $video['name'] }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="id_lagu" value="{{ $idLagu }}">
                                        <input type="hidden" name="file" value="{{ $video['name'] }}">
                                        <button type="submit" class="btn-delete">
                                            <i class="fa fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                            
                            <video controls controlsList="nodownload" class="video-player" preload="metadata">
                                <source src="{{ $video['path'] }}" type="video/mp4">
                                Browser Anda tidak mendukung video tag.
                            </video>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>

<style>
    .btn-upload {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 40px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .btn-upload:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 93, 69, 0.3);
        color: white;
    }
    
    .badge-role {
        background: rgba(26, 93, 69, 0.1);
        color: #1a5d45;
        padding: 8px 16px;
        border-radius: 40px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
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
    
    .info-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-radius: 16px;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        border-left: 4px solid #16a34a;
    }
    .info-icon {
        width: 36px;
        height: 36px;
        background: #16a34a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
    }
    .info-content {
        flex: 1;
        color: #166534;
        font-size: 13px;
    }
    .info-content code {
        background: rgba(0,0,0,0.08);
        padding: 2px 6px;
        border-radius: 6px;
    }
    .info-upload-link {
        background: white;
        color: #16a34a;
        padding: 6px 14px;
        border-radius: 30px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .info-upload-link:hover {
        background: #16a34a;
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 20px;
        color: #64748b;
    }
    
    .video-card {
        background: white;
        border-radius: 20px;
        margin-bottom: 24px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .video-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -5px rgba(0,0,0,0.1);
    }
    
    .video-card-header {
        padding: 18px 24px;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .header-icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
    .header-title {
        flex: 1;
    }
    .video-title {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #0f3b2c;
    }
    .video-count {
        font-size: 11px;
        color: #64748b;
    }
    .btn-add-video {
        background: #e2e8f0;
        color: #475569;
        padding: 6px 14px;
        border-radius: 20px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.2s;
    }
    .btn-add-video:hover {
        background: #0f3b2c;
        color: white;
    }
    
    .video-card-body {
        padding: 20px 24px;
    }
    
    .video-item {
        margin-bottom: 20px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 12px;
    }
    .video-item:last-child {
        margin-bottom: 0;
    }
    .video-item:hover {
        background: #f1f5f9;
    }
    
    .video-info {
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .file-icon {
        font-size: 18px;
        color: #1a5d45;
    }
    .video-name {
        flex: 1;
    }
    .video-name strong {
        font-size: 14px;
    }
    .video-name small {
        font-size: 11px;
        color: #64748b;
        margin-left: 8px;
    }
    
    .btn-delete {
        color: #dc2626;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 6px;
        background: #fee2e2;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-delete:hover {
        background: #fecaca;
    }
    
    .video-player {
        width: 100%;
        border-radius: 10px;
        margin-top: 8px;
        background: #000;
    }
    
    @media (max-width: 768px) {
        .video-card-header {
            flex-wrap: wrap;
        }
        .btn-add-video {
            margin-left: auto;
        }
        .video-info {
            flex-direction: column;
            align-items: flex-start;
        }
        .video-name strong {
            display: block;
            margin-bottom: 4px;
        }
        .video-name small {
            margin-left: 0;
            display: block;
        }
    }
</style>

<script>
    // Disable right-click on video
    document.addEventListener('contextmenu', function(e) {
        if (e.target.tagName === 'VIDEO' || e.target.closest('video')) {
            e.preventDefault();
        }
    });
</script>
@endsection
