{{--
    Shared create/edit form.

    Expects: $app (DevApp, possibly unsaved), $authModes, $roles, $users.
--}}
@php
    $host = rtrim(parse_url(config('devapps.public_host_url') ?: config('app.url'), PHP_URL_HOST) ?: 'datakita', '/');
    $prefix = trim((string) config('devapps.mount_prefix', ''), '/');
    $selectedRoles = old('allowed_roles', $app->allowed_roles ?? []);
    $selectedUsers = old('allowed_users', $app->exists ? $app->allowedUsers->pluck('id')->all() : []);
@endphp

<div class="space-y-8">

    {{-- ── Identity ──────────────────────────────────────────────────── --}}
    <section>
        <h2 class="bps-subtitle">Identitas Aplikasi</h2>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="name" class="bps-label">Nama aplikasi</label>
                <input type="text" id="name" name="name" class="bps-input"
                       value="{{ old('name', $app->name) }}" required
                       placeholder="Survei Listrik Mandiri">
            </div>

            <div>
                <label for="slug" class="bps-label">Alamat</label>
                <div class="flex items-stretch">
                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm font-mono">
                        {{ $host }}/{{ $prefix ? $prefix . '/' : '' }}
                    </span>
                    <input type="text" id="slug" name="slug" class="bps-input rounded-l-none font-mono"
                           value="{{ old('slug', $app->slug) }}" required
                           pattern="[a-z0-9]+(-[a-z0-9]+)*"
                           placeholder="survei-listrik">
                </div>
                <p class="bps-help-text">Huruf kecil, angka, dan tanda hubung. Tidak boleh memakai alamat yang sudah dipakai DataKita.</p>
            </div>
        </div>

        <div class="mt-5">
            <label for="description" class="bps-label">Deskripsi <span class="text-gray-400 font-normal">(opsional)</span></label>
            <textarea id="description" name="description" rows="2" class="bps-input"
                      placeholder="Untuk apa aplikasi ini dan siapa penggunanya.">{{ old('description', $app->description) }}</textarea>
        </div>
    </section>

    {{-- ── Source ────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="bps-subtitle">Sumber Kode</h2>
        <p class="bps-help-text mb-4">
            Repositori Anda sendiri. DataKita hanya membangunnya — kode Anda tidak pernah digabung ke dalam kode DataKita.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label for="git_repo" class="bps-label">URL repositori</label>
                <input type="text" id="git_repo" name="git_repo" class="bps-input font-mono text-sm"
                       value="{{ old('git_repo', $app->git_repo) }}" required
                       placeholder="https://github.com/nama-anda/survei-listrik.git">
            </div>

            <div>
                <label for="git_branch" class="bps-label">Branch</label>
                <input type="text" id="git_branch" name="git_branch" class="bps-input font-mono text-sm"
                       value="{{ old('git_branch', $app->git_branch ?: 'main') }}" required>
            </div>

            <div>
                <label for="git_build_path" class="bps-label">Direktori build</label>
                <input type="text" id="git_build_path" name="git_build_path" class="bps-input font-mono text-sm"
                       value="{{ old('git_build_path', $app->git_build_path ?: '/') }}" placeholder="/">
                <p class="bps-help-text">Isi <code class="text-xs">/</code> kalau aplikasi ada di akar repositori.</p>
            </div>

            <div>
                <label for="ssh_key_id" class="bps-label">SSH Key ID <span class="text-gray-400 font-normal">(repo privat)</span></label>
                <input type="text" id="ssh_key_id" name="ssh_key_id" class="bps-input font-mono text-sm"
                       value="{{ old('ssh_key_id', $app->ssh_key_id) }}">
                <p class="bps-help-text">Kosongkan untuk repositori publik.</p>
            </div>
        </div>
    </section>

    {{-- ── Build & runtime ───────────────────────────────────────────── --}}
    <section>
        <h2 class="bps-subtitle">Build &amp; Runtime</h2>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label for="build_type" class="bps-label">Metode build</label>
                <select id="build_type" name="build_type" class="bps-input" data-build-type>
                    @foreach(['nixpacks' => 'Nixpacks (deteksi otomatis)', 'dockerfile' => 'Dockerfile', 'heroku_buildpacks' => 'Heroku Buildpacks', 'paketo' => 'Paketo'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('build_type', $app->build_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div data-dockerfile-field class="hidden">
                <label for="dockerfile_path" class="bps-label">Lokasi Dockerfile</label>
                <input type="text" id="dockerfile_path" name="dockerfile_path" class="bps-input font-mono text-sm"
                       value="{{ old('dockerfile_path', $app->dockerfile_path ?: 'Dockerfile') }}">
            </div>

            <div>
                <label for="container_port" class="bps-label">Port aplikasi</label>
                <input type="number" id="container_port" name="container_port" class="bps-input"
                       value="{{ old('container_port', $app->container_port ?: 3000) }}" min="1" max="65535" required>
                <p class="bps-help-text">Port yang didengarkan aplikasi Anda di dalam kontainer.</p>
            </div>
        </div>

        <div class="mt-5">
            <label class="flex items-start gap-3">
                <input type="hidden" name="strip_prefix" value="0">
                <input type="checkbox" name="strip_prefix" value="1"
                       class="mt-1 rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500"
                       @checked(old('strip_prefix', $app->exists ? $app->strip_prefix : true))>
                <span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Potong awalan alamat sebelum diteruskan</span>
                    <span class="block bps-help-text">
                        Aktif: aplikasi Anda menerima <code class="text-xs">/</code> dan tidak perlu tahu dipasang di mana.
                        Nonaktif: aplikasi menerima alamat lengkap dan harus menangani awalannya sendiri.
                    </span>
                </span>
            </label>
        </div>

        <div class="mt-5">
            <label for="env_vars" class="bps-label">Variabel Lingkungan <span class="text-gray-400 font-normal">(opsional)</span></label>
            <textarea id="env_vars" name="env_vars" rows="5" class="bps-input font-mono text-xs"
                      placeholder="DB_HOST=nama-layanan-database&#10;DB_DATABASE=aplikasi_saya&#10;DB_USERNAME=aplikasi&#10;DB_PASSWORD=...">{{ old('env_vars', $app->env_vars) }}</textarea>
            <p class="bps-help-text">
                Satu <code class="text-xs">KUNCI=nilai</code> per baris. Baris kosong dan baris diawali
                <code class="text-xs">#</code> diabaikan.
                <strong>Butuh database?</strong> Buat database sendiri di Dokploy
                (Datakita Dev Apps &rarr; Create Service &rarr; Database), lalu isikan kredensialnya di sini.
                Jangan pernah memakai database DataKita — aplikasi Anda tidak akan pernah menerima kredensialnya.
                Nama yang diawali <code class="text-xs">DATAKITA_</code> dan <code class="text-xs">PORT</code>
                diatur otomatis dan tidak bisa ditimpa.
            </p>
            @error('env_vars')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </section>

    {{-- ── Access ────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="bps-subtitle">Siapa yang Boleh Mengakses</h2>
        <p class="bps-help-text mb-4">
            Diperiksa DataKita pada setiap permintaan, sebelum permintaan itu sampai ke aplikasi Anda.
            Perubahan berlaku seketika — tanpa deploy ulang.
        </p>

        <div class="space-y-3">
            @foreach($authModes as $value => $mode)
                <label class="flex items-start gap-3 p-3 rounded-md border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <input type="radio" name="auth_mode" value="{{ $value }}" data-auth-mode
                           class="mt-1 border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500"
                           @checked(old('auth_mode', $app->auth_mode ?: 'login_required') === $value)>
                    <span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $mode['label'] }}</span>
                        <span class="block bps-help-text">{{ $mode['hint'] }}</span>
                    </span>
                </label>
            @endforeach
        </div>

        {{-- Role picker, shown only in role mode. --}}
        <div data-role-picker class="hidden mt-5 pl-4 border-l-2 border-blue-200 dark:border-blue-800">
            <span class="bps-label">Role yang diizinkan</span>
            <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach($roles as $key => $definition)
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="allowed_roles[]" value="{{ $key }}"
                               class="rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500"
                               @checked(in_array($key, (array) $selectedRoles, true))>
                        {{ $definition['label'] }}
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Explicit user picker, shown only in allowlist mode. --}}
        <div data-user-picker class="hidden mt-5 pl-4 border-l-2 border-blue-200 dark:border-blue-800">
            <span class="bps-label">Pengguna yang diizinkan</span>
            <p class="bps-help-text mb-2">Pemilik aplikasi selalu punya akses dan tidak perlu dipilih.</p>
            <select name="allowed_users[]" multiple size="8" class="bps-input font-normal">
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(in_array($user->id, (array) $selectedUsers))>
                        {{ $user->name }} — {{ $user->email }}
                    </option>
                @endforeach
            </select>
            <p class="bps-help-text">Tahan Ctrl (atau Cmd) untuk memilih lebih dari satu.</p>
        </div>
    </section>
</div>

<script>
    // Progressive disclosure only — every one of these fields is validated
    // again server-side, so hiding a field never grants anything.
    (function () {
        const form = document.currentScript.closest('form') || document;

        function sync() {
            const mode = form.querySelector('[data-auth-mode]:checked')?.value;
            form.querySelector('[data-role-picker]')?.classList.toggle('hidden', mode !== 'role');
            form.querySelector('[data-user-picker]')?.classList.toggle('hidden', mode !== 'allowlist');

            const build = form.querySelector('[data-build-type]')?.value;
            form.querySelector('[data-dockerfile-field]')?.classList.toggle('hidden', build !== 'dockerfile');
        }

        form.addEventListener('change', function (e) {
            if (e.target.matches('[data-auth-mode], [data-build-type]')) sync();
        });

        sync();
    })();
</script>
