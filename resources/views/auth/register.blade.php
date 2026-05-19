@extends('layouts.auth')

@section('title', 'Daftar - ChatApplication')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand auth-brand">
            <div class="logo">CA</div>
            <div>
                <h2>ChatApplication</h2>
                <p>Buat akun baru</p>
            </div>
        </div>

        <h1>Daftar</h1>
        <p>Isi data akun tanpa status profil.</p>

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input class="form-control" type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label>Nomor HP</label>
                <input class="form-control" type="text" name="phone" value="{{ old('phone') }}" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label>Tanggal Lahir</label>
                <input class="form-control" type="date" name="birth_date" value="{{ old('birth_date') }}" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input class="form-control" type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input class="form-control" type="password" name="password_confirmation" required>
            </div>
            <button class="btn w-full" type="submit">Daftar</button>
        </form>

        <p class="auth-footer">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </div>
</div>
@endsection
