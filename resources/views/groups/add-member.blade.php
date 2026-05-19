@extends('layouts.app')
@section('title','Tambah Anggota Grup - ChatApplication')
@section('content')
<div class="page-wrap">
    <div class="form-card">
        <h1>Tambah Anggota</h1>
        <form method="POST" action="{{ route('groups.storeMember', $group) }}">
            @csrf
            <label>Nomor HP Anggota</label>
            <input name="phone" required>
            <div class="form-actions">
                <a class="btn" href="{{ route('groups.show', $group) }}">Batal</a>
                <button class="btn primary" type="submit">Tambah</button>
            </div>
        </form>
    </div>
</div>
@endsection
