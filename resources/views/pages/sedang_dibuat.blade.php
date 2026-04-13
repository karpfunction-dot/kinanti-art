@extends('layouts.admin_layout')

@section('content')
<div class="container text-center mt-5">
    <div class="card shadow p-5">
        <h1 class="display-4 text-warning"><i class="fa fa-person-digging"></i></h1>
        <h2 class="mt-3">Halaman {{ $judul }}</h2>
        <p class="lead">Fitur ini sedang dalam proses pemindahan dari sistem lama ke Laravel.</p>
        <hr>
        <a href="/dashboard" class="btn btn-success">Kembali ke Dashboard</a>
    </div>
</div>
@endsection