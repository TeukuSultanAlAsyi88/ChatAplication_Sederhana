@extends('layouts.app')
@section('title','Edit Kontak - ChatApplication')
@section('content')
<div class="page-wrap">
    <div class="form-card">
        <h1>Edit Nama Kontak</h1>
        <form method="POST" action="{{ route('contacts.update', $contact) }}">
            @csrf
            @method('PUT')
            <label>Nama Kontak</label>
            <input name="saved_name" value="{{ old('saved_name', $contact->saved_name) }}" required>
            <p class="muted">Nomor HP: {{ $contact->phone }}</p>
            <div class="form-actions">
                <a class="btn" href="{{ route('contacts.index') }}">Batal</a>
                <button class="btn primary" type="submit">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
