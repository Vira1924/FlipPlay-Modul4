# FlipPlay REST API — Demo Modul 4 (Laravel 12)

Pengembangan lanjutan **FlipPlay** dari simulasi OOP berbasis terminal (Modul 3) menjadi **REST API** menggunakan **Laravel 12**, Eloquent ORM, Migration, Form Request Validation, API Resource, dan relasi *many-to-many* (`belongsToMany`).

## Setup Awal (MySQL)

1. Nyalakan MySQL melalui **XAMPP** atau **Laragon**.
2. Buat database bernama `flipplay_api` melalui phpMyAdmin (`http://localhost/phpmyadmin`) atau HeidiSQL.
3. Pastikan file `.env` sudah sesuai:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=flipplay_api
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Jalankan perintah berikut di folder `flipplay-api`:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate:fresh --seed
   php artisan serve
   ```

## Daftar Endpoint API

| Operasi | Method | Endpoint | Keterangan |
| :--- | :--- | :--- | :--- |
| Daftar seluruh media | `GET` | `/api/media` | Pagination, mendukung filter `?title=...` dan `?type=...` |
| Detail satu media | `GET` | `/api/media/{media}` | Menampilkan relasi `users` (`whenLoaded`) |
| Tambah media baru | `POST` | `/api/media` | Validasi `StoreMediaRequest` (`201` / `422`) |
| Ubah data media | `PATCH` | `/api/media/{media}` | Validasi parsial `sometimes` (`200` / `422`) |
| Hapus media | `DELETE` | `/api/media/{media}` | Menghapus media (`200` / `404`) |
| Daftar pengguna | `GET` | `/api/users` | Menampilkan daftar pengguna |
| Detail pengguna | `GET` | `/api/users/{user}` | Menampilkan data user beserta `media` (`whenLoaded`) |
| Tambah pengguna baru | `POST` | `/api/users` | Validasi `StoreUserRequest` (`201` / `422`) |
| Koleksi media pengguna | `GET` | `/api/users/{user}/collection` | Menampilkan daftar media di koleksi user |
| Tambah ke koleksi | `POST` | `/api/users/{user}/collection` | Custom action `attach()`, `409` jika duplikat |
| Hapus dari koleksi | `DELETE` | `/api/users/{user}/collection/{media}` | Custom action `detach()`, `404` jika belum ada |

File `postman_collection.json` juga tersedia untuk langsung di-import ke Postman.
