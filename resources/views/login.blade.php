<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Kinanti Art Productions</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Font - Inter (sama dengan admin_layout) -->
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
        /**background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 50%, #0f3b2c 100%);**/
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Decorative background elements */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 80%;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            pointer-events: none;
        }
        
        body::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -30%;
            width: 80%;
            height: 80%;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 50%;
            pointer-events: none;
        }
        
        /* Login Card */
        .login-card {
            background: white;
            border-radius: 32px;
            width: 100%;
            max-width: 440px;
            margin: 20px;
            position: relative;
            z-index: 1;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
            padding: 32px 28px;
            text-align: center;
            color: white;
        }
        
        .login-icon {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 32px;
        }
        
        .login-header h2 {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }
        
        .login-header p {
            font-size: 0.8rem;
            opacity: 0.8;
            margin: 0;
        }
        
        .login-body {
            padding: 32px 28px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 8px;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-label i {
            color: #1a5d45;
            width: 18px;
        }
        
        .input-group-custom {
            position: relative;
        }
        
        .input-group-custom i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
            z-index: 2;
        }
        
        .form-control-custom {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            background: #f8fafc;
        }
        
        .form-control-custom:focus {
            outline: none;
            border-color: #1a5d45;
            background: white;
            box-shadow: 0 0 0 3px rgba(26, 93, 69, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
            border: none;
            border-radius: 14px;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 93, 69, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert-custom {
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.85rem;
        }
        
        .alert-custom i {
            font-size: 1.2rem;
        }
        
        .alert-danger-custom {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }
        
        .alert-success-custom {
            background: #f0fdf4;
            border-left: 4px solid #16a34a;
            color: #166534;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 24px;
            font-size: 0.7rem;
            color: #94a3b8;
        }
        
        .footer-text a {
            color: #1a5d45;
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer-text a:hover {
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                margin: 16px;
            }
            
            .login-header {
                padding: 24px 20px;
            }
            
            .login-body {
                padding: 24px 20px;
            }
            
            .login-icon {
                width: 55px;
                height: 55px;
                font-size: 26px;
            }
            
            .login-header h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="login-icon">
                <i class="fa fa-masks-theater"></i>
            </div>
            <h2>Kinanti Art Productions</h2>
            <p>Sistem Manajemen Sanggar Tari</p>
        </div>
        
        <div class="login-body">
            <!-- Menampilkan Pesan Error jika Login Gagal -->
            @if(session('error'))
                <div class="alert-custom alert-danger-custom">
                    <i class="fa fa-exclamation-circle"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert-custom alert-success-custom">
                    <i class="fa fa-check-circle"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <form action="{{ url('/login-proses') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa fa-barcode"></i>
                        Kode Barcode / Username
                    </label>
                    <div class="input-group-custom">
                        <i class="fa fa-qrcode"></i>
                        <input type="text" 
                               name="kode_barcode" 
                               class="form-control-custom" 
                               placeholder="Contoh: MJ-001 atau KAP-001" 
                               required
                               autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fa fa-lock"></i>
                        Password
                    </label>
                    <div class="input-group-custom">
                        <i class="fa fa-key"></i>
                        <input type="password" 
                               name="password" 
                               class="form-control-custom" 
                               placeholder="Masukkan password" 
                               required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fa fa-arrow-right-to-bracket"></i>
                    MASUK
                </button>
            </form>
            
          
             <div class="footer-text">
                <i class="fa fa-copyright me-1"></i> {{ date('Y') }} Kinanti Art Productions<br>
                Sistem Informasi Sanggar Modern
                <br><br>
                <a href="{{ url('/register') }}">Belum punya akun? Daftar di sini</a>
            </div>
        </div>
    </div>

</body>
</html>
