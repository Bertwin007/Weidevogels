<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Greidefugels — weidevogels en greideland in Fryslân. Platform van Agrarisch Natuurfonds Fryslân.">
    <title>Greidefugels — Agrarisch Natuurfonds Fryslân</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Georgia, "Times New Roman", serif;
            background: #f5f0e6;
            color: #1b4332;
        }
        .hero {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
            background: linear-gradient(165deg, #1b4332 0%, #2d6a4f 55%, #40916c 100%);
            color: #fff;
            text-align: center;
        }
        .hero-inner { max-width: 40rem; }
        .badge {
            display: inline-block;
            margin-bottom: 1rem;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            font-family: system-ui, sans-serif;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
        }
        h1 {
            margin: 0 0 1rem;
            font-size: clamp(2rem, 5vw, 3rem);
            line-height: 1.15;
            font-weight: 600;
        }
        .lead {
            margin: 0 auto 1.5rem;
            max-width: 32rem;
            font-family: system-ui, sans-serif;
            font-size: 1.1rem;
            line-height: 1.65;
            color: #d8f3dc;
        }
        .content {
            max-width: 42rem;
            margin: 0 auto;
            padding: 3rem 1.5rem 4rem;
            font-family: system-ui, sans-serif;
            line-height: 1.7;
        }
        .content h2 {
            font-family: Georgia, serif;
            font-size: 1.35rem;
            margin: 0 0 0.75rem;
        }
        .content p { margin: 0 0 1rem; color: #344e41; }
        .links { margin-top: 2rem; display: flex; flex-wrap: wrap; gap: 0.75rem; }
        .links a {
            color: #1b4332;
            text-decoration: none;
            padding: 0.6rem 1rem;
            border: 1px solid #95d5b2;
            border-radius: 0.5rem;
            background: #fff;
        }
        .links a:hover { background: #d8f3dc; }
        footer {
            text-align: center;
            padding: 1.5rem;
            font-family: system-ui, sans-serif;
            font-size: 0.85rem;
            color: #52796f;
            border-top: 1px solid #d8e2dc;
        }
    </style>
</head>
<body>
    <header class="hero">
        <div class="hero-inner">
            <div class="badge">Agrarisch Natuurfonds Fryslân</div>
            <h1>Vastleggen wat leeft in ons greideland</h1>
            <p class="lead">
                Greidefugels.nl wordt opnieuw opgebouwd — een plek voor weidevogels,
                verhalen uit het greideland en betrokkenheid bij ANF-projecten in Fryslân.
            </p>
        </div>
    </header>

    <main class="content">
        <h2>Fase A — site live</h2>
        <p>
            De technische basis staat: domein, beveiligde verbinding (HTTPS) en Laravel op de server.
            De volgende stap is het ontwerpen van het platform samen — bewust en from scratch.
        </p>
        <div class="links">
            <a href="https://www.agrarischnatuurfondsfryslan.nl/" rel="noopener">Naar ANF Fryslân</a>
        </div>
    </main>

    <footer>
        &copy; {{ date('Y') }} Agrarisch Natuurfonds Fryslân · greidefugels.nl
    </footer>
</body>
</html>
