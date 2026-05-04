# 23️⃣ REFACTOR: Modul Absensi

**Status:** 🔴 Not Started  
**Priority:** 🟡 HIGH  
**Duration:** ~0.5 day  
**Dependency:** [#21 Auth](./21-REFACTOR-AUTH.md) selesai  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

Core feature yang dipakai setiap hari. Controller punya 3 mode absensi: scanner form, scanner API, dan input massal. Buat Model `Absensi`, Form Request, lalu refactor controller.

**File yang diubah:**
- `app/Http/Controllers/AbsensiController.php` (333 baris, 10+ `DB::table()`)

**File yang dibuat:**
- `app/Models/Absensi.php`
- `app/Http/Requests/Absensi/AbsensiScanRequest.php`

---

## 📝 Implementation Checklist

### Langkah 1 — Buat Model `Absensi`

**Buat file:** `app/Models/Absensi.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $primaryKey = 'id_absensi';

    // Tabel hanya punya created_at, tidak ada updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'id_user', 'id_kelas', 'kode_barcode', 'tanggal', 'waktu',
        'status', 'kategori', 'lokasi', 'keterangan', 'status_absen',
    ];

    // --- RELATIONSHIPS ---

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }
}
```

**Validasi:**
- [ ] File ada di `app/Models/Absensi.php`
- [ ] `const UPDATED_AT = null;` sudah di-set
- [ ] Relationships ke `User` dan `Kelas` sudah benar

---

### Langkah 2 — Buat Form Request `AbsensiScanRequest`

**Buat file:** `app/Http/Requests/Absensi/AbsensiScanRequest.php`

```php
<?php

namespace App\Http\Requests\Absensi;

use Illuminate\Foundation\Http\FormRequest;

class AbsensiScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = strtolower(auth()->user()->role->nama_role ?? '');
        return in_array($role, ['admin', 'pelatih', 'manajemen']);
    }

    public function rules(): array
    {
        return [
            'kode_barcode' => 'required|string|min:3|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_barcode.required' => 'Kode barcode harus diisi',
            'kode_barcode.min'      => 'Kode barcode minimal 3 karakter',
        ];
    }
}
```

**Validasi:**
- [ ] `authorize()` cek role admin/pelatih/manajemen

---

### Langkah 3 — Refactor `AbsensiController`

**Buka file:** `app/Http/Controllers/AbsensiController.php`

1. **Tambahkan import:**
   ```php
   use App\Models\Absensi;
   use App\Models\User;
   use App\Models\Kelas;
   use App\Models\KelasSiswa; // dibuat di issue #25, gunakan DB::table dulu jika belum ada
   use App\Http\Requests\Absensi\AbsensiScanRequest;
   ```

2. **Method `index()`** — ganti semua `DB::table('absensi as a')` dengan Eloquent query:
   ```php
   $query = Absensi::query()
       ->leftJoin('users as u', 'absensi.id_user', '=', 'u.id_user')
       ->leftJoin('profil_anggota as p', 'p.id_user', '=', 'absensi.id_user')
       ->leftJoin('roles as r', 'u.id_role', '=', 'r.id_role')
       ->select(
           'absensi.id_absensi', 'absensi.tanggal', 'absensi.waktu',
           'absensi.status', 'absensi.kategori',
           'p.nama_lengkap', 'p.foto_profil', 'u.kode_barcode',
           DB::raw('COALESCE(r.nama_role, "Member") AS role_name')
       );
   ```
   > **Catatan:** Untuk query kompleks dengan join, boleh tetap pakai `DB::raw()` di dalam select. Yang penting base query pakai Model, bukan `DB::table()`.

   Ganti juga `DB::table('roles')` → `Role::select('nama_role')->get()`

3. **Method `proses()`:**
   - Ganti parameter: `Request $request` → `AbsensiScanRequest $request`
   - Hapus `$request->validate([...])`
   - Hapus pengecekan role manual (sudah di `authorize()`)
   - Ganti `DB::table('absensi')->where(...)->exists()` → `Absensi::where(...)->exists()`
   - Ganti `DB::table('absensi')->insert([...])` → `Absensi::create([...])`

4. **Method `prosesApi()`:**
   - Ganti parameter: `Request $request` → `AbsensiScanRequest $request`
   - Hapus `$request->validate(...)` dan pengecekan role
   - Ganti `DB::table('absensi')` → `Absensi::`

5. **Method `pilihKelas()`:**
   - Ganti `DB::table('kelas')` → `Kelas::where('aktif', 1)->get()`

6. **Method `inputKelas()`:**
   - Ganti `DB::table('kelas')->where(...)` → `Kelas::where(...)->first()`
   - Ganti `DB::table('absensi')->where(...)` → `Absensi::where(...)`
   - `DB::table('kelas_siswa')` boleh tetap pakai `DB::table()` dulu jika Model `KelasSiswa` belum dibuat (issue #25)

7. **Method `storeMassal()`:**
   - Ganti `DB::table('absensi')->updateOrInsert(...)` → `Absensi::updateOrCreate(...)`

8. **Method `destroy()`:**
   - Ganti `DB::table('absensi')->where(...)->delete()` → `Absensi::findOrFail($id)->delete()`

**Validasi:**
- [ ] Semua `DB::table('absensi')` sudah diganti ke `Absensi::`
- [ ] `DB::table('kelas')` sudah diganti ke `Kelas::`
- [ ] Form Request sudah di-inject di method `proses()` dan `prosesApi()`

---

## 🧪 Testing

```
1. Buka /absensi (login admin) → data riwayat tampil
2. Buka /absensi/scan → scan barcode → absensi tercatat
3. Buka /absensi/scan → scan barcode yang sama → tampil warning "sudah absen"
4. Buka /absensi/pilih-kelas → pilih kelas → input massal → simpan
5. Hapus data absensi → berhasil
```

---

## ✅ Definition of Done

- [ ] Model `Absensi` dibuat dengan relationships
- [ ] `AbsensiScanRequest` dibuat
- [ ] Controller refactored ke Eloquent
- [ ] Scanner (form + API) tetap berfungsi
- [ ] Input massal tetap berfungsi
- [ ] Hapus absensi tetap berfungsi

---

**Created:** May 4, 2026
