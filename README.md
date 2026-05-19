# ChatAplication_Sederhana

ChatAplication adalah aplikasi web chat berbasis Laravel 10 + MySQL. Project ini sudah disesuaikan dari struktur Laravel asli, bukan stub, sehingga `php artisan` bisa berjalan normal.

## Fitur

- Login memakai email/nomor HP + password.
- Register memakai nama, nomor HP, email, tanggal lahir, status, dan password.
- Kontak setiap akun berbeda seperti WhatsApp.
- Kontak bisa tambah, edit, hapus, dan lihat info profil.
- Chat private memakai `user_id`, bukan nama kontak, supaya pesan tidak nyasar.
- Grup chat dan tambah anggota setelah grup dibuat.
- Online/offline, last seen, centang terkirim/dibaca.
- Reply message, pinned chat, archive chat.
- Upload gambar/file di chat.
- Edit profil, bio/status, nomor HP, email, password.
- Upload/ganti/hapus foto profil.

## Setup Lokal

1. Buat database MySQL bernama `chattatan`.
2. Copy `.env.example` menjadi `.env` jika belum ada.
3. Pastikan `.env` memakai:

```env
APP_NAME=Chattatan
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chattatan
DB_USERNAME=root
DB_PASSWORD=
```

4. Jalankan:

```bash
composer install
php artisan key:generate
php artisan migrate:fresh
php artisan storage:link
npm install
npm run dev
```

5. Terminal lain:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`.
