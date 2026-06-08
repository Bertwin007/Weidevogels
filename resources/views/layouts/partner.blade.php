<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Greidefugels') — Agrarisch Natuurfonds Fryslân</title>
    <meta name="description" content="@yield('meta_description', 'Adopteer een Fries weidegebied en krijg geverifieerd biodiversiteits-bewijs voor uw MVO- en CSRD-verhaal.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/partner.css') }}">
    @stack('head')
</head>
<body class="partner-page">
    @yield('content')
    @stack('scripts')
</body>
</html>
