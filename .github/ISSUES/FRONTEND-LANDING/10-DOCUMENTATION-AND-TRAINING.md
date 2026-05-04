# 🔟 PHASE 5: Documentation & User Training (Post-Launch)

**Status:** 🔴 Not Started (After Phase 4 passes)  
**Priority:** 🟡 MEDIUM (Optional, can run post-launch)  
**Duration:** ~0.5 day  
**Assignee:** @[doc-team]  
**Dependency:** ✅ Validation (#09) must be passed  
**Related Master:** [#00-MASTER-ISSUE](./00-MASTER-ISSUE.md)

---

## 📋 Deskripsi

Persiapan dokumentasi teknis & user guide untuk tim content manager & admin yang mengelola landing page setelah launch. Termasuk: user guide, API documentation, troubleshooting guide, dan video tutorial (opsional).

---

## ✅ Acceptance Criteria

- [ ] User Guide untuk Content Manager (PDF + online)
- [ ] Admin Guide untuk Site Settings
- [ ] API Documentation (untuk future mobile app integration)
- [ ] Technical README updated
- [ ] FAQ created
- [ ] Troubleshooting guide
- [ ] Video tutorials recorded (opsional)
- [ ] Team training session conducted
- [ ] Documentation link shared di README

---

## 📝 Documentation to Create

### 1. User Guide: Content Manager (30 min)

**File:** `docs/USER_GUIDE_CONTENT_MANAGER.md`

```markdown
# User Guide: Manajemen Konten Landing Page

## Pendahuluan
Panduan ini untuk tim marketing & admin yang mengelola konten landing page Kinanti Art Productions.

## Login & Dashboard
1. Buka https://kinanti-art.local/login
2. Masukkan email & password
3. Klik "Login"
4. Di dashboard, lihat menu sidebar "CMS Landing"

## Manajemen Artikel

### Membuat Artikel Baru
1. Sidebar → "CMS Landing" → "Artikel"
2. Klik tombol "Artikel Baru" (biru)
3. Isi form:
   - **Judul**: Nama artikel (5-100 karakter)
   - **Slug**: Auto-generate dari judul (bisa diedit manual)
   - **Konten**: Tulis artikel dengan editor WYSIWYG (formatting text, bold, italic, dll)
   - **Ringkasan**: Excerpt yang tampil di list (max 200 karakter)
   - **Kategori**: Pilih (Berita / Pengumuman / Artikel)
   - **Gambar Unggulan**: Upload JPG/PNG (max 2MB)
   - **Status**: Draft (tidak publish) atau Published (langsung live)
   - **Tanggal Publikasi**: Pilih tanggal (hanya untuk published)
4. Klik "Buat Artikel"

**Catatan:**
- Jika status = "Draft", artikel hanya terlihat untuk admin (tidak publish)
- Jika status = "Published" tanpa tanggal, akan publish hari ini
- Gambar otomatis ter-compress di server

### Mengubah Artikel
1. Sidebar → "Artikel"
2. Cari artikel di tabel (bisa search atau filter)
3. Klik tombol edit (pensil kuning)
4. Ubah field sesuai kebutuhan
5. Klik "Perbarui Artikel"

**Catatan:**
- Hanya bisa edit artikel yang dibuat sendiri (bukan orang lain)
- Admin bisa edit semua artikel

### Menghapus Artikel
1. Sidebar → "Artikel"
2. Cari artikel
3. Klik tombol delete (tong sampah merah)
4. Confirm "Yakin ingin hapus?"
5. Artikel akan di-soft-delete (tetap di DB tapi tidak tampil)

**Catatan:**
- Hanya admin yang bisa delete (content manager tidak bisa)
- Jika perlu restore, hubungi admin

## Manajemen Event

### Membuat Event
1. Sidebar → "CMS Landing" → "Event"
2. Klik "Event Baru"
3. Isi form:
   - **Judul**: Nama event
   - **Deskripsi**: Detail event
   - **Tanggal Mulai & Selesai**: Pilih range tanggal
   - **Lokasi**: Tempat event
   - **Poster**: Upload gambar (max 2MB)
   - **Featured**: Centang jika ingin highlight di homepage
4. Klik "Buat Event"

**Tips:**
- Event yang belum dimulai (upcoming) otomatis tampil di homepage
- Event featured akan mendapat prioritas tampil

## Manajemen Prestasi (View-Only untuk Content Manager)

Content Manager tidak bisa edit/hapus prestasi. Hanya bisa view di list page.

(Admin bisa CRUD via Admin Guide)

## Manajemen Pelatih (View-Only)

Sama seperti Prestasi - view only untuk content manager.

## Manajemen Kegiatan

### Membuat Kegiatan (Pasanggiri/Ujian)
1. Sidebar → "CMS Landing" → "Kegiatan"
2. Klik "Kegiatan Baru"
3. Pilih tipe: Pasanggiri atau Ujian
4. Isi detail (title, date, description, location)
5. Upload dokumentasi foto
6. (Opsional) Masukkan link hasil
7. Klik "Buat Kegiatan"

## Pengaturan Landing (Admin Only)

Content Manager tidak bisa akses. Hanya admin yang bisa manage:
- Site name, logo, favicon
- Contact info (email, WhatsApp, phone)
- Social media links

## Troubleshooting

### Gambar tidak upload?
- Cek ukuran file (max 2MB)
- Format harus JPEG, PNG, atau WebP
- Cek koneksi internet

### Artikel tidak muncul di homepage?
- Pastikan status = "Published"
- Pastikan "Tanggal Publikasi" sudah lewat
- Clear browser cache (Ctrl+Shift+Delete)

### Lupa password?
- Klik "Lupa Password" di login page
- Masukkan email
- Cek email untuk reset link

### Perlu bantuan?
- Hubungi admin: +62xxx
- Email: admin@kinanti-art.local
```

### 2. Admin Guide: Site Settings (20 min)

**File:** `docs/ADMIN_GUIDE_SITE_SETTINGS.md`

```markdown
# Admin Guide: Pengaturan Situs

## Akses Settings
1. Sidebar → "CMS Landing" → "Pengaturan" (hanya untuk admin)
2. Form dengan tab-tab:
   - **Tab 1: Informasi Situs**
     - Site Name: Nama organisasi
     - Logo: Upload logo PNG/JPG
     - Favicon: Upload favicon (icon kecil di browser tab)
   
   - **Tab 2: Kontak**
     - Email
     - Nomor WhatsApp: Format 62xxx (tanpa +)
     - Telepon
     - Alamat lengkap
   
   - **Tab 3: Social Media**
     - YouTube: Link channel
     - Instagram: Link profile atau @handle
     - TikTok: Link profile
     - Facebook: Link page
     - Twitter: Link profile

3. Setiap field bisa dikosongkan (opsional)
4. Update → klik "Simpan Pengaturan"

**Tips:**
- Social media links otomatis tampil di footer
- Jika tidak diisi, icon tidak tampil di footer
- Logo tampil di navbar dan footer
```

### 3. Technical Documentation (20 min)

**File:** `docs/TECHNICAL_README.md`

```markdown
# Dokumentasi Teknis Landing Page

## Struktur Direktori

```
app/
├── Models/Landing/
│   ├── Article.php
│   ├── Event.php
│   ├── Achievement.php
│   ├── Trainer.php
│   ├── Activity.php
│   ├── HeroSlide.php
│   └── SiteSetting.php
├── Http/Controllers/Landing/
│   ├── ArticleController.php
│   ├── PublicController.php
│   └── ... (other controllers)
├── Services/Landing/
│   └── ArticleService.php
└── Policies/Landing/
    └── ArticlePolicy.php

database/
└── migrations/
    └── YYYY_MM_DD_*.php (landing page tables)

resources/views/
└── landing/
    ├── public/
    │   ├── home.blade.php
    │   ├── articles/
    │   └── ... (other pages)
    └── articles/ (admin views)

routes/
└── web.php (admin routes di prefix /admin/landing)
```

## Database Schema

### Articles Table
```sql
CREATE TABLE articles (
  id BIGINT PRIMARY KEY,
  title VARCHAR(100) UNIQUE,
  slug VARCHAR(255) UNIQUE,
  content LONGTEXT,
  excerpt VARCHAR(200),
  featured_image VARCHAR(255),
  category ENUM('berita', 'pengumuman', 'artikel'),
  status ENUM('draft', 'published'),
  published_at TIMESTAMP,
  created_by BIGINT FOREIGN KEY (users.id),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

(Lihat FSD untuk semua tabel)

## API Endpoints (Future Integration)

Untuk future mobile app / SPA integration:

```
GET /api/articles (list)
GET /api/articles/{id} (detail)
GET /api/articles?category=berita&status=published
GET /api/events (upcoming events)
GET /api/achievements
GET /api/trainers
```

Dokumentasi lengkap di `docs/API_SPEC.md`

## Caching Strategy

- Homepage: cache key `landing_homepage` → 1 hour
- Articles latest 3: cache key `articles_latest_3` → 30 minutes
- Invalidate otomatis saat create/update/delete

Cache dapat di-clear manual:
```bash
php artisan cache:clear
```

## Deployment

Lihat `DEPLOYMENT.md` untuk production deployment steps.

## Reporting Issues

Bug reports: GitHub Issues repo
Features request: GitHub Discussions
Security issues: Email security@kinanti-art.local
```

### 4. API Documentation (20 min)

**File:** `docs/API_SPEC.md`

```markdown
# API Specification

## Base URL
```
https://kinanti-art.local/api
```

## Articles API

### List Articles
```
GET /articles?category=berita&status=published&page=1

Response:
{
  "data": [
    {
      "id": 1,
      "title": "...",
      "slug": "...",
      "excerpt": "...",
      "featured_image": "https://...",
      "category": "berita",
      "published_at": "2026-05-04T10:00:00Z"
    }
  ],
  "pagination": {...}
}
```

### Get Article Detail
```
GET /articles/{slug}

Response:
{
  "id": 1,
  "title": "...",
  "content": "...",
  "author": {...},
  "created_at": "...",
  "updated_at": "..."
}
```

(Lengkap untuk semua endpoints)
```

### 5. FAQ Document (15 min)

**File:** `docs/FAQ.md`

```markdown
# FAQ - Pertanyaan Umum

## Konten
**Q: Berapa lama artikel muncul di homepage?**
A: Artikel muncul langsung jika status "Published". Ketika diedit, cache homepage di-update otomatis.

**Q: Bisa hapus article yang sudah di-publish?**
A: Hanya admin yang bisa hapus. Content manager hanya bisa edit/draft.

**Q: Ukuran gambar berapa yang cocok?**
A: Maksimal 2MB. Sistem otomatis compress di Cloudinary.

## Teknis
**Q: Kenapa homepage loading lambat?**
A: Clear cache (Ctrl+Shift+Delete) atau hubungi admin untuk cache clear server.

**Q: Gambar tidak tampil?**
A: Cek koneksi internet. Jika masih error, screenshot error message dan lapor ke admin.

**Q: Bisa restore article yang sudah dihapus?**
A: Hubungi admin. Soft-delete bisa di-restore oleh admin melalui database.
```

### 6. Troubleshooting Guide (15 min)

**File:** `docs/TROUBLESHOOTING.md`

```markdown
# Troubleshooting Guide

## Error: "File too large"
**Solusi:** Compress gambar lebih dulu menggunakan tool online (TinyPNG.com) atau gunakan format WebP

## Error: "Unauthorized"
**Solusi:** Pastikan sudah login dan role memiliki permission. Refresh page.

## Error: "CSRF Token Mismatch"
**Solusi:** Session expired. Refresh page & login ulang.

## Homepage tidak update setelah create article
**Solusi:** 
1. Pastikan article status = "Published"
2. Refresh browser (Ctrl+F5)
3. Jika masih tidak muncul, hub admin clear cache
```

### 7. Video Tutorial (Optional - 30 min)

Create 3 short videos (2-3 min each):
1. **Login & Dashboard Overview** – Apa itu CMS, cara navigate
2. **Membuat Artikel** – Step-by-step create article dengan image
3. **Manage Event & Prestasi** – Quick tour

Host di YouTube unlisted (atau internal platform)

---

## 🎯 Definition of Done

- [ ] User Guide PDF created & shared (Google Drive link)
- [ ] Admin Guide created
- [ ] Technical README updated
- [ ] API Specification documented
- [ ] FAQ answered common questions
- [ ] Troubleshooting guide created
- [ ] Video tutorials recorded (opsional)
- [ ] Training session conducted
- [ ] All links added to README.md
- [ ] Team feedback collected
- [ ] Documentation version 1.0 released

---

## 📞 Post-Launch Support

**Week 1:** Daily support (monitor for issues)
**Week 2-4:** Scheduled office hours
**Month 2+:** On-demand support

Monitor Slack channel: #landing-page-cms

---

**Created:** May 4, 2026
