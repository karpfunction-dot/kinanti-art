<h1>Halo, Pelatih!</h1>
<p>Ini adalah jadwal mengajar Anda.</p>
<form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>
