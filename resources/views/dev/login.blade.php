<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>[DEV] Login — DataKita</title>
    <style>
        /* ── Reset ─────────────────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Root variables ─────────────────────────────────────────────────── */
        :root {
            --bg:        #0d1117;
            --surface:   #161b22;
            --border:    #30363d;
            --accent:    #f0883e;
            --accent-dk: #c96c20;
            --text:      #e6edf3;
            --muted:     #8b949e;
            --danger-bg: #1f1117;
            --danger-bd: #6e2232;
            --danger-tx: #f85149;
            --success:   #3fb950;
            --input-bg:  #0d1117;
            --input-bd:  #30363d;
            --input-foc: #58a6ff;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;

            /* subtle dot-grid pattern */
            background-image: radial-gradient(circle, #21262d 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* ── Dev banner strip ────────────────────────────────────────────────── */
        .dev-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: repeating-linear-gradient(
                -45deg,
                #f0883e,
                #f0883e 10px,
                #c96c20 10px,
                #c96c20 20px
            );
            color: #fff;
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            text-align: center;
            padding: 0.35rem 1rem;
            z-index: 9999;
            user-select: none;
        }

        /* ── Card ───────────────────────────────────────────────────────────── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            padding: 2rem 2rem 1.75rem;
            box-shadow: 0 8px 32px rgba(0,0,0,.5);
            margin-top: 1.5rem; /* clear the banner */
        }

        /* ── Header ─────────────────────────────────────────────────────────── */
        .card-head {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .logo-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .logo-row img {
            height: 44px;
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,.4));
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.25rem;
        }

        .env-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: rgba(240,136,62,.15);
            border: 1px solid rgba(240,136,62,.4);
            color: var(--accent);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            margin-top: 0.5rem;
        }

        .env-badge::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 1.8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.35; }
        }

        .card-subtitle {
            font-size: 0.82rem;
            color: var(--muted);
            margin-top: 0.6rem;
        }

        /* ── Errors ──────────────────────────────────────────────────────────── */
        .alert-error {
            background: var(--danger-bg);
            border: 1px solid var(--danger-bd);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.84rem;
            color: var(--danger-tx);
        }

        .alert-error ul {
            padding-left: 1.1rem;
        }

        /* ── Form fields ─────────────────────────────────────────────────────── */
        .field {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--muted);
            margin-bottom: 0.4rem;
            letter-spacing: 0.03em;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--input-bd);
            border-radius: 6px;
            color: var(--text);
            font-size: 0.95rem;
            padding: 0.55rem 0.75rem;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        input:focus {
            border-color: var(--input-foc);
            box-shadow: 0 0 0 3px rgba(88,166,255,.12);
        }

        /* password wrapper */
        .pw-wrap {
            position: relative;
        }

        .pw-wrap input {
            padding-right: 2.75rem;
        }

        .pw-toggle {
            position: absolute;
            right: 0.6rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            padding: 0.25rem;
            line-height: 1;
        }

        .pw-toggle:hover { color: var(--text); }

        /* ── Submit button ────────────────────────────────────────────────────── */
        .btn-submit {
            width: 100%;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            padding: 0.65rem 1rem;
            cursor: pointer;
            transition: background .15s, transform .1s;
            margin-top: 0.5rem;
        }

        .btn-submit:hover    { background: var(--accent-dk); }
        .btn-submit:active   { transform: scale(.98); }
        .btn-submit:disabled { opacity: .5; cursor: not-allowed; }

        /* ── Footer note ─────────────────────────────────────────────────────── */
        .card-footer {
            margin-top: 1.5rem;
            border-top: 1px solid var(--border);
            padding-top: 1rem;
            text-align: center;
        }

        .card-footer p {
            font-size: 0.75rem;
            color: var(--muted);
            line-height: 1.6;
        }

        .card-footer code {
            font-family: "SFMono-Regular", Consolas, monospace;
            font-size: 0.72rem;
            background: rgba(255,255,255,.06);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 0.1rem 0.35rem;
            color: var(--accent);
        }

        /* ── Bottom wordmark ─────────────────────────────────────────────────── */
        .wordmark {
            margin-top: 1.5rem;
            font-size: 0.72rem;
            color: var(--muted);
            opacity: .5;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

    {{-- ── Dev environment top banner ──────────────────────────────────────── --}}
    <div class="dev-banner">
        &nbsp;⚠&nbsp; Development Environment &nbsp;—&nbsp; This page is not visible in production &nbsp;⚠&nbsp;
    </div>

    {{-- ── Login card ──────────────────────────────────────────────────────── --}}
    <div class="card">

        {{-- Header --}}
        <div class="card-head">
            <div class="logo-row">
                <img src="{{ asset('img/Logo BPS 1.png') }}" alt="Logo BPS">
                <img src="{{ asset('img/Logo SE2026.png') }}" alt="Logo SE2026">
            </div>

            <p class="card-title">DataKita — Dev Access</p>

            <span class="env-badge">
                {{ strtoupper(config('app.env')) }} &nbsp;·&nbsp; DEV LOGIN
            </span>

            <p class="card-subtitle">
                Masuk dengan akun yang berwenang untuk membuka akses dev.<br>
                Setelah itu, semua akun bisa login &amp; daftar seperti di production.
            </p>
        </div>

        {{-- Error messages --}}
        @if ($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Login form --}}
        <form method="POST" action="{{ route('dev.login.submit') }}" autocomplete="off" id="devLoginForm">
            @csrf

            <div class="field">
                <label for="email">Alamat Email</label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="dev@example.com"
                       autocomplete="username"
                       required>
            </div>

            <div class="field">
                <label for="password">Kata Sandi</label>
                <div class="pw-wrap">
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="••••••••"
                           autocomplete="current-password"
                           required>
                    <button type="button" class="pw-toggle" id="pwToggle" title="Tampilkan/sembunyikan kata sandi">
                        {{-- Eye icon --}}
                        <svg id="eyeOpen" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eyeClosed" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                Masuk ke Dev Environment
            </button>
        </form>

        {{-- Footer note --}}
        <div class="card-footer">
            @if($allowedEmail)
                <p>
                    Hanya akun yang berwenang yang dapat membuka kunci ini.<br>
                    Setelah berhasil masuk, semua akun dapat login &amp; menggunakan app secara penuh.
                </p>
            @else
                <p>
                    Di production, route ini tidak terdaftar dan halaman ini tidak dapat diakses.
                </p>
            @endif
        </div>
    </div>

    <p class="wordmark">DataKita &copy; {{ date('Y') }} &nbsp;·&nbsp; Development Build</p>

    <script>
        // Password toggle
        const pwToggle  = document.getElementById('pwToggle');
        const pwInput   = document.getElementById('password');
        const eyeOpen   = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        pwToggle.addEventListener('click', () => {
            const isText = pwInput.type === 'text';
            pwInput.type = isText ? 'password' : 'text';
            eyeOpen.style.display   = isText ? 'block' : 'none';
            eyeClosed.style.display = isText ? 'none'  : 'block';
        });

        // Disable submit while processing to prevent double-submit
        document.getElementById('devLoginForm').addEventListener('submit', function () {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').textContent = 'Memproses…';
        });
    </script>
</body>
</html>
