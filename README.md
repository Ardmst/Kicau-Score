<div align="center">

# 🏆 KicauScore
### *Real-Time Multi-Judge Avian Singing Competition Platform*

Platform sistem informasi cerdas untuk digitalisasi penjurian dan manajemen kompetisi burung berkicau secara **akurat, transparan, anti-bentrok, dan real-time**.

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![SQLite](https://img.shields.io/badge/Database-SQLite3-003B57?style=for-the-badge&logo=sqlite&logoColor=white)](https://sqlite.org)
[![Azure VPS](https://img.shields.io/badge/Cloud-Azure_VPS-0078D4?style=for-the-badge&logo=microsoftazure&logoColor=white)](https://azure.microsoft.com)
[![Cloudflare](https://img.shields.io/badge/Security-Cloudflare_Proxied-F38020?style=for-the-badge&logo=cloudflare&logoColor=white)](https://cloudflare.com)
[![Status](https://img.shields.io/badge/Production-Live-10533F?style=for-the-badge&logo=nginx&logoColor=white)](https://kicaumania.ardmst.my.id)

[🌐 Live Staging](https://kicaumania.ardmst.my.id) • [📊 Live Scoreboard](https://kicaumania.ardmst.my.id/scoreboard) • [🎯 Fitur Utama](#-fitur-unggulan) • [🛠️ Arsitektur](#%EF%B8%8F-arsitektur-dan-erd) • [🚀 Instalasi](#-panduan-instalasi-lokal) • [👥 Tim Pengembang](#-tim-pengembang-kelompok-3)

---

</div>

## 📌 Latar Belakang & Perbandingan Sistem

Pada kompetisi burung berkicau konvensional, penilaian puluhan burung di tiang gantangan masih mengandalkan kertas tancap fisik dan rekap manual. Hal ini memicu lamanya pengumuman juara, potensi salah hitung, nomor gantangan ganda, hingga isu kecurangan "burung titipan".

| Aspek Penilaian | Sistem Konvensional (Kertas Manual) | Platform Digital KicauScore |
| :--- | :--- | :--- |
| **Input Nilai Juri** | Kertas tancap fisik di bawah tiang | Mobile-First UI (langsung input dari smartphone juri) |
| **Kalkulasi Juara** | Rekap kalkulator manual di meja panitia | **Real-time Average Scoring Engine** otomatis |
| **Penanganan Nilai Seri** | Tos koin / debat juri subjektif | **4-Tier Tie-Breaker Engine** berstandar pakem PBI & BNR |
| **Integritas Skor** | Nilai kertas rentan diubah diam-diam | **Immutable Audit Trail** (`penilaian_logs`) & Lock Policy |
| **Alokasi Gantangan** | Catat WhatsApp manual (rawan *double-booking*) | **Atomic Allocation** (`DB::transaction` & `lockForUpdate`) |
| **Publikasi Hasil** | Tunggu papan tulis diumumkan berjam-jam | **Live Scoreboard Asinkron (Auto-Polling 10 detik)** |
| **Histori Prestasi Burung** | Piagam kertas mudah rusak atau hilang | **Digital Avian Passport** publik (`/burung/{slug}`) |

---

## ✨ Fitur-Fitur Unggulan

### 1. 🎯 Multi-Judge Average Scoring Engine
Sistem menghitung rata-rata nilai akumulatif dari 3 hingga 6 juri secara instan setiap kali juri menyimpan penilaian. Penilaian mencakup dua kriteria utama:
- **Aspek Audio (Rentang 1.00 – 100.00):** Kualitas variasi irama lagu, volume tembus kristal, dan kestabilan durasi kerja 10 menit.
- **Aspek Visual (Rentang 1.00 – 100.00):** Gaya tarung (sujud/ngentrok), fisik tegap, dan keindahan gerak ekor.

### 2. ⚖️ 4-Tier Algoritma Tie-Breaker (Pemecah Nilai Seri)
Ketika terjadi nilai akhir yang sama persis antar-burung di akhir sesi lomba, sistem menentukan peringkat juara secara matematis:
```mermaid
flowchart TD
    A[Mulai Penentuan Peringkat] --> B{Total Skor Akhir Tertinggi?}
    B -- Beda Nilai --> C[Juara Berdasarkan Total Skor]
    B -- Nilai Seri --> D{Rata-rata Skor Audio Tertinggi?}
    D -- Beda Audio --> E[Juara Berdasarkan Kualitas Lagu & Volume]
    D -- Audio Masih Seri --> F{Rata-rata Skor Visual Tertinggi?}
    F -- Beda Visual --> G[Juara Berdasarkan Gaya & Fisik]
    F -- Visual Masih Seri --> H[Nomor Gantangan Terkecil ASC]
```

### 3. 🛡️ Immutable Audit Trail & Score Lock Policy
- **Log Revisi Otomatis:** Setiap kali juri merevisi nilai sebelum sesi ditutup, sistem mewajibkan pengisian alasan dan mencatat nilai lama, nilai baru, serta ID juri ke tabel `penilaian_logs`.
- **Penguncian Permanen:** Saat admin mengubah status kategori menjadi `validated`, seluruh nilai terkunci otomatis (**HTTP 403 Forbidden** jika ada percobaan edit susulan).

### 4. ⚡ Concurrency-Safe Gantangan Allocation (`GantanganService`)
Menerapkan transaksi basis data ACID dan *Pessimistic Locking* (`lockForUpdate`). Ketika administrator memverifikasi bukti bayar peserta, nomor gantangan (`G-01` s.d. `G-36`) diterbitkan secara berurutan tanpa resiko duplikasi nomor, serta otomatis dirilis kembali jika pembayaran ditolak.

### 5. 📱 Mobile-First Thumb-Friendly Jury Experience
Antarmuka khusus perangkat genggam juri lapangan:
- Grid nomor gantangan dengan kode warna status (*Sudah Dinilai* vs *Belum Dinilai*).
- Tombol **"Simpan & Lanjut ke Gantangan Berikutnya"** untuk alur kerja cepat satu tangan di lapangan outdoor.

### 6. 🔄 Asynchronous Live Scoreboard (AJAX Auto-Polling)
Papan klasemen publik terhubung ke endpoint `/api/scoreboard`. Tabel klasemen dan kartu podium (Emas, Perak, Perunggu) terupdate otomatis setiap 10 detik tanpa me-reload halaman browser.

### 7. 📜 Portofolio Digital Burung Gacoan (`/burung/{slug}`)
Sertifikat rekam jejak digital publik yang menampilkan Nomor Ring kaki terdaftar, identitas pemilik, serta galeri trofi juara lengkap dengan rincian skor Audio-Visual.

### 8. 👥 Autentikasi No. WhatsApp & CRUD Pengguna (RBAC)
- Pendaftaran akun peserta ringkas berbasis **Nomor WhatsApp** (tanpa kewajiban email).
- Panel Admin dilengkapi modul **CRUD Pengguna Penuh** (Create, Read, Update, Delete) dengan filter peran: Admin, Juri, dan Peserta.

---

## 🛠️ Arsitektur dan ERD

Proyek ini dibangun di atas **Laravel 13** dengan pola **MVC + Service Layer Architecture**, memisahkan logika bisnis kompleks ke dalam *Services*:
- `app/Services/ScoringEngineService.php` &rarr; Agregasi rata-rata multi-juri & kalkulasi 4-tier tie-breaker.
- `app/Services/GantanganService.php` &rarr; Alokasi nomor gantangan atomik anti-race-condition.

### Skema Relasi Basis Data (7 Tabel Inti)
```
┌──────────────┐       1:N       ┌──────────────┐       1:1       ┌──────────────┐
│    users     │────────────────>│   burungs    │────────────────>│ pendaftarans │
└──────────────┘                 └──────────────┘                 └──────┬───────┘
       │ 1:N                                                             │ N:1
       ▼                                                                 ▼
┌──────────────┐                 ┌──────────────┐       1:N       ┌──────────────┐
│  penilaians  │<────────────────│penilaian_logs│                 │kategori_lomba│
└──────────────┘                 └──────────────┘                 └──────┬───────┘
       ▲                                                                 │ N:1
       │ 1:N                                                             ▼
       └──────────────────────────────────────────────────────────┌──────────────┐
                                                                  │    events    │
                                                                  └──────────────┘
```

---

## 💻 Struktur Direktori Proyek

```
KicauScore/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Auth, Admin, Juri, Peserta, Public Controllers
│   │   └── Middleware/        # RoleMiddleware (RBAC Guard)
│   ├── Models/                # Eloquent Models (User, Event, KategoriLomba, Burung, dll)
│   ├── Policies/              # PenilaianPolicy (Score Lock Authorization)
│   └── Services/              # ScoringEngineService & GantanganService
├── database/
│   ├── migrations/            # 10 Skema Migrasi Tabel Relasional & Waktu Event
│   └── seeders/               # DatabaseSeeder (Dataset Lomba & Akun Realistis)
├── resources/
│   └── views/
│       ├── layouts/           # Master Layout App & Juri Mobile Layout
│       ├── public/            # Landing Page, Rundown, Scoreboard, & Profil Burung
│       ├── auth/              # Login & Register No. WhatsApp
│       ├── peserta/           # Dashboard, Koleksi Burung, Daftar Lomba, E-Tiket
│       ├── juri/              # Grid Dashboard & Form Penilaian Mobile
│       └── admin/             # Dashboard, Events, CRUD User, Verifikasi Queue
├── routes/
│   └── web.php                # Group Routing Berbasis Role & Public Endpoints
└── tests/
    └── Feature/               # Automated Tests (Scoring & Lock Validation)
```

---

## 🚀 Panduan Instalasi Lokal

### Prasyarat Sistem
* **PHP:** Versi 8.4 atau lebih baru (dengan ekstensi `pdo_sqlite`, `mbstring`, `curl`, `openssl`)
* **Composer:** Versi 2.x
* **Node.js:** Versi 18+ & NPM (Opsional untuk asset build)

### Langkah Instalasi
```bash
# 1. Clone repositori dari GitHub
git clone https://github.com/Ardmst/Kicau-Score.git
cd Kicau-Score

# 2. Pasang seluruh dependensi PHP via Composer
composer install

# 3. Buat file konfigurasi environment lokal
cp .env.example .env

# 4. Generate Application Encryption Key
php artisan key:generate

# 5. Jalankan migrasi database dan isi data simulasi awal (seeder)
php artisan migrate --seed

# 6. Jalankan server lokal Laravel
php artisan serve
```

Aplikasi siap diakses melalui peramban di `http://127.0.0.1:8000`.

---

## 🔑 Akun Default Pengujian

Semua akun menggunakan kata sandi default: **`password`**

| Peran (Role) | Identitas Login | Keterangan & Hak Akses |
| :--- | :--- | :--- |
| **Administrator** | Email: `admin@kicauscore.com`<br>No. HP: `081234567890` | Akses penuh: Master Event, Kategori, CRUD Akun, Verifikasi Bayar, Kunci Skor |
| **Dewan Juri 01** | Email: `bambang@kicauscore.com`<br>No. HP: `081298765431` | Sesi penilaian mobile di arena gantangan (Bambang Sutrisno) |
| **Dewan Juri 02** | Email: `joko@kicauscore.com`<br>No. HP: `081298765432` | Sesi penilaian mobile di arena gantangan (Joko Ansori) |
| **Dewan Juri 03** | Email: `tri@kicauscore.com`<br>No. HP: `081298765433` | Sesi penilaian mobile di arena gantangan (Tri Wibowo) |
| **Peserta Lomba** | No. HP: `081311223344`<br>*(Ahmad Fauzi)* | Pemilik burung *Samurai X* & *Golden Boy*, pemegang E-Tiket Gantangan G-12 |

---

## 🌐 Infrastruktur Cloud & Deployment

Sistem telah di-deploy dan dapat diuji secara langsung pada staging environment:
* **Domain Produksi:** [https://kicaumania.ardmst.my.id](https://kicaumania.ardmst.my.id)
* **Cloud Platform:** Microsoft Azure Virtual Machine (Ubuntu 24.04 LTS, 2 vCPU, 4 GiB RAM)
* **Web Server:** Nginx 1.24 dengan proteksi pemblokiran akses langsung ke file sensitif (`.env`, `.git`, `.sqlite`)
* **PHP Runtime:** PHP 8.4.27-FPM (Ondřej Surý PPA)
* **Security & SSL:** Cloudflare Edge Proxy + Let's Encrypt SSL Certificate (Full Strict)

---

## 👥 Tim Pengembang (Kelompok 3)

| Nama Anggota | Peran / Jabatan | Modul & Tanggung Jawab Utama |
| :--- | :--- | :--- |
| **Ardi** | **Project Manager, Lead Architect & DevOps** | Arsitektur MVC & Service Layer, skema database migrations, concurrency service (`GantanganService`), deployment VPS & security hardening. |
| **Rafly** | **Backend Developer (Scoring Engine & Validasi)** | Algoritma rata-rata multi-juri, 4-Tier Tie-Breaker, controller juri lapangan, audit trail revisi skor, & automated testing. |
| **Daaris** | **Backend Developer (Auth, Event & Transaksi)** | Autentikasi No. WA, RoleMiddleware (RBAC), CRUD Event & Kategori, CRUD Akun Pengguna, dan seeder data kontes. |
| **Natasya** | **Frontend UI/UX (Portal Publik & Peserta)** | Design system responsif, landing page jadwal lomba, dashboard peserta, formulir pendaftaran, dan portofolio rekam jejak burung. |
| **Hasna** | **Frontend UI/UX (Juri & Admin) & QA / Tech Writer** | Antarmuka juri mobile-first, papan live scoreboard asinkron, panel verifikasi admin, matriks pengujian UAT, dan laporan akhir. |

---

## 📄 Lisensi

Proyek ini dikembangkan sebagai pemenuhan Tugas Besar mata kuliah **Praktikum Pemrograman 2** dan dirilis di bawah lisensi [MIT License](LICENSE).
