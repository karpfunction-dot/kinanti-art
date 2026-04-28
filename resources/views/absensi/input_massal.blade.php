@extends('layouts.admin_layout')

@section('title', 'Input Kehadiran - ' . $kelas->nama_kelas)

@section('content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold text-dark mb-1">{{ $kelas->nama_kelas }}</h4>
                    <p class="text-muted mb-0"><i class="fa fa-calendar me-2"></i>{{ date('l, d F Y') }}</p>
                </div>
                <button type="button" onclick="setSemuaAlfa()" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    Set Semua Alpa
                </button>
            </div>
            
            <div class="mt-3">
                <input type="text" id="searchSiswa" class="form-control rounded-pill border-light bg-light" placeholder="Cari nama siswa...">
            </div>
        </div>

        <form action="{{ route('absensi.storeMassal') }}" method="POST" id="formAbsensi">
            @csrf
            <input type="hidden" name="id_kelas" value="{{ $kelas->id_kelas }}">
            
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="tableSiswa">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Siswa</th>
                            <th class="text-center">Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswas as $s)
                        @php $statusExisting = $absensi_hari_ini[$s->id_user] ?? null; @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    
                                    <div>
                                        <div class="fw-bold text-dark">{{ $s->nama_lengkap }}</div>
                                        <small class="text-muted">{{ $s->kode_barcode }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group shadow-none" role="group">
                                    <input type="radio" class="btn-check" name="status[{{ $s->id_user }}]" id="h{{ $s->id_user }}" value="Hadir" {{ $statusExisting == 'Hadir' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-success px-3" for="h{{ $s->id_user }}">H</label>

                                    <input type="radio" class="btn-check" name="status[{{ $s->id_user }}]" id="i{{ $s->id_user }}" value="Izin" {{ $statusExisting == 'Izin' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-warning px-3" for="i{{ $s->id_user }}">I</label>

                                    <input type="radio" class="btn-check" name="status[{{ $s->id_user }}]" id="s{{ $s->id_user }}" value="Sakit" {{ $statusExisting == 'Sakit' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-info px-3" for="s{{ $s->id_user }}">S</label>

                                    <input type="radio" class="btn-check" name="status[{{ $s->id_user }}]" id="a{{ $s->id_user }}" value="Alfa" {{ $statusExisting == 'Alfa' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger px-3" for="a{{ $s->id_user }}">A</label>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white p-4 border-0 text-end">
                <a href="{{ route('absensi.index') }}" class="btn btn-light rounded-pill px-4 me-2">Batal</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow">Simpan Kehadiran</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Fitur Search Cepat
    document.getElementById('searchSiswa').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableSiswa tbody tr');
        rows.forEach(row => {
            let name = row.querySelector('.fw-bold').innerText.toLowerCase();
            row.style.display = name.includes(val) ? '' : 'none';
        });
    });

    // Fitur Set Semua Alfa untuk yang kosong
    function setSemuaAlfa() {
        document.querySelectorAll('input[value="Alfa"]').forEach(radio => {
            let name = radio.getAttribute('name');
            // Hanya isi yang belum dipilih sama sekali
            if (!document.querySelector(`input[name="${name}"]:checked`)) {
                radio.checked = true;
            }
        });
    }

    // Validasi form sebelum submit
    document.getElementById('formAbsensi').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let rows = document.querySelectorAll('#tableSiswa tbody tr');
        let unselected = [];
        
        rows.forEach(row => {
            // Skip jika row tidak terlihat (tersembunyi oleh search)
            if (row.style.display === 'none') {
                return;
            }
            
            let studentName = row.querySelector('.fw-bold').innerText;
            let statusInputs = row.querySelectorAll('input[type="radio"]');
            let isChecked = Array.from(statusInputs).some(input => input.checked);
            
            if (!isChecked) {
                unselected.push(studentName);
            }
        });
        
        if (unselected.length > 0) {
            alert('❌ Silakan pilih status kehadiran untuk siswa berikut:\n\n' + unselected.join('\n'));
            return false;
        }
        
        // Jika validasi lulus, submit form
        this.submit();
    });
</script>

<style>
    .btn-check:checked + .btn-outline-success { background-color: #198754; color: white; }
    .btn-check:checked + .btn-outline-warning { background-color: #ffc107; color: black; }
    .btn-check:checked + .btn-outline-info { background-color: #0dcaf0; color: white; }
    .btn-check:checked + .btn-outline-danger { background-color: #dc3545; color: white; }
    .btn-group .btn { border-width: 2px; font-weight: bold; font-size: 14px; }
</style>
@endsection