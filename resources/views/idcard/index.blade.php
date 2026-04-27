@extends('layouts.admin_layout')

@section('title', 'ID Card - Kinanti Art')

@section('content')
<div class="container-fluid py-4">

    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold">ID Card Anggota</h2>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header d-flex justify-content-between">
            <h5>Daftar Anggota</h5>
            <span>Total: {{ $members->count() }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Foto</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Barcode</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($members as $member)
                    @php
                        $foto = \App\Support\PhotoUrl::resolve($member->foto_profil);
                    @endphp

                    <tr>
                        <td class="text-center">
                            <img src="{{ $foto }}" 
                                 class="profile-thumb"
                                 onerror="this.src='{{ asset('assets/img/blank-profile.webp') }}'">
                        </td>

                        <td>{{ $member->nama_lengkap ?? '-' }}</td>
                        <td>{{ ucfirst($member->nama_role ?? '-') }}</td>
                        <td><code>{{ $member->kode_barcode ?? '-' }}</code></td>

                        <td class="text-center">
                            <a href="{{ route('idcard.preview', $member->id_user) }}" 
                               target="_blank" 
                               class="btn btn-primary btn-sm">
                                Lihat / Cetak
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
                </tbody>

            </table>
        </div>
    </div>

</div>

<style>
.profile-thumb{
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #1a5d45;
}
</style>
@endsection