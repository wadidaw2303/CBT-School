# CBT School - Computer Based Testing System

Sistem Computer Based Testing (CBT) modern, lengkap, dan siap produksi untuk sekolah. Dibangun dengan **PHP 8.3+ native** (tanpa framework) untuk kemudahan maintenance, debugging, dan penambahan fitur.

## 🚀 Fitur Utama

### 🔐 Manajemen Pengguna & Otorisasi
- **RBAC (Role-Based Access Control)**: Admin, Guru, Siswa
- Login dengan remember me, reset password via email
- Session management aman dengan regenerasi ID
- CSRF protection pada semua form

### 📚 Bank Soal Komprehensif
- **7 Tipe Soal**: Pilihan Ganda (Tunggal/Berganda), Benar/Salah, Uraian, Isian Singkat, Pencocokan, Pengurutan
- Kategori hierarkis per mata pelajaran
- Tingkat kesulitan (Mudah/Sedang/Sulit)
- Media pendukung: Gambar, Audio, Video
- Import massal via CSV/JSON
- Versioning & preview soal

### 📝 Manajemen Ujian
- Wizard pembuatan ujian 4 langkah
- Pemilihan soal: manual, acak dari kategori, atau campuran
- Pengaturan: durasi, nilai kelulusan, acak soal/opsi, tampilkan hasil
- Penjadwalan: Draft → Published → Archived
- Peserta: per kelas, individu, atau kode akses publik
- Browser lockdown & IP restriction

### 🎯 Antarmuka Ujian Siswa (Exam Engine)
- **Fullscreen enforcement** dengan deteksi keluar layar
- Timer sinkron server-side (anti-manipulasi)
- **Panel soal (palette)**: navigasi cepat, warna status (dijawab/belum/ditandai)
- **Auto-save** tiap 30 detik + on navigate/blur
- Keyboard shortcuts (Ctrl+Enter submit, arrow keys navigasi)
- Auto-submit saat waktu habis
- Indikator koneksi real-time
- Anti-cheat: disable copy/paste/right-click, deteksi dev tools, log kecurangan

### ✅ Penilaian & Grading
- **Auto-grading** instan untuk soal objektif (MCQ, TF, Fill-blank, Matching, Ordering)
- **Manual grading** esai dengan rubrik & feedback inline
- Skor parsial untuk multiple choice berganda
- Re-grading & koreksi nilai
- Finalisasi nilai oleh guru

### 📊 Laporan & Analitik
- **Dashboard per peran**: Admin, Guru, Siswa
- **Item Analysis**: Indeks kesukaran, daya beda (discrimination), distractor analysis
- Ranking kelas, statistik per soal, analisis kelemahan per materi
- Export: CSV (native), HTML (print-to-PDF), JSON
- Sertifikat otomatis untuk siswa lulus

### 🏫 Manajemen Akademik
- Tahun ajaran & semester
- Kelas, mata pelajaran, guru pengampu
- Profil siswa lengkap (NISN, NIS, orang tua)
- Import/export data pengguna via CSV

### 🔒 Keamanan & Sistem
- Prepared statements (anti SQL injection)
- Output escaping (anti XSS)
- Rate limiting pada auth endpoints
- Audit logging semua aktivitas
- Backup/restore database + uploads
- Password hashing bcrypt (cost 12)

## 📋 Persyaratan Sistem

| Komponen | Minimum | Direkomendasikan |
|----------|---------|------------------|
| PHP | 8.3+ | 8.3+ |
| MySQL | 8.0+ | 8.0+ / MariaDB 10.6+ |
| Web Server | Apache 2.4+ / Nginx 1.18+ | Nginx 1.24+ + PHP-FPM |
| Memory | 256 MB | 512 MB+ |
| Disk | 500 MB | 2 GB+ |
| Ekstensi PHP | pdo_mysql, mbstring, json, gd, fileinfo, openssl | + redis (optional) |

## 🛠️ Instalasi

### 1. Clone & Setup
```bash
cd /path/to/webroot
git clone <repository> cbt-school
cd cbt-school
```

### 2. Konfigurasi Environment
```bash
cp .env.example .env
# Edit .env dengan konfigurasi database Anda
```

### 3. Setup Database
```bash
# Buat database MySQL
mysql -u root -p -e "CREATE DATABASE cbt_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Jalankan migrasi
php scripts/migrate.php migrate

# Jalankan seeder (roles, admin user, demo data)
php scripts/seed.php
```

### 4. Konfigurasi Web Server

**Apache** (sudah ada `.htaccess` di `public/`):
```apache
<VirtualHost *:80>
    ServerName cbt-school.local
    DocumentRoot /path/to/cbt-school/public
    <Directory /path/to/cbt-school/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx** (gunakan `nginx.conf`):
```bash
sudo cp nginx.conf /etc/nginx/sites-available/cbt-school
sudo ln -s /etc/nginx/sites-available/cbt-school /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### 5. File Permissions
```bash
chmod -R 755 public/uploads
chmod -R 755 storage  # jika ada
```

### 6. Akses Aplikasi
Buka browser: `http://cbt-school.local` atau `http://localhost:8000` (builtin server)

**Default Admin:**
- Username: `admindemo`
- Password: `admindemo123`

## 🏗️ Struktur Proyek

