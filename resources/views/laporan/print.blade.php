<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Print Laporan Absensi - {{ $bulanText }}</title>
    <style>
        /* Copy style yang sama dari pdf.blade.php */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10pt; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0f3b2c; padding-bottom: 10px; }
        .header h1 { color: #0f3b2c; font-size: 18pt; }
        .stats { display: flex; gap: 15px; margin: 20px 0; }
        .stat-card { flex: 1; padding: 10px; border-radius: 8px; text-align: center; }
        .stat-card.total { background: #0f3b2c; color: white; }
        .stat-card.hadir { background: #dcfce7; color: #166534; }
        .stat-card.izin { background: #fef9c3; color: #854d0e; }
        .stat-card.alfa { background: #fee2e2; color: #991b1b; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #0f3b2c; color: white; padding: 8px; text-align: left; }
        td { padding: 6px; border-bottom: 1px solid #ddd; }
        .footer { margin-top: 20px; text-align: center; font-size: 8pt; color: #666; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 20px; background: #0f3b2c; color: white; border: none; border-radius: 8px; cursor: pointer;">
            🖨️ Cetak Laporan
        </button>
        <button onclick="window.close()" style="padding: 8px 20px; background: #666; color: white; border: none; border-radius: 8px; cursor: pointer; margin-left: 10px;">
            ❌ Tutup
        </button>
    </div>
    
    <div class="header">
        <h1>KINANTI ART PRODUCTIONS</h1>
        <h3>Laporan Kehadiran Anggota</h3>
        <p>Periode: {{ $bulanText }}</p>
    </div>
    
    <div class="stats">
        <div class="stat-card total"><h2>{{ $statistics['total'] }}</h2><small>Total</small></div>
        <div class="stat-card hadir"><h2>{{ $statistics['hadir'] }}</h2><small>Hadir</small></div>
        <div class="stat-card izin"><h2>{{ $statistics['izin'] }}</h2><small>Izin</small></div>
        <div class="stat-card alfa"><h2>{{ $statistics['alfa'] }}</h2><small>Alfa</small></div>
    </div>
    
    <table>
        <thead>
            <tr><th>#</th><th>Nama</th><th>Kode</th><th>Peran</th><th>Tanggal</th><th>Waktu</th><th>Status</th><th>Lokasi</th></tr>
        </thead>
        <tbody>
            @forelse($rows as $no => $row)
            <tr>
                <td>{{ $no+1 }}</td>
                <td>{{ $row->nama_lengkap ?? '-' }}</td>
                <td>{{ $row->kode_barcode ?? '-' }}</td>
                <td>{{ ucfirst($row->role_name ?? '-') }}</td>
                <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $row->waktu ?? '-' }}</td>
                <td>{{ $row->status ?? '-' }}</td>
                <td>{{ $row->lokasi ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer"><p>&copy; {{ date('Y') }} Kinanti Art Productions</p></div>
</body>
</html>