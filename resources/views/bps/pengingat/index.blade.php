@extends('layouts.bps')

@section('title', 'Pengingat SIBSTR - BPS Dashboard')
@section('description', 'Atur dan kirim email pengingat pengisian SIBSTR Triwulanan')

@php
    $roman = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
    $periodLabel = fn ($tahun, $triwulan) => 'Triwulan ' . ($roman[$triwulan] ?? $triwulan) . ' ' . $tahun;
    $wib = fn ($dt) => \Illuminate\Support\Carbon::parse($dt)->setTimezone('Asia/Jakarta');
    // ['2', '7', '13'] → "2, 7 dan 13"
    $joinWith = fn (array $items, string $last = 'dan') => count($items) > 1
        ? implode(', ', array_slice($items, 0, -1)) . " {$last} " . $items[count($items) - 1]
        : (string) ($items[0] ?? '');
    $tabs = [
        'kirim'      => ['Kirim Pengingat', 'mail'],
        'riwayat'    => ['Riwayat', 'list'],
        'pengaturan' => ['Jadwal & Pengaturan', 'clock'],
        'template'   => ['Template Email', 'doc'],
    ];
@endphp

@push('styles')
@include('bps.pengingat.partials.styles')
@endpush

@section('content')
<div class="pg">
    {{-- Header --}}
    <div class="pg-head">
        <div>
            <h1 class="pg-title">Pengingat Pengisian SIBSTR</h1>
            <p class="pg-subtitle">
                Kirim email ke responden yang belum menyelesaikan SIBSTR Triwulanan, langsung dari sini atau otomatis sesuai jadwal.
            </p>
        </div>
        <a href="{{ route('bps.pengingat.preview') }}" target="_blank" rel="noopener" class="pg-btn pg-btn-ghost">
            @include('bps.pengingat.partials.icon', ['name' => 'eye'])
            Lihat contoh email
        </a>
    </div>

    @unless($serverEnabled)
        <div class="pg-alert pg-alert-warn" role="note">
            @include('bps.pengingat.partials.icon', ['name' => 'warn'])
            <div>
                <strong>Server ini tidak mengirim email ke responden.</strong>
                Hanya server produksi yang mengirim (<code>SIBSTR_REMINDER_ENABLED</code>). Di sini Anda tetap bisa melihat
                penerima, mengubah jadwal, dan mengirim email uji dari halaman template, tetapi tombol kirim dinonaktifkan.
            </div>
        </div>
    @endunless

    @if($errors->any())
        <div class="pg-alert pg-alert-error" role="alert">
            @include('bps.pengingat.partials.icon', ['name' => 'warn'])
            <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        </div>
    @endif

    {{-- Where things stand --}}
    <div class="pg-overview">
        <div class="pg-overview-item">
            <span class="pg-icon-circle pg-icon-blue">@include('bps.pengingat.partials.icon', ['name' => 'calendar'])</span>
            <div>
                <div class="pg-overview-label">Sedang didata</div>
                <div class="pg-overview-value">
                    {{ $currentPeriod ? $periodLabel($currentPeriod['tahun'], $currentPeriod['triwulan']) : 'Belum ada triwulan yang dibuka' }}
                </div>
                <div class="pg-overview-sub">Email otomatis selalu mengingatkan triwulan ini.</div>
            </div>
        </div>
        <div class="pg-overview-item">
            <span class="pg-icon-circle {{ $settings->auto_enabled ? 'pg-icon-green' : 'pg-icon-gray' }}">
                @include('bps.pengingat.partials.icon', ['name' => 'clock'])
            </span>
            <div>
                <div class="pg-overview-label">Pengingat otomatis</div>
                <div class="pg-overview-value">
                    <i class="pg-dot {{ $settings->auto_enabled ? 'pg-dot-green' : '' }}"></i>
                    {{ $settings->auto_enabled ? 'Aktif' : 'Nonaktif' }}
                </div>
                <div class="pg-overview-sub">
                    @if($nextRun)
                        Berikutnya {{ $nextRun->translatedFormat('l, d M Y') }} pukul {{ $nextRun->format('H.i') }} WIB.
                    @elseif($settings->auto_enabled)
                        Tidak ada jadwal dalam 15 bulan ke depan.
                    @else
                        Email hanya terkirim saat Anda menekan tombol Kirim.
                    @endif
                    @if($tab !== 'pengaturan')
                        <a href="{{ route('bps.pengingat.index', ['tab' => 'pengaturan']) }}" class="pg-link">
                            {{ $settings->auto_enabled ? 'Ubah jadwal' : 'Atur jadwal' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <details class="pg-help">
        <summary>
            @include('bps.pengingat.partials.icon', ['name' => 'help'])
            Bagaimana cara kerja pengingat ini?
            @include('bps.pengingat.partials.icon', ['name' => 'chevron', 'class' => 'pg-chevron'])
        </summary>
        <ol class="pg-help-list">
            <li><strong>Siapa yang menerima</strong>
                Perusahaan yang belum menyelesaikan triwulan yang dipilih, diambil dari data yang Anda centang (mis. yang sudah
                selesai Triwulan I/II, atau Tahunan). Email otomatis memakai semua triwulan sebelumnya. Dikirim ke alamat email
                Blok I (rincian 106).</li>
            <li><strong>Kapan terkirim</strong>
                Setelah tombol Kirim ditekan, email masuk antrean dan terkirim bertahap (±{{ config('sibstr.reminder.batch_size') }} per menit). Pantau hasilnya di tab Riwayat.</li>
            <li><strong>Tidak dobel</strong>
                Satu alamat email paling banyak menerima satu pengingat per triwulan per hari, dari kiriman manual maupun otomatis.
                Beberapa perusahaan dengan email yang sama digabung dalam satu email.</li>
            <li><strong>Isi email</strong>
                Diambil dari Template Email. Nama perusahaan, penghubung, dan periode terisi sendiri untuk tiap responden.</li>
        </ol>
    </details>

    {{-- Tabs --}}
    <nav class="pg-tabs" aria-label="Menu pengingat">
        @foreach($tabs as $key => [$label, $icon])
            <a href="{{ route('bps.pengingat.index', ['tab' => $key]) }}"
               class="pg-tab {{ $tab === $key ? 'is-active' : '' }}" @if($tab === $key) aria-current="page" @endif>
                @include('bps.pengingat.partials.icon', ['name' => $icon])
                {{ $label }}
            </a>
        @endforeach
    </nav>

    {{-- ─────────────── Kirim Pengingat ─────────────── --}}
    @if($tab === 'kirim')
        @if($periods === [])
            <div class="pg-panel pg-empty">
                @include('bps.pengingat.partials.icon', ['name' => 'calendar'])
                <p class="pg-empty-title">Belum ada triwulan yang dibuka</p>
                <p>Pengingat bisa dikirim setelah pendataan SIBSTR Triwulanan dibuka.</p>
            </div>
        @else
            @include('bps.pengingat.partials.kirim')
        @endif

    {{-- ─────────────── Riwayat ─────────────── --}}
    @elseif($tab === 'riwayat')
        @if($blasts->isEmpty())
            <div class="pg-panel pg-empty">
                @include('bps.pengingat.partials.icon', ['name' => 'inbox'])
                <p class="pg-empty-title">Belum ada pengingat yang dikirim</p>
                <p>Setiap pengiriman, manual maupun otomatis, tercatat di sini beserta status tiap email.</p>
                <a href="{{ route('bps.pengingat.index', ['tab' => 'kirim']) }}" class="pg-btn pg-btn-primary">Kirim pengingat</a>
            </div>
        @else
            <div class="pg-panel pg-flush">
                <div class="pg-scroll-x">
                    <table class="pg-table">
                        <thead>
                            <tr><th>Waktu</th><th>Triwulan</th><th>Dikirim oleh</th><th>Hasil</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($blasts as $b)
                                <tr class="pg-row-click" data-href="{{ route('bps.pengingat.blast', $b) }}">
                                    <td class="pg-nowrap">
                                        <div class="pg-strong">{{ $wib($b->created_at)->translatedFormat('d M Y') }}</div>
                                        <div class="pg-muted">{{ $wib($b->created_at)->format('H.i') }} WIB</div>
                                    </td>
                                    <td class="pg-nowrap">{{ $periodLabel($b->tahun, $b->triwulan) }}</td>
                                    <td>
                                        @if($b->trigger === 'otomatis')
                                            <span class="pg-badge pg-badge-blue">Otomatis</span>
                                        @else
                                            <span class="pg-badge pg-badge-gray">Manual</span>
                                            <div class="pg-muted">{{ $b->createdBy->name ?? '-' }}</div>
                                        @endif
                                    </td>
                                    <td class="pg-col-result">
                                        @include('bps.pengingat.partials.progress', [
                                            'total' => $b->logs_count, 'sent' => $b->sent_count,
                                            'queued' => $b->queued_count, 'failed' => $b->failed_count,
                                        ])
                                    </td>
                                    <td class="pg-nowrap"><a href="{{ route('bps.pengingat.blast', $b) }}" class="pg-link">Detail &rarr;</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pg-pagination">{{ $blasts->links('bps.partials.pagination', ['label' => 'pengiriman']) }}</div>
            </div>
        @endif

    {{-- ─────────────── Jadwal & Pengaturan ─────────────── --}}
    @elseif($tab === 'pengaturan')
        @php
            $autoOn   = (bool) old('auto_enabled', $settings->auto_enabled);
            $days     = array_map('intval', old('days', $settings->days ?? []));
            $targets  = old('target_statuses', $settings->target_statuses ?? []);
            $excluded = old('excluded_kondisi', $settings->excluded_kondisi ?? []);
            $sendTime = old('send_time', $settings->send_time);

            $months = $joinWith(collect(config('sibstr.reminder.collection_months'))
                ->map(fn ($m) => \Illuminate\Support\Carbon::create(2000, $m, 1)->translatedFormat('F'))->all());
            $targetPhrases = [
                'belum_mulai'  => 'sudah menyelesaikan triwulan sebelumnya tetapi belum mulai mengisi',
                'dalam_proses' => 'sudah mulai mengisi tetapi belum selesai',
            ];
            $chosenPhrases = array_values(array_intersect_key($targetPhrases, array_flip($targets)));
            $sortedDays = collect($days)->sort()->values()->all();
        @endphp
        <form method="POST" action="{{ route('bps.pengingat.settings') }}" id="pg-settings-form" class="pg-stack">
            @csrf
            @method('PUT')

            <section class="pg-panel pg-section {{ $autoOn ? '' : 'is-off' }}" id="pg-auto-section">
                <div class="pg-section-head">
                    <div>
                        <h2>Pengiriman otomatis</h2>
                        <p>Sistem mengirim pengingat sendiri sesuai jadwal ini, tanpa perlu membuka halaman ini.</p>
                    </div>
                    <label class="pg-toggle">
                        <input type="hidden" name="auto_enabled" value="0">
                        <input type="checkbox" name="auto_enabled" value="1" id="auto_enabled" @checked($autoOn)>
                        <span class="pg-toggle-track" aria-hidden="true"><span class="pg-toggle-thumb"></span></span>
                        <span class="pg-toggle-label" id="pg-toggle-label">{{ $autoOn ? 'Aktif' : 'Nonaktif' }}</span>
                    </label>
                </div>

                <div class="pg-summary {{ $autoOn ? '' : 'is-off' }}" id="pg-schedule-summary" aria-live="polite"
                     data-months="{{ $months }}" data-phrases='@json($targetPhrases)'>
                    @include('bps.pengingat.partials.icon', ['name' => 'calendar'])
                    <div>
                        <span id="pg-schedule-text">
                            @if(! $autoOn)
                                Pengiriman otomatis <b>nonaktif</b>. Pengingat hanya terkirim saat Anda menekan tombol Kirim di tab Kirim Pengingat.
                            @elseif($sortedDays === [] || $chosenPhrases === [])
                                Lengkapi tanggal dan penerima di bawah.
                            @else
                                Setiap tanggal <b>{{ $joinWith($sortedDays) }}</b> bulan {{ $months }}, pukul
                                <b>{{ str_replace(':', '.', $sendTime) }} WIB</b>, sistem mengingatkan triwulan yang sedang didata
                                kepada perusahaan yang <b>{{ $joinWith($chosenPhrases, 'atau') }}</b>.
                            @endif
                        </span>
                        @if($nextRun)
                            <span class="pg-summary-next" id="pg-schedule-next">
                                Berikutnya: {{ $nextRun->translatedFormat('l, d F Y') }} pukul {{ $nextRun->format('H.i') }} WIB.
                            </span>
                        @else
                            <span class="pg-summary-next" id="pg-schedule-next"></span>
                        @endif
                    </div>
                </div>

                <div class="pg-auto-fields">
                    <div class="pg-block">
                        <h3>Tanggal kirim</h3>
                        <p class="pg-hint">
                            Berlaku di bulan {{ $months }}, yaitu bulan dibukanya pendataan triwulan.
                            Tanggal yang tidak ada di bulan itu (mis. 31 April) dilewati.
                        </p>
                        <div class="pg-days">
                            @for($d = 1; $d <= 31; $d++)
                                <label class="pg-day">
                                    <input type="checkbox" name="days[]" value="{{ $d }}" @checked(in_array($d, $days, true))>
                                    <span>{{ $d }}</span>
                                </label>
                            @endfor
                        </div>
                    </div>

                    <div class="pg-grid-2">
                        <div class="pg-field">
                            <label for="send_time" class="pg-label">Jam kirim (WIB)</label>
                            <input type="time" id="send_time" name="send_time" class="pg-input" value="{{ $sendTime }}" required>
                        </div>
                        <div class="pg-field">
                            <span class="pg-label">Kirim ke responden yang</span>
                            <div class="pg-checks">
                                @foreach($statusLabels as $value => $label)
                                    <label class="pg-check">
                                        <input type="checkbox" name="target_statuses[]" value="{{ $value }}" @checked(in_array($value, $targets, true))>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            <div class="pg-hint">
                                Diambil dari perusahaan yang sudah menyelesaikan triwulan sebelumnya. Yang hanya mengisi Tahunan tidak
                                ikut; untuk mereka, kirim manual dengan sumber Tahunan.
                            </div>
                        </div>
                        <div class="pg-field">
                            <label for="settings_template_id" class="pg-label">Template email</label>
                            <select id="settings_template_id" name="template_id" class="pg-input">
                                @foreach($templates as $t)
                                    <option value="{{ $t->id }}" @selected(old('template_id', $autoTemplate->id) === $t->id)>{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pg-field pg-span-all">
                            <label for="settings_extra_message" class="pg-label">Pesan tambahan <span class="pg-optional">(opsional)</span></label>
                            <textarea id="settings_extra_message" name="extra_message" rows="2" maxlength="1000" class="pg-input"
                                      placeholder="Contoh: Batas akhir pengisian tanggal 25 bulan ini.">{{ old('extra_message', $settings->extra_message) }}</textarea>
                            <div class="pg-hint">Disertakan di email otomatis, dan menjadi isian awal saat kirim manual.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="pg-panel pg-section">
                <div class="pg-section-head">
                    <div>
                        <h2>Berlaku untuk semua pengingat</h2>
                        <p>Dipakai pengiriman otomatis maupun manual.</p>
                    </div>
                </div>

                <div class="pg-block">
                    <h3>Jangan kirim ke perusahaan yang berstatus</h3>
                    <p class="pg-hint">Berdasarkan jawaban Blok II (R201) terakhir perusahaan.</p>
                    <div class="pg-checks">
                        @foreach($kondisiLabels as $value => $label)
                            <label class="pg-check">
                                <input type="checkbox" name="excluded_kondisi[]" value="{{ $value }}" @checked(in_array($value, $excluded, true))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pg-grid-2">
                    <div class="pg-field">
                        <label for="from_name" class="pg-label">Nama pengirim</label>
                        <input type="text" id="from_name" name="from_name" maxlength="100" class="pg-input"
                               value="{{ old('from_name', $settings->from_name) }}" placeholder="BPS Kota Batam">
                        <div class="pg-hint">Alamat pengirim: {{ $fromAddress }} (diatur di server).</div>
                    </div>
                    <div class="pg-field">
                        <label for="reply_to" class="pg-label">Balasan responden dikirim ke <span class="pg-optional">(opsional)</span></label>
                        <input type="email" id="reply_to" name="reply_to" maxlength="255" class="pg-input"
                               value="{{ old('reply_to', $settings->reply_to) }}" placeholder="nama@instansi.go.id">
                        <div class="pg-hint">Kotak masuk yang dipantau petugas. Kosongkan bila email tidak perlu bisa dibalas.</div>
                    </div>
                </div>
            </section>

            <div class="pg-panel pg-savebar">
                <span class="pg-hint">
                    @if($settings->exists)
                        Terakhir diubah {{ $wib($settings->updated_at)->translatedFormat('d M Y H.i') }} WIB
                        @if($settings->updatedBy) oleh {{ $settings->updatedBy->name }} @endif
                    @else
                        Belum pernah disimpan. Nilai di atas adalah bawaan.
                    @endif
                </span>
                <button type="submit" class="pg-btn pg-btn-primary">Simpan Pengaturan</button>
            </div>
        </form>

    {{-- ─────────────── Template Email ─────────────── --}}
    @else
        <div class="pg-toolbar">
            <p class="pg-intro">
                Template adalah teks email yang bisa dipilih saat mengirim. Tulisan seperti <code>{periode}</code> dan
                <code>{nama_perusahaan}</code> diganti otomatis, jadi satu template berlaku untuk semua triwulan.
            </p>
            <a href="{{ route('bps.pengingat.template.create') }}" class="pg-btn pg-btn-primary">
                @include('bps.pengingat.partials.icon', ['name' => 'plus'])
                Template Baru
            </a>
        </div>

        <div class="pg-template-grid">
            @foreach($templates as $t)
                <article class="pg-panel pg-template-card">
                    <div class="pg-template-title">
                        <h3>{{ $t->name }}</h3>
                        @if($autoTemplate->is($t))
                            <span class="pg-badge pg-badge-blue">Dipakai otomatis</span>
                        @endif
                    </div>
                    <div class="pg-template-subject"><span class="pg-muted">Subjek:</span> {{ $t->subject }}</div>
                    <div class="pg-template-excerpt">{{ \Illuminate\Support\Str::limit(preg_replace('/\s+/u', ' ', $t->body), 220) }}</div>
                    <div class="pg-hint">
                        Diubah {{ $wib($t->updated_at)->translatedFormat('d M Y H.i') }}@if($t->updatedBy) oleh {{ $t->updatedBy->name }}@endif
                    </div>
                    <div class="pg-actions">
                        <a href="{{ route('bps.pengingat.template.edit', $t) }}" class="pg-btn pg-btn-primary pg-btn-sm">Ubah &amp; Uji</a>
                        <a href="{{ route('bps.pengingat.template.create', ['dari' => $t->id]) }}" class="pg-btn pg-btn-ghost pg-btn-sm">Duplikat</a>
                        @if($templates->count() > 1 && ! $autoTemplate->is($t))
                            <form method="POST" action="{{ route('bps.pengingat.template.destroy', $t) }}"
                                  data-confirm="Hapus template ini?"
                                  data-confirm-text="Template “{{ $t->name }}” dihapus permanen. Riwayat pengiriman yang memakainya tetap tersimpan."
                                  data-confirm-ok="Ya, Hapus Template" data-confirm-tone="danger">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pg-btn pg-btn-danger pg-btn-sm">Hapus</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    @include('bps.pengingat.partials.confirm')
</div>
@endsection

@push('scripts')
<script>
// ── Kirim ──
(function () {
    var serverEnabled = @json($serverEnabled);
    var root = document.getElementById('pg-kirim');
    if (!root) return;

    // ── Step 1: a choice fetches the section and swaps it in, no reload ──
    var pending = null;

    // The current quarter plus the source cards ticked right now, so quick
    // clicks on several cards add up instead of undoing each other.
    function sourcesUrl(root) {
        var url = new URL(root.getAttribute('data-url'), location.href);
        url.searchParams.set('tab', 'kirim');
        url.searchParams.set('periode', root.getAttribute('data-period-key'));
        url.searchParams.set('pilih', '1');
        root.querySelectorAll('[data-pg-source].is-active').forEach(function (card) {
            url.searchParams.append('sumber[]', card.getAttribute('data-pg-source'));
        });
        return url.toString();
    }

    function load(url, focusSelector) {
        var current = document.getElementById('pg-kirim');
        // What the user typed or unticked survives the swap.
        var keep = {};
        ['template_id', 'extra_message', 'pg-search'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) keep[id] = el.value;
        });
        var unticked = [].slice.call(current.querySelectorAll('.pg-row-check:not(:disabled)'))
            .filter(function (b) { return !b.checked; })
            .map(function (b) { return b.value; });

        if (pending) pending.abort();
        pending = new AbortController();
        current.setAttribute('aria-busy', 'true');

        var fetchUrl = new URL(url, location.href);
        fetchUrl.searchParams.set('partial', '1');
        fetch(fetchUrl.toString(), {
            credentials: 'same-origin',
            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            signal: pending.signal,
        })
            .then(function (res) {
                // A redirect means the session ended; let the browser handle it.
                if (!res.ok || res.redirected) throw new Error('reload');
                return res.text();
            })
            .then(function (html) {
                var holder = document.createElement('div');
                holder.innerHTML = html;
                var fresh = holder.querySelector('#pg-kirim');
                if (!fresh) throw new Error('reload');

                current.replaceWith(fresh);
                Object.keys(keep).forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el) el.value = keep[id];
                });
                fresh.querySelectorAll('.pg-row-check:not(:disabled)').forEach(function (b) {
                    if (unticked.indexOf(b.value) !== -1) b.checked = false;
                });
                history.replaceState(null, '', url);
                init(fresh);
                var focus = focusSelector && fresh.querySelector(focusSelector);
                if (focus) focus.focus({ preventScroll: true });
                pending = null;
            })
            .catch(function (err) {
                if (err.name !== 'AbortError') window.location.href = url;
            });
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest('#pg-kirim [data-pg-source], #pg-kirim a[data-pg-period]');
        if (!link || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        e.preventDefault();
        var current = document.getElementById('pg-kirim');

        if (link.hasAttribute('data-pg-source')) {
            var key = link.getAttribute('data-pg-source');
            var on  = !link.classList.contains('is-active');
            link.classList.toggle('is-active', on);
            link.setAttribute('aria-checked', on ? 'true' : 'false');
            load(sourcesUrl(current), '[data-pg-source="' + key + '"]');
        } else {
            current.querySelectorAll('a[data-pg-period]').forEach(function (a) { a.classList.toggle('is-active', a === link); });
            // The cards belong to the old quarter until the new one arrives.
            current.setAttribute('data-switching', '');
            load(link.href, 'a[data-pg-period="' + link.getAttribute('data-pg-period') + '"]');
        }
    });

    // The cards act as checkboxes, so Space toggles them like Enter does.
    document.addEventListener('keydown', function (e) {
        if (e.key === ' ' && e.target.closest && e.target.closest('#pg-kirim [data-pg-source]')) {
            e.preventDefault();
            e.target.click();
        }
    });

    // The quarter dropdown (shown once there are many quarters).
    document.addEventListener('change', function (e) {
        if (!e.target.matches('#pg-kirim select[data-pg-period]')) return;
        var url = new URL(e.target.form.action, location.href);
        new FormData(e.target.form).forEach(function (value, name) { url.searchParams.set(name, value); });
        document.getElementById('pg-kirim').setAttribute('data-switching', '');
        load(url.toString(), '#periode');
    });

    init(root);

    // ── Steps 2 and 3, wired up again after every swap ──
    function init(root) {
        var form = root.querySelector('#pg-send-form');
        if (!form) return;

        var rows    = [].slice.call(form.querySelectorAll('tr[data-row]'));
        var boxes   = [].slice.call(form.querySelectorAll('.pg-row-check:not(:disabled)'));
        var all     = form.querySelector('#pg-check-all');
        var search  = form.querySelector('#pg-search');
        var noMatch = form.querySelector('#pg-no-match');
        var btn     = form.querySelector('#pg-send-btn');
        var counts  = [].slice.call(form.querySelectorAll('[data-selected-count]'));

        function checked() { return boxes.filter(function (b) { return b.checked; }).length; }
        function visible() { return boxes.filter(function (b) { return !b.closest('tr').hidden; }); }

        function refresh() {
            var n = checked();
            counts.forEach(function (el) { el.textContent = n; });
            if (btn) btn.disabled = !serverEnabled || n === 0;
            if (all) {
                var vis = visible(), on = vis.filter(function (b) { return b.checked; }).length;
                all.checked = vis.length > 0 && on === vis.length;
                all.indeterminate = on > 0 && on < vis.length;
            }
        }

        // "Select all" acts on the rows the search currently shows.
        if (all) all.addEventListener('change', function () {
            visible().forEach(function (b) { b.checked = all.checked; });
            refresh();
        });
        boxes.forEach(function (b) { b.addEventListener('change', refresh); });

        // Clicking anywhere on a row toggles it.
        rows.forEach(function (row) {
            row.addEventListener('click', function (e) {
                var box = row.querySelector('.pg-row-check');
                if (!box || box.disabled || e.target === box || e.target.closest('a, button, label')) return;
                box.checked = !box.checked;
                refresh();
            });
        });

        if (search) {
            search.addEventListener('input', function () {
                var q = search.value.trim().toLowerCase(), shown = 0;
                rows.forEach(function (row) {
                    var hit = !q || row.getAttribute('data-search').indexOf(q) !== -1;
                    row.hidden = !hit;
                    if (hit) shown++;
                });
                if (noMatch) noMatch.hidden = shown > 0;
                refresh();
            });
            // Enter in the search box must not submit the send form.
            search.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
        }

        // Keep "Pratinjau" showing the template and message currently chosen.
        var preview  = form.querySelector('#pg-preview-link');
        var template = form.querySelector('#template_id');
        var message  = form.querySelector('#extra_message');
        function syncPreview() {
            var url = new URL(preview.href);
            url.searchParams.set('template', template.value);
            url.searchParams.set('pesan', message.value);
            preview.href = url.toString();
        }
        if (preview && template && message) {
            template.addEventListener('change', syncPreview);
            message.addEventListener('input', syncPreview);
            syncPreview();
        }

        // Ask in the page's own dialog, then submit for real. The button is
        // disabled so a double click cannot queue the same blast twice.
        form.addEventListener('submit', function (e) {
            if (form.hasAttribute('data-confirmed')) return;
            e.preventDefault();

            var n = checked();
            var facts = [
                ['Periode', form.getAttribute('data-period')],
                ['Penerima', n.toLocaleString('id-ID') + ' alamat email'],
            ];
            if (template) facts.push(['Template', template.options[template.selectedIndex].text]);
            if (message && message.value.trim()) facts.push(['Pesan tambahan', message.value.trim(), true]);

            pgConfirm({
                title: 'Kirim email pengingat sekarang?',
                facts: facts,
                note:  'Email masuk antrean dan terkirim bertahap dalam beberapa menit. Email yang sudah terkirim tidak bisa ditarik kembali.',
                ok:    'Ya, Kirim ' + n.toLocaleString('id-ID') + ' Email',
            }).then(function (ok) {
                if (!ok) return;
                form.setAttribute('data-confirmed', '');
                if (btn) {
                    btn.disabled = true;
                    btn.lastChild.textContent = ' Mengirim…';
                }
                form.submit();
            });
        });

        // A search kept across a swap applies to the new list too.
        if (search && search.value) search.dispatchEvent(new Event('input'));
        refresh();
    }
})();

