@extends('layouts.admin_layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card bg-info text-white border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h4>Halo, {{ Auth::user()->profil?->nama_lengkap ?? Auth::user()->kode_barcode ?? 'Siswa' }}!</h4>
                    <p class="mb-0">Kamu sudah hadir <strong>{{ $kehadiran_bulan_ini ?? 0 }} kali</strong> di bulan ini. Tetap semangat berlatih tari!</p>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-white fw-bold">Riwayat Kehadiran Terakhir</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($riwayat_absensi ?? [] as $absen)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="fa fa-calendar-check text-success me-2"></i>
                                {{ isset($absen->tanggal) ? \Carbon\Carbon::parse($absen->tanggal)->format('d M Y') : '-' }}
                            </div>
                            <span class="badge bg-success rounded-pill">{{ $absen->status ?? '-' }}</span>
                        </li>
                        @empty
                        <li class="list-group-item text-center py-3">
                            <span class="text-muted">Belum ada riwayat kehadiran</span>
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection