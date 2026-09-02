# SiMoU — Sistem Informasi MoU & MoA RSUD Kilisuci
![GitHub License](https://img.shields.io/github/license/krisnadwiki/SiMoU-RSK)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker)](https://docs.docker.com/compose/)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)](https://www.php.net/)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.11-003545?logo=mariadb)](https://mariadb.org/)

Platform repositori dan sistem manajemen dokumen **Kerjasama**, **MoU (Memorandum of Understanding)**, serta **MoA (Memorandum of Agreement)** untuk **RSUD Kilisuci Kota Kediri** berbasis Web Native (Tanpa Framework heavyweight).

---

## 🌟 Fitur Utama & Keunggulan System

- **Public Portal & Katalog MoU Transparan** — Pencarian katalog dokumen kerjasama publik tanpa perlu login, dilengkapi pencarian kata kunci dan filter interaktif.
- **Dual Nomor Surat (RSUD & Mitra)** — Pencatatan 2 nomor surat resmi sekaligus (**Nomor Surat RSUD Kilisuci** dan **Nomor Surat Mitra/Institusi**). Pencarian kata kunci secara otomatis mencari di kedua nomor surat.
- **PDF Viewer & Download Protection Native** — Preview langsung berkas PDF dokumen utama maupun berkas Addendum/Perpanjangan di dalam browser secara aman melalui endpoint terproteksi.
- **Validasi PDF & Limit Konfigurasi Dynamic (30 MB)** — Penguncian format berkas khusus **PDF (`.pdf`)** secara client-side & server-side (MIME magic bytes), dengan batas ukuran berkas yang terkonfigurasi dinamis via `.env` (`UPLOAD_MAX_MB=30`).
- **Tom Select Searchable Dropdowns** — Seluruh pilihan dropdown di aplikasi (termasuk filter bar) menggunakan plugin `dropdown_input` Tom Select yang bersih, konsisten, dan mudah dicari.
- **Alert Expiry & Notifikasi Masa Berlaku** — Pemantauan otomatis status dokumen (*Aktif*, *Segera Berakhir*, *Berakhir*, *Dihentikan*) dengan pengingat terkonfigurasi (H-30, H-60, H-90, H-120).
- **Versioning & Histori Perpanjangan/Addendum** — Pengelolaan histori perpanjangan dokumen MoU/MoA tanpa menimpa berkas atau tanggal dokumen awal.
- **Dashboard Statistik & Analitik** — Visualisasi grafik interaktif dokumen per tahun, per kategori, serta indikator rekapitulasi cepat berbasis Chart.js.
- **Modul Backup & Restore System (Database & PDF)** — 
  - **Backup Database (.SQL)**: Unduh skrip dump SQL cadangan skema & data.
  - **Backup Berkas Dokumen (.ZIP)**: Mengompresi seluruh berkas PDF fisik ke dalam arsip ZIP.
  - **Backup Lengkap System (.ZIP)**: Unduh paket komplit `.sql` + seluruh berkas PDF dokumen.
  - **Live Restore Modal & AJAX Progress**: Pemulihan database asinkron dengan indikator *progress bar* (`0%` ➔ `100%`), *step checklist* real-time, serta umpan balik kesalahan SQL / sukses secara interaktif.
- **Manajemen Pengguna & Penguatan Keamanan Password** — Pengelolaan pengguna (Superadmin & Admin), verifikasi password lama saat pembaruan password mandiri, enkripsi BCrypt, CSRF Protection, Session Hardening, serta Rate Limiting Login (lockout otomatis 15 menit jika 5x gagal).
- **Manajemen Institusi & Unit Kerja Internal** — Pengelolaan data mitra (Pendidikan, Kedinasan, Swasta, BUMN) dan unit kerja internal RSUD Kilisuci berbasis Data Table dengan *sticky header*.
- **Export Data CSV** — Rekapitulasi laporan dokumen ke format CSV (UTF-8 BOM, kompatibel penuh dengan Microsoft Excel).
- **Audit Trail & Activity Log** — Pencatatan log aktivitas pengurus dan aktivitas sistem secara otomatis.

---

## 💻 Requirement System

### Sistem Operasi
- Ubuntu Server 22.04 LTS / 24.04 LTS (Linux) / Windows Server / macOS dengan Docker Desktop

### Software & Container Engine
- Docker Engine 24.0+
- Docker Compose Plugin v2.0+

### Minimal Hardware Recommendation

| Komponen | Minimal | Direkomendasikan (Produksi) |
| -------- | ------- | -------------------------- |
| **CPU**  | 1 Core  | 2 Core / 4 Thread          |
| **RAM**  | 1 GB    | 2 GB – 4 GB                |
| **Storage** | 10 GB | 30 GB + (Sesuai volume PDF) |

---

## 🛠️ Tech Stack & Komponen Architecture

| Layer | Teknologi | Versi | Keterangan |
| ----- | --------- | ----- | ---------- |
| **Runtime** | PHP Native | 8.2 | Clean MVC Architecture tanpa framework heavyweight |
| **Web Server** | Apache | 2.4 | Apache HTTP Server (`mod_rewrite` & `mod_alias` enabled) |
| **Database** | MariaDB | 10.11 | Database server relasional dengan skema terstruktur |
| **Container** | Docker & Compose | v2+ | Multi-container architecture (`simou-app` & `simou-db`) |
| **Frontend** | Vanilla CSS & JS | ES6+ | Modern Custom CSS System, Tom Select, FontAwesome 6, Chart.js |

---

## 📁 Struktur File & Folder

```text
SiMoU - RSK/
├── Dockerfile                          # Build image php:8.2-apache + pdo_mysql, gd, zip
├── docker-compose.yml                  # Service web (simou-app :8083) & DB (simou-db :3306)
├── .dockerignore
├── .gitignore
├── docker-entrypoint-initdb.d/
│   └── init.sql                        # Skema database MariaDB + Master Seed Produksi
└── app/
    ├── .env                            # Konfigurasi environment aplikasi & database
    ├── .htaccess                       # Routing Apache mod_rewrite ke index.php
    ├── index.php                       # Front Controller & Router Native PHP
    ├── config/
    │   ├── env.php                     # Parser File .env Native PHP
    │   ├── app.php                     # Konfigurasi Global, Session Hardening & Security
    │   └── database.php                # Koneksi Singleton PDO MariaDB
    ├── database/
    │   └── init.sql                    # Skrip inisialisasi database bersih (Produksi)
    ├── helpers/
    │   ├── auth.php                    # Logic Autentikasi, Role Check & Rate Limiting
    │   ├── functions.php               # Sanitasi XSS, Format Tanggal Indo, Audit Log Helper
    │   └── upload.php                  # File Upload Handler (Validasi MIME PDF & Size)
    ├── controllers/
    │   ├── AuthController.php          # Autentikasi Admin Login & Logout
    │   ├── BackupController.php        # Backup (.SQL & .ZIP) & Live Restore Modal AJAX
    │   ├── DashboardController.php     # Controller Analytics, Expiry Alert & Summary
    │   ├── ExportController.php        # Controller Export Report CSV
    │   ├── InstitutionController.php   # Controller Data Mitra / Institusi
    │   ├── LogController.php           # Controller Audit Trail Activity Log
    │   ├── MasterController.php        # Controller Kategori MoU
    │   ├── MouController.php           # Controller CRUD MoU/MoA, Dual Number & Renewal
    │   ├── PublicController.php        # Controller Portal Publik, Catalog & Detail PDF
    │   ├── UnitController.php          # Controller Data Unit Kerja Internal
    │   └── UserController.php          # Controller Manajemen User & Password Hardening
    ├── views/
    │   ├── layouts/                    # Layout Header, Footer, Topbar & Navigation
    │   ├── auth/                       # Halaman Login Admin Panel
    │   ├── admin/                      # Halaman Admin (Dashboard, MoU, Backup, User, Log)
    │   ├── public/                     # Halaman Portal Publik, Catalog & Detail PDF
    │   └── errors/                     # Halaman Error 404
    ├── assets/
    │   ├── css/style.css               # Modern Vanilla CSS Design System
    │   └── js/main.js                  # Script UI, Chart Init, & Event Handlers
    └── uploads/
        └── documents/                  # Storage Penyimpanan Berkas Berkas PDF (Maks 30MB)
```

---

## 🚀 Panduan Instalasi & Deployment

### 1. Setup Docker Engine (Ubuntu Server)

Jika belum menginstall Docker pada server:

```bash
# 1.1 Hapus instalasi paket lama (jika ada)
sudo apt remove -y docker.io docker-compose docker-doc runc

# 1.2 Setup Repository Resmi Docker
sudo apt update
sudo apt install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

sudo tee /etc/apt/sources.list.d/docker.sources <<EOF
Types: deb
URIs: https://download.docker.com/linux/ubuntu
Suites: $(. /os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}")
Components: stable
Signed-By: /etc/apt/keyrings/docker.asc
EOF

# 1.3 Install Docker Engine & Compose Plugin
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# 1.4 Aktifkan Service Docker
sudo systemctl enable --now docker
sudo usermod -aG docker $USER
```

---

### 2. Clone Repository & Setup Environment

```bash
# Clone repository ke server
git clone https://github.com/krisnadwiki/SiMoU-RSK.git simou
cd simou

# Salin konfigurasi environment .env
cp app/.env app/.env.example # Sesuaikan jika diperlukan
```

Contoh isi konfigurasi `app/.env`:

```env
APP_NAME="SiMoU RSUD Kilisuci"
APP_SHORT="SiMoU"
APP_ENV=production
APP_URL=http://localhost:8083
APP_VERSION=2.0.0

# Database Config (Merujuk pada service MariaDB Docker)
DB_HOST=simou-db
DB_PORT=3306
DB_DATABASE=simou_db
DB_USERNAME=simou_user
DB_PASSWORD=simou_password

# Session & Keamanan
SESSION_TIMEOUT=3600
TIMEZONE=Asia/Jakarta

# Login Rate Limiting
LOGIN_MAX_ATTEMPTS=5
LOGIN_WINDOW_SECONDS=600
LOGIN_LOCKOUT_SECONDS=900

# File Upload Limit (Maksimal MB)
UPLOAD_MAX_MB=30
```

---

### 3. Menjalankan Service dengan Docker Compose

Jalankan perintah berikut untuk membuat container:

```bash
docker compose up -d --build
```

Periksa status service container:

```bash
docker compose ps
```

---

### 4. Mengakses Aplikasi & Kredensial Default

Setelah container berjalan aktif:

- **Portal Publik**: [http://localhost:8083](http://localhost:8083)
- **Katalog Dokumen MoU**: [http://localhost:8083/catalog](http://localhost:8083/catalog)
- **Login Admin Panel**: [http://localhost:8083/auth/login](http://localhost:8083/auth/login)
- **Dashboard Admin**: [http://localhost:8083/admin](http://localhost:8083/admin)

#### 🔑 Kredensial Default Superadmin:

| Parameter | Nilai Default |
| --------- | ------------- |
| **Username** | `admin` |
| **Password** | `password123` |
| **Role** | `superadmin` |

> ⚠️ **Penting:** Segera ubah password superadmin default setelah pertama kali login melalui menu profil di pojok kanan atas.

---

## 💾 Panduan Backup & Pemulihan (Backup & Restore)

Administrasi sistem menyediakan modul pencadangan dan pemulihan lengkap pada halaman `/admin/backup`:

1. **Pencadangan Database (.SQL)**: Mengunduh berkas `.sql` yang berisi skema dan isi seluruh tabel database.
2. **Pencadangan Berkas PDF (.ZIP)**: Mengompresi seluruh berkas PDF fisik dokumen MoU/Addendum ke dalam 1 berkas `.zip`.
3. **Pencadangan Lengkap (.ZIP)**: Mengunduh paket arsip komplit yang berisi berkas `.sql` dan seluruh folder dokumen PDF.
4. **Pemulihan Database (Restore)**: Unggah berkas `.sql` cadangan, konfirmasi melalui modal interaktif, dan pantau proses pemulihan secara *real-time* via *Progress Bar & Checklist*.

---

## 🛠️ Docker Cheat Sheet

| Perintah | Fungsi |
| -------- | ------ |
| `docker compose up -d` | Menjalankan aplikasi di background |
| `docker compose up -d --build` | Rebuild image dan jalankan ulang container |
| `docker compose stop` | Menghentikan sementara service |
| `docker compose start` | Menyalakan kembali service yang dihentikan |
| `docker compose restart` | Restart seluruh service container |
| `docker compose down` | Menghapus container dan jaringan |
| `docker compose down -v` | Menghapus container, jaringan, dan volume database |
| `docker compose logs -f simou-app` | Pemantauan log aplikasi web real-time |

---

## 📄 Lisensi & Hak Cipta

Dikembangkan oleh **Information, Communication and Technology (ICT)**  
**RSUD Kilisuci Kota Kediri** &copy; 2022–2026.  
Seluruh hak cipta dilindungi undang-undang.
