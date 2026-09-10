<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Dokumentasi interaktif Mini-STESY API">
    <title>Mini-STESY API</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui.css">
    <style>
        :root {
            --slate-950: #0f172a;
            --sky-700: #0369a1;
            --sky-50: #f0f9ff;
            --paper: #ffffff;
            --signal: #38bdf8;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--sky-50);
            color: var(--slate-950);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .docs-header {
            padding: 28px clamp(20px, 5vw, 72px) 32px;
            background: var(--slate-950);
            color: var(--paper);
        }

        .docs-kicker {
            margin: 0 0 8px;
            color: var(--signal);
            font: 700 12px/1.2 ui-monospace, SFMono-Regular, Menlo, monospace;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .docs-title {
            margin: 0;
            font-size: clamp(28px, 4vw, 46px);
            line-height: 1.05;
            letter-spacing: -.035em;
        }

        .docs-summary {
            max-width: 720px;
            margin: 12px 0 0;
            color: #cbd5e1;
            font-size: 15px;
            line-height: 1.6;
        }

        .docs-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 18px;
            align-items: center;
            margin-top: 20px;
            font: 600 12px/1.4 ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .docs-meta a { color: var(--paper); text-underline-offset: 4px; }
        .docs-meta a:focus-visible { outline: 3px solid var(--signal); outline-offset: 4px; }

        #swagger-ui {
            max-width: 1500px;
            margin: 0 auto;
            padding: 18px clamp(4px, 2vw, 28px) 48px;
        }

        .swagger-ui .topbar { display: none; }
        .swagger-ui .info { margin: 24px 0; }

        @media (max-width: 600px) {
            .docs-header { padding-top: 22px; }
            #swagger-ui { padding-inline: 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; }
        }
    </style>
</head>
<body>
    <header class="docs-header">
        <p class="docs-kicker">Beacon Engineering &middot; Telemetry Reference</p>
        <h1 class="docs-title">Mini-STESY API</h1>
        <p class="docs-summary">
            Endpoint mobile dan ingest alat. Baca dulu bagian <strong>Hak akses logger per user</strong> di
            keterangan bawah &mdash; data yang dikembalikan setiap endpoint dibatasi logger milik user yang
            memegang token. Pakai <strong>Authorize</strong> untuk menempelkan Bearer token hasil login.
        </p>
        <div class="docs-meta">
            <span>OpenAPI 3.0.3</span>
            <span>&middot;</span>
            <a href="{{ route('docs.api.json') }}">Buka raw OpenAPI JSON</a>
            <span>&middot;</span>
            <a href="{{ url('/') }}">Kembali ke aplikasi</a>
        </div>
    </header>

    <main id="swagger-ui" aria-label="Dokumentasi endpoint Mini-STESY"></main>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui-bundle.js" crossorigin="anonymous"></script>
    <script>
        window.addEventListener('load', function () {
            window.ui = SwaggerUIBundle({
                url: @json(route('docs.api.json')),
                dom_id: '#swagger-ui',
                deepLinking: true,
                displayRequestDuration: true,
                docExpansion: 'list',
                defaultModelsExpandDepth: 1,
                presets: [SwaggerUIBundle.presets.apis]
            });
        });
    </script>
</body>
</html>
