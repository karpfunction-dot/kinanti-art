# 22️⃣ REFACTOR: Modul Lagu

**Status:** 🔴 Not Started  
**Priority:** 🟡 HIGH  
**Duration:** ~0.5 day  
**Dependency:** [#21 Auth](./21-REFACTOR-AUTH.md) selesai  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

Modul paling sederhana — cocok untuk latihan pola refactor. Buat Model `Lagu`, Form Request, lalu refactor controller.

**File yang diubah:**
- `app/Http/Controllers/LaguController.php` (165 baris, 5x `DB::table()`)

**File yang dibuat:**
- `app/Models/Lagu.php`
- `app/Http/Requests/Lagu/LaguRequest.php`

---

## 📝 Implementation Checklist

### Langkah 1 — Buat Model `Lagu`

**Buat file:** `app/Models/Lagu.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lagu extends Model
{
    protected $table = 'lagu';
    protected $primaryKey = 'id_lagu';

    protected $fillable = [
        'judul_lagu', 'pencipta', 'lisensi',
        'status_lisensi', 'status', 'id_kelas', 'link_lisensi',
    ];

    // --- RELATIONSHIPS ---

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function videoInventaris()
    {
        return $this->hasMany(VideoInventaris::class, 'id_lagu', 'id_lagu');
    }
}
```

**Validasi:**
- [ ] File ada di `app/Models/Lagu.php`
- [ ] Primary key `id_lagu` sudah di-set
- [ ] Relationship ke `Kelas` sudah benar

---

### Langkah 2 — Buat Form Request `LaguRequest`

**Buat file:** `app/Http/Requests/Lagu/LaguRequest.php`

```php
<?php

namespace App\Http\Requests\Lagu;

use Illuminate\Foundation\Http\FormRequest;

class LaguRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role->nama_role === 'admin';
    }

    public function rules(): array
    {
        return [
            'judul_lagu'     => 'required|string|max:150',
            'pencipta'       => 'nullable|string|max:100',
            'lisensi'        => 'required|in:gratis,berbayar',
            'status_lisensi' => 'required|in:bebas,izin,internal',
            'status'         => 'required|in:aktif,nonaktif',
            'id_kelas'       => 'nullable|exists:kelas,id_kelas',
            'link_lisensi'   => 'nullable|url|max:255',
        ];
    }
}
```

**Validasi:**
- [ ] Rules sama persis dengan yang ada di controller saat ini
- [ ] `authorize()` cek role admin

---

### Langkah 3 — Refactor `LaguController`

**Buka file:** `app/Http/Controllers/LaguController.php`

1. **Tambahkan import:**
   ```php
   use App\Models\Lagu;
   use App\Models\Kelas;
   use App\Http\Requests\Lagu\LaguRequest;
   ```
   **Hapus:** `use Illuminate\Support\Facades\DB;` dan `use Illuminate\Support\Facades\Validator;`

2. **Method `index()`** — ganti isi menjadi:
   ```php
   public function index()
   {
       if (auth()->user()->role->nama_role !== 'admin') {
           return redirect()->route('dashboard')->with('error', 'Akses ditolak');
       }

       $lagu = Lagu::with('kelas')->orderBy('id_lagu', 'desc')->get();
       $kelas = Kelas::where('aktif', 1)->orderBy('nama_kelas')->get();

       return view('lagu.index', compact('lagu', 'kelas'));
   }
   ```
   > **Catatan:** View blade mungkin perlu penyesuaian karena field `nama_kelas` sekarang diakses via `$item->kelas->nama_kelas` bukan `$item->nama_kelas`. Periksa view-nya.

3. **Method `store()`** — ubah parameter dan isi:
   ```php
   public function store(LaguRequest $request)
   {
       try {
           Lagu::create($request->validated());

           return response()->json([
               'success' => true,
               'message' => '✅ Lagu berhasil ditambahkan'
           ]);
       } catch (\Exception $e) {
           return response()->json([
               'success' => false,
               'message' => 'Gagal menambahkan: ' . $e->getMessage()
           ]);
       }
   }
   ```
   **Hapus:** blok `Validator::make(...)` dan `DB::table('lagu')->insert([...])`

4. **Method `update()`** — ubah parameter dan isi:
   ```php
   public function update(LaguRequest $request, $id)
   {
       try {
           $lagu = Lagu::findOrFail($id);
           $lagu->update($request->validated());

           return response()->json([
               'success' => true,
               'message' => '✏️ Lagu berhasil diperbarui'
           ]);
       } catch (\Exception $e) {
           return response()->json([
               'success' => false,
               'message' => 'Gagal memperbarui: ' . $e->getMessage()
           ]);
       }
   }
   ```

5. **Method `destroy()`** — ganti:
   ```php
   public function destroy($id)
   {
       try {
           Lagu::findOrFail($id)->delete();

           return response()->json([
               'success' => true,
               'message' => '🗑️ Lagu berhasil dihapus'
           ]);
       } catch (\Exception $e) {
           return response()->json([
               'success' => false,
               'message' => 'Gagal menghapus: ' . $e->getMessage()
           ]);
       }
   }
   ```

6. **Method `getLagu()`** — ganti:
   ```php
   public function getLagu($id)
   {
       $lagu = Lagu::find($id);

       if ($lagu) {
           return response()->json(['success' => true, 'data' => $lagu]);
       }

       return response()->json(['success' => false, 'message' => 'Data tidak ditemukan']);
   }
   ```

**Validasi:**
- [ ] Tidak ada `DB::table(...)` tersisa
- [ ] Tidak ada `Validator::make(...)` tersisa
- [ ] Tidak ada import `DB` atau `Validator` tersisa

---

## 🧪 Testing

```
1. Buka /lagu (login sebagai admin)
2. Tambah lagu baru → pastikan muncul di list
3. Edit lagu → pastikan data berubah
4. Hapus lagu → pastikan hilang dari list
5. Klik lagu untuk edit via AJAX → pastikan data ter-load
```

---

## ✅ Definition of Done

- [ ] Model `Lagu` dibuat dengan relationships
- [ ] `LaguRequest` dibuat
- [ ] `LaguController` pakai Eloquent + Form Request
- [ ] Tidak ada `DB::table()` tersisa
- [ ] CRUD lagu tetap berfungsi normal

---

**Created:** May 4, 2026
