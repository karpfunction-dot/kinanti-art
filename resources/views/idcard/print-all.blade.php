<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>ID Card Semua Anggota - Kinanti Art</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #e5e7eb;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-family: 'Poppins', sans-serif;
    padding: 20px;
}

/* Container Grid */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
    max-width: 1400px;
    margin: 0 auto;
}

/* Kartu Dasar */
.idcard {
    position: relative;
    width: 100%;
    max-width: 300px;
    height: 460px;
    border-radius: 18px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    background-color: #fff;
    margin: 0 auto;
    transition: transform 0.2s;
}

.idcard:hover {
    transform: translateY(-5px);
}

/* Background Depan */
.front { 
    background: url('{{ asset("assets/idcard/dpn-1.png") }}') no-repeat center/cover; 
    position: relative;
    width: 100%;
    height: 100%;
}

/* Foto Profil */
.photo {
    position: absolute;
    top: 80px;
    left: 50%;
    transform: translateX(-50%);
    width: 106px;
    height: 106px;
    border-radius: 50%;
    border: 3px solid #fff;
    object-fit: cover;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Informasi Nama & Role */
.info {
    position: absolute;
    top: 205px;
    left: 0;
    width: 100%;
    text-align: center;
    color: #1b1b1b;
    font-size: 14px;
    line-height: 1.4;
}

.info strong {
    color: #166534;
    font-size: 15px;
}

.role {
    font-size: 12px;
    color: #2563eb;
    font-weight: 600;
    margin-top: 4px;
}

/* QR Code */
.qr {
    position: absolute;
    bottom: 55px;
    left: 50%;
    transform: translateX(-50%);
    width: 110px;
    height: 110px;
    background: #fff;
    padding: 6px;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

/* Teks Barcode */
.barcode-text {
    position: absolute;
    bottom: 20px;
    width: 100%;
    text-align: center;
    color: #14532d;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 1px;
    font-family: monospace;
}

/* Tombol Cetak */
.print-controls {
    text-align: center;
    margin-bottom: 30px;
    position: sticky;
    top: 10px;
    z-index: 100;
    background: rgba(255, 255, 255, 0.95);
    padding: 12px 20px;
    border-radius: 50px;
    backdrop-filter: blur(4px);
    display: inline-block;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.btn-print {
    background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
    color: white;
    border: none;
    padding: 10px 28px;
    border-radius: 40px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    margin: 0 5px;
    transition: all 0.2s;
}

.btn-print:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(26, 93, 69, 0.3);
}

.btn-close {
    background: #6b7280;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 40px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    margin: 0 5px;
    transition: all 0.2s;
}

.btn-close:hover {
    background: #4b5563;
}

/* Header Info */
.print-header {
    text-align: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
}

.print-header h2 {
    color: #0f3b2c;
    font-size: 20px;
    font-weight: 700;
}

.print-header p {
    color: #64748b;
    font-size: 12px;
}

.print-header .total {
    background: #dcfce7;
    color: #166534;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    display: inline-block;
    margin-top: 8px;
}

/* Mode Cetak */
@media print {
    body {
        background: white;
        padding: 0;
        margin: 0;
    }
    
    .print-controls {
        display: none;
    }
    
    .idcard {
        box-shadow: none;
        border: 1px solid #ddd;
        page-break-inside: avoid;
        break-inside: avoid;
        margin: 0 auto 10px auto;
    }
    
    .cards-grid {
        display: block;
    }
    
    .print-header {
        margin-bottom: 15px;
    }
    
    .print-header h2 {
        font-size: 16px;
    }
}
</style>
</head>
<body>

<div class="print-controls">
    <button class="btn-print" onclick="window.print()">
        🖨️ Cetak Semua ID Card
    </button>
    <button class="btn-close" onclick="window.close()">
        ❌ Tutup
    </button>
</div>

<div class="print-header">
    <h2>KINANTI'S ART PRODUCTIONS</h2>
    <p>ID Card Seluruh Anggota Sanggar Tari</p>
    <span class="total">Total: {{ $members->count() }} Anggota</span>
</div>

<div class="cards-grid">
@foreach($members as $member)
@php
    // Cari foto profil
    $fotoPath = !empty($member->foto_profil)
    ? $member->foto_profil
    : asset('assets/img/blank-profile.webp');
    
    // QR Code URL
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=" . urlencode($member->kode_barcode);
    
    // Nama dan role
    $displayName = $member->nama_lengkap ?? 'Tanpa Nama';
    $displayRole = ucfirst($member->nama_role ?? 'Member');
    $displayBarcode = $member->kode_barcode ?? '---';
@endphp
<div class="idcard">
    <div class="front">
        <img src="{{ $fotoPath }}" alt="Foto" class="photo" onerror="this.src='{{ asset('assets/img/blank-profile.webp') }}'">
        <div class="info">
            <strong>{{ $displayName }}</strong>
            <div class="role">{{ $displayRole }}</div>
        </div>
        <img src="{{ $qrCodeUrl }}" alt="QR Code" class="qr">
        <div class="barcode-text">{{ $displayBarcode }}</div>
    </div>
</div>
@endforeach
</div>

<script>
    console.log('Total ID Card: {{ $members->count() }}');
</script>
</body>
</html>
