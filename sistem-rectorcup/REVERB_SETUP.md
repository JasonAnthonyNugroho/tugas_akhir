# Laravel Reverb Setup Guide

## ⚠️ MASALAH YANG SERING TERJADI

Jika dashboard guest tidak update otomatis, periksa hal berikut:

## 1. Cek Konfigurasi .env

Pastikan file `.env` memiliki konfigurasi berikut:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

## 2. Jalankan Reverb Server

**WAJIB** jalankan Reverb server di terminal terpisah:

```bash
php artisan reverb:start
```

Atau dengan port spesifik:
```bash
php artisan reverb:start --port=8080
```

## 3. Cek Log Laravel

Saat update skor, periksa log di `storage/logs/laravel.log`:
- Harus ada: "Broadcasting ScoreUpdated"
- Harus ada: "ScoreUpdated event created"
- Harus ada: "ScoreUpdated broadcasting with data"

Jika tidak ada log ini, berarti BROADCAST_CONNECTION belum di-set ke 'reverb'.

## 4. Cek Console Browser

Buka dashboard guest, lalu tekan F12 → Console:
- Harus ada: "Reverb initialized with config"
- Harus ada: "Reverb: Connected successfully!"
- Status badge di pojok kanan atas harus berubah dari "Connecting..." menjadi "Live"

## 5. Cek Network Tab

Di F12 → Network → WS (WebSocket):
- Harus ada koneksi WebSocket aktif ke ws://localhost:8080

## Troubleshooting

### Status tetap "Disconnected"
1. Cek apakah Reverb server jalan: `php artisan reverb:start`
2. Cek firewall tidak memblok port 8080
3. Cek konfigurasi host di .env sesuai (localhost/IP yang benar)

### Event tidak terkirim
1. Cek `BROADCAST_CONNECTION=reverb` di .env
2. Clear config cache: `php artisan config:clear`
3. Restart Reverb server

### Port sudah digunakan
```bash
# Cek port yang digunakan
netstat -ano | findstr :8080

# Ganti port di .env
REVERB_PORT=8081
REVERB_SERVER_PORT=8081
```

## Perintah yang Perlu Dijalankan

```bash
# 1. Clear config cache
php artisan config:clear

# 2. Jalankan Reverb server
php artisan reverb:start

# 3. Di terminal terpisah, jalankan Laravel
php artisan serve

# 4. Atau jika pakai Apache/Nginx, pastikan sudah running
```

## Perubahan yang Sudah Dibuat

1. ✅ Logging di Controller dan Events
2. ✅ Connection status indicator di dashboard
3. ✅ Console logging di JavaScript
4. ✅ Update .env.example dengan konfigurasi Reverb

## Langkah Setelah Setup

1. Buka dashboard guest di browser
2. Buka console (F12)
3. Cek status koneksi
4. Update skor di admin panel
5. Lihat console untuk log event
6. Dashboard guest harus auto-update!
