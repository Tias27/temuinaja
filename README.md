# Temuin Aja

Aplikasi Laravel untuk laporan barang hilang dan ditemukan di kampus. Pengunjung mencari
dan mengirim laporan tanpa akun. Petugas memeriksa, mempublikasikan, lalu menyelesaikan
laporan saat barang ditemukan atau diambil.

## Teknologi

- Laravel 12 dan PHP 8.3
- MySQL untuk aplikasi; SQLite in-memory untuk pengujian
- Blade, CSS, JavaScript, dan Vite
- GD dengan dukungan WebP untuk memproses foto

## Struktur kode

| Lokasi                           | Tanggung jawab                                                                |
| -------------------------------- | ----------------------------------------------------------------------------- |
| `app/Http/Controllers`           | Request publik, autentikasi admin, dan pengelolaan laporan                    |
| `app/Http/Requests`              | Validasi pengiriman laporan                                                   |
| `app/Models`                     | Model admin, laporan, penyelesaian, dan catatan proses lama                   |
| `routes/web.php`                 | Route publik dan admin                                                        |
| `database/migrations`            | Skema database                                                                |
| `database/seeders`               | Akun admin dan data contoh                                                    |
| `resources/views/layouts`        | Kerangka halaman beranda, publik, dan admin                                   |
| `resources/views/home`           | Bagian beranda dan pratinjau: hero, laporan, cara kerja, serta ajakan melapor |
| `resources/views/illustrations`  | Ilustrasi SVG beranda                                                         |
| `resources/views/public`         | Katalog, formulir, detail, dan konfirmasi laporan                             |
| `resources/views/admin/partials` | Pemeriksaan, formulir penyelesaian, dan riwayat penyelesaian                  |
| `resources/views/partials`       | Tampilan kecil yang dipakai bersama                                           |
| `resources/css/app.css`          | Gaya katalog dan halaman publik                                               |
| `resources/css/home.css`         | Entry CSS beranda; mengimpor gaya halaman dan komponen                        |
| `resources/css/pages`            | Gaya khusus halaman                                                           |
| `resources/css/components`       | Tema bersama, kartu laporan, cara kerja, dan ajakan melapor                   |
| `resources/css/admin.css`        | Gaya panel admin                                                              |
| `resources/js/home.js`           | Entry interaksi beranda, slider laporan, dan animasi ilustrasi                |
| `resources/js/app.js`            | Navigasi publik dan pratinjau foto formulir                                   |
| `public/index.php`               | Entry HTTP Laravel                                                            |
| `public/build`                   | Aset hasil build Vite; dibuat ulang, bukan diedit manual                      |
| `storage/app/private/laporan`    | Foto laporan privat                                                           |
| `tests`                          | Pengujian aplikasi                                                            |

Beranda dan `/mockup` memakai `home/index.blade.php` dengan flag `$preview`.
Route `/mockup` hanya tersedia pada environment `local`.
`resources/views/welcome.blade.php` merupakan template bawaan Laravel dan tidak dipakai route aktif.

Komponen `site.css`, `partials/site-header`, dan `partials/site-footer` dipakai lintas halaman.
Kartu dan gambar laporan berbagi `partials/report-card`, `partials/report-media`, serta
`components/report-card.css`; gambar ilustrasi hanya menggantikan data dengan kode sampel yang dikenal.
Gaya katalog, detail/formulir, dan admin berada di `resources/css/pages`.

## Alur admin

Daftar admin memisahkan **Barang ditemukan** dan **Barang hilang**, masing-masing dengan
tahap Menunggu, Dipublikasikan, Selesai, dan Ditolak. Petugas bisa langsung
mempublikasikan atau menolak laporan baru. Saat laporan selesai, petugas mengisi nama
dan WhatsApp penemu (barang hilang) atau penerima beserta catatan verifikasi
(barang ditemukan). Lokasi penemuan wajib untuk barang hilang. Nama petugas yang
menyelesaikan serta tanggal dan jamnya diambil otomatis dari akun dan waktu server.
Catatan klaim dan penyerahan dari alur lama tetap dapat dilihat di detail admin.
Menu **Pengaturan** di `/admin/pengaturan` menyediakan edit nama/nama pengguna,
ganti kata sandi sendiri, dan penambahan admin petugas. Semua perubahan memerlukan
kata sandi akun yang sedang login. Kata sandi baru minimal 12 karakter; username
harus unik. Pergantian kata sandi mencabut sesi perangkat lain dan mempertahankan
sesi yang sedang dipakai. Daftar admin memakai 10 akun per halaman di desktop
dan 5 akun di mobile. Semua akun petugas memiliki akses pengelolaan yang sama.
Laporan selesai masih tampil di publik selama 48 jam sejak waktu penyelesaian.
Setelah itu, laporan otomatis tersembunyi dari beranda, katalog, pencarian, detail,
konfirmasi, dan foto publik; riwayat serta foto tetap tersedia untuk admin.
Penyaringan dilakukan saat halaman diminta, tanpa cron atau penghapusan data.
Laporan lama memakai waktu penyerahan ke pemilik, atau waktu pembaruan terakhir
jika belum memiliki catatan penyelesaian maupun penyerahan.