// ── Jadwal & Pengaturan: live summary of the schedule ──
(function () {
    var form = document.getElementById('pg-settings-form');
    if (!form) return;

    var toggle  = document.getElementById('auto_enabled');
    var section = document.getElementById('pg-auto-section');
    var label   = document.getElementById('pg-toggle-label');
    var box     = document.getElementById('pg-schedule-summary');
    var text    = document.getElementById('pg-schedule-text');
    var next    = document.getElementById('pg-schedule-next');
    var months  = box.getAttribute('data-months');
    var phrases = JSON.parse(box.getAttribute('data-phrases'));

    function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function join(items, last) {
        return items.length > 1 ? items.slice(0, -1).join(', ') + ' ' + last + ' ' + items[items.length - 1] : (items[0] || '');
    }
    function values(name) {
        return [].slice.call(form.querySelectorAll('input[name="' + name + '"]:checked')).map(function (i) { return i.value; });
    }

    function render() {
        var on      = toggle.checked;
        var days    = values('days[]');
        var targets = values('target_statuses[]').map(function (v) { return phrases[v]; }).filter(Boolean);
        var time    = (document.getElementById('send_time').value || '').replace(':', '.');
        var invalid = on && (!days.length || !targets.length);

        section.classList.toggle('is-off', !on);
        box.classList.toggle('is-off', !on);
        box.classList.toggle('is-invalid', invalid);
        label.textContent = on ? 'Aktif' : 'Nonaktif';

        if (!on) {
            text.innerHTML = 'Pengiriman otomatis <b>nonaktif</b>. Pengingat hanya terkirim saat Anda menekan tombol Kirim di tab Kirim Pengingat.';
        } else if (!days.length) {
            text.textContent = 'Pilih minimal satu tanggal kirim di bawah.';
        } else if (!targets.length) {
            text.textContent = 'Pilih minimal satu kelompok responden di bawah.';
        } else {
            text.innerHTML = 'Setiap tanggal <b>' + esc(join(days, 'dan')) + '</b> bulan ' + esc(months) + ', pukul <b>'
                + esc(time) + ' WIB</b>, sistem mengingatkan triwulan yang sedang didata kepada perusahaan yang <b>'
                + esc(join(targets, 'atau')) + '</b>.';
        }
    }

    function changed() {
        render();
        next.textContent = 'Perubahan belum disimpan. Tekan Simpan Pengaturan untuk menerapkannya.';
    }
    form.addEventListener('change', changed);
    document.getElementById('send_time').addEventListener('input', changed);
})();

// ── Riwayat: the whole row opens the detail page ──
document.querySelectorAll('tr[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('a, button')) return;
        window.location = row.getAttribute('data-href');
    });
});
</script>
@endpush
