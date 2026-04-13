<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dev Kinanti</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (Ikon) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { min-height: 100vh; display: flex; flex-direction: column; }
        .wrapper { display: flex; flex: 1; }
        .sidebar { min-width: 250px; max-width: 250px; background: #212529; color: white; min-height: 100vh; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #0d6efd; color: white; }
        .content { flex: 1; padding: 20px; background: #f8f9fa; }
        .sidebar-header { padding: 20px; font-weight: bold; border-bottom: 1px solid #495057; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- SIDEBAR MENU -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-masks-theater me-2"></i> KINANTI ART
        </div>
        
        <div class="mt-3">
            <a href="/dashboard" class="{{ Request::is('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge me-2"></i> Dashboard
            </a>
            <a href="/kelas" class="{{ Request::is('kelas') ? 'active' : '' }}">
                <i class="fa-solid fa-chalkboard-user me-2"></i> Data Kelas
            </a>
            <a href="#" class="">
                <i class="fa-solid fa-users me-2"></i> Data Siswa
            </a>
            
            <hr class="text-secondary mx-3">

            <!-- TOMBOL LOGOUT -->
            <a href="/logout" class="text-danger">
                <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
            </a>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="content">
        <!-- Header Kecil -->
        <nav class="navbar navbar-light bg-white shadow-sm mb-4 rounded px-3">
            <span class="navbar-brand mb-0 h1 fs-6">Sistem Informasi Sanggar (Dev)</span>
            <div class="ms-auto">
                Halo, <strong>{{ Auth::user()->kode_barcode ?? 'Admin' }}</strong>
            </div>
        </nav>

        <!-- Area Isi Halaman Berubah-ubah di sini -->
        @yield('content')
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>