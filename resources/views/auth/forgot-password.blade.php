@extends('layouts.app')

@section('title', 'Lupa Password - DataKita')
@section('description', 'Reset password akun DataKita')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900 px-4">
    <div class="w-full max-w-md">

        @if (session('status'))
            {{-- ── Success State ── --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 text-center">
                <div class="flex items-center justify-center w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full mx-auto mb-5">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Email Terkirim!</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">
                    Kami telah mengirimkan tautan reset password ke
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ old('email') }}</span>.
                    Silakan periksa kotak masuk Anda.
                </p>

                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/40 rounded-lg p-4 text-left text-sm text-amber-700 dark:text-amber-400 space-y-2 mb-6">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>Tautan berlaku selama <strong>60 menit</strong>. Setelah itu Anda perlu meminta ulang.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>Tidak ada email? Cek folder <strong>Spam</strong> atau <strong>Promosi</strong>.</span>
                    </div>
                </div>

                <form id="resendForm" method="POST" action="{{ route('password.email') }}" class="mb-4">
                    @csrf
                    <input type="hidden" name="email" value="{{ old('email') }}">
                    <button id="resendBtn" type="submit"
                        class="w-full border border-blue-600 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 font-medium py-2.5 px-4 rounded-md transition-colors duration-200 text-sm">
                        Kirim Ulang Email
                    </button>
                </form>

                <a href="{{ route('login') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:underline transition-colors">
                    &larr; Kembali ke halaman login
                </a>
            </div>

        @else
            {{-- ── Request Form State ── --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-6">
                <div class="text-center mb-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full mx-auto mb-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Lupa Password?</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Masukkan email Anda dan kami akan mengirimkan tautan untuk membuat password baru.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/40 text-red-600 dark:text-red-400 p-3 rounded-md mb-5 text-sm">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="initialForm" method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-5">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:bg-gray-900 dark:text-white text-sm"
                            placeholder="nama@contoh.com">
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition-colors duration-300 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim Link Reset Password
                    </button>
                </form>

                <div class="mt-5 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:underline transition-colors">
                        &larr; Kembali ke halaman login
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
(function () {
    const COOLDOWN_SECONDS = 300; // 5 minutes — matches config/auth.php throttle
    const LS_KEY = 'datakita_reset_sent_at';

    // Save timestamp whenever either form is submitted
    document.querySelectorAll('#initialForm, #resendForm').forEach(function (form) {
        form.addEventListener('submit', function () {
            localStorage.setItem(LS_KEY, Date.now().toString());
        });
    });

    // Countdown logic — only runs on the success state (resendBtn exists)
    var btn = document.getElementById('resendBtn');
    if (!btn) return;

    var timer = null;

    function remaining() {
        var sentAt = parseInt(localStorage.getItem(LS_KEY) || '0', 10);
        if (!sentAt) return 0;
        return Math.max(0, COOLDOWN_SECONDS - Math.floor((Date.now() - sentAt) / 1000));
    }

    function fmt(secs) {
        var m = Math.floor(secs / 60);
        var s = secs % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function lock(secs) {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
        btn.textContent = 'Kirim ulang dalam ' + fmt(secs);
    }

    function unlock() {
        btn.disabled = false;
        btn.style.opacity = '';
        btn.style.cursor = '';
        btn.textContent = 'Kirim Ulang Email';
    }

    function tick() {
        var secs = remaining();
        if (secs > 0) {
            lock(secs);
        } else {
            clearInterval(timer);
            unlock();
            localStorage.removeItem(LS_KEY);
        }
    }

    var secs = remaining();
    if (secs > 0) {
        // Page just loaded after a send — start the countdown immediately
        lock(secs);
        timer = setInterval(tick, 1000);
    } else {
        // First time on success page with no prior send recorded — save now
        // (covers the case where the page loaded fresh after a successful POST)
        if (!localStorage.getItem(LS_KEY)) {
            localStorage.setItem(LS_KEY, Date.now().toString());
            lock(COOLDOWN_SECONDS);
            timer = setInterval(tick, 1000);
        }
    }
})();
</script>
@endsection
