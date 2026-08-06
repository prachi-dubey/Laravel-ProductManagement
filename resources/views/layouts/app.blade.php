<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Notes Demo') — Shop API</title>
    <style>
        :root {
            --bg: #f4f6f8;
            --card: #ffffff;
            --ink: #1c2430;
            --muted: #5b6775;
            --line: #d9e0e8;
            --accent: #0f6e56;
            --accent-soft: #e6f4ef;
            --danger: #b42318;
            --danger-soft: #fef3f2;
            --radius: 10px;
            --shadow: 0 8px 24px rgba(28, 36, 48, 0.06);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, #e8f5f0 0, transparent 40%),
                var(--bg);
            min-height: 100vh;
        }

        a { color: var(--accent); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .wrap {
            width: min(880px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 2rem 0 3rem;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .brand {
            font-weight: 700;
            letter-spacing: 0.02em;
            color: var(--ink);
            text-decoration: none;
        }

        .brand span {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--muted);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.25rem 1.4rem;
        }

        .stack > * + * { margin-top: 1rem; }

        h1 {
            margin: 0 0 0.35rem;
            font-size: 1.6rem;
        }

        .lead {
            margin: 0;
            color: var(--muted);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 0.55rem 0.95rem;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover { text-decoration: none; }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }

        .btn-secondary {
            background: #fff;
            border-color: var(--line);
            color: var(--ink);
        }

        .btn-danger {
            background: var(--danger-soft);
            color: var(--danger);
            border-color: #fecdca;
        }

        .flash {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            background: var(--accent-soft);
            color: var(--accent);
            border: 1px solid #b7e0d1;
            margin-bottom: 1rem;
        }

        .errors {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            background: var(--danger-soft);
            color: var(--danger);
            border: 1px solid #fecdca;
            margin-bottom: 1rem;
        }

        .errors ul {
            margin: 0.35rem 0 0;
            padding-left: 1.1rem;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }

        input[type="text"],
        textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 0.7rem 0.8rem;
            font: inherit;
            background: #fff;
        }

        textarea { min-height: 160px; resize: vertical; }

        .field { margin-bottom: 1rem; }

        .field-error {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.3rem;
        }

        .note-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .note-list li {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--line);
        }

        .note-list li:last-child { border-bottom: 0; }

        .note-meta {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .empty {
            color: var(--muted);
            padding: 1.5rem 0;
            text-align: center;
        }

        .body-text {
            white-space: pre-wrap;
            line-height: 1.55;
        }

        footer {
            margin-top: 1.5rem;
            color: var(--muted);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header class="topbar">
            <a class="brand" href="{{ route('notes.index') }}">
                Notes Demo
                <span>Blade / MVC learning module</span>
            </a>
            <span class="badge">Not the shop API</span>
        </header>

        @if (session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <strong>Please fix the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')

        <footer>
            Main project work is API + Postman. This Notes UI teaches Blade layouts, forms, and resource controllers.
        </footer>
    </div>
</body>
</html>
