@extends('layouts.admin_layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card bg-info text-white border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h4>Halo, {{ Auth::user()->profil->nama_lengkap }}!</h4>
                    <p class="mb-0">Kamu sudah hadir <strong>{{ $kehadiran_bulan_ini }} kali</strong> di bulan ini. Tetap semangat berlatih tari!</p>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-white fw-bold">Riwayat Kehadiran Terakhir</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($riwayat_absensi as $absen)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="fa fa-calendar-check text-success me-2"></i>
                                {{ \Carbon\Carbon::parse($absen->tanggal)->format('d M Y') }}
                            </div>
                            <span class="badge bg-success rounded-pill">{{ $absen->status }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection