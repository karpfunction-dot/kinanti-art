<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>ID Card - {{ $nama }}</title>

<style>
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

.idcard {
  position:relative;
  width:300px;
  height:460px;
  border-radius:18px;
  box-shadow:0 4px 12px rgba(0,0,0,0.2);
  overflow:hidden;
  background-color:#fff;
}

.front { 
  background:url('{{ asset("assets/idcard/dpn-1.png") }}') no-repeat center/cover; 
}

.back { 
  background:url('{{ asset("assets/idcard/blk-1.png") }}') no-repeat center/cover; 
}

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

.info {
  position:absolute;
  top:205px;
  width:100%;
  text-align:center;
}

.qr {
  position:absolute;
  bottom:55px;
  left:50%;
  transform:translateX(-50%);
  width:110px;
}

.barcode-text {
  position:absolute;
  bottom:25px;
  width:100%;
  text-align:center;
}

.btn-print {
  position:fixed;
  top:10px;
  right:20px;
  background:#22c55e;
  color:#fff;
  padding:8px 14px;
  border-radius:8px;
  border:none;
  cursor:pointer;
}

@media print {
  .btn-print { display:none; }
}
</style>
</head>

<body>

<button class="btn-print" onclick="window.print()">Cetak</button>

@php
    $foto = \App\Support\PhotoUrl::resolve($member->foto_profil);
@endphp

<div class="idcard front">

<img 
    src="{{ $foto }}"
    class="photo"
    onerror="this.src='{{ asset('assets/img/blank-profile.webp') }}'"
>

<div class="info">
    <strong>{{ $nama }}</strong><br>
    {{ $role }}
</div>

<img src="{{ $qrCodeUrl }}" class="qr">
<div class="barcode-text">{{ $idcode }}</div>

</div>

<div class="idcard back"></div>

</body>
</html>