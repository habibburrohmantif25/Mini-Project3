# Mini Project 3 — Sistem Pelaporan Insiden MBG

## Identitas Mahasiswa

- Nama: Habib Burrohman
- NIM: 251511039
- Kelas: 2B-D3

## Kebutuhan Environment

- PHP >= 8.3
- Composer
- Node.js & npm
- MySQL

## Langkah Instalasi

```bash
git clone <url-repositori>
cd Mini-Project3
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
```

## Konfigurasi Database

Buat database MySQL dengan nama `db_laporan_mbg`, lalu pastikan `.env` berisi:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_laporan_mbg
DB_USERNAME=root
DB_PASSWORD=
```

## Migration dan Seeding

```bash
php artisan migrate --seed
```

Untuk reset dari awal:

```bash
php artisan migrate:fresh --seed
```

## Akun Demo

Setelah seeding, akun berikut tersedia:

- Pelapor: pelapor@mbg.test / password
- Pemantau: pemantau@mbg.test / password
- Koordinator: koordinator@mbg.test / password

## Menjalankan Aplikasi

```bash
php artisan serve
```

Buka http://localhost:8000 di browser.

