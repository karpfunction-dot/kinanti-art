# 🎯 MASTER ISSUE: Landing Page Dinamis Kinanti Art Productions v1.0

**Status:** 🔴 Not Started  
**Target Completion:** May 18, 2026 (2 weeks)  
**Assignee:** @team  
**Dependency:** ⚠️ MUST complete [#00-SETUP](#00-setup-and-preparation) first

---

## 📌 Deskripsi Singkat

Implementasi CMS **Landing Page Dinamis** untuk Kinanti Art Productions dengan 5 tipe konten (Artikel, Event, Prestasi, Profil Pelatih, Kegiatan) yang dapat dikelola admin tanpa coding. **CRITICAL: Tidak mengubah fitur operasional yang sudah ada (absensi, kelas, transaksi).**

---

## 🎯 Business Goals

- ✅ 100% konten landing page kelola via admin dashboard
- ✅ Marketing team bisa publish berita real-time tanpa developer
- ✅ Integrasi dengan Cloudinary untuk media management
- ✅ SEO-friendly dan responsif mobile/desktop

---

## 📋 Acceptance Criteria (Master Level)

- [ ] Semua 7 modul sudah CRUD-ready (tested)
- [ ] Homepage menampilkan konten dinamis (tidak hardcoded)
- [ ] Admin dashboard berfungsi untuk minimal 5 content manager
- [ ] Tidak ada error/deprecation warning di Laravel logs
- [ ] Performance: FCP < 1.5s, LCP < 2.5s (diukur di staging)
- [ ] Semua media via Cloudinary (no local uploads)
- [ ] Authorization working: Admin full access, Content Manager partial
- [ ] 60% test coverage untuk critical paths

---

## 🗂️ Sub-Issues (Parallel & Sequential)

### Phase 1: Setup & Preparation (Dependencies for all)
- **[#00-SETUP-AND-PREPARATION](./00-SETUP-AND-PREPARATION.md)** ⏱️ 1 day
  - Database migrations
  - Models creation
  - Service layers
  - Menu sidebar setup

### Phase 2: Backend CRUD (Can run in parallel per module)
- **[#01-MODULE-ARTICLE](./01-MODULE-ARTICLE.md)** ⏱️ 0.5 day
- **[#02-MODULE-EVENT](./02-MODULE-EVENT.md)** ⏱️ 0.5 day
- **[#03-MODULE-ACHIEVEMENT](./03-MODULE-ACHIEVEMENT.md)** ⏱️ 0.5 day
- **[#04-MODULE-TRAINER](./04-MODULE-TRAINER.md)** ⏱️ 0.5 day
- **[#05-MODULE-ACTIVITY](./05-MODULE-ACTIVITY.md)** ⏱️ 0.5 day
- **[#06-MODULE-HERO-SLIDER](./06-MODULE-HERO-SLIDER.md)** ⏱️ 0.5 day
- **[#07-MODULE-SITE-SETTINGS](./07-MODULE-SITE-SETTINGS.md)** ⏱️ 0.5 day

### Phase 3: Frontend Landing (Dependent on Phase 2)
- **[#08-LANDING-PAGE-FRONTEND](./08-LANDING-PAGE-FRONTEND.md)** ⏱️ 2 days
  - Homepage blade template
  - Detail pages (artikel, event, prestasi, pelatih, kegiatan)
  - Caching implementation
  - Responsive design

### Phase 4: Testing & Deployment
- **[#09-VALIDATION-AND-SECURITY-CHECKLIST](./09-VALIDATION-AND-SECURITY-CHECKLIST.md)** ⏱️ 1 day
  - Security review
  - Performance testing
  - User acceptance testing
  - Bug fixes

### Phase 5: Post-Launch (Optional)
- **[#10-DOCUMENTATION-AND-TRAINING](./10-DOCUMENTATION-AND-TRAINING.md)** ⏱️ 0.5 day
  - Admin user guide
  - Technical documentation
  - Runbook

---

## 🔗 Related Documentation

- **BRD (Business Requirements):** [Link ke doc/BRD.md]
- **FSD (Functional Specifications):** [Link ke doc/FSD.md]
- **NFR (Non-Functional Requirements):** [Link ke doc/NFR.md]
- **Architecture Diagram:** [Link ke doc/ARCHITECTURE.md]

---

## 🚀 Getting Started

### Prerequisites
- [ ] Review dokumen FSD lengkap
- [ ] Clone repo & setup local environment
- [ ] Run `composer install` & `npm install`
- [ ] Konfigurasi `.env` (DATABASE_URL, CLOUDINARY_API_KEY, dll)

### Run Issue #00 (Setup) Terlebih Dahulu
```bash
# Assign ke seorang developer untuk execute setup
git checkout -b feature/landing-page-setup
# Ikuti checklist di issue #00
# Setelah selesai & merged, baru mulai Phase 2
```

### Phase 2: Parallel Development
```bash
# Setiap developer bisa assign 1-2 module issue
# Misal:
# - Dev A: artikel + event
# - Dev B: prestasi + trainer
# - Dev C: activity + hero_slider + settings
# Semua buat branch terpisah: feature/landing-page-[module-name]
```

---

## 📊 Progress Tracking

| Phase | Status | % Complete | Notes |
|-------|--------|-----------|-------|
| Setup | 🔴 Not Started | 0% | Blocked until assigned |
| Article | 🔴 Not Started | 0% | Waiting for Setup |
| Event | 🔴 Not Started | 0% | Waiting for Setup |
| Achievement | 🔴 Not Started | 0% | Waiting for Setup |
| Trainer | 🔴 Not Started | 0% | Waiting for Setup |
| Activity | 🔴 Not Started | 0% | Waiting for Setup |
| Hero Slider | 🔴 Not Started | 0% | Waiting for Setup |
| Site Settings | 🔴 Not Started | 0% | Waiting for Setup |
| Frontend | 🔴 Not Started | 0% | Blocked after Phase 2 |
| Testing | 🔴 Not Started | 0% | Blocked after Phase 3 |

---

## 🛑 Blockers & Risks

| Risk | Mitigation | Status |
|------|-----------|--------|
| **DB migration conflict** | Run `php artisan migrate:status` dulu | ✅ Preventive |
| **Cloudinary API rate limit** | Implement retry logic + cache | ⏳ During Dev |
| **Cache invalidation stale data** | Automated test + manual flush endpoint | ⏳ During Dev |
| **Permission model undefined** | Buat explicit auth matrix (lihat #00-SETUP) | ⏳ During Dev |
| **Junior dev confusion** | Detailed spec di setiap module issue | ✅ Preventive |

---

## ✅ Definition of Done (Master Issue)

- [ ] Semua 7 sub-issue Phase 2 merged ke `develop`
- [ ] Frontend issue (#08) merged
- [ ] Validation checklist (#09) passed 100%
- [ ] Zero critical security issues (OWASP Top 10)
- [ ] Performance testing passed (FCP, LCP targets)
- [ ] Admin & Content Manager tested by marketing team
- [ ] Documentation updated
- [ ] Merged ke `main` branch
- [ ] Deployed ke staging environment
- [ ] Ready for production launch

---

## 📞 Communication

**Daily Standup:** 10:00 AM (discuss blockers, parallelize work)  
**Code Review:** Every PR reviewed by 1 senior dev + 1 junior dev  
**Testing:** QA team does E2E testing after Phase 3 merge  

---

## 📝 Notes

- Jangan modify operational tables (user, kelas, transaksi) kecuali urgent bug fix
- Semua model baru harus punya timestamps = true
- Gunakan Service layer untuk Cloudinary uploads
- Cache invalidation harus explicit (jangan rely on timeout)
- Test di staging dulu sebelum production

---

**Created:** May 4, 2026  
**Last Updated:** May 4, 2026
