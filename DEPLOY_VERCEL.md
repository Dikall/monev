# Deployment Vercel

Proyek Laravel 10 ini sudah disiapkan untuk berjalan sebagai Vercel Serverless Function menggunakan `vercel-php`.

## 1. Import repository

Buka tombol berikut, lalu pilih akun/team Vercel dan klik **Create**:

[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https%3A%2F%2Fgithub.com%2FDikall%2Fmonev)

Framework Preset boleh dibiarkan **Other**. Konfigurasi build sudah berada di `vercel.json` dan `composer.json`.

## 2. Environment Variables wajib

Masukkan variabel berikut pada layar import atau melalui **Project Settings → Environment Variables**. Terapkan ke Production, Preview, dan Development sesuai kebutuhan.

| Nama | Nilai |
| --- | --- |
| `APP_NAME` | `SIMANTAP MONEV` |
| `APP_KEY` | Key Laravel yang sama dengan sistem saat ini (`base64:...`) |
| `APP_URL` | URL Vercel hasil deployment, mis. `https://monev.vercel.app` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | Host database PostgreSQL yang sudah tersedia |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | Nama database |
| `DB_USERNAME` | Username database |
| `DB_PASSWORD` | Password database |
| `DB_SSLMODE` | `require` untuk Supabase/host PostgreSQL yang mewajibkan SSL |

Jangan menaruh kredensial database atau `APP_KEY` di repository maupun `vercel.json`.

Jika belum memiliki `APP_KEY`, buat sekali di komputer yang memiliki PHP/Laravel:

```bash
php artisan key:generate --show
```

## 3. Deploy

Klik **Deploy**. Vercel akan otomatis:

1. memasang dependency Composer;
2. menjalankan `npm ci`;
3. membangun aset Vite ke `public/build`;
4. menjalankan Laravel melalui PHP Function `api/index.php`.

Database tidak dibuat dan migration tidak dijalankan oleh konfigurasi deployment ini.

## Catatan serverless

- Session menggunakan cookie agar login tidak bergantung pada filesystem sementara.
- Entry point Laravel berada di `api/index.php` agar Vercel mengeksekusinya sebagai PHP Function, bukan mengirim source PHP sebagai file unduhan.
- Cache menggunakan memory per-request (`array`), queue berjalan sinkron, dan log dikirim ke Vercel (`stderr`).
- Filesystem Vercel bersifat sementara. Fitur upload dokumen masih memerlukan object storage persisten (misalnya Supabase Storage atau S3) agar file baru tidak hilang setelah function didaur ulang. File statis yang sudah ada di `public/` tetap ikut deployment.
- Setelah URL final diketahui, perbarui `APP_URL`, lalu redeploy supaya URL aset dan tautan aplikasi konsisten.

## Pemeriksaan setelah deploy

1. Buka halaman utama dan pastikan CSS/JavaScript termuat.
2. Uji login dan logout.
3. Buka halaman yang membaca data dari database.
4. Periksa **Vercel → Project → Logs** jika muncul HTTP 500.
5. Jangan jalankan migration karena database disebut sudah tersedia.