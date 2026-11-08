<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SURSEA Lantsa Hub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}?v={{ filemtime(public_path('css/base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/passenger.css') }}?v={{ filemtime(public_path('css/passenger.css')) }}">
</head>
<body class="auth-body">

<header class="auth-header">
    <div class="auth-header-inner">
        <a href="{{ route('home') }}" class="brand">@include('partials.logo')</a>
        <nav class="auth-nav">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('schedules') }}">Schedules</a>
            <a href="{{ route('routes') }}">Routes</a>
        </nav>
    </div>
</header>

<main class="auth-wrap">
    @yield('content')
</main>

<script src="{{ asset('js/common.js') }}?v={{ filemtime(public_path('js/common.js')) }}"></script>
<script src="{{ asset('js/passenger.js') }}?v={{ filemtime(public_path('js/passenger.js')) }}"></script>
</body>
</html>