```
cbt-school/
├── public/                 # Document root
│   ├── index.php          # Front controller
│   ├── .htaccess          # Apache config
│   ├── assets/
│   │   ├── css/           # Stylesheets
│   │   └── js/            # JavaScript modules
│   └── uploads/           # User uploads
├── src/
│   ├── Core/              # Framework core classes
│   │   ├── App.php        # Application bootstrap
│   │   ├── Router.php     # Regex-based router
│   │   ├── Database.php   # PDO wrapper + query builder
│   │   ├── Session.php    # Session management
│   │   ├── Csrf.php       # CSRF protection
│   │   ├── Validator.php  # Validation rules
│   │   ├── View.php       # Template rendering
│   │   ├── Request.php    # Request wrapper
│   │   ├── Response.php   # Response helper
│   │   ├── Config.php     # Config loader
│   │   └── Helpers.php    # Global helpers
│   ├── Auth/              # Authentication & Authorization
│   │   ├── Auth.php       # Auth service
│   │   ├── User.php       # User entity
│   │   ├── Role.php       # Role entity
│   │   └── Middleware/    # Auth, Role, Permission middleware
│   └── Modules/           # Feature modules
│       ├── User/
│       ├── Question/
│       ├── Exam/
│       ├── Attempt/
│       ├── Report/
│       └── Dashboard/
├── templates/
│   ├── layouts/           # Base layouts
│   ├── partials/          # Reusable components
│   ├── auth/              # Login, register, password reset
│   ├── admin/             # Admin panel
│   ├── teacher/           # Teacher views
│   ├── student/           # Student views (exam, results)
│   └── errors/            # 404, 500, maintenance
├── database/
│   ├── migrations/        # SQL migrations
│   └── seeds/             # Seed data
├── config/                # Configuration files
├── scripts/               # CLI scripts
└── tests/                 # Simple tests
```

## 🔧 Pengembangan

### Menjalankan Development Server
```bash
php -S localhost:8000 -t public
```

### Membuat Migration Baru
```bash
php scripts/migrate.php create create_nama_table
```

### Menjalankan Migrasi
```bash
php scripts/migrate.php migrate        # Jalankan semua pending
php scripts/migrate.php rollback       # Rollback 1 step
php scripts/migrate.php rollback --step=3  # Rollback 3 steps
php scripts/migrate.php status         # Lihat status migrasi
php scripts/migrate.php fresh          # Drop all & re-run (HATI-HATI!)
```

### Menambah User via CLI
```bash
# (Buat script scripts/create-user.php jika perlu)
```

## 📖 Panduan Penggunaan

### Untuk Administrator
1. Login sebagai admin
2. Kelola pengguna (tambah guru/siswa)
3. Setup struktur akademik (tahun ajaran, semester, kelas, mapel)
4. Assign guru ke kelas & mata pelajaran
5. Monitor log aktivitas & backup sistem

### Untuk Guru
1. Login sebagai guru
2. Buat kategori soal per mata pelajaran
3. Tambah soal (manual atau import CSV)
4. Buat ujian via wizard
5. Publikasikan ujian ke kelas
6. Nilai soal esai (jika ada)
7. Lihat laporan hasil & analisis butir soal

### Untuk Siswa
1. Login sebagai siswa
2. Lihat ujian yang tersedia (upcoming/in-progress/completed)
3. Klik "Mulai Ujian" → masuk mode fullscreen
4. Kerjakan soal (auto-save, timer, panel navigasi)
5. Kumpulkan ujian atau auto-submit saat waktu habis
6. Lihat hasil & ranking (jika diaktifkan guru)

## 🔌 API Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/time` | Sinkronisasi waktu server |
| POST | `/exam/auto-save` | Auto-save jawaban |
| POST | `/exam/submit` | Submit ujian final |
| POST | `/exam/start` | Mulai ujian (set started_at) |
| POST | `/exam/cheat-log` | Log percobaan curang |

## 🧪 Testing

```bash
# Jalankan test sederhana
php tests/run.php
```

## 📦 Backup & Restore

```bash
# Backup database + uploads
php scripts/backup.php

# Restore dari file backup
php scripts/restore.php backup_20240101_120000.tar.gz
```

## 🔧 Konfigurasi Lanjutan

### Environment Variables (.env)
```env
APP_NAME="CBT School"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cbt_school
DB_USERNAME=your_user
DB_PASSWORD=your_password

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CSRF_SECRET=your-32-char-random-string
BCRYPT_ROUNDS=12

UPLOAD_MAX_SIZE=10485760
```

### Production Checklist
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Generate `CSRF_SECRET` acak 32 karakter
- [ ] Enable HTTPS & HSTS
- [ ] Setup SSL certificate
- [ ] Configure `SESSION_SECURE_COOKIE=true`
- [ ] Setup email driver untuk reset password
- [ ] Setup cron job untuk backup harian
- [ ] Setup monitoring & log rotation
- [ ] Test load dengan concurrent users

## 🤝 Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/nama-fitur`)
3. Commit perubahan (`git commit -am 'Tambah fitur X'`)
4. Push ke branch (`git push origin feature/nama-fitur`)
5. Buat Pull Request

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

## 🆘 Dukungan

- **Dokumentasi**: Lihat folder `docs/` (jika ada)
- **Issue Tracker**: GitHub Issues
- **Email**: support@cbt-school.local

---

**CBT School** - Dibangun dengan ❤️ untuk pendidikan Indonesia