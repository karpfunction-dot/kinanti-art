# 03️⃣ MODULE: Achievement Management (Prestasi)

**Status:** 🔴 Not Started  
**Priority:** 🔴 HIGH  
**Duration:** ~0.5 day  
**Assignee:** @[dev-2]  
**Dependency:** ✅ [#00-SETUP](./00-SETUP-AND-PREPARATION.md)  
**Pattern:** Follow [#01-MODULE-ARTICLE](./01-MODULE-ARTICLE.md) structure

---

## 📋 Deskripsi

CRUD untuk Achievement/Prestasi dengan fitur: title, year, description, icon (Font Awesome), image, ordering (drag-drop), featured flag untuk homepage.

---

## ✅ Acceptance Criteria

- [ ] CRUD lengkap
- [ ] Achievement image upload via Cloudinary
- [ ] Icon selection (Font Awesome classes)
- [ ] Year filter & sort by year DESC
- [ ] Drag-drop order dalam list (AJAX)
- [ ] Featured achievements highlight di homepage
- [ ] Cache invalidate on change
- [ ] Admin full access, Content Manager read-only (view only, no CRUD)
- [ ] Test: 80%

---

## 📝 Quick Implementation

### 1. Controller
- index: table dengan year filter, order column, featured badge
- create/store: year input, icon picker, image upload
- edit/update: modify achievement
- destroy: delete + cache flush
- **Custom action** (opsional): updateOrder (AJAX) untuk drag-drop reordering

### 2. Form Requests
- Year: required, integer, 1900-current year
- Title: required, string, unique
- Description: required, text
- Icon: nullable, string (validate Font Awesome class format)
- Image: nullable, image, max 2MB

### 3. Views
- index: grid atau table view achievements, year filter, order handle (drag-drop), edit/delete buttons
- create/edit: form dengan icon picker dropdown (pre-populated FA icons)
- Icon picker bisa dropdown atau modal dengan preview

### 4. Tests
- Year filter accurate
- Featured sorting correct
- Drag-drop order saved to DB
- Image upload working

---

## 📌 Kolom Tabel

```sql
id, title, year, description, icon_class, achievement_image, 
order, is_featured, created_at, updated_at
```

---

## 🛠️ Icon Picker Implementation (Simple)

```blade
<div class="mb-3">
    <label class="form-label">Icon</label>
    <select name="icon_class" class="form-select">
        <option value="">Tanpa Icon</option>
        <option value="fas fa-trophy">🏆 Trophy</option>
        <option value="fas fa-medal">🥇 Medal</option>
        <option value="fas fa-star">⭐ Star</option>
        <option value="fas fa-crown">👑 Crown</option>
        <!-- Tambah lebih banyak sesuai kebutuhan -->
    </select>
</div>
```

---

## 🎯 Definition of Done

- [ ] All CRUD working
- [ ] Year filter tested
- [ ] Drag-drop order (AJAX) working
- [ ] Featured achievements correct di homepage
- [ ] Image upload via Cloudinary
- [ ] Cache invalidate
- [ ] Authorization: admin full, content_manager read-only
- [ ] Tests passed
- [ ] Merged to `develop`

---

**Created:** May 4, 2026
