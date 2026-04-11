<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Kinanti Art Productions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            /* background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%); */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-card {
            background: white;
            border-radius: 32px;
            width: 100%;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
            padding: 24px;
            text-align: center;
            color: white;
        }
        .register-header h2 {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .register-body {
            padding: 28px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 6px;
            display: block;
            color: #334155;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
        }
        .form-control:focus {
            outline: none;
            border-color: #1a5d45;
        }
        .btn-register {
            background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26,93,69,0.3);
        }
        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
        .alert {
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 16px;
        }
        .alert-danger { background: #fee2e2; color: #991b1b; border-left: 4px solid #dc2626; }
        .alert-success { background: #dcfce7; color: #166534; border-left: 4px solid #16a34a; }
        .required { color: #dc2626; }
        .row { display: flex; gap: 12px; flex-wrap: wrap; }
        .col { flex: 1; min-width: 120px; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <h2>Daftar Akun Baru</h2>
            <p>Isi formulir untuk menjadi anggota sanggar</p>
        </div>
        <div class="register-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.proses') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap') }}" required>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label>Telepon</label>
                    <input type="text" name="telepon" class="form-control" value="{{ old('telepon') }}">
                </div>
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password <span class="required">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}">
                    </div>
                    <div class="col">
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-control">
                            <option value="">Pilih</option>
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2">{{ old('alamat') }}</textarea>
                </div>
                <button type="submit" class="btn-register">Daftar</button>
            </form>
            <div class="footer-text">
                Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
            </div>
        </div>
    </div>
</body>
</html>