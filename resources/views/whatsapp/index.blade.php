@extends('layouts.admin_layout')

@section('title', 'Kirim WhatsApp - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="rounded-circle bg-success bg-opacity-10 p-3">
            <i class="fab fa-whatsapp fa-2x text-success"></i>
        </div>
        <div>
            <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Kirim WhatsApp</h1>
            <p class="text-muted small mb-0 mt-1">Kirim notifikasi ke anggota sanggar via WhatsApp</p>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i>
        <strong>Catatan:</strong> Untuk mengirim WhatsApp, pastikan nomor tujuan sudah terdaftar. 
        Saat ini fitur ini masih dalam mode demo (pesan akan tercatat di log).
    </div>

    <div id="alertContainer"></div>

    <!-- Tab Navigation -->
    <div class="tab-navigation mb-4">
        <button class="tab-btn active" onclick="showTab('akun')">
            <i class="fa fa-user-plus"></i> Informasi Akun
        </button>
        <button class="tab-btn" onclick="showTab('tunggakan')">
            <i class="fa fa-exclamation-triangle"></i> Tunggakan SPP
        </button>
        <button class="tab-btn" onclick="showTab('gaji')">
            <i class="fa fa-money-bill-wave"></i> Struk Gaji
        </button>
        <button class="tab-btn" onclick="showTab('kegiatan')">
            <i class="fa fa-calendar-alt"></i> Kegiatan
        </button>
        <button class="tab-btn" onclick="showTab('custom')">
            <i class="fa fa-pen"></i> Custom Pesan
        </button>
    </div>

    <!-- Tab Content -->
    <div class="whatsapp-card">
        <!-- Tab Informasi Akun -->
        <div id="tab-akun" class="tab-content active">
            <div class="form-group">
                <label>Nomor WhatsApp Tujuan</label>
                <input type="text" id="nomor_akun" class="form-control" placeholder="Contoh: 081234567890 atau 6281234567890">
                <small class="form-text">Masukkan nomor WhatsApp dengan kode area (62 atau 0)</small>
            </div>
            <div class="form-group">
                <label>Nama Penerima</label>
                <input type="text" id="nama_akun" class="form-control" placeholder="Nama siswa">
            </div>
            <div class="form-group">
                <label>Kode Barcode</label>
                <input type="text" id="kode_akun" class="form-control" placeholder="Kode barcode">
            </div>
            <div class="form-group">
                <label>Template Pesan</label>
                <textarea id="template_akun" class="form-control" rows="10" readonly style="background: #f8fafc; font-family: monospace;"></textarea>
            </div>
            <div class="form-actions">
                <button class="btn-send" onclick="sendMessage('akun')">
                    <i class="fab fa-whatsapp"></i> Kirim Pesan
                </button>
            </div>
        </div>

        <!-- Tab Tunggakan SPP -->
        <div id="tab-tunggakan" class="tab-content" style="display: none;">
            <div class="form-group">
                <label>Nomor WhatsApp Tujuan</label>
                <input type="text" id="nomor_tunggakan" class="form-control" placeholder="Contoh: 081234567890">
            </div>
            <div class="form-group">
                <label>Nama Penerima</label>
                <input type="text" id="nama_tunggakan" class="form-control" placeholder="Nama siswa">
            </div>
            <div class="form-group">
                <label>Periode Tunggakan</label>
                <input type="month" id="periode_tunggakan" class="form-control" value="{{ date('Y-m') }}">
            </div>
            <div class="form-group">
                <label>Total Tunggakan (Rp)</label>
                <input type="number" id="total_tunggakan" class="form-control" placeholder="Masukkan total tunggakan" value="150000">
            </div>
            <div class="form-group">
                <label>Template Pesan</label>
                <textarea id="template_tunggakan" class="form-control" rows="10" style="font-family: monospace;"></textarea>
            </div>
            <div class="form-actions">
                <button class="btn-send" onclick="sendMessage('tunggakan')">
                    <i class="fab fa-whatsapp"></i> Kirim Peringatan
                </button>
            </div>
        </div>

        <!-- Tab Struk Gaji -->
        <div id="tab-gaji" class="tab-content" style="display: none;">
            <div class="form-group">
                <label>Nomor WhatsApp Tujuan</label>
                <input type="text" id="nomor_gaji" class="form-control" placeholder="Contoh: 081234567890">
            </div>
            <div class="form-group">
                <label>Nama Pelatih</label>
                <input type="text" id="nama_gaji" class="form-control" placeholder="Nama pelatih">
            </div>
            <div class="form-group">
                <label>Periode Gaji</label>
                <input type="month" id="periode_gaji" class="form-control" value="{{ date('Y-m') }}">
            </div>
            <div class="form-group">
                <label>Total Honor (Rp)</label>
                <input type="number" id="total_gaji" class="form-control" placeholder="Total honor">
            </div>
            <div class="form-group">
                <label>Jumlah Koreografi</label>
                <input type="number" id="koreografi_gaji" class="form-control" value="0">
            </div>
            <div class="form-group">
                <label>Template Pesan</label>
                <textarea id="template_gaji" class="form-control" rows="10" style="font-family: monospace;"></textarea>
            </div>
            <div class="form-actions">
                <button class="btn-send" onclick="sendMessage('gaji')">
                    <i class="fab fa-whatsapp"></i> Kirim Struk Gaji
                </button>
            </div>
        </div>

        <!-- Tab Kegiatan -->
        <div id="tab-kegiatan" class="tab-content" style="display: none;">
            <div class="form-group">
                <label>Nomor WhatsApp Tujuan</label>
                <input type="text" id="nomor_kegiatan" class="form-control" placeholder="Contoh: 081234567890">
            </div>
            <div class="form-group">
                <label>Nama Penerima</label>
                <input type="text" id="nama_kegiatan" class="form-control" placeholder="Nama penerima">
            </div>
            <div class="form-group">
                <label>Nama Kegiatan</label>
                <select id="nama_kegiatan_preset" class="form-control">
                    <option value="Ujian">🎓 Ujian</option>
                    <option value="Pasanggiri">🏆 Pasanggiri</option>
                    <option value="Lisensi Lagu">🎵 Lisensi Lagu</option>
                    <option value="Koreografi Baru">💃 Koreografi Baru</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Kegiatan</label>
                <input type="date" id="tanggal_kegiatan" class="form-control">
            </div>
            <div class="form-group">
                <label>Waktu & Tempat</label>
                <div class="row">
                    <div class="col-md-6">
                        <input type="time" id="waktu_kegiatan" class="form-control" placeholder="Waktu">
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="tempat_kegiatan" class="form-control" placeholder="Tempat">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Template Pesan</label>
                <textarea id="template_kegiatan" class="form-control" rows="10" style="font-family: monospace;"></textarea>
            </div>
            <div class="form-actions">
                <button class="btn-send" onclick="sendMessage('kegiatan')">
                    <i class="fab fa-whatsapp"></i> Kirim Pengumuman
                </button>
            </div>
        </div>

        <!-- Tab Custom Pesan -->
        <div id="tab-custom" class="tab-content" style="display: none;">
            <div class="form-group">
                <label>Nomor WhatsApp Tujuan</label>
                <input type="text" id="nomor_custom" class="form-control" placeholder="Contoh: 081234567890">
            </div>
            <div class="form-group">
                <label>Pesan Custom</label>
                <textarea id="custom_pesan" class="form-control" rows="8" placeholder="Tulis pesan Anda di sini..."></textarea>
            </div>
            <div class="form-actions">
                <button class="btn-send" onclick="sendCustomMessage()">
                    <i class="fab fa-whatsapp"></i> Kirim Pesan
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .tab-navigation {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0;
    }
    .tab-btn {
        padding: 10px 20px;
        background: none;
        border: none;
        border-radius: 12px 12px 0 0;
        cursor: pointer;
        font-weight: 600;
        color: #64748b;
        transition: all 0.2s;
    }
    .tab-btn:hover {
        background: #f1f5f9;
        color: #0f3b2c;
    }
    .tab-btn.active {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
    }
    
    .whatsapp-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #334155;
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
    }
    .form-text {
        font-size: 11px;
        color: #64748b;
        margin-top: 4px;
        display: block;
    }
    
    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    .btn-send {
        background: linear-gradient(135deg, #075e54 0%, #128c7e 100%);
        color: white;
        border: none;
        padding: 10px 28px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(7, 94, 84, 0.3);
    }
    
    .alert-success-custom, .alert-error-custom {
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .alert-success-custom { background: #dcfce7; border-left: 4px solid #16a34a; color: #166534; }
    .alert-error-custom { background: #fee2e2; border-left: 4px solid #dc2626; color: #991b1b; }
    
    .alert-info {
        background: #e0f2fe;
        border-left: 4px solid #0284c7;
        padding: 12px 16px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #0c4a6e;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .tab-navigation { flex-wrap: wrap; }
        .tab-btn { flex: 1; text-align: center; font-size: 12px; padding: 8px 12px; }
        .form-actions { flex-direction: column; }
        .btn-send { width: 100%; text-align: center; }
    }
</style>

<script>
    var csrfToken = '{{ csrf_token() }}';
    
    function showAlert(message, type) {
        var alertContainer = document.getElementById('alertContainer');
        var alertClass = type === 'success' ? 'alert-success-custom' : 'alert-error-custom';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        alertContainer.innerHTML = '<div class="' + alertClass + '"><i class="fa ' + icon + '"></i><div>' + message + '</div></div>';
        setTimeout(function() { 
            alertContainer.innerHTML = ''; 
        }, 5000);
    }
    
    function showTab(tab) {
        var tabs = document.querySelectorAll('.tab-content');
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].style.display = 'none';
        }
        var btns = document.querySelectorAll('.tab-btn');
        for (var i = 0; i < btns.length; i++) {
            btns[i].classList.remove('active');
        }
        document.getElementById('tab-' + tab).style.display = 'block';
        event.target.classList.add('active');
        loadTemplate(tab);
    }
    
    function loadTemplate(type) {
        var params = {};
        
        if (type === 'tunggakan') {
            var periode = document.getElementById('periode_tunggakan');
            var total = document.getElementById('total_tunggakan');
            params = {
                nama: '@{{nama}}',
                periode: periode ? periode.value : '',
                total: total ? total.value : ''
            };
        } else if (type === 'gaji') {
            var periode = document.getElementById('periode_gaji');
            var koreografi = document.getElementById('koreografi_gaji');
            var total = document.getElementById('total_gaji');
            params = {
                nama: '@{{nama}}',
                periode: periode ? periode.value : '',
                koreografi: koreografi ? koreografi.value : '0',
                persen: '10',
                total: total ? total.value : ''
            };
        } else if (type === 'kegiatan') {
            var namaKegiatan = document.getElementById('nama_kegiatan_preset');
            var tanggal = document.getElementById('tanggal_kegiatan');
            var waktu = document.getElementById('waktu_kegiatan');
            var tempat = document.getElementById('tempat_kegiatan');
            params = {
                nama: '@{{nama}}',
                nama_kegiatan: namaKegiatan ? namaKegiatan.value : '',
                tanggal: tanggal ? tanggal.value : '',
                waktu: waktu ? waktu.value : '',
                tempat: tempat ? tempat.value : ''
            };
        }
        
        fetch('/whatsapp/template/' + type, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(params)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            var textarea = document.getElementById('template_' + type);
            if (textarea) textarea.value = data.template;
        })
        .catch(function(error) {
            console.error('Error loading template:', error);
        });
    }
    
    function sendMessage(type) {
        var nomorInput = document.getElementById('nomor_' + type);
        var templateTextarea = document.getElementById('template_' + type);
        
        if (!nomorInput || !templateTextarea) {
            showAlert('Form tidak lengkap', 'error');
            return;
        }
        
        var nomor = nomorInput.value;
        var pesan = templateTextarea.value;
        
        if (!nomor) {
            showAlert('Masukkan nomor WhatsApp tujuan', 'error');
            return;
        }
        
        // Replace variables based on type
        if (type === 'akun') {
            var namaEl = document.getElementById('nama_akun');
            var kodeEl = document.getElementById('kode_akun');
            var nama = namaEl ? namaEl.value : '';
            var kode = kodeEl ? kodeEl.value : '';
            pesan = pesan.replace(/@{{nama}}/g, nama || 'Siswa');
            pesan = pesan.replace(/@{{kode_barcode}}/g, kode || '-');
        } else if (type === 'tunggakan') {
            var namaEl = document.getElementById('nama_tunggakan');
            var periodeEl = document.getElementById('periode_tunggakan');
            var totalEl = document.getElementById('total_tunggakan');
            var nama = namaEl ? namaEl.value : '';
            var periode = periodeEl ? periodeEl.value : '';
            var total = totalEl ? totalEl.value : '';
            pesan = pesan.replace(/@{{nama}}/g, nama || 'Siswa');
            pesan = pesan.replace(/@{{periode}}/g, periode);
            pesan = pesan.replace(/@{{total}}/g, total);
        } else if (type === 'gaji') {
            var namaEl = document.getElementById('nama_gaji');
            var periodeEl = document.getElementById('periode_gaji');
            var koreografiEl = document.getElementById('koreografi_gaji');
            var totalEl = document.getElementById('total_gaji');
            var nama = namaEl ? namaEl.value : '';
            var periode = periodeEl ? periodeEl.value : '';
            var koreografi = koreografiEl ? koreografiEl.value : '0';
            var total = totalEl ? totalEl.value : '';
            pesan = pesan.replace(/@{{nama}}/g, nama || 'Pelatih');
            pesan = pesan.replace(/@{{periode}}/g, periode);
            pesan = pesan.replace(/@{{koreografi}}/g, koreografi);
            pesan = pesan.replace(/@{{persen}}/g, '10');
            pesan = pesan.replace(/@{{total}}/g, total);
        } else if (type === 'kegiatan') {
            var namaEl = document.getElementById('nama_kegiatan');
            var namaKegiatanEl = document.getElementById('nama_kegiatan_preset');
            var tanggalEl = document.getElementById('tanggal_kegiatan');
            var waktuEl = document.getElementById('waktu_kegiatan');
            var tempatEl = document.getElementById('tempat_kegiatan');
            var nama = namaEl ? namaEl.value : '';
            var namaKegiatan = namaKegiatanEl ? namaKegiatanEl.value : '';
            var tanggal = tanggalEl ? tanggalEl.value : '';
            var waktu = waktuEl ? waktuEl.value : '';
            var tempat = tempatEl ? tempatEl.value : '';
            pesan = pesan.replace(/@{{nama}}/g, nama || 'Anggota');
            pesan = pesan.replace(/@{{nama_kegiatan}}/g, namaKegiatan);
            pesan = pesan.replace(/@{{tanggal}}/g, tanggal);
            pesan = pesan.replace(/@{{waktu}}/g, waktu);
            pesan = pesan.replace(/@{{tempat}}/g, tempat);
        }
        
        fetch('/whatsapp/send', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ nomor: nomor, pesan: pesan })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            showAlert(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                nomorInput.value = '';
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            showAlert('Terjadi kesalahan: ' + error, 'error');
        });
    }
    
    function sendCustomMessage() {
        var nomor = document.getElementById('nomor_custom').value;
        var pesan = document.getElementById('custom_pesan').value;
        
        if (!nomor) {
            showAlert('Masukkan nomor WhatsApp tujuan', 'error');
            return;
        }
        
        if (!pesan) {
            showAlert('Tulis pesan terlebih dahulu', 'error');
            return;
        }
        
        fetch('/whatsapp/send', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ nomor: nomor, pesan: pesan })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            showAlert(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                document.getElementById('nomor_custom').value = '';
                document.getElementById('custom_pesan').value = '';
            }
        });
    }
    
    // Load initial template on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadTemplate('akun');
        
        // Add event listeners
        var periodeTunggakan = document.getElementById('periode_tunggakan');
        var totalTunggakan = document.getElementById('total_tunggakan');
        if (periodeTunggakan) periodeTunggakan.addEventListener('change', function() { loadTemplate('tunggakan'); });
        if (periodeTunggakan) periodeTunggakan.addEventListener('input', function() { loadTemplate('tunggakan'); });
        if (totalTunggakan) totalTunggakan.addEventListener('change', function() { loadTemplate('tunggakan'); });
        if (totalTunggakan) totalTunggakan.addEventListener('input', function() { loadTemplate('tunggakan'); });
        
        var periodeGaji = document.getElementById('periode_gaji');
        var totalGaji = document.getElementById('total_gaji');
        var koreografiGaji = document.getElementById('koreografi_gaji');
        if (periodeGaji) periodeGaji.addEventListener('change', function() { loadTemplate('gaji'); });
        if (periodeGaji) periodeGaji.addEventListener('input', function() { loadTemplate('gaji'); });
        if (totalGaji) totalGaji.addEventListener('change', function() { loadTemplate('gaji'); });
        if (totalGaji) totalGaji.addEventListener('input', function() { loadTemplate('gaji'); });
        if (koreografiGaji) koreografiGaji.addEventListener('change', function() { loadTemplate('gaji'); });
        if (koreografiGaji) koreografiGaji.addEventListener('input', function() { loadTemplate('gaji'); });
        
        var namaKegiatan = document.getElementById('nama_kegiatan_preset');
        var tanggalKegiatan = document.getElementById('tanggal_kegiatan');
        var waktuKegiatan = document.getElementById('waktu_kegiatan');
        var tempatKegiatan = document.getElementById('tempat_kegiatan');
        if (namaKegiatan) namaKegiatan.addEventListener('change', function() { loadTemplate('kegiatan'); });
        if (tanggalKegiatan) tanggalKegiatan.addEventListener('change', function() { loadTemplate('kegiatan'); });
        if (waktuKegiatan) waktuKegiatan.addEventListener('change', function() { loadTemplate('kegiatan'); });
        if (tempatKegiatan) tempatKegiatan.addEventListener('change', function() { loadTemplate('kegiatan'); });
    });
</script>
@endsection