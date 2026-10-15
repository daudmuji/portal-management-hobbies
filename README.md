# Portal Management Hobbies

Aplikasi CRUD User dan Hobi (one-to-many) dengan Laravel: antarmuka Blade
dan REST API yang diamankan JWT.

## Kebutuhan Sistem
- PHP >= 8.2, Composer
- PostgreSQL
- Laravel >= 12

## Instalasi
```bash
git clone https://github.com/daudmuji/portal-management-hobbies.git
cd portal-management-hobbies
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
# atur koneksi database di .env
php artisan migrate --seed
php artisan serve
```

## Akun Login (untuk JWT)
- Email: `admin@example.com`
- Password: `password`

## Halaman Web
- `/users` : daftar, tambah, edit, dan hapus User beserta hobinya

## Endpoint API (prefix `/api`)
| Method | URL | Akses |
|---|---|---|
| POST | /auth/login | Publik |
| POST | /auth/logout | Token |
| POST | /auth/refresh | Token |
| GET | /auth/me | Token |
| GET | /users | Token |
| GET | /users/{id} | Token |
| POST | /users | Token |
| PUT | /users/{id} | Token |
| DELETE | /users/{id} | Token |

Header yang diperlukan: `Accept: application/json` dan
`Authorization: bearer <token>` (kecuali login).
