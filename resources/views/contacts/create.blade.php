@extends('layouts.app')
@section('title','Tambah Kontak - ChatApplication')
@section('content')
<div class="page-wrap">
    <div class="form-card">
        <h1>Tambah Kontak</h1>
        <form method="POST" action="{{ route('contacts.store') }}">
            @csrf
            <label>Nama Kontak</label>
            <input name="saved_name" value="{{ old('saved_name') }}" required>
            <label>Nomor HP</label>
            <input name="phone" value="{{ old('phone', $phone ?? '') }}" required>
            <div class="form-actions">
                <a class="btn" href="{{ route('contacts.index') }}">Batal</a>
                <button class="btn primary" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
