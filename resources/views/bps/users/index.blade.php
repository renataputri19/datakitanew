@extends('layouts.bps')

@section('title', 'Manajemen Pengguna - BPS DataKita')
@section('description', 'Kelola akun pengguna sistem DataKita')

@section('content')
<div class="bps-card">
    <div class="bps-card-header flex items-center justify-between">
        <h1 class="bps-title">Manajemen Pengguna</h1>
        <span class="text-sm text-gray-500 dark:text-gray-400">Total: {{ $users->total() }} pengguna</span>
    </div>

    <div class="bps-card-body">
        <!-- Search Bar -->
        <form method="GET" action="{{ route('bps.users.index') }}" class="mb-4">
            <div class="flex gap-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari nama atau email pengguna..."
                       class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Cari
                </button>
                @if(request('search'))
                <a href="{{ route('bps.users.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </form>

        <!-- Users Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="users-table">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Institusi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Terdaftar</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $index => $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors user-row">
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $users->firstItem() + $loop->index }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white user-name">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 user-email">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ $user->institution?->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if($user->is_bps)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">BPS</span>
                                @endif
                                @if($user->is_kominfo_user)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">Kominfo</span>
                                @endif
                                @if($user->is_superadmin)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Superadmin</span>
                                @endif
                                @if($user->is_admin)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300">Admin</span>
                                @endif
                                @if(!$user->is_bps && !$user->is_kominfo_user && !$user->is_superadmin && !$user->is_admin)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Pengguna</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $user->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-md transition-colors duration-200"
                                    onclick="openResetModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->email }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Reset Password
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                            Belum ada pengguna terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- No results message -->
        @if($users->isEmpty())
        <div class="text-center py-10 text-gray-400 dark:text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Tidak ada pengguna yang cocok dengan pencarian.
        </div>
        @endif

        <!-- Custom Pagination -->
        @if($users->lastPage() > 1)
        @php
            $currentPage = $users->currentPage();
            $lastPage    = $users->lastPage();
            $paginator   = $users->appends(request()->query());

            $pages = collect(range(1, $lastPage))->filter(
                fn($p) => $p === 1 || $p === $lastPage || abs($p - $currentPage) <= 1
            )->values();
        @endphp
        <div class="mt-5 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan <span class="font-medium text-gray-700 dark:text-gray-300">{{ $users->firstItem() }}</span>
                &ndash;
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $users->lastItem() }}</span>
                dari
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $users->total() }}</span> pengguna
            </p>
            <nav class="flex items-center gap-1" aria-label="Pagination">
                {{-- Prev --}}
                @if($users->onFirstPage())
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none text-sm">
                    &#8249;
                </span>
                @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-400 hover:text-blue-600 transition-colors text-sm">
                    &#8249;
                </a>
                @endif

                {{-- Page numbers with ellipsis --}}
                @foreach($pages as $i => $page)
                    @if($i > 0 && $page - $pages[$i - 1] > 1)
                    <span class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 text-sm select-none">…</span>
                    @endif
                    @if($page === $currentPage)
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-blue-600 bg-blue-600 text-white font-semibold text-sm shadow-sm select-none">
                        {{ $page }}
                    </span>
                    @else
                    <a href="{{ $paginator->url($page) }}"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-400 hover:text-blue-600 transition-colors text-sm">
                        {{ $page }}
                    </a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($users->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-400 hover:text-blue-600 transition-colors text-sm">
                    &#8250;
                </a>
                @else
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed select-none text-sm">
                    &#8250;
                </span>
                @endif
            </nav>
        </div>
        @endif
    </div>
</div>

<!-- Reset Password Modal (Ganti Password) -->
<div id="reset-modal"
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-title">

    <!-- Backdrop (visual only – click handled by JS on outer div) -->
    <div id="modal-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <!-- Modal Panel -->
    <div id="modal-panel" class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md z-10">

        <!-- Header -->
        <div class="flex items-center justify-between px-8 py-6 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <h2 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-white">Ganti Password</h2>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                        Akun: <span id="modal-user-name" class="font-medium text-gray-600 dark:text-gray-300"></span>
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeResetModal()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="px-8 py-6 space-y-6">

            <!-- New Password -->
            <div class="space-y-2">
                <label for="cp_new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Password Baru <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password"
                           id="cp_new_password"
                           autocomplete="new-password"
                           placeholder="Minimal 8 karakter, huruf &amp; angka"
                           class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <button type="button" onclick="togglePwd('cp_new_password', 'eye-new')"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg id="eye-new" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>

                <!-- Strength bar -->
                <div id="pwd-strength-wrap" class="hidden mt-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-gray-400 dark:text-gray-500">Kekuatan password:</span>
                        <span id="pwd-strength-label" class="text-xs font-medium"></span>
                    </div>
                    <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                        <div id="pwd-strength-fill" class="h-full rounded-full transition-all duration-300" style="width:0"></div>
                    </div>
                </div>

                <!-- Requirements checklist -->
                <ul id="pwd-requirements" class="mt-2 space-y-1 hidden">
                    <li id="req-length" class="flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                        <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-300 dark:border-gray-600 flex-shrink-0 flex items-center justify-center"></span>
                        Minimal 8 karakter
                    </li>
                    <li id="req-letter" class="flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                        <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-300 dark:border-gray-600 flex-shrink-0 flex items-center justify-center"></span>
                        Mengandung huruf (a-z / A-Z)
                    </li>
                    <li id="req-number" class="flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                        <span class="req-dot w-3.5 h-3.5 rounded-full border border-gray-300 dark:border-gray-600 flex-shrink-0 flex items-center justify-center"></span>
                        Mengandung angka (0-9)
                    </li>
                </ul>
            </div>

            <!-- Confirm New Password -->
            <div class="space-y-2">
                <label for="cp_confirm_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Konfirmasi Password Baru <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password"
                           id="cp_confirm_password"
                           autocomplete="new-password"
                           placeholder="Ulangi password baru"
                           class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <button type="button" onclick="togglePwd('cp_confirm_password', 'eye-cfm')"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg id="eye-cfm" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <p id="err-confirm" class="text-xs mt-1 hidden"></p>
            </div>

        </div>

        <!-- Footer -->
        <div class="px-8 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
            <button type="button"
                    onclick="closeResetModal()"
                    id="modal-cancel-btn"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Batal
            </button>
            <button type="button"
                    id="modal-submit-btn"
                    onclick="submitPasswordReset()"
                    class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                <svg id="submit-spinner" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span id="submit-text">Simpan Password</span>
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const ROUTE_BASE = '{{ url('/bps/users') }}';
    const CSRF      = '{{ csrf_token() }}';
    let   targetUserId = null;

    /* ── Eye-toggle icons ────────────────────────────────────────────── */
    const EYE_ON  = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
    const EYE_OFF = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;

    /* ── Public helpers (called from onclick attrs) ───────────────────── */
    window.openResetModal = function (userId, userName) {
        targetUserId = userId;
        document.getElementById('modal-user-name').textContent = userName;
        ['cp_new_password', 'cp_confirm_password'].forEach(id => {
            const el = document.getElementById(id);
            el.value = '';
            setFieldState(el, 'neutral');
        });
        hideMsg('err-confirm');
        ['pwd-strength-wrap', 'pwd-requirements'].forEach(id =>
            document.getElementById(id).classList.add('hidden'));
        document.getElementById('pwd-strength-fill').style.width = '0';
        setLoading(false);
        document.getElementById('reset-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => document.getElementById('cp_current_password').focus());
    };

    window.closeResetModal = function () {
        document.getElementById('reset-modal').classList.add('hidden');
        document.body.style.overflow = '';
        targetUserId = null;
    };

    window.togglePwd = function (inputId, eyeId) {
        const input = document.getElementById(inputId);
        const eye   = document.getElementById(eyeId);
        const show  = input.type === 'password';
        input.type  = show ? 'text' : 'password';
        eye.innerHTML = show ? EYE_OFF : EYE_ON;
    };

    /* ── Real-time validation ────────────────────────────────────────── */
    document.getElementById('cp_new_password').addEventListener('input', function () {
        const val = this.value;
        if (!val) {
            ['pwd-strength-wrap', 'pwd-requirements'].forEach(id =>
                document.getElementById(id).classList.add('hidden'));
            setFieldState(this, 'neutral');
            return;
        }
        ['pwd-strength-wrap', 'pwd-requirements'].forEach(id =>
            document.getElementById(id).classList.remove('hidden'));
        const ok = checkNewPasswordRules(val);
        setFieldState(this, ok ? 'valid' : 'neutral');
        checkConfirmMatch();
    });

    document.getElementById('cp_confirm_password').addEventListener('input', checkConfirmMatch);

    /* ── AJAX submission ─────────────────────────────────────────────── */
    window.submitPasswordReset = async function () {
        const newPwd     = document.getElementById('cp_new_password').value;
        const confirmPwd = document.getElementById('cp_confirm_password').value;

        /* Client-side guard */
        if (!checkNewPasswordRules(newPwd)) {
            document.getElementById('cp_new_password').focus();
            return;
        }
        if (newPwd !== confirmPwd) {
            showMsg('err-confirm', 'Password tidak cocok.', 'error');
            setFieldState(document.getElementById('cp_confirm_password'), 'error');
            document.getElementById('cp_confirm_password').focus();
            return;
        }

        setLoading(true);
        try {
            const resp = await fetch(`${ROUTE_BASE}/${targetUserId}/reset-password`, {
                method : 'POST',
                headers: {
                    'Content-Type' : 'application/json',
                    'Accept'       : 'application/json',
                    'X-CSRF-TOKEN' : CSRF,
                },
                body: JSON.stringify({
                    new_password             : newPwd,
                    new_password_confirmation: confirmPwd,
                }),
            });

            const data = await resp.json();

            if (resp.ok && data.success) {
                closeResetModal();
                Swal.fire({
                    icon             : 'success',
                    title            : 'Berhasil!',
                    text             : data.message,
                    confirmButtonColor: '#2563eb',
                    timer            : 4000,
                    timerProgressBar : true,
                    showConfirmButton : false,
                });
            } else {
                const errMsg = data.message || (data.errors && Object.values(data.errors)[0]?.[0]) || 'Terjadi kesalahan.';
                Swal.fire({
                    icon             : 'error',
                    title            : 'Gagal!',
                    text             : errMsg,
                    confirmButtonColor: '#2563eb',
                });
            }
        } catch (_) {
            Swal.fire({
                icon             : 'error',
                title            : 'Kesalahan Jaringan',
                text             : 'Gagal terhubung ke server. Periksa koneksi Anda.',
                confirmButtonColor: '#2563eb',
            });
        } finally {
            setLoading(false);
        }
    };

    /* ── Internal helpers ────────────────────────────────────────────── */
    function checkNewPasswordRules(val) {
        const hasLength = val.length >= 8;
        const hasLetter = /[a-zA-Z]/.test(val);
        const hasNumber = /[0-9]/.test(val);
        setReq('req-length', hasLength);
        setReq('req-letter', hasLetter);
        setReq('req-number', hasNumber);
        const score = [hasLength, hasLetter, hasNumber].filter(Boolean).length;
        const fill  = document.getElementById('pwd-strength-fill');
        const label = document.getElementById('pwd-strength-label');
        const meta  = [
            { w: '33%',  cls: 'bg-red-500',   txt: 'Lemah',  color: 'text-red-500'   },
            { w: '66%',  cls: 'bg-amber-500',  txt: 'Sedang', color: 'text-amber-500'  },
            { w: '100%', cls: 'bg-green-500',  txt: 'Kuat',   color: 'text-green-600' },
        ][score - 1] || { w: '0', cls: '', txt: '', color: '' };
        fill.style.width = meta.w;
        fill.className   = 'h-full rounded-full transition-all duration-300 ' + meta.cls;
        label.textContent = meta.txt;
        label.className   = 'text-xs font-medium ' + meta.color;
        return hasLength && hasLetter && hasNumber;
    }

    function setReq(id, met) {
        const li  = document.getElementById(id);
        const dot = li.querySelector('.req-dot');
        if (met) {
            li.className  = 'flex items-center gap-1.5 text-xs text-green-600 dark:text-green-400';
            dot.className = 'req-dot w-3.5 h-3.5 rounded-full bg-green-500 flex-shrink-0 flex items-center justify-center';
            dot.innerHTML = '<svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
        } else {
            li.className  = 'flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500';
            dot.className = 'req-dot w-3.5 h-3.5 rounded-full border border-gray-300 dark:border-gray-600 flex-shrink-0 flex items-center justify-center';
            dot.innerHTML = '';
        }
    }

    function checkConfirmMatch() {
        const pwd     = document.getElementById('cp_new_password').value;
        const confirm = document.getElementById('cp_confirm_password').value;
        const input   = document.getElementById('cp_confirm_password');
        if (!confirm) { hideMsg('err-confirm'); setFieldState(input, 'neutral'); return; }
        if (pwd === confirm) {
            showMsg('err-confirm', '✓ Password cocok', 'success');
            setFieldState(input, 'valid');
        } else {
            showMsg('err-confirm', 'Password tidak cocok', 'error');
            setFieldState(input, 'error');
        }
    }

    function setFieldState(el, state) {
        el.classList.remove('border-red-500', 'border-green-500', 'focus:ring-red-500', 'focus:ring-green-500',
                            'dark:border-red-500', 'dark:border-green-500');
        if (state === 'error') {
            el.classList.add('border-red-500', 'dark:border-red-500');
        } else if (state === 'valid') {
            el.classList.add('border-green-500', 'dark:border-green-500');
        }
    }

    function showMsg(id, text, type) {
        const el = document.getElementById(id);
        el.textContent = text;
        el.className   = 'text-xs mt-1 ' + (type === 'error' ? 'text-red-500' : 'text-green-600 dark:text-green-400');
        el.classList.remove('hidden');
    }

    function hideMsg(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function setLoading(on) {
        const btn     = document.getElementById('modal-submit-btn');
        const cancel  = document.getElementById('modal-cancel-btn');
        const spinner = document.getElementById('submit-spinner');
        const text    = document.getElementById('submit-text');
        btn.disabled    = on;
        cancel.disabled = on;
        spinner.classList.toggle('hidden', !on);
        text.textContent = on ? 'Menyimpan...' : 'Simpan Password';
    }

})();
</script>
@endpush
