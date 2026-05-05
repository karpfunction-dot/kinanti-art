# 22️⃣ REFACTOR: Modul Lagu (REVISED - SYSTEM AWARE)

**Status:** 🔴 Not Started  
**Priority:** 🟡 HIGH  
**Duration:** ~0.5 – 1 day  
**Dependency:** #21 Auth selesai  
**Type:** Refactor + System Integration  
**Level:** 🧠 Intermediate → System Thinking  

---

## 🎯 TUJUAN

Refactor modul Lagu agar:
- Menggunakan Eloquent & FormRequest
- Terintegrasi dengan sistem (Kelas, Jadwal, Koreografi)
- Siap digunakan dalam alur latihan & perform

---

## 🧠 PERUBAHAN KONSEP (WAJIB DIPAHAMI)

### ❌ Sebelum:
Lagu hanya sebagai data statis (CRUD)

### ✅ Sesudah:
Lagu menjadi bagian dari sistem:


Lagu → Koreografi → Jadwal → Latihan → Absensi


---

## 📦 STRUKTUR DATA

### Tabel: `lagu`

Tambahkan / pastikan field berikut:

- judul_lagu
- pencipta
- lisensi
- status_lisensi
- status
- id_kelas
- link_lisensi

### 🔥 TAMBAHAN BARU (WAJIB)

```php
status_penggunaan ENUM:
- latihan
- lomba
- arsip
🔗 RELATIONSHIP (WAJIB)
Model: Lagu.php
class Lagu extends Model
{
    protected $table = 'lagu';
    protected $primaryKey = 'id_lagu';

    protected $fillable = [
        'judul_lagu', 'pencipta', 'lisensi',
        'status_lisensi', 'status',
        'status_penggunaan',
        'id_kelas', 'link_lisensi',
    ];

    // Relasi ke kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    // Relasi ke video
    public function videoInventaris()
    {
        return $this->hasMany(VideoInventaris::class, 'id_lagu', 'id_lagu');
    }

    // 🔥 Relasi ke jadwal (PENTING)
    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'id_lagu', 'id_lagu');
    }

    // 🔥 Relasi ke koreografi
    public function koreografi()
    {
        return $this->hasOne(Koreografi::class, 'id_lagu', 'id_lagu');
    }
}
📝 FORM REQUEST
LaguRequest.php
public function authorize(): bool
{
    return auth()->check() &&
           optional(auth()->user()->role)->nama_role === 'admin';
}

public function rules(): array
{
    return [
        'judul_lagu'     => 'required|string|max:150|unique:lagu,judul_lagu,' . $this->id,
        'pencipta'       => 'nullable|string|max:100',
        'lisensi'        => 'required|in:gratis,berbayar',
        'status_lisensi' => 'required|in:bebas,izin,internal',
        'status'         => 'required|in:aktif,nonaktif',
        'status_penggunaan' => 'required|in:latihan,lomba,arsip',
        'id_kelas'       => 'nullable|exists:kelas,id_kelas',
        'link_lisensi'   => 'nullable|url|max:255',
    ];
}
⚙️ CONTROLLER (REFACTOR)
LaguController.php
🔹 INDEX
$lagu = Lagu::with('kelas')
    ->orderBy('id_lagu', 'desc')
    ->get();
🔹 STORE
Lagu::create($request->validated());
🔹 UPDATE
$lagu = Lagu::findOrFail($id);
$lagu->update($request->validated());
🔹 DESTROY (🔥 UPGRADE LOGIC)
public function destroy($id)
{
    $lagu = Lagu::findOrFail($id);

    // 🔥 CEGAH HAPUS JIKA MASIH DIPAKAI
    if ($lagu->jadwal()->exists() || $lagu->koreografi()->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Lagu tidak bisa dihapus karena masih digunakan'
        ]);
    }

    $lagu->delete();

    return response()->json([
        'success' => true,
        'message' => 'Lagu berhasil dihapus'
    ]);
}