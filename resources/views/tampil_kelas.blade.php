<!DOCTYPE html>
<html>
<head>
    <title>Data Kelas - Dev Kinanti</title>
    <!-- Kita pakai CSS Bootstrap biar tabelnya langsung bagus -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

    <div class="container">
        <h2 class="mb-4">Daftar Kelas (Dari Database Office)</h2>
        
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama Kelas</th>
                            <th>Status</th>
                            <th>Dibuat Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Ini cara Laravel melakukan Loop (Pengulangan) -->
                        @foreach($data_kelas as $k)
                        <tr>
                            <td>{{ $k->id_kelas }}</td>
                            <td>{{ $k->nama_kelas }}</td>
                            <td>
                                @if($k->aktif == 1)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Non-Aktif</span>
                                @endif
                            </td>
                            <td>{{ $k->created_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>