# 29️⃣ REFACTOR: Modul Transaksi & Keuangan

**Status:** 🔴 Not Started  
**Priority:** 🟠 MEDIUM  
**Duration:** ~1.5 day  
**Dependency:** Phase 2 selesai (butuh Model User, Kelas, Lagu, Koreografi)  
**Related Master:** [#20-MASTER-BACKEND-REFACTOR](./20-MASTER-BACKEND-REFACTOR.md)

---

## 📋 Deskripsi

**Controller terbesar di project (829 baris!).** Menangani 3 jenis transaksi (SPP, Tabungan, Lainnya), laporan keuangan, dan accounting settings. Butuh 4 model baru.

**File yang diubah:**
- `app/Http/Controllers/TransaksiController.php` (829 baris, 50+ `DB::table()`)
- `app/Http/Controllers/AccountingSettingController.php` (208 baris, 8x `DB::table()`)

**File yang dibuat:**
- `app/Models/TransaksiSpp.php`
- `app/Models/TransaksiTabungan.php`
- `app/Models/TransaksiLainnya.php`
- `app/Models/AccountingSetting.php`
- `app/Http/Requests/Transaksi/TransaksiRequest.php`
- `app/Http/Requests/Transaksi/AccountingSettingRequest.php`

---

## 📝 Implementation Checklist

### Langkah 1 — Buat Model `TransaksiSpp`

**Buat file:** `app/Models/TransaksiSpp.php`
- Table: `transaksi_spp`, PK: `id_transaksi_spp`
- Fillable: `id_user`, `periode`, `tanggal_pembayaran`, `tanggal_rekap`, `total`, `keterangan`
- Relationship: `belongsTo(User::class, 'id_user', 'id_user')`

### Langkah 2 — Buat Model `TransaksiTabungan`

**Buat file:** `app/Models/TransaksiTabungan.php`
- Table: `transaksi_tabungan`, PK: `id_transaksi_tabungan`
- Fillable: `id_user`, `jenis`, `tanggal_pembayaran`, `tanggal_rekap`, `total`, `keterangan`
- Relationship: `belongsTo(User::class, 'id_user', 'id_user')`

### Langkah 3 — Buat Model `TransaksiLainnya`

**Buat file:** `app/Models/TransaksiLainnya.php`
- Table: `transaksi_lainnya`, PK: `id_transaksi_lainnya`
- Fillable: `id_user`, `kategori`, `tanggal_pembayaran`, `tanggal_rekap`, `total`, `keterangan`
- Relationship: `belongsTo(User::class, 'id_user', 'id_user')`

### Langkah 4 — Buat Model `AccountingSetting`

**Buat file:** `app/Models/AccountingSetting.php`
- Table: `accounting_setting`
- Fillable: `tahun_bulan`, `omset_manual`, `operasional_manual`, `pelatih_percent`, `admin_percent`, `manajemen_keuangan_percent`, `manajemen_sapras_percent`, `koreo_default_percent`, `transport_nominal`, `max_pertemuan`

### Langkah 5 — Buat Form Requests

**`app/Http/Requests/Transaksi/TransaksiRequest.php`:**
- Rules dari `TransaksiController::store()`:
  - `jenis` required|in:SPP,Tabungan,Lainnya
  - `id_user` required|exists:users,id_user
  - `total` required|numeric|min:0
  - `tanggal_pembayaran` required|date
  - `keterangan` nullable|string
- Tambahkan conditional rules di method `withValidator()` untuk field khusus per jenis:
  - SPP: `bulan` required, `tahun` required
  - Tabungan: `jenis_tabungan` required|in:Setor,Tarik
  - Lainnya: `kategori` required|string

**`app/Http/Requests/Transaksi/AccountingSettingRequest.php`:**
- Rules dari `AccountingSettingController::save()`

### Langkah 6 — Refactor `TransaksiController`

> ⚠️ **PERHATIAN:** Controller ini sangat besar. Kerjakan method per method, test setelah setiap perubahan.

1. Tambahkan import semua model baru
2. **Method `index()`:**
   - Ganti `DB::table('transaksi_spp')` → `TransaksiSpp::`
   - Ganti `DB::table('transaksi_tabungan')` → `TransaksiTabungan::`
   - Ganti `DB::table('transaksi_lainnya')` → `TransaksiLainnya::`
   - Query UNION tetap boleh pakai `->select(DB::raw(...))` karena Eloquent mendukungnya
3. **Method `store()`:**
   - Inject `TransaksiRequest`
   - Ganti `DB::table('transaksi_spp')->insert(...)` → `TransaksiSpp::create(...)`
   - Sama untuk Tabungan dan Lainnya
4. **Method `destroy()`:**
   - ⚠️ Saat ini menerima `$sumber` (nama tabel) sebagai parameter: `DB::table($sumber)->where('id', $id)->delete()`
   - **Refactor menjadi:**
   ```php
   public function destroy($sumber, $id)
   {
       $model = match($sumber) {
           'transaksi_spp'      => TransaksiSpp::class,
           'transaksi_tabungan' => TransaksiTabungan::class,
           'transaksi_lainnya'  => TransaksiLainnya::class,
           default              => null,
       };
       if (!$model) { return redirect()->back()->with('error', 'Sumber tidak valid'); }
       $model::findOrFail($id)->delete();
   }
   ```
5. **Method `searchUser()`:** ganti query pakai `User::` + `ProfilAnggota::`
6. **Method `laporanKeuangan()`, `laporan()`, `getStats()`, dll:**
   - Ganti semua `DB::table(...)` → Model masing-masing
   - Query agregat (`->sum()`, `->count()`) tetap didukung Eloquent

### Langkah 7 — Refactor `AccountingSettingController`

1. Ganti semua `DB::table('accounting_setting')` → `AccountingSetting::`
2. Ganti `DB::table('transaksi_spp')` → `TransaksiSpp::`
3. Ganti `DB::table('accounting_koreografi')` → `Koreografi::` (dari issue #27)
4. Inject `AccountingSettingRequest` di method `save()`

---

## 🧪 Testing

```
1. /transaksi → list semua transaksi tampil
2. Tambah transaksi SPP → berhasil
3. Tambah transaksi Tabungan (Setor/Tarik) → berhasil
4. Tambah transaksi Lainnya → berhasil
5. Hapus transaksi → berhasil
6. /transaksi/laporan → rekap SPP, tabungan, detail per siswa
7. /transaksi/laporan-keuangan → laporan keuangan global
8. /accounting/setting → setting tampil, simpan berhasil
9. /accounting/payroll → payroll tampil dengan perhitungan honor
10. /transaksi-saya (login siswa) → siswa lihat transaksi sendiri
```

---

## ✅ Definition of Done

- [ ] 4 model baru dibuat (TransaksiSpp, TransaksiTabungan, TransaksiLainnya, AccountingSetting)
- [ ] 2 Form Request dibuat
- [ ] `TransaksiController` (829 baris) refactored — tidak ada `DB::table()` tersisa
- [ ] `AccountingSettingController` refactored
- [ ] Semua laporan keuangan tetap berfungsi
- [ ] Transaksi siswa read-only tetap berfungsi

---

**Created:** May 4, 2026
