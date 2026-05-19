# Database Model

## users
Menyimpan akun asli pengguna.

Field utama:
- id
- name
- phone
- email
- birth_date
- status
- avatar
- is_online
- last_seen_at
- show_online
- show_last_seen
- password

## contacts
Kontak personal per akun.

Field utama:
- owner_user_id: akun yang menyimpan kontak
- target_user_id: akun target jika nomor HP sudah terdaftar
- saved_name
- phone
- status

## chat_rooms
Ruang chat private atau grup.

Field utama:
- type
- title
- created_by
- target_user_id
- pinned
- archived

## messages
Pesan dalam chat.

Field utama:
- room_id
- sender_id
- receiver_id
- body
- encrypted_body
- attachment_url
- attachment_type
- reply_to_id
- delivered_at
- read_at

## groups dan group_members
Menyimpan grup dan anggota grup.
