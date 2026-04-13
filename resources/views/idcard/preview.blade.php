<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>ID Card - {{ $nama }}</title>

<style>
/* 🌿 Layout Umum */
body {
  background:#e5e7eb;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  font-family:'Poppins',sans-serif;
  padding:20px;
  gap:20px;
}

/* 🌿 Kartu Dasar */
.idcard {
  position:relative;
  width:300px;
  height:460px;
  border-radius:18px;
  box-shadow:0 4px 12px rgba(0,0,0,0.2);
  overflow:hidden;
  background-color:#fff;
  margin:auto;
}

/* 🌿 Background depan & belakang */
.front { 
  background:url('{{ asset("assets/idcard/dpn-1.png") }}') no-repeat center/cover; 
}
.back { 
  background:url('{{ asset("assets/idcard/blk-1.png") }}') no-repeat center/cover; 
}

/* 🌿 Foto Profil */
.photo {
  position:absolute;
  top:80px;
  left:50%;
  transform:translateX(-50%);
  width:106px;
  height:106px;
  border-radius:50%;
  border:3px solid #fff;
  object-fit:cover;
  background:#fff;
}

/* 🌿 Informasi Nama & Role */
.info {
  position:absolute;
  top:205px;
  left:0;
  width:100%;
  text-align:center;
  color:#1b1b1b;
  font-size:15px;
  line-height:1.5;
}
.info strong {
  color:#166534;
}
.role {
  font-size:13px;
  color:#2563eb;
  font-weight:600;
}

/* 🌿 QR dan Barcode */
.qr {
  position:absolute;
  bottom:55px;
  left:50%;
  transform:translateX(-50%);
  width:110px;
  height:110px;
  background:#fff;
  padding:6px;
  border-radius:10px;
}
.barcode-text {
  position:absolute;
  bottom:25px;
  width:100%;
  text-align:center;
  color:#14532d;
  font-weight:600;
  font-size:14px;
  letter-spacing:1px;
}

/* 🌿 Tombol Cetak */
.btn-print {
  position:fixed;
  top:10px;
  right:20px;
  background:#22c55e;
  color:#fff;
  padding:8px 14px;
  border-radius:8px;
  border:none;
  font-size:14px;
  cursor:pointer;
  z-index:999;
  box-shadow:0 2px 6px rgba(0,0,0,0.2);
}
.btn-print:hover { background:#16a34a; }

/* 🌿 Mode Cetak */
@media print {
  body {
    background:none;
    padding:0;
    margin:0;
    gap:0;
    align-items:center;
  }
  .btn-print { display:none; }
  .idcard {
    box-shadow:none;
    page-break-after:always;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }
}
</style>
</head>
<body>

<!-- Tombol Cetak -->
<button class="btn-print" onclick="safePrint()">🖨️ Cetak</button>

<!-- 🌿 Kartu Depan -->
<div class="idcard front">
  <img src="{{ $member->foto_profil ?? asset('assets/img/blank-profile.webp') }}" 
     class="profile-thumb" 
     alt="Foto">  <div class="info">
    <p><strong>{{ $nama }}</strong></p>
    <p class="role">{{ $role }}</p>
  </div>
  <img src="{{ $qrCodeUrl }}" alt="QR" class="qr">
  <div class="barcode-text">{{ $idcode }}</div>
</div>

<!-- 🌿 Kartu Belakang -->
<div class="idcard back"></div>

<script>
// ✅ Aman untuk print di HP dan desktop
function safePrint() {
  window.onafterprint = () => window.focus();
  window.print();
}
</script>

</body>
</html>
