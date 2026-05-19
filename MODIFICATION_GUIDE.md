# Panduan Modifikasi ChatApplication

Project ini sudah dimodifikasi dari Chattatan menjadi ChatApplication.

## Yang sudah diubah

1. Nama aplikasi diganti ke **ChatApplication** di layout, halaman login/register, welcome, app.html, package.json, dan .env.example.
2. Fitur arsip chat dihapus dari tampilan dan route.
3. Fitur profil dihapus dari tampilan dan route.
4. Fitur kirim foto dan file dihapus dari form chat. Sekarang pesan hanya chat teks biasa.
5. Halaman register tidak memakai status.
6. Kontak tidak memakai status.
7. Kontak tetap bisa ditambah.
8. Kontak bisa edit nama.
9. Kontak bisa dihapus.
10. Bubble chat dibuat kotak sederhana.
11. CSS dibuat standar, rapi, dan tidak terlalu dekoratif.
12. Route chat diperbaiki supaya private chat memakai room yang benar dan tidak nyasar.

## File utama yang diganti

- `routes/web.php`
- `app/Http/Controllers/ChatController.php`
- `app/Http/Controllers/ContactController.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/auth.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/chat/index.blade.php`
- `resources/views/contacts/index.blade.php`
- `resources/views/contacts/create.blade.php`
- `resources/views/contacts/edit.blade.php`
- `resources/views/contacts/show.blade.php`
- `resources/views/groups/index.blade.php`
- `resources/views/groups/create.blade.php`
- `resources/views/groups/show.blade.php`
- `resources/views/groups/add-member.blade.php`
- `resources/views/welcome.blade.php`
- `resources/css/app.css`
- `package.json`
- `.env.example`
- `config/app.php`

## File/folder yang boleh dihapus dari project lama

Karena fitur arsip dan profil dihapus, file berikut tidak dipakai lagi:

- `resources/views/chat/archive.blade.php`
- `resources/views/chat/room.blade.php`
- `resources/views/profile/`
- `app/Http/Controllers/ProfileController.php`
- `docs/PROFILE_AVATAR_UPLOAD.md`
- `public/uploads/profile/`

Database lama masih punya kolom `archived`, `avatar`, `bio`, dan `status`. Itu tidak masalah kalau dibiarkan. Kalau mau benar-benar bersih, buat migration baru untuk drop kolom tersebut, tapi tidak wajib.

## Cara pasang di project kamu

1. Backup project lama dulu.
2. Extract zip modifikasi ini.
3. Copy semua file dari folder hasil extract ke folder project Laravel kamu.
4. Jangan copy folder `node_modules` dari project lama kalau pindah komputer.
5. Jalankan perintah berikut:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Kalau kamu sudah punya `.env`, tidak perlu `cp .env.example .env`; cukup ubah:

```env
APP_NAME=ChatApplication
```

## Catatan penting

Di sandbox ini `npm run build` tidak bisa dites karena `node_modules` dari zip kamu berisi esbuild versi Windows, sedangkan sandbox berjalan di Linux. Di laptop Windows kamu, hapus `node_modules`, lalu jalankan `npm install` ulang.

