@extends('layouts.auth')

@section('title', 'Masuk - ChatApplication')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand auth-brand">
            <div class="logo">CA</div>
            <div>
                <h2>ChatApplication</h2>
                <p>Chat aja yaa</p>
            </div>
        </div>

        <h1>Masuk</h1>
        <p>Login pakai email atau nomor HP.</p>

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="form-group">
                <label>Email atau Nomor HP</label>
                <input
                    class="form-control"
                    type="text"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                >

                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    class="form-control"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >

                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <button class="btn w-full" type="submit">Masuk</button>
        </form>

        <p class="auth-footer">
            Belum punya akun?
            <a href="{{ route('register') }}">Daftar</a>
        </p>
    </div>
</div>
@endsection