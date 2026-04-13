<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Evaluasi Absensi - {{ $bulanText }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 10pt; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0f3b2c; padding-bottom: 10px; }
        .header h1 { color: #0f3b2c; font-size: 16pt; }
        .header h3 { font-size: 11pt; font-weight: normal; }
        .info { margin: 15px 0; padding: 10px; background: #f5f5f5; border-radius: 5px; }
        .stats { display: flex; gap: 10px; margin: 15px 0; }
        .stat-card { flex: 1; padding: 8px; border-radius: 5px; text-align: center; }
        .stat-card.total { background: #0f3b2c; color: white; }
        .stat-card.hadir { background: #dcfce7; color: #166534; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #0f3b2c; color: white; padding: 8px; text-align: left; font-size: 9pt; }
        td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 9pt; }
        .text-center { text-align: center; }
        .footer { margin-top: 20px; text-align: center; font-size: 8pt; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
        .badge-success { background: #16a34a; color: white; padding: 2px 8px; border-radius: 12px; }
        .badge-warning { background: #ca8a04; color: white; padding: 2px 8px; border-radius: 12px; }
        .badge-danger { background: #dc2626; color: white; padding: 2px 8px; border-radius: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KINANTI ART PRODUCTIONS</h1>
        <h3>Laporan Evaluasi Kehadiran Anggota</h3>
        <p>Periode: {{ $bulanText }}</p>
        @if($kelas)
        <p>Kelas: {{ $kelas }}</p>
        @endif
    </div>
    
    <div class="stats">
        <div class="stat-card total"><strong>Total Hadir</strong><br>{{ $statistik['total_hadir'] }}</div>
        <div class="stat-card hadir"><strong>Total Siswa</strong><br>{{ $statistik['total_siswa'] }}</div>
    </div>
    
    <h4>Rekapitulasi Kehadiran Per Siswa</h4>
    <table>
        <thead>
            <tr><th>#</th><th>Nama Siswa</th><th>Kelas</th><th class="text-center">Hadir</th><th class="text-center">Persentase</th></tr>
        </thead>
        <tbody>
            @foreach($rekapSiswa as $no => $siswa)
            <tr>
                <td>{{ $no+1 }}</td>
                <td>{{ $siswa->nama_lengkap }}</td>
                <td>{{ $siswa->nama_kelas ?? '-' }}</td>
                <td class="text-center">{{ $siswa->total_hadir }}</td>
                <td class="text-center">{{ $siswa->total_hadir > 0 ? $siswa->total_hadir : 0 }}x</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>Dokumen dicetak dari Sistem Kinanti Art Productions | {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>