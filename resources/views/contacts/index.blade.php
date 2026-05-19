@extends('layouts.app')
@section('title','Kontak - ChatApplication')
@section('content')
<div class="page-wrap">
    <header class="page-header">
        <h1>Kontak Saya</h1>
        <div class="header-actions">
            <a class="btn" href="{{ route('chat.index') }}">Kembali</a>
            <a class="btn primary" href="{{ route('contacts.create') }}">Tambah Kontak</a>
        </div>
    </header>

    <div class="list-card">
        @forelse($contacts as $contact)
            <div class="list-row">
                <div class="avatar">{{ strtoupper(substr($contact->saved_name, 0, 1)) }}</div>
                <div class="grow">
                    <strong>{{ $contact->saved_name }}</strong>
                    <small>{{ $contact->phone }}</small>
                </div>
                @if($contact->target)
                    <a class="btn small primary" href="{{ route('chat.with', $contact->target) }}">Chat</a>
                @endif
                <a class="btn small" href="{{ route('contacts.edit', $contact) }}">Edit Nama</a>
                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Hapus kontak ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn small danger" type="submit">Hapus</button>
                </form>
            </div>
        @empty
            <p>Belum ada kontak. Klik tombol Tambah Kontak untuk menambahkan kontak baru.</p>
        @endforelse
    </div>
</div>
@endsection
