# 05️⃣ MODULE: Activity Management (Kegiatan)

**Status:** 🔴 Not Started  
**Priority:** 🔴 HIGH  
**Duration:** ~0.5 day  
**Assignee:** @[dev-3]  
**Dependency:** ✅ [#00-SETUP](./00-SETUP-AND-PREPARATION.md)  
**Pattern:** Follow [#01-MODULE-ARTICLE](./01-MODULE-ARTICLE.md) structure

---

## 📋 Deskripsi

CRUD untuk Activity/Kegiatan (Pasanggiri/Ujian) dengan tipe kegiatan, tanggal, lokasi, deskripsi, dokumentasi (foto), dan link hasil (jika ada).

---

## ✅ Acceptance Criteria

- [ ] CRUD lengkap
- [ ] Activity type: enum (pasanggiri, ujian) dengan filter
- [ ] Date & location input
- [ ] Description & documentation image via Cloudinary
- [ ] Result link: optional (URL eksternal untuk nilai/hasil)
- [ ] List page: sortable by date (newest first), type filter
- [ ] Cache invalidate on change
- [ ] Admin full access, Content Manager create/edit/view
- [ ] Test: 80%

---

## 📝 Quick Implementation

### 1. Controller
- index: list activities, filter by activity_type, sort by date DESC, pagination
- create/store: select type, date input, upload documentation
- edit/update: modify activity
- destroy: delete + cache flush
- scopes: byType(), upcoming(), past()

### 2. Form Requests
- Title: required, string
- Activity_type: required, in:pasanggiri,ujian
- Date: required, date
- Location: nullable, string
- Description: nullable, text
- Image: nullable, image, max 2MB
- Result_link: nullable, url

### 3. Views
- index: table/card view, type badge (color: pasanggiri=blue, ujian=orange), date formatted
- create/edit: form dengan activity type radio, date picker
- show: activity detail, documentation image, result link

### 4. Tests
- Type filter accurate
- Date sorting correct
- Image upload working
- URL validation for result_link

---

## 📌 Kolom Tabel

```sql
id, activity_type, title, date, description, location, 
image_url, result_link, created_at, updated_at
```

---

## 📌 Catatan

- Pasanggiri: pertunjukan/performance
- Ujian: ujian kenaikan tingkat atau ujian akhir
- Result link: bisa link ke Google Drive (nilai), atau halaman pengumuman eksternal

---

## 🎯 Definition of Done

- [ ] CRUD working
- [ ] Type filter & sort by date tested
- [ ] Image upload via Cloudinary
- [ ] URL validation working
- [ ] Cache invalidate on change
- [ ] Tests passed
- [ ] Merged to `develop`

---

**Created:** May 4, 2026
