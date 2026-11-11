# Hosting Temuin Aja

## Kebutuhan

- PHP 8.3 dengan extension `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml`, `curl`, dan GD dengan dukungan WebP
- MySQL dan akun database khusus aplikasi
- Composer 2; Node.js 22 dan npm untuk build aset (bisa dijalankan lewat GitHub Actions)
- HTTPS dan document root yang mengarah ke folder `public`
- Folder `storage` dan `bootstrap/cache` dapat ditulis oleh proses PHP
- Penyimpanan persisten untuk `storage/app/private/laporan`, agar foto tidak hilang saat deploy ulang

Repository: `https://github.com/Tias27/temuinaja`

## Instalasi pertama

1. Clone repository ke folder aplikasi di luar document root publik

    ```sh
    git clone https://github.com/Tias27/temuinaja.git
    cd temuinaja
    composer install --no-dev --prefer-dist --optimize-autoloader
    cp .env.example .env
    ```

2. Isi `.env` langsung di hosting. Contoh pengaturan; ganti seluruh placeholder sebelum dipakai

    ```dotenv
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://domain-kamu.example
    APP_ALLOWED_HOSTS=

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nama_database
    DB_USERNAME=akun_database_khusus
    DB_PASSWORD=isi_password_database

    SESSION_DRIVER=database
    SESSION_SECURE_COOKIE=true
    SESSION_HTTP_ONLY=true
    SESSION_SAME_SITE=lax
    CACHE_STORE=database

    LOG_CHANNEL=daily
    LOG_LEVEL=info
    LOG_DAILY_DAYS=14

    ADMIN_NAME="Petugas Temuin Aja"
    ADMIN_USERNAME=nama_admin_baru
    ADMIN_PASSWORD=isi_password_unik_minimal_12_karakter

    TEMUIN_WHATSAPP=isi_nomor_62_tanpa_plus
    TEMUIN_CONTACT="Kontak petugas"
    TEMUIN_ADDRESS="Lokasi layanan"
    TEMUIN_HOURS="Jam layanan"
    ```

    Host `APP_URL` otomatis diizinkan. Isi `APP_ALLOWED_HOSTS` hanya bila perlu host
    tambahan, dipisahkan koma. Jangan memakai akun MySQL root atau kredensial dari
    lingkungan lokal. Untuk hosting baru, gunakan APP_KEY baru dari langkah berikut

3. Buat key dan skema database, lalu buat admin awal

    ```sh
    php artisan key:generate --force
    php artisan migrate --force
    php artisan db:seed --class=AdminSeeder --force
    ```

    Jalankan seeder awal sebelum `config:cache` karena kredensial seed dibaca dari
    environment. Setelah akun dibuat, hapus nilai `ADMIN_PASSWORD` dari `.env`.
    Seeder tidak menimpa akun yang sudah ada. Pengelolaan berikutnya lewat
    `/admin/pengaturan` atau `php artisan admin:password nama_admin`

4. Build aset

    ```sh
    npm ci
    npm run build
    ```

    Jika hosting tidak menyediakan Node.js, gunakan artifact `temuin-public-build`
    dari workflow GitHub Actions pada commit yang sama. Ekstrak isi artifact ke
    `public/build`, sehingga file `public/build/manifest.json` tersedia

5. Arahkan domain ke `public`, aktifkan HTTPS, lalu cache aplikasi

    ```sh
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

    Jangan memakai permission `777`. Atur kepemilikan dan izin folder sesuai user
    PHP hosting. Foto laporan berada di disk privat; tidak memerlukan
    `php artisan storage:link`

## Batas upload

```ini
upload_max_filesize=5M
post_max_size=20M
```

Form menerima maksimal 3 foto, masing-masing 5 MB dan 4096 x 4096 piksel. Pastikan
batas request web server mencukupi 3 foto dan field form. Restart layanan PHP jika
pengaturan PHP berubah

## Pembaruan berikutnya

Backup database dan foto terlebih dahulu, lalu deploy commit yang sudah lolos CI.
Pertahankan `.env`, APP_KEY, database, dan `storage/app/private` yang sudah ada.
Jangan menjalankan `key:generate`, `migrate:fresh`, atau seeder contoh saat update

```sh
git pull --ff-only
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Untuk hosting tanpa Node.js, ganti dua langkah npm dengan pemasangan artifact build
dari commit yang sedang dideploy. Bila memakai queue worker untuk fitur tambahan,
restart worker setelah deploy

## Pemeriksaan setelah deploy

- `/.env`, `/composer.json`, `/storage/logs/laravel.log`, dan folder source tidak dapat diunduh
- Beranda, katalog hilang/ditemukan, foto, dan aset CSS/JS merespons normal melalui HTTPS
- Buat satu laporan pengujian, publikasikan dari admin, lalu selesaikan; verifikasi foto dan riwayatnya
- Login, logout, edit profil, dan penambahan admin memerlukan autentikasi serta konfirmasi yang sesuai
- Cookie sesi memiliki `Secure`, `HttpOnly`, dan `SameSite=Lax`; debug produksi tetap mati
- Backup dapat dipulihkan dan log ditinjau berkala; konfigurasi reverse proxy hanya mempercayai proxy yang dikelola

Menyembunyikan laporan selesai setelah 48 jam memakai penyaringan saat request,
sehingga fitur ini tidak memerlukan cron. Upload ke GitHub tidak memindahkan
database atau foto lokal; migrasi data tersebut harus dilakukan terpisah melalui
kanal privat bila memang diperlukan
