<h1>Selamat Datang, Siswa!</h1>
<p>Ini adalah halaman khusus Siswa.</p>
<form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>
