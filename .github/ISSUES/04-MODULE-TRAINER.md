# 04️⃣ MODULE: Trainer Management (Profil Pelatih)

**Status:** 🔴 Not Started  
**Priority:** 🔴 HIGH  
**Duration:** ~0.5 day  
**Assignee:** @[dev-2]  
**Dependency:** ✅ [#00-SETUP](./00-SETUP-AND-PREPARATION.md)  
**Pattern:** Follow [#01-MODULE-ARTICLE](./01-MODULE-ARTICLE.md) structure

---

## 📋 Deskripsi

CRUD untuk Trainer/Pelatih dengan profil lengkap: nama, role, bio, foto, social media links (Instagram, YouTube), active/inactive flag, ordering untuk homepage.

---

## ✅ Acceptance Criteria

- [ ] CRUD lengkap
- [ ] Photo upload via Cloudinary
- [ ] Social media links (Instagram handle, YouTube channel URL)
- [ ] Active/inactive toggle
- [ ] Order management (manual input atau drag-drop)
- [ ] List page dengan active/inactive filter
- [ ] Only admin can full CRUD, content_manager view-only
- [ ] Homepage menampilkan trainer aktif (up to 3 atau random)
- [ ] Test: 80%

---

## 📝 Quick Implementation

### 1. Controller
- index: table, filter by is_active, order ASC
- create/store: upload photo, save social media links
- edit/update: modify trainer profile
- destroy: delete (soft delete consideration)
- Bio bisa plain text atau simple formatting

### 2. Form Requests
- Name: required, unique, string
- Role: required, string (e.g., "Koreografer Utama", "Trainer", "Asisten")
- Bio: nullable, text
- Photo: nullable, image, max 2MB
- Instagram: nullable, string (just handle atau full URL)
- YouTube: nullable, string
- Order: integer, default 0
- Is_active: boolean

### 3. Views
- index: card/table view trainers, show photo thumbnail, role, is_active badge
- create/edit: form dengan all fields, photo preview
- Responsive grid (mobile: 1 col, tablet: 2, desktop: 3)

### 4. Tests
- Active filter accurate
- Photo upload working
- Social links stored correctly

---

## 📌 Kolom Tabel

```sql
id, name, role, bio, photo_url, instagram, youtube, order, 
is_active, created_at, updated_at
```

---

## 📌 Catatan

- Instagram: bisa input "@username" atau "https://instagram.com/username"
- YouTube: bisa input "https://youtube.com/@channel" atau channel name
- Validation bisa simple (string check) atau URL validation
- Homepage query: `Trainer::where('is_active', true)->orderBy('order')->limit(3)->get()`

---

## 🎯 Definition of Done

- [ ] CRUD working
- [ ] Photo upload via Cloudinary
- [ ] Active/inactive filter working
- [ ] Order logic correct
- [ ] Homepage integration ready (link dalam FSD frontend)
- [ ] Authorization: admin full, content_manager view-only
- [ ] Tests passed
- [ ] Merged to `develop`

---

**Created:** May 4, 2026
