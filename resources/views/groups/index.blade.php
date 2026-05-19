@extends('layouts.app')
@section('title','Grup - ChatApplication')
@section('content')
<div class="page-wrap">
    <header class="page-header">
        <h1>Grup</h1>
        <div class="header-actions">
            <a class="btn" href="{{ route('chat.index') }}">Kembali</a>
            <a class="btn primary" href="{{ route('groups.create') }}">Buat Grup</a>
        </div>
    </header>
    <div class="list-card">
        @forelse($groups ?? [] as $group)
            <div class="list-row">
                <div class="avatar muted-avatar">G</div>
                <div class="grow">
                    <strong>{{ $group->name }}</strong>
                    <small>{{ $group->members_count ?? 0 }} anggota</small>
                </div>
                <a class="btn small" href="{{ route('groups.show', $group) }}">Buka</a>
            </div>
        @empty
            <p>Belum ada grup.</p>
        @endforelse
    </div>
</div>
@endsection
