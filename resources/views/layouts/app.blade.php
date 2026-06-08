<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Greidefûgels') — Agrarisch Natuurfonds Fryslân</title>
    <meta name="description" content="@yield('meta_description', 'Greidefûgels en greideland in Fryslân — Agrarisch Natuurfonds Fryslân.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: system-ui, -apple-system, sans-serif;
            background: #f5f0e6;
            color: #1b4332;
        }
        a { color: #1b4332; }
        .site-header {
            background: #1b4332;
            color: #fff;
            padding: 0.85rem 1.5rem;
        }
        .site-header-inner {
            max-width: 56rem;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .brand {
            color: #fff;
            text-decoration: none;
            font-family: Georgia, serif;
            font-size: 1.15rem;
            font-weight: 600;
        }
        nav { display: flex; flex-wrap: wrap; gap: 0.5rem 1rem; }
        nav a {
            color: #d8f3dc;
            text-decoration: none;
            font-size: 0.95rem;
        }
        nav a:hover { color: #fff; }
        main { flex: 1; }
        .container {
            max-width: 56rem;
            margin: 0 auto;
            padding: 2rem 1.5rem 3rem;
        }
        .container-narrow { max-width: 40rem; }
        h1, h2 {
            font-family: Georgia, "Times New Roman", serif;
            line-height: 1.2;
        }
        h1 { margin: 0 0 1rem; font-size: clamp(1.75rem, 4vw, 2.25rem); }
        h2 { margin: 0 0 0.75rem; font-size: 1.35rem; }
        p { line-height: 1.65; color: #344e41; }
        .lead { font-size: 1.05rem; color: #52796f; }
        .alert {
            padding: 0.85rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.25rem;
        }
        .alert-success { background: #d8f3dc; border: 1px solid #95d5b2; }
        .alert-info { background: #e8f5e9; border: 1px solid #b7e4c7; }
        .alert-error { background: #ffe5e5; border: 1px solid #ffb3b3; color: #7f1d1d; }
        .btn {
            display: inline-block;
            padding: 0.65rem 1.15rem;
            border-radius: 0.5rem;
            border: none;
            background: #40916c;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:hover { background: #2d6a4f; }
        .btn-secondary {
            background: #fff;
            color: #1b4332;
            border: 1px solid #95d5b2;
        }
        .btn-secondary:hover { background: #d8f3dc; }
        .btn-donate { background: #bc6c25; }
        .btn-donate:hover { background: #9a5518; }
        label { display: block; font-weight: 600; margin-bottom: 0.35rem; }
        input[type=text], input[type=email], input[type=password], input[type=file], textarea, select {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 1px solid #b7e4c7;
            border-radius: 0.45rem;
            margin-bottom: 1rem;
            font: inherit;
        }
        textarea { min-height: 6rem; resize: vertical; }
        .field-help { margin: -0.5rem 0 1rem; font-size: 0.9rem; color: #52796f; }
        .card {
            background: #fff;
            border: 1px solid #d8e2dc;
            border-radius: 0.75rem;
            overflow: hidden;
        }
        .card-body { padding: 1.25rem; }
        .grid-moments {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr));
        }
        .moment-card img {
            width: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            display: block;
        }
        .moment-card .card-body { padding: 1rem; }
        .moment-card h3 {
            margin: 0 0 0.35rem;
            font-family: Georgia, serif;
            font-size: 1.05rem;
        }
        .moment-card p { margin: 0; font-size: 0.95rem; }
        .meta { font-size: 0.85rem; color: #52796f; margin-top: 0.5rem; }
        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 1.5rem 0;
        }
        .stat {
            background: #fff;
            border: 1px solid #d8e2dc;
            border-radius: 0.5rem;
            padding: 0.85rem 1rem;
            min-width: 8rem;
        }
        .stat strong { display: block; font-size: 1.5rem; color: #1b4332; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td { padding: 0.6rem; border-bottom: 1px solid #d8e2dc; text-align: left; }
        .badge {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.75rem;
            background: #e8f5e9;
        }
        footer {
            text-align: center;
            padding: 1.5rem;
            font-size: 0.85rem;
            color: #52796f;
            border-top: 1px solid #d8e2dc;
        }
        .hero {
            background: linear-gradient(165deg, #1b4332 0%, #2d6a4f 55%, #40916c 100%);
            color: #fff;
            padding: 3rem 1.5rem;
            text-align: center;
        }
        .hero-inner { max-width: 40rem; margin: 0 auto; }
        .hero h1 { color: #fff; }
        .hero .lead { color: #d8f3dc; }
        .checkbox-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
        .checkbox-row input { width: auto; margin: 0; }
    </style>
    @stack('head')
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner">
            <a class="brand" href="{{ route('home') }}">Greidefûgels</a>
            <nav>
                <a href="{{ route('home') }}#scan">Greide-scan</a>
                <a href="{{ route('moments.index') }}">Momenten</a>
                <a href="{{ route('donate') }}">Steun ANF</a>
                @auth
                    @if(auth()->user()->isAnnotator())
                        <a href="{{ route('annotate.index') }}">Annoteren</a>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">Beheer</a>
                        <a href="{{ route('admin.callcenter') }}">Callcenter</a>
                        <a href="{{ route('admin.submissions.index') }}">Inzendingen</a>
                        <a href="{{ route('admin.esg-reports.index') }}">ESG-rapporten</a>
                    @endif
                    <form action="{{ route('logout') }}" method="post" style="display:inline">
                        @csrf
                        <button type="submit" style="background:none;border:none;color:#d8f3dc;cursor:pointer;font:inherit">Uitloggen</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Inloggen</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @if(session('success'))
            <div class="container"><div class="alert alert-success">{{ session('success') }}</div></div>
        @endif
        @if(session('info'))
            <div class="container"><div class="alert alert-info">{{ session('info') }}</div></div>
        @endif
        @if($errors->any())
            <div class="container">
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer>
        &copy; {{ date('Y') }} Agrarisch Natuurfonds Fryslân · greidefugels.nl
    </footer>
</body>
</html>
