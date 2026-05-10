<p align="center">
  <h1 align="center">🏆 Sistem Rector Cup</h1>
  <p align="center">Platform Manajemen Pertandingan Multi-Cabang Olahraga UKDW</p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue" alt="PHP">
  <img src="https://img.shields.io/badge/Reverb-WebSocket-purple" alt="Reverb">
  <img src="https://img.shields.io/badge/Status-Active%20Development-brightgreen" alt="Status">
</p>

---

## 📋 Deskripsi

Sistem Rector Cup adalah platform untuk mengelola turnamen olahraga & e-sport multi-cabang di lingkungan UKDW. Sistem mendukung **bracket single-elimination** dengan drag-and-drop arrangement, **live score real-time** via WebSocket, serta **integrasi Google Sheet** untuk cabang dengan sistem poin manual.

### Cabang Olahraga yang Didukung
- 🎮 **PES**, **PUBG Mobile**, **Mobile Legends** (e-sport)
- 🏀 **Basket**, **Volleyball**, **Futsal**, **Badminton**, **Billiard** (olahraga fisik)
- 🎤 **Vocal Group** (lomba)
- ♟ **Catur** (poin manual via Google Sheet)

---

## 🎯 Fitur Utama

### 🏠 Untuk Penonton/Mahasiswa (tanpa login)
- 📺 **Live Score Real-time** — skor update otomatis tanpa refresh (WebSocket via Laravel Reverb)
- 📅 **Jadwal Pertandingan** — daftar pertandingan per hari & per cabang
- 🏆 **Riwayat & Bracket Visual** — bracket ala Liquipedia dengan connector lines, plus podium juara 1-2-3
- 🔗 **Link Skor Eksternal** — untuk cabang manual (Catur/PUBG), tombol redirect ke Google Sheet resmi panitia

### 🎛️ Untuk Panitia/Admin (login)
- 🧙 **Custom Bracket Builder (2 langkah)**
  1. Konfigurasi turnamen + pilih tim peserta (4/8/16/32)
  2. Drag-and-drop arrangement tim ke slot bracket
- 🥉 **Auto Generate Perebutan Juara 3** — match khusus di luar bracket utama
- 📊 **Manajemen Skor Live** — update skor real-time dengan tombol +/- atau bulk update
- 📋 **Google Sheet Integration** — input link sheet opsional untuk cabang poin manual
- 🗂️ **Multi-Tournament History** — filter per tahun, per cabang, dengan podium & bracket lengkap
- 🔧 **Edit Pertandingan** — ubah waktu, lokasi, tim, atau skor kapan saja

---

## 🛠️ Tech Stack

| Layer        | Tool                                      |
|--------------|-------------------------------------------|
| Backend      | Laravel 11 (PHP 8.2+)                     |
| Database     | MySQL / MariaDB                           |
| Realtime     | Laravel Reverb (WebSocket native)         |
| Queue        | Database driver (`php artisan queue:work`)|
| Frontend     | Blade + Bootstrap 4 + Bootstrap Icons     |
| Drag & Drop  | SortableJS                                |
| Notifikasi   | SweetAlert2                               |

---

## 🚀 Setup & Instalasi

### Prasyarat
- PHP 8.2+
- Composer
- Node.js (untuk asset jika ada perubahan)
- MySQL/MariaDB
- Laragon / XAMPP / serupa

### Langkah Setup

```bash
# 1. Clone & masuk folder
git clone <repo-url> sistem-rectorcup
cd sistem-rectorcup

# 2. Install dependencies
composer install

# 3. Copy env & generate app key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi DB di .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# 5. Migrate + seed data master (sport, prodi, tim, akun admin)
php artisan migrate:fresh --seed

# 6. Setup storage symlink
php artisan storage:link
```

### Akun Default Admin
- **Username**: `admin`
- **Password**: `admin#1234`

---

## 🏃 Menjalankan Aplikasi

Aplikasi membutuhkan **3 terminal** berjalan bersamaan:

```bash
# Terminal 1 — Web server
php artisan serve

# Terminal 2 — WebSocket server (Reverb)
php artisan reverb:start

# Terminal 3 — Queue worker (untuk broadcast event)
php artisan queue:work
```

