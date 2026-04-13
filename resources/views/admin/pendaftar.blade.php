@extends('layouts.admin_layout')

@section('title', 'Manajemen Pendaftar - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="rounded-circle bg-success bg-opacity-10 p-3">
            <i class="fa fa-user-plus fa-2x text-success"></i>
        </div>
        <div>
            <h1 class="mb-0" style="color: #0f3b2c;">Manajemen Pendaftar</h1>
            <p class="text-muted">Setujui atau tolak pendaftaran anggota baru</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Tanggal Daftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendaftar as $p)
                        <tr>
                            <td>{{ $p->id }}</td>
                            <td>{{ $p->nama_lengkap }}</td>
                            <td>{{ $p->email }}</td>
                            <td>{{ $p->telepon ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</td>
                            <td>
                                @if($p->status == 'pending')
                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                @elseif($p->status == 'approved')
                                    <span class="badge bg-success">Disetujui</span>
                                @else
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                @if($p->status == 'pending')
                                    <form action="{{ route('admin.pendaftar.approve', $p->id) }}" method="POST" style="display:inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui pendaftar ini? Kode barcode akan otomatis dibuat.')">
                                            <i class="fa fa-check"></i> Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.pendaftar.reject', $p->id) }}" method="POST" style="display:inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tolak pendaftar ini?')">
                                            <i class="fa fa-times"></i> Tolak
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">Belum ada pendaftar</td
                        @endforelse
                    </tbody>
                </table
            </div>
        </div>
    </div>
</div>
@endsection