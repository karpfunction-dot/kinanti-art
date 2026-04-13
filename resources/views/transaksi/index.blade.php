@extends('layouts.admin_layout')

@section('title', 'Transaksi & Keuangan - Kinanti Art')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-5">
            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fa fa-money-bill-wave fa-2x text-success"></i>
                </div>
                <div>
                    <h1 style="color: #0f3b2c; font-weight: 700; font-size: 1.75rem; margin: 0;">Transaksi & Keuangan</h1>
                    <p class="text-muted small mb-0 mt-1">Catat SPP, Tabungan, dan Transaksi Lainnya</p>
                </div>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert-success-custom">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert-error-custom">
                    <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            <!-- Form Input Transaksi -->
            <div class="form-card">
                <div class="form-header">
                    <h5><i class="fa fa-plus-circle"></i> Input Transaksi</h5>
                </div>
                
                <form method="POST" action="{{ route('transaksi.store') }}" id="transaksiForm">
                    @csrf
                    
                    <div class="form-group">
                        <label>Jenis Transaksi <span class="required">*</span></label>
                        <select name="jenis" id="jenis_transaksi" class="form-control" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="SPP">SPP</option>
                            <option value="Tabungan">Tabungan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Siswa <span class="required">*</span></label>
                        <div style="position: relative;">
                            <input type="hidden" name="id_user" id="id_user">
                            <input type="text" id="search_user" class="form-control" placeholder="Ketik nama siswa..." autocomplete="off">
                            <div id="result_user" class="search-result"></div>
                        </div>
                    </div>
                    
                    <!-- Field SPP -->
                    <div id="field_spp" class="dynamic-field" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Bulan</label>
                                <select name="bulan" class="form-control">
                                    @foreach($bulanAngka as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tahun</label>
                                <input type="number" name="tahun" class="form-control" value="{{ date('Y') }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Field Tabungan -->
                    <div id="field_tabungan" class="dynamic-field" style="display: none;">
                        <div class="form-group">
                            <label>Jenis Tabungan</label>
                            <select name="jenis_tabungan" class="form-control">
                                <option value="Setor">Setor</option>
                                <option value="Tarik">Tarik</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Field Lainnya -->
                    <div id="field_lainnya" class="dynamic-field" style="display: none;">
                        <div class="form-group">
                            <label>Kategori</label>
                            <input type="text" name="kategori" class="form-control" placeholder="Misal: Ujian, Kostum, Lomba">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Total (Rp) <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fa fa-rupiah-sign"></i>
                            <input type="number" step="0.01" name="total" class="form-control" placeholder="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal Pembayaran <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fa fa-calendar"></i>
                            <input type="date" name="tanggal_pembayaran" id="tanggal_pembayaran" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-save">
                            <i class="fa fa-save"></i> Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-7">
            <!-- Filter Bulan -->
            <div class="filter-card mb-3">
                <form method="GET" action="{{ route('transaksi.index') }}" class="filter-form">
                    <div class="filter-group">
                        <label>Filter Bulan</label>
                        <select name="bulan" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Semua Bulan --</option>
                            @foreach($availableMonths as $month)
                                <option value="{{ $month->bulan }}" {{ $filterBulan == $month->bulan ? 'selected' : '' }}>
                                    {{ date('F Y', strtotime($month->bulan . '-01')) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if($filterBulan)
                        <div class="filter-group">
                            <a href="{{ route('transaksi.index') }}" class="btn-reset">Reset Filter</a>
                        </div>
                    @endif
                </form>
            </div>
            
            <!-- Statistik Ringkasan -->
            @if(!empty($statistik))
            <div class="stats-row mb-3">
                <div class="stats-scroll">
                    @foreach($statistik as $bulan => $stats)
                        <div class="stat-card {{ $filterBulan == $bulan ? 'active' : '' }}" 
                             onclick="window.location.href='{{ route('transaksi.index', ['bulan' => $bulan]) }}'">
                            <div class="stat-month">{{ date('M Y', strtotime($bulan . '-01')) }}</div>
                            <div class="stat-total">Rp {{ number_format($stats['total_semua'], 0, ',', '.') }}</div>
                            <div class="stat-detail">
                                <span>SPP: Rp {{ number_format($stats['total_spp'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            <!-- Tabel Riwayat -->
            <div class="table-card">
                <div class="table-header">
                    <div class="header-left">
                        <i class="fa fa-history"></i>
                        <h5>Riwayat Transaksi</h5>
                    </div>
                    <span class="total-badge">Total: {{ $transaksi->count() }} Transaksi</span>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th>Detail</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksi as $t)
                            <tr>
                                <td>{{ $t->id }}</td
                                <td>
                                    <div class="user-info">
                                        <div class="user-name">{{ $t->nama_lengkap }}</div>
                                        <div class="user-code">{{ $t->kode_barcode }}</div>
                                    </div>
                                </td
                                <td>
                                    <span class="badge {{ $t->jenis == 'SPP' ? 'badge-spp' : ($t->jenis == 'Tabungan' ? 'badge-tabungan' : 'badge-lainnya') }}">
                                        {{ $t->jenis }}
                                    </span>
                                </td
                                <td>{{ $t->detail }}</td
                                <td>{{ \Carbon\Carbon::parse($t->tanggal_pembayaran)->format('d/m/Y') }}</td
                                <td>
                                    <span class="total-amount">Rp {{ number_format($t->total, 0, ',', '.') }}</span>
                                </td
                                <td>
                                    <a href="{{ route('transaksi.destroy', ['sumber' => $t->sumber, 'id' => $t->id]) }}" 
                                       class="btn-delete"
                                       onclick="return confirm('Hapus transaksi ini?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td
                            </tr
                            @empty
                            <tr
                                <td colspan="7" class="text-center">Belum ada transaksi</td
                            </tr
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
    
    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 12px 16px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .filter-form {
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }
    .filter-group {
        flex: 1;
    }
    .filter-group label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .btn-reset {
        background: #e2e8f0;
        color: #334155;
        padding: 8px 16px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        display: inline-block;
    }
    
    .stats-row {
        overflow-x: auto;
        margin-bottom: 16px;
    }
    .stats-scroll {
        display: flex;
        gap: 12px;
        min-width: min-content;
    }
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 12px 16px;
        min-width: 140px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .stat-card.active {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
    }
    .stat-card.active .stat-detail {
        color: rgba(255,255,255,0.8);
    }
    .stat-month {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .stat-total {
        font-size: 18px;
        font-weight: 700;
    }
    .stat-detail {
        font-size: 10px;
        color: #64748b;
        margin-top: 6px;
    }
    
    .form-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }
    .form-header {
        padding: 15px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .form-header h5 { margin: 0; font-size: 15px; font-weight: 600; }
    
    .form-group {
        padding: 0 20px;
        margin-bottom: 16px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #334155;
    }
    .form-row {
        display: flex;
        gap: 15px;
    }
    .form-row .form-group {
        flex: 1;
        padding: 0;
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
    }
    .input-icon {
        position: relative;
    }
    .input-icon i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }
    .input-icon .form-control {
        padding-left: 35px;
    }
    .required { color: #dc2626; }
    
    .form-actions {
        padding: 16px 20px;
        background: #f8fafc;
        text-align: right;
    }
    .btn-save {
        background: linear-gradient(135deg, #0f3b2c 0%, #1a5d45 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .table-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .table-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header-left { display: flex; align-items: center; gap: 10px; }
    .header-left i { font-size: 18px; color: #1a5d45; }
    .header-left h5 { margin: 0; font-size: 15px; font-weight: 600; }
    .total-badge {
        background: #dcfce7;
        color: #166534;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table th, .data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }
    .data-table th {
        background: #f8fafc;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
    }
    .user-info .user-name {
        font-weight: 600;
        font-size: 14px;
    }
    .user-info .user-code {
        font-size: 11px;
        color: #94a3b8;
    }
    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-spp { background: #dbeafe; color: #1e40af; }
    .badge-tabungan { background: #fef3c7; color: #92400e; }
    .badge-lainnya { background: #e0e7ff; color: #3730a3; }
    .total-amount {
        font-weight: 600;
        color: #0f3b2c;
    }
    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .btn-delete:hover { background: #fecaca; }
    
    .search-result {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 100;
        display: none;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .search-item {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
    }
    .search-item:hover { background: #dcfce7; }
    
    .dynamic-field {
        animation: fadeIn 0.2s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 768px) {
        .form-row { flex-direction: column; gap: 0; }
        .stats-scroll { padding-bottom: 4px; }
        .stat-card { min-width: 120px; }
    }
</style>

<script>
    const jenisSelect = document.getElementById('jenis_transaksi');
    const fieldSpp = document.getElementById('field_spp');
    const fieldTabungan = document.getElementById('field_tabungan');
    const fieldLainnya = document.getElementById('field_lainnya');
    
    jenisSelect.addEventListener('change', function() {
        fieldSpp.style.display = 'none';
        fieldTabungan.style.display = 'none';
        fieldLainnya.style.display = 'none';
        
        if (this.value === 'SPP') fieldSpp.style.display = 'block';
        else if (this.value === 'Tabungan') fieldTabungan.style.display = 'block';
        else if (this.value === 'Lainnya') fieldLainnya.style.display = 'block';
    });
    
    // Search User
    const searchInput = document.getElementById('search_user');
    const resultBox = document.getElementById('result_user');
    const hiddenId = document.getElementById('id_user');
    
    searchInput.addEventListener('keyup', function() {
        const query = this.value.trim();
        if (query.length < 2) {
            resultBox.style.display = 'none';
            return;
        }
        
        fetch(`/transaksi/search-user?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                resultBox.innerHTML = '';
                data.forEach(user => {
                    const div = document.createElement('div');
                    div.className = 'search-item';
                    div.innerHTML = `<strong>${user.nama_lengkap}</strong><br><small>${user.kode_barcode}</small>`;
                    div.onclick = () => {
                        hiddenId.value = user.id_user;
                        searchInput.value = user.nama_lengkap;
                        resultBox.style.display = 'none';
                    };
                    resultBox.appendChild(div);
                });
                resultBox.style.display = 'block';
            });
    });
    
    document.addEventListener('click', function(e) {
        if (!resultBox.contains(e.target) && e.target !== searchInput) {
            resultBox.style.display = 'none';
        }
    });
</script>
@endsection