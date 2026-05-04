# 24️⃣ REFACTOR: Modul Jadwal

**Status:** 🔴 Not Started  
**Priority:** 🟡 HIGH  
**Duration:** ~0.5 day  
**Dependency:** [#21 Auth](./21-REFACTOR-AUTH.md) selesai  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

Mirip pola Lagu. Controller sudah punya helper `withTimestamps()` yang harus dihapus karena Eloquent handle otomatis.

**File yang diubah:**
- `app/Http/Controllers/JadwalController.php` (226 baris)

**File yang dibuat:**
- `app/Models/Jadwal.php`
- `app/Http/Requests/Jadwal/JadwalRequest.php`

---

## 📝 Implementation Checklist

### Langkah 1 — Buat Model `Jadwal`

**Buat file:** `app/Models/Jadwal.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'jadwal_dev';
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'hari', 'jam_mulai', 'jam_selesai', 'id_kelas',
        'id_pelatih', 'lokasi', 'kategori', 'keterangan', 'status',
    ];

    // --- RELATIONSHIPS ---

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function pelatih()
    {
        return $this->belongsTo(User::class, 'id_pelatih', 'id_user');
    }
}
```

**Validasi:**
- [ ] Table name `jadwal_dev` (bukan `jadwal` — sesuai database!)
- [ ] Relationships benar

---

### Langkah 2 — Buat Form Request `JadwalRequest`

**Buat file:** `app/Http/Requests/Jadwal/JadwalRequest.php`

```php
<?php

namespace App\Http\Requests\Jadwal;

use Illuminate\Foundation\Http\FormRequest;

class JadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return strtolower(auth()->user()->role->nama_role ?? '') === 'admin';
    }

    public function rules(): array
    {
        return [
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'id_kelas'    => 'nullable|exists:kelas,id_kelas',
            'id_pelatih'  => 'nullable|exists:users,id_user',
            'lokasi'      => 'nullable|string|max:100',
            'kategori'    => 'required|string|max:50',
            'keterangan'  => 'nullable|string',
            'status'      => 'required|in:aktif,nonaktif',
        ];
    }
}
```

---

### Langkah 3 — Refactor `JadwalController`

**Buka file:** `app/Http/Controllers/JadwalController.php`

1. **Tambahkan import:**
   ```php
   use App\Models\Jadwal;
   use App\Models\Kelas;
   use App\Http\Requests\Jadwal\JadwalRequest;
   ```
   **Hapus:** `use Illuminate\Support\Facades\Schema;` dan `use Illuminate\Support\Facades\Validator;`

2. **Hapus method `withTimestamps()`** (baris 24–35) — Eloquent otomatis handle timestamps.

3. **Method `index()`:**
   - Ganti `DB::table('kelas')` → `Kelas::where('aktif', 1)->orderBy(...)->get()`
   - Ganti `DB::table('wewenang as w')` → tetap `DB::table()` dulu (Model Wewenang dibuat di issue #30)
   - Ganti `DB::table('jadwal_dev as jd')` → `Jadwal::query()->leftJoin(...)`
   - Ganti `DB::table('kelas_siswa')` → tetap dulu (Model KelasSiswa dibuat di issue #25)

4. **Method `store()`:**
   - Ganti parameter: `Request $request` → `JadwalRequest $request`
   - Hapus pengecekan `canManage()` (sudah di `authorize()`)
   - Hapus `Validator::make(...)`
   - Ganti body:
   ```php
   public function store(JadwalRequest $request)
   {
       try {
           Jadwal::create($request->validated());
           return response()->json(['success' => true, 'message' => '✅ Jadwal baru berhasil ditambahkan']);
       } catch (\Exception $e) {
           return response()->json(['success' => false, 'message' => 'Gagal menambahkan: ' . $e->getMessage()], 500);
       }
   }
   ```

5. **Method `update()`:** — pola sama:
   ```php
   public function update(JadwalRequest $request, $id)
   {
       try {
           Jadwal::findOrFail($id)->update($request->validated());
           return response()->json(['success' => true, 'message' => '✏️ Jadwal berhasil diperbarui']);
       } catch (\Exception $e) {
           return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
       }
   }
   ```

6. **Method `destroy()`:**
   ```php
   public function destroy($id)
   {
       if (!$this->canManage()) {
           return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
       }
       try {
           Jadwal::findOrFail($id)->delete();
           return response()->json(['success' => true, 'message' => '🗑️ Jadwal berhasil dihapus']);
       } catch (\Exception $e) {
           return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
       }
   }
   ```

7. **Method `getJadwal()`:**
   ```php
   public function getJadwal($id)
   {
       if (!$this->canManage()) {
           return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
       }
       $jadwal = Jadwal::find($id);
       if ($jadwal) {
           return response()->json(['success' => true, 'data' => $jadwal]);
       }
       return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
   }
   ```

**Validasi:**
- [ ] Method `withTimestamps()` sudah dihapus
- [ ] Tidak ada `DB::table('jadwal_dev')` tersisa
- [ ] `Validator::make()` sudah dihapus

---

## 🧪 Testing

```
1. Buka /jadwal (login admin) → jadwal tampil
2. Tambah jadwal baru → berhasil
3. Edit jadwal → berhasil
4. Hapus jadwal → berhasil
5. Login sebagai siswa → hanya lihat jadwal kelas sendiri
```

---

## ✅ Definition of Done

- [ ] Model `Jadwal` dibuat
- [ ] `JadwalRequest` dibuat
- [ ] `withTimestamps()` dihapus
- [ ] Controller refactored
- [ ] CRUD jadwal tetap berfungsi untuk admin, pelatih, dan siswa

---

**Created:** May 4, 2026
