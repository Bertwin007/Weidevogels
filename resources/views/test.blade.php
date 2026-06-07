<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test — greidefugels.nl</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(160deg, #1a4d3a 0%, #2d6a4f 45%, #95d5b2 100%);
            color: #fff;
            padding: 1.5rem;
        }
        main {
            max-width: 32rem;
            text-align: center;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 1.25rem;
            padding: 2.5rem 2rem;
            backdrop-filter: blur(8px);
        }
        h1 { margin: 0 0 0.75rem; font-size: 1.75rem; }
        p { margin: 0.5rem 0; line-height: 1.6; color: #e8f5e9; }
        .ok {
            display: inline-block;
            margin-top: 1.25rem;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            background: #40916c;
            font-weight: 600;
            font-size: 0.95rem;
        }
        small { display: block; margin-top: 1.5rem; opacity: 0.85; }
    </style>
</head>
<body>
    <main>
            <h1>greidefugels.nl werkt</h1>
            <p>Laravel {{ app()->version() }} draait op productie.</p>
            <p>Platform v2 — we ontwerpen opnieuw vanaf scratch.</p>
        <span class="ok">Deploy OK</span>
        <small>{{ now()->timezone('Europe/Amsterdam')->format('d-m-Y H:i') }} (NL)</small>
    </main>
</body>
</html>
