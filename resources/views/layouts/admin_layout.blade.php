<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Kinanti Art Productions')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fb;
            color: #1e293b;
            min-height: 100vh;
        }
        
        /* HEADER MODERN */
        .kinanti-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 28px;
            z-index: 1300;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        #menuToggle {
            border: none;
            background: rgba(255, 255, 255, 0.15);
            font-size: 22px;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #menuToggle:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.02);
        }
        
        .brand-text {
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: -0.3px;
            color: white;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-name {
            font-size: 0.9rem;
            font-weight: 500;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 30px;
        }
        
        /* Display user info safely */
        .user-info {
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
        
        /* MAIN CONTENT */
        .main-content {
            margin-top: 70px;
            padding: 28px 32px;
            flex: 1;
            margin-left: 0;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 70px);
        }
        
        @media (min-width: 768px) {
            .main-content {
                margin-left: 280px;
            }
        }
        
        /* FOOTER */
        .main-footer {
            text-align: center;
            background: transparent;
            color: #64748b;
            padding: 20px 28px;
            font-size: 0.8rem;
            border-top: 1px solid #e2e8f0;
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }
        
        @media (min-width: 768px) {
            .main-footer {
                margin-left: 280px;
            }
        }
    </style>
    
    @stack('css')
</head>
<body>

    <!-- HEADER -->
    <header class="kinanti-header">
        <div class="header-left">
            <button id="menuToggle" aria-label="Toggle sidebar">
                <i class="fa fa-bars"></i>
            </button>
            <span class="brand-text">Kinanti Art Productions</span>
        </div>
        <div class="header-right">
            <span class="user-name user-info">
                <i class="fa fa-user-circle me-2"></i>
                {{ Auth::user()->profil?->nama_lengkap ?? Auth::user()->kode_barcode ?? 'Admin' }}
                <small>({{ ucfirst(Auth::user()->role?->nama_role ?? 'Guest') }})</small>
            </span>
        </div>
    </header>

    <!-- INCLUDE SIDEBAR -->
    @include('layouts.sidebar_native')

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="main-footer">
        <p>© {{ date('Y') }} Kinanti Art Productions — Sistem Manajemen Sanggar Modern</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            
            if (menuToggle && sidebar && overlay) {
                // Toggle sidebar saat klik tombol menu
                menuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebar.classList.toggle('open');
                    overlay.classList.toggle('active');
                });
                
                // Tutup sidebar saat klik overlay
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                });
                
                // Tutup sidebar saat klik di luar (khusus mobile)
                document.body.addEventListener('click', function(e) {
                    const isMobile = window.innerWidth < 768;
                    if (isMobile && sidebar.classList.contains('open')) {
                        const isClickInsideSidebar = sidebar.contains(e.target);
                        const isClickOnToggle = menuToggle.contains(e.target);
                        if (!isClickInsideSidebar && !isClickOnToggle) {
                            sidebar.classList.remove('open');
                            overlay.classList.remove('active');
                        }
                    }
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>