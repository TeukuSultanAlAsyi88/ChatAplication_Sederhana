
<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img src="https://laravel.com/img/logomark.min.svg" width="120" alt="Laravel Logo">
    </a>
</p>

<h1 align="center">ChatApplication</h1>

<p align="center">
Realtime Chat Application built with Laravel Framework
</p>

<p align="center">
<img src="https://img.shields.io/badge/Laravel-10-red" alt="Laravel">
<img src="https://img.shields.io/badge/PHP-8.1-blue" alt="PHP">
<img src="https://img.shields.io/badge/MySQL-Database-orange" alt="MySQL">
<img src="https://img.shields.io/badge/Realtime-Reverb-success" alt="Realtime">
<img src="https://img.shields.io/badge/License-MIT-green" alt="License">
</p>

---

# About Project

ChatApplication adalah aplikasi chat realtime berbasis web yang dikembangkan menggunakan framework Laravel.  
Project ini dibuat untuk menyediakan sistem komunikasi modern antar pengguna secara realtime dengan fitur private chat, group chat, online status, contact management, dan realtime broadcasting menggunakan Laravel Reverb serta Laravel Echo.

Aplikasi ini dirancang dengan arsitektur Laravel modern yang memanfaatkan:

- Authentication System
- RESTful Routing
- Event Broadcasting
- Queue & Realtime Communication
- Modern Frontend Build menggunakan Vite
- Secure Middleware Protection
- Database Relational Structure

Project ini cocok digunakan sebagai:

- Sistem komunikasi internal perusahaan
- Aplikasi chat komunitas
- Realtime support system
- Media komunikasi realtime berbasis web
- Pembelajaran implementasi realtime Laravel

---

# Main Features

## Authentication System

- User Register
- User Login
- User Logout
- Session Authentication
- Middleware Protection

## Realtime Chat

- Realtime private messaging
- Instant message delivery
- Live message update
- Realtime event broadcasting

## Group Chat

- Create group
- Add members to group
- Send message to group
- Group conversation management

## Contact Management

- Add contact
- Contact list
- User interaction system

## User Presence

- Online status
- Offline status
- Presence broadcasting

## Modern Laravel Stack

- Laravel Reverb
- Laravel Echo
- Pusher JS
- Vite Asset Bundler
- Sanctum Authentication

---

# Technologies Used

| Technology | Description |
|---|---|
| Laravel 10 | Backend Framework |
| PHP 8.1+ | Server-side Language |
| MySQL | Database |
| Laravel Reverb | Realtime WebSocket Server |
| Laravel Echo | Frontend Broadcasting |
| Pusher JS | Realtime Listener |
| Vite | Frontend Build Tool |
| Composer | PHP Dependency Manager |
| NPM | Frontend Dependency Manager |

---

# Project Structure

```text
ChatApplication/
│
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── vendor/
├── package.json
├── composer.json
└── .env
Jalankan:

```bash
composer install
php artisan key:generate
php artisan migrate:fresh
php artisan storage:link
npm install
npm run dev
```

Terminal lain:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`.
