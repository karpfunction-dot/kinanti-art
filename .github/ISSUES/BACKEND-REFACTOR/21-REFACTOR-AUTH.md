# 21️⃣ REFACTOR: Auth & Register

**Status:** 🔴 Not Started  
**Priority:** 🔴 CRITICAL (Security Fix)  
**Duration:** ~0.5 day  
**Assignee:** @[dev]  
**Dependency:** Tidak ada  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

Perbaiki modul autentikasi: hapus kompatibilitas password plain text, buat Model `Pendaftar`, dan pindahkan validasi ke Form Request.

**File yang diubah:**
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/RegisterController.php`

**File yang dibuat:**
- `app/Models/Pendaftar.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Requests/Auth/RegisterRequest.php`

---

## 📝 Implementation Checklist

### Langkah 1 — Buat Model `Pendaftar`

**Buat file:** `app/Models/Pendaftar.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendaftar extends Model
{
    protected $table = 'pendaftar';

    // Cek tabel: jika hanya ada created_at tanpa updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'nama_lengkap', 'email', 'telepon', 'password',
        'alamat', 'tanggal_lahir', 'jenis_kelamin', 'status',
    ];

    protected $hidden = ['password'];
}
```

**Validasi:**
- [ ] File ada di `app/Models/Pendaftar.php`
- [ ] `$table` sesuai nama tabel di database
- [ ] `$fillable` mencakup semua kolom yang diinsert di `RegisterController`

---

### Langkah 2 — Buat Form Request `LoginRequest`

**Buat file:** `app/Http/Requests/Auth/LoginRequest.php`

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // halaman publik
    }

    public function rules(): array
    {
        return [
            'kode_barcode' => 'required|string',
            'password'     => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_barcode.required' => 'Kode barcode harus diisi',
            'password.required'     => 'Password harus diisi',
            'password.min'          => 'Password minimal 6 karakter',
        ];
    }
}
```

**Validasi:**
- [ ] File ada di `app/Http/Requests/Auth/LoginRequest.php`

---

### Langkah 3 — Buat Form Request `RegisterRequest`

**Buat file:** `app/Http/Requests/Auth/RegisterRequest.php`

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap'  => 'required|string|max:100',
            'email'         => 'required|email|unique:pendaftar,email',
            'telepon'       => 'nullable|string|max:20',
            'password'      => 'required|string|min:6|confirmed',
            'alamat'        => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
        ];
    }
}
```

**Validasi:**
- [ ] File ada di `app/Http/Requests/Auth/RegisterRequest.php`

---

### Langkah 4 — Hapus plain text password di `AuthController`

**Buka file:** `app/Http/Controllers/AuthController.php`

**Cari method `isValidPassword()` (sekitar baris 56–75). Ganti SELURUH method dengan:**

```php
protected function isValidPassword(string $plainPassword, User $user): bool
{
    return Hash::check($plainPassword, $user->password);
}
```

**Yang dihapus:** blok `hash_equals($storedPassword, $plainPassword)` dan `password_get_info()`.

**Validasi:**
- [ ] Method `isValidPassword` hanya berisi 1 baris: `return Hash::check(...)`
- [ ] Tidak ada lagi referensi ke `hash_equals` atau `password_get_info`

---

### Langkah 5 — Inject `LoginRequest` di `AuthController`

**Buka file:** `app/Http/Controllers/AuthController.php`

1. Tambahkan di atas file: `use App\Http\Requests\Auth\LoginRequest;`
2. Ubah method `processLogin`:
   - Dari: `public function processLogin(Request $request)`
   - Ke: `public function processLogin(LoginRequest $request)`
3. **Hapus** blok `$request->validate([...])` di dalam method (baris 24–27) — Form Request sudah handle.

**Validasi:**
- [ ] `use` statement ditambahkan
- [ ] Parameter method sudah `LoginRequest`
- [ ] Tidak ada `$request->validate()` lagi di method

---

### Langkah 6 — Refactor `RegisterController` pakai Model

**Buka file:** `app/Http/Controllers/RegisterController.php`

1. Tambahkan import:
   ```php
   use App\Models\Pendaftar;
   use App\Models\User;
   use App\Models\ProfilAnggota;
   use App\Http\Requests\Auth\RegisterRequest;
   ```

2. **Method `register()`:**
   - Ganti parameter: `Request $request` → `RegisterRequest $request`
   - Hapus blok `Validator::make(...)` dan `if ($validator->fails())`
   - Ganti `DB::table('pendaftar')->insert([...])` → `Pendaftar::create([...])`

3. **Method `listPendaftar()`:**
   - Ganti `DB::table('pendaftar')->orderBy(...)` → `Pendaftar::orderBy(...)`

4. **Method `approve()`:**
   - Ganti `DB::table('pendaftar')->where('id', $id)->first()` → `Pendaftar::find($id)`
   - Ganti `DB::table('users')->insertGetId([...])` → `User::create([...])->id_user`
   - Ganti `DB::table('profil_anggota')->insert([...])` → `ProfilAnggota::create([...])`
   - Ganti `DB::table('pendaftar')->where('id', $id)->update([...])` → `$pendaftar->update([...])`

5. **Method `reject()`:**
   - Ganti `DB::table('pendaftar')->where('id', $id)->update([...])` → `Pendaftar::where('id', $id)->update([...])`

**Validasi:**
- [ ] Tidak ada `DB::table(...)` yang tersisa di `RegisterController`
- [ ] Tidak ada `Validator::make(...)` yang tersisa
- [ ] Semua import ada

---

## 🧪 Testing

```bash
# Test login masih berfungsi
1. Buka /login
2. Login dengan kode_barcode dan password yang benar → harus redirect ke dashboard
3. Login dengan password salah → harus tampil pesan error

# Test register masih berfungsi
1. Buka /register
2. Isi form → submit → harus redirect ke login dengan pesan sukses
3. Coba submit dengan email duplikat → harus tampil error

# Test approve pendaftar
1. Login sebagai admin
2. Buka /admin/pendaftar
3. Klik approve → harus berhasil generate kode barcode
```

---

## ✅ Definition of Done

- [ ] Plain text password compatibility **dihapus**
- [ ] Model `Pendaftar` dibuat
- [ ] `LoginRequest` dan `RegisterRequest` dibuat
- [ ] `AuthController` dan `RegisterController` pakai Model + Form Request
- [ ] Login, register, approve, reject tetap berfungsi normal
- [ ] Tidak ada `DB::table()` tersisa di kedua controller

---

**Created:** May 4, 2026
