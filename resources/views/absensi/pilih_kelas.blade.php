@extends('layouts.admin_layout')

@section('content')
<div class="container py-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold" style="color: #0f3b2c;">Pilih Kelas Latihan</h3>
        <p class="text-muted">Pilih kelas untuk melakukan absensi manual hari ini</p>
    </div>

    <div class="row g-3">
        @forelse($kelas as $k)
        <div class="col-12 col-md-6">
            <a href="{{ route('absensi.kelas', $k->id_kelas) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; border-left: 6px solid #16a34a !important;">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fa fa-users text-success fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0 fw-bold text-dark">{{ $k->nama_kelas }}</h5>
                            <small class="text-muted">Klik untuk input kehadiran</small>
                        </div>
                        <i class="fa fa-chevron-right text-muted"></i>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">Tidak ada kelas aktif.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection