<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ChatApplication')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @if(session('success'))
        <div class="toast toast-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="toast toast-error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="toast toast-error">{{ $errors->first() }}</div>
    @endif

    @yield('content')
</body>
</html>
