@extends('layouts.admin_layout')

@section('title', 'Video Pembelajaran - Kinanti Art')

@section('content')
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                <i class="fa fa-video fa-2x text-success"></i>
            </div>
            <div>
                <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">
                    Video Pembelajaran
                </h1>
                <p class="text-muted small mb-0 mt-1">
                    Daftar video sesuai kelas
                </p>
            </div>
        </div>
    </div>

    <!-- Info -->
    <div class="info-card mb-4">
        <div class="info-icon"><i class="fa fa-info-circle"></i></div>
        <div class="info-content">
            Video hanya dapat ditonton. Hubungi admin jika ada perubahan.
        </div>
    </div>

    <!-- Table -->
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
                        <th>No</th>
                        <th>Urutan</th>
                        <th>Judul</th>
                        <th>Tipe</th>
                        <th>Lagu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($videoList as $index => $v)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>

                        <td class="text-center">
                            {{ $v->urutan }}
                        </td>

                        <td>
                            <strong>{{ $v->judul }}</strong>

                            @if($v->deskripsi)
                                <br>
                                <small class="text-muted">
                                    {{ \Illuminate\Support\Str::limit($v->deskripsi, 60) }}
                                </small>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($v->tipe == 'upload')
                                <span class="badge-upload">Upload</span>
                            @elseif($v->tipe == 'youtube')
                                <span class="badge-youtube">YouTube</span>
                            @elseif($v->tipe == 'vimeo')
                                <span class="badge-vimeo">Vimeo</span>
                            @elseif($v->tipe == 'googledrive')
                                <span class="badge-drive">Drive</span>
                            @else
                                <span class="badge-other">{{ ucfirst($v->tipe) }}</span>
                            @endif
                        </td>

                        <td class="text-center">
                            {{ $v->judul_lagu ?? '-' }}
                        </td>

                        <td class="text-center">
                            @if($v->status == 'aktif')
                                <span class="status-badge status-active">Aktif</span>
                            @else
                                <span class="status-badge status-inactive">Nonaktif</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <!-- 🔥 HANYA PLAY -->
                            <a href="{{ route('video.player', $v->id_video) }}"
                               class="btn-play" target="_blank">
                                <i class="fa fa-play"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fa fa-video fa-2x text-muted mb-2"></i>
                            <p>Belum ada video tersedia</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

</div>

<!-- STYLE (ikut admin style supaya seragam) -->
<style>
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

.total-badge {
    background: #dcfce7;
    color: #166534;
    padding: 6px 14px;
    border-radius: 20px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 14px;
    border-bottom: 1px solid #f0f0f0;
}

.data-table th {
    background: #f8fafc;
    font-size: 13px;
    color: #64748b;
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
}

.status-active {
    background: #dcfce7;
    color: #166534;
    padding: 4px 12px;
    border-radius: 20px;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
    padding: 4px 12px;
    border-radius: 20px;
}

.info-card {
    background: #e0f2fe;
    border-left: 4px solid #0284c7;
    border-radius: 12px;
    padding: 12px 16px;
    display: flex;
    gap: 10px;
}
</style>

@endsection