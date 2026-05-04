# 06️⃣ MODULE: Hero Slider Management

**Status:** 🔴 Not Started  
**Priority:** 🔴 MEDIUM (Homepage display)  
**Duration:** ~0.5 day  
**Assignee:** @[dev-3]  
**Dependency:** ✅ [#00-SETUP](./00-SETUP-AND-PREPARATION.md)  
**Pattern:** Follow [#01-MODULE-ARTICLE](./01-MODULE-ARTICLE.md) structure

---

## 📋 Deskripsi

CRUD untuk Hero Slider / Banner homepage dengan support responsive image (desktop/mobile), text overlay, CTA button, ordering, dan active/inactive flag.

---

## ✅ Acceptance Criteria

- [ ] CRUD lengkap
- [ ] Upload image desktop (1920x600) & mobile (480x600) via Cloudinary
- [ ] Responsive <picture> tag rendering
- [ ] CTA button text & link input
- [ ] Order management untuk slide sequence
- [ ] Active/inactive toggle
- [ ] Homepage carousel displays active slides in order
- [ ] Only admin can CRUD
- [ ] Test: 80%

---

## 📝 Quick Implementation

### 1. Controller
- index: table, sortable by order, active/inactive badge
- create/store: upload 2 images (desktop + mobile), save text, button config
- edit/update: modify slider
- destroy: delete images dari Cloudinary + delete row
- Custom action: updateOrder (AJAX) untuk drag-drop

### 2. Form Requests
- Title: required, string
- Subtitle: nullable, string
- Button_text: nullable, string
- Button_link: nullable, url
- Image_desktop: required|nullable, image, max 2MB
- Image_mobile: required|nullable, image, max 2MB
- Order: integer

### 3. Views
- index: table dengan order col (drag handle), thumbnail preview, order, active badge
- create/edit: form dengan 2 file inputs (preview), text inputs
- Responsive grid view untuk preview both images

### 4. Tests
- Desktop & mobile image upload
- Order update (AJAX call)
- Active filter
- Image URLs generated correctly

---

## 📌 Kolom Tabel

```sql
id, title, subtitle, button_text, button_link, image_desktop, 
image_mobile, order, is_active, created_at, updated_at
```

---

## 🖼️ Frontend Integration (Homepage Template - FSD Phase 3)

```blade
<div id="heroCarousel" class="carousel slide">
    <div class="carousel-inner">
        @forelse ($slides as $slide)
            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                <picture>
                    <source media="(max-width: 768px)" srcset="{{ $slide->image_mobile }}">
                    <img src="{{ $slide->image_desktop }}" alt="{{ $slide->title }}" class="d-block w-100">
                </picture>
                @if ($slide->button_text)
                    <a href="{{ $slide->button_link }}" class="btn btn-primary">
                        {{ $slide->button_text }}
                    </a>
                @endif
            </div>
        @empty
            <div class="carousel-item active">
                <img src="/assets/default-hero.jpg" alt="Default">
            </div>
        @endforelse
    </div>
</div>
```

---

## 📌 Image Size Recommendation

- Desktop: 1920 x 600 px (16:5 aspect ratio)
- Mobile: 480 x 600 px (4:5 portrait bias)
- Cloudinary transform: `w_auto,q_auto,f_auto`

---

## 🎯 Definition of Done

- [ ] CRUD working
- [ ] Desktop & mobile image handling correct
- [ ] Order management (drag-drop AJAX) working
- [ ] Active/inactive filter accurate
- [ ] Homepage carousel displays slides correctly
- [ ] Responsive image tags rendered properly
- [ ] Cloudinary URLs generated
- [ ] Tests passed
- [ ] Merged to `develop`

---

**Created:** May 4, 2026
