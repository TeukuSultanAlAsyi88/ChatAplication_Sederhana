@extends('layouts.app')
@section('title','Detail Kontak - ChatApplication')
@section('content')
<div class="page-wrap">
    <div class="profile-card">
        <div class="avatar large">{{ strtoupper(substr($contact->saved_name, 0, 1)) }}</div>
        <h1>{{ $contact->saved_name }}</h1>
        <p class="muted">{{ $contact->phone }}</p>
        <div class="hero-actions">
            <a class="btn" href="{{ route('contacts.index') }}">Kembali</a>
            <a class="btn" href="{{ route('contacts.edit', $contact) }}">Edit Nama</a>
            @if($contact->target)
                <a class="btn primary" href="{{ route('chat.with', $contact->target) }}">Chat</a>
            @endif
        </div>
    </div>
</div>
@endsection