Akses aplikasi di `http://127.0.0.1:8000`.

---

## 🎮 Alur Kerja Panitia

### 1️⃣ Buat Bracket Baru
```
Dashboard → Bracket Builder
  ├─ Step 1: Konfigurasi
  │   ├─ Nama Tournament (mis. "Rector Cup Basket 2026")
  │   ├─ Tanggal mulai & selesai
  │   ├─ Cabang olahraga (visual picker)
  │   ├─ Ukuran bracket (4/8/16/32)
  │   ├─ [OPSIONAL] Link Google Sheet skor — untuk Catur/PUBG
  │   └─ Pilih tim peserta (klik tim per prodi)
  │
  └─ Step 2: Arrange Bracket
      └─ Drag tim dari panel kiri ke slot bracket
          → Save Bracket
```

### 2️⃣ Aktifkan Live & Update Skor
```
Dashboard → Pertandingan target → "Set Live"
   ↓
Sidebar "Kelola Skor" → klik match LIVE → tombol +/- skor
   ↓
Skor broadcast ke semua client real-time via WebSocket
```

### 3️⃣ Selesaikan Pertandingan
```
Halaman skor → "Selesaikan Pertandingan" → konfirmasi pemenang
   → Auto-advance pemenang ke round berikutnya
   → Final selesai → Podium juara muncul di History page
```

---

## 🏆 Sistem Podium & Skor

### Cabang Reguler (skor kuantitatif)
History page menampilkan **Podium Pemenang 1-2-3** dengan medal:
- 🥇 **1st** — Juara (gold gradient, paling tinggi)
- 🥈 **2nd** — Runner-up (silver)
- 🥉 **3rd** — Juara 3 (bronze, dari match Perebutan Juara 3)

### Cabang Poin Manual (Catur, PUBG Mobile)
Saat tournament dibuat dengan **link Google Sheet**, history page tidak menampilkan podium melainkan **card hijau "Lihat Hasil & Skor di Spreadsheet"** yang membuka sheet resmi panitia di tab baru.

> 💡 **Best Practice**: Sheet panitia harus di-share dengan opsi *"Anyone with the link → Viewer"* agar penonton bisa akses tanpa login.

---

## 📐 Bracket Visual (Liquipedia Style)

Bracket di history page menggunakan **connector lines L-shape** yang menyambung pasangan match ke round berikutnya, dengan:
- Garis ungu untuk match belum selesai
- Garis hijau tebal untuk match yang sudah punya pemenang
- Kolom **Perebutan Juara 3** dipisah dengan border dashed (di luar bracket utama, bukan playoff)

---

## 💡 FAQ

**Q: Apakah penonton perlu login?**
A: Tidak. Halaman publik (jadwal, skor live, history, bracket) bisa diakses tanpa login.

**Q: Bagaimana jika ada cabang baru yang sistem skornya unik?**
A: Gunakan field **Link Google Sheet Skor** saat buat tournament — sistem akan menampilkan link ke sheet alih-alih podium internal.

**Q: Skor live tidak update otomatis di browser penonton?**
A: Pastikan **Reverb** dan **Queue Worker** sedang berjalan (lihat bagian "Menjalankan Aplikasi").

**Q: Bisa edit bracket setelah tournament dibuat?**
A: Edit pemain manual via halaman tournament masih dalam pengembangan. Sementara, jika ada kesalahan total bisa hapus tournament & buat ulang via Bracket Builder.

**Q: Mendukung double elimination atau group stage?**
A: Saat ini hanya **single elimination**. Group stage ada di roadmap.

---

## 🧰 Git Workflow Cepat

```bash
git status                              # cek file yang berubah
git add .                               # stage semua perubahan
git commit -m "feat: deskripsi fitur"   # commit dengan pesan jelas
git push origin main                    # push ke remote
```

---

## 📞 Kontak & Dukungan

- **Developer**: Jason Anthony Nugroho
- **Repo**: `JasonAnthonyNugroho/tugas_ahkir`
- **Institusi**: Universitas Kristen Duta Wacana (UKDW)

---

<p align="center">
  <sub>Dibuat dengan ❤️ untuk Rector Cup UKDW</sub>
</p>
