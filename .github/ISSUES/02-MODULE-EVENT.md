# 02️⃣ MODULE: Event Management

**Status:** 🔴 Not Started  
**Priority:** 🔴 HIGH  
**Duration:** ~0.5 day  
**Assignee:** @[dev-1]  
**Dependency:** ✅ [#00-SETUP](./00-SETUP-AND-PREPARATION.md)  
**Pattern:** Follow [#01-MODULE-ARTICLE](./01-MODULE-ARTICLE.md) structure

---

## 📋 Deskripsi

CRUD untuk Event dengan fitur: create/update/delete, upload poster via Cloudinary, tanggal mulai-selesai, featured flag, filter upcoming/past, search by title.

---

## ✅ Acceptance Criteria

- [ ] CRUD lengkap (Create, Read list + detail, Update, Delete)
- [ ] Poster upload via Cloudinary (max 2MB)
- [ ] Date range validation (end_date >= start_date)
- [ ] Featured event flag untuk homepage highlight
- [ ] List page: pagination, search, filter upcoming/past
- [ ] Cache invalidation otomatis
- [ ] Authorization: Admin full, Content Manager create/edit/view
- [ ] Test coverage: 80%

---

## 📝 Quick Implementation Guide

### 1. Controller (`app/Http/Controllers/Landing/EventController.php`)
- index: list events paginated, filter by date range, search
- create/store: upload poster via CloudinaryService
- edit/update: modify event details
- destroy: soft delete + cache flush
- Scopes: upcoming(), past(), featured()

### 2. Form Requests
- `StoreEventRequest`: required (title, start_date, end_date, location), unique slug, date validation
- `UpdateEventRequest`: same as store

### 3. Blade Views
- `index`: table dengan upcoming events di atas, filter: status/date range
- `create/edit`: form dengan date pickers (HTML5 datetime-local), file upload, featured checkbox
- `show`: event detail + poster

### 4. Policy
- EventPolicy: admin full, content_manager bisa edit milik sendiri

### 5. Tests
- Test upcoming filter
- Test featured flag
- Test date validation (end_date harus >= start_date)
- Test poster upload

---

## 📌 Kolom Tabel (dari Setup)

```sql
id, title, slug, description, start_date, end_date, location, 
poster_image, is_featured, status, created_at, updated_at
```

---

## 🎯 Definition of Done

- [ ] CRUD semua working
- [ ] Upcoming/past filter accurate
- [ ] Featured events tampil di homepage (future: link ke public page)
- [ ] Cache invalidate on update
- [ ] Test passed 80%
- [ ] Merged to `develop`

---

**Created:** May 4, 2026