## Menjalankan lokal

Pasang dependency:

```sh
composer install
npm ci
```

Untuk instalasi baru, salin `.env.example` ke `.env`, lalu isi koneksi database dan
`ADMIN_NAME`, `ADMIN_USERNAME`, serta `ADMIN_PASSWORD`. Konfigurasi kanal dan tempat
layanan berada pada `TEMUIN_CONTACT`, `TEMUIN_ADDRESS`, dan `TEMUIN_HOURS`.
Isi `TEMUIN_WHATSAPP` dengan nomor WhatsApp berawalan `62` tanpa tanda `+` untuk
menampilkan tombol chat melayang pada halaman publik.

Detail barang memakai nomor petugas tersebut untuk chat tentang laporan tertentu,
dengan kode dan tautan laporan otomatis terisi. Nomor WhatsApp pelapor wajib pada
laporan baru, hanya tampil di detail admin, dan tidak disertakan dalam pesan publik.
Laporan lama tetap bisa dibuka tanpa nomor kontak. Jalankan `php artisan migrate`
setelah memperbarui aplikasi untuk menambahkan kolom kontak.

```sh
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
```

Jalankan server dan Vite di terminal terpisah:

```sh
php artisan serve
npm run dev
```

Di Laragon, arahkan document root situs ke folder `public`. Pastikan PHP yang dipakai
CLI maupun web server memuat GD dan mendukung WebP. Gunakan akun admin yang disiapkan
melalui environment untuk membuka `/admin/login`.

Form laporan menerima maksimal 3 foto, masing-masing 5 MB. Pada `php.ini` yang
ditunjukkan oleh `php --ini`, aktifkan GD dan sesuaikan batas unggah:

```ini
extension=gd
upload_max_filesize=5M
post_max_size=20M
```

Mulai ulang server PHP setelah mengubah konfigurasi. Periksa `php --ri gd` dan
pastikan `WebP Support` aktif; PHP CLI dan web server dapat memakai konfigurasi berbeda.

`SampleLaporanSeeder` bersifat opsional untuk demonstrasi lokal dan membuat gambar
contoh beserta laporan yang sudah terbit.

## Format kode

PHP mengikuti Laravel Pint. Blade memakai blade-formatter. CSS, JavaScript, JSON, dan
README memakai Prettier. Konfigurasinya tersimpan di `pint.json`, `.bladeformatterrc.json`,
`.prettierrc.json`, dan `.editorconfig`.

```sh
composer format
npm run format
```

Periksa format tanpa mengubah berkas:

```sh
composer format:check
npm run format:check
```

Dependency, cache, foto, dan aset hasil build dikecualikan dari formatter. CSS dan
JavaScript dalam `public/build` tetap dipadatkan untuk browser; edit sumbernya di
`resources`, lalu jalankan `npm run build`.

## Validasi

Panduan hosting tersedia di [DEPLOYMENT.md](DEPLOYMENT.md). Catatan audit internal
disimpan secara lokal dan tidak disertakan di repository publik. Untuk mengganti
sandi admin lewat prompt tersembunyi, gunakan `php artisan admin:password <nama-pengguna>`.
Host publik mengikuti `APP_URL`; host tambahan dapat diatur melalui
`APP_ALLOWED_HOSTS`. Produksi harus memakai document root `public` dan HTTPS.

```sh
php artisan test
php artisan view:cache
npm run build
```

Konfigurasi `phpunit.xml` menggunakan SQLite in-memory dan session/cache array sehingga
pengujian bawaan tidak memakai database aplikasi. `view:cache` memeriksa kompilasi Blade;
setelah mengubah aset atau menambah entry Vite, jalankan build agar manifest diperbarui.

## Repository dan CI

Repository menyimpan source code, migrasi, tes, dan lockfile dependency. File `.env`,
database, foto laporan, log, cache, `vendor`, `node_modules`, dan hasil build tidak
diunggah. Gunakan `.env.example` sebagai template tanpa kredensial asli.

GitHub Actions menjalankan pemeriksaan format, tes PHP, audit dependency, dan build
Vite pada setiap push dan pull request. Hasil build tersedia sebagai artifact CI
`temuin-public-build`. Deployment hosting belum diotomatisasi.
