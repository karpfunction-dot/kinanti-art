# 28️⃣ REFACTOR: Modul Video & Video Inventaris

**Status:** 🔴 Not Started  
**Priority:** 🟠 MEDIUM  
**Duration:** ~0.5 day  
**Dependency:** [#22 Lagu](./22-REFACTOR-LAGU.md) (butuh Model Lagu)  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

Dua controller yang saling terkait: `VideoController` (streaming file lokal) dan `VideoInventarisController` (database inventaris + YouTube/embed). Hanya VideoInventaris yang butuh Model baru.

**File yang diubah:**
- `app/Http/Controllers/VideoController.php` (217 baris, 2x `DB::table()`)
- `app/Http/Controllers/VideoInventarisController.php` (216 baris, 5x `DB::table()`)

**File yang dibuat:**
- `app/Models/VideoInventaris.php`
- `app/Http/Requests/Video/VideoInventarisRequest.php`

---

## 📝 Implementation Checklist

### Langkah 1 — Buat Model `VideoInventaris`

**Buat file:** `app/Models/VideoInventaris.php`
- Table: `video_inventaris`, PK: `id_video`
- Fillable: `judul`, `deskripsi`, `id_lagu`, `id_kelas`, `tipe`, `url_embed`, `youtube_id`, `file_path`, `urutan`, `status`
- Relationships:
  - `belongsTo(Lagu::class, 'id_lagu', 'id_lagu')`
  - `belongsTo(Kelas::class, 'id_kelas', 'id_kelas')`

### Langkah 2 — Buat Form Request `VideoInventarisRequest`

**Buat file:** `app/Http/Requests/Video/VideoInventarisRequest.php`
- Rules (pindahkan dari `VideoInventarisController::store()`):
  - `judul` required|string|max:200
  - `deskripsi` nullable|string
  - `id_lagu` nullable|exists:lagu,id_lagu
  - `id_kelas` nullable|integer
  - `tipe` required|in:upload,youtube,vimeo,googledrive,other
  - `urutan` nullable|integer
  - `status` required|in:aktif,nonaktif
- Tambahkan conditional rules di method `rules()`:
  ```php
  if ($this->tipe == 'upload') {
      $rules['file_video'] = 'required|file|mimes:mp4|max:204800';
  } else {
      $rules['url_embed'] = 'required|string|max:500';
  }
  ```

### Langkah 3 — Refactor `VideoController`

**Buka file:** `app/Http/Controllers/VideoController.php`

1. Tambahkan: `use App\Models\Lagu;`
2. Ganti 2 instance `DB::table('lagu')` → `Lagu::where('status', 'aktif')->orderBy('judul_lagu')->get()`
3. Hapus `use Illuminate\Support\Facades\DB;`

> **Catatan:** Controller ini banyak logika file system (streaming, upload ke folder). Bagian itu TIDAK perlu diubah — hanya ganti query DB.

### Langkah 4 — Refactor `VideoInventarisController`

**Buka file:** `app/Http/Controllers/VideoInventarisController.php`

1. Tambahkan import:
   ```php
   use App\Models\VideoInventaris;
   use App\Models\Lagu;
   use App\Http\Requests\Video\VideoInventarisRequest;
   ```
2. Ganti semua `DB::table('video_inventaris')` → `VideoInventaris::`
3. Ganti semua `DB::table('lagu')` → `Lagu::`
4. Inject `VideoInventarisRequest` di method `store()`
5. Hapus `Validator::make(...)` di `store()`
6. Method `destroy()`: Ganti `DB::table()->where()->first()` → `VideoInventaris::find($id)`

---

## 🧪 Testing

```
1. /video → list video per lagu tampil
2. /video/upload → upload video mp4 → berhasil
3. /video/stream → video bisa di-play
4. /video/inventaris → list inventaris tampil
5. Tambah video inventaris (upload + youtube) → berhasil
6. Hapus video inventaris → file terhapus + record terhapus
7. /video/inventaris/player/{id} → player tampil
```

---

## ✅ Definition of Done

- [ ] Model `VideoInventaris` dibuat
- [ ] Form Request dibuat
- [ ] `VideoController` pakai Model Lagu
- [ ] `VideoInventarisController` pakai Eloquent
- [ ] Upload, streaming, dan CRUD tetap berfungsi

---

**Created:** May 4, 2026
