@extends('layouts.auth')

@section('title', 'ChatApplication')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand auth-brand">
            <div class="logo">CA</div>
            <div>
                <h2>ChatApplication</h2>
                <p>Chat sederhana dan rapi</p>
            </div>
        </div>

        <h1>Selamat Datang</h1>
        <p>Aplikasi chat sederhana untuk kontak personal dan grup.</p>

        <div class="hero-actions">
            <a href="{{ route('login') }}" class="btn primary">Masuk</a>
            <a href="{{ route('register') }}" class="btn">Daftar</a>
        </div>
    </div>
</div>
@endsection
