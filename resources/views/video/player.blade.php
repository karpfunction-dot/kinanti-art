<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $video->judul }} - Kinanti Art</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            color: white;
        }
        .player-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .video-wrapper {
            background: #000;
            border-radius: 16px;
            overflow: hidden;
            aspect-ratio: 16 / 9;
            margin-bottom: 20px;
        }
        .video-wrapper iframe, .video-wrapper video {
            width: 100%;
            height: 100%;
        }
        .video-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .video-meta {
            display: flex;
            gap: 20px;
            color: #94a3b8;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #334155;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #334155;
            color: white;
            padding: 8px 16px;
            border-radius: 30px;
            text-decoration: none;
            margin-bottom: 20px;
        }
        .related-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }
        .related-card {
            background: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            text-decoration: none;
            color: white;
            transition: transform 0.2s;
        }
        .related-card:hover {
            transform: translateY(-4px);
        }
        .related-thumb {
            aspect-ratio: 16 / 9;
            background: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }
        .related-info {
            padding: 10px;
        }
        .related-title-sm {
            font-size: 14px;
            font-weight: 500;
        }
        @media (max-width: 768px) {
            .video-title { font-size: 18px; }
            .related-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
        }
    </style>
</head>
<body>
    <div class="player-container">
        <a href="{{ url()->previous() }}" class="back-btn">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
        
        <div class="video-wrapper">
            @if($video->tipe == 'upload')
                <video controls autoplay>
                    <source src="{{ asset($video->file_path) }}" type="video/mp4">
                    Browser Anda tidak mendukung video tag.
                </video>
            @else
                <iframe src="{{ $video->url_embed }}" frameborder="0" allowfullscreen></iframe>
            @endif
        </div>
        
        <h1 class="video-title">{{ $video->judul }}</h1>
        <div class="video-meta">
            <span><i class="fa fa-tag"></i> {{ ucfirst($video->tipe) }}</span>
            @if($video->durasi)
                <span><i class="fa fa-clock"></i> {{ gmdate('i:s', $video->durasi) }}</span>
            @endif
            <span><i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($video->created_at)->format('d M Y') }}</span>
        </div>
        
        @if($video->deskripsi)
            <div style="margin-bottom: 30px; line-height: 1.6; color: #cbd5e1;">
                {{ $video->deskripsi }}
            </div>
        @endif
        
        @if($relatedVideos->count() > 0)
            <div class="related-title">Video Terkait</div>
            <div class="related-grid">
                @foreach($relatedVideos as $rv)
                    <a href="{{ route('video.player', $rv->id_video) }}" class="related-card">
                        <div class="related-thumb">
                            @if($rv->tipe == 'youtube')
                                <i class="fab fa-youtube fa-2x text-danger"></i>
                            @elseif($rv->tipe == 'upload')
                                <i class="fa fa-video fa-2x"></i>
                            @else
                                <i class="fa fa-link fa-2x"></i>
                            @endif
                        </div>
                        <div class="related-info">
                            <div class="related-title-sm">{{ Str::limit($rv->judul, 50) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>