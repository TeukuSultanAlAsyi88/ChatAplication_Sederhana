@extends('layouts.app')
@section('title','Buat Grup - ChatApplication')
@section('content')
<div class="page-wrap">
    <div class="form-card">
        <h1>Buat Grup</h1>
        <form method="POST" action="{{ route('groups.store') }}">
            @csrf
            <label>Nama Grup</label>
            <input name="name" required>
            <label>Deskripsi</label>
            <textarea name="description"></textarea>
            <div class="form-actions">
                <a class="btn" href="{{ route('groups.index') }}">Batal</a>
                <button class="btn primary" type="submit">Buat Grup</button>
            </div>
        </form>
    </div>
</div>
@endsection
