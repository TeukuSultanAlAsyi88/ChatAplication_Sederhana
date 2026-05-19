# Fitur Ganti Foto Profil

Versi ini sudah menambahkan upload/ganti/hapus foto profil di halaman Profile.

## File yang terlibat

- `app/Http/Controllers/ProfileController.php`
- `app/Models/User.php`
- `database/migrations/2026_05_16_000001_create_users_table.php`
- `resources/views/profile/show.blade.php`
- `resources/views/profile/edit.blade.php`
- `resources/views/contacts/index.blade.php`
- `resources/views/contacts/show.blade.php`
- `public/uploads/profile/default-avatar.svg`
- `storage/app/public/profile/`

## Cara menjalankan upload avatar di Laravel

Jalankan:

```bash
php artisan storage:link
```

Foto profil akan disimpan di:

```text
storage/app/public/profile
```

URL tampilannya memakai:

```blade
{{ auth()->user()->avatar_url }}
```

## Sinkron avatar

Avatar user akan dipakai di:

- Profil saya
- Edit profil
- Info kontak
- List kontak
- Tampilan chat/grup yang menggunakan data user

`user_id` tetap tidak berubah saat foto, nama, bio, nomor HP, atau email diedit. Jadi chat tidak nyasar.
