@extends('layouts.bps')

@section('title', 'Pengingat SIBSTR - BPS Dashboard')
@section('description', 'Atur dan kirim email pengingat pengisian SIBSTR Triwulanan')

@php
    $roman = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
    $periodLabel = fn ($tahun, $triwulan) => 'Triwulan ' . ($roman[$triwulan] ?? $triwulan) . ' ' . $tahun;
    $wib = fn ($dt) => \Illuminate\Support\Carbon::parse($dt)->setTimezone('Asia/Jakarta');
@endphp

@push('styles')
@include('bps.pengingat.partials.styles')
@endpush

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengingat Pengisian SIBSTR</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Email pengingat ke alamat email Blok I responden yang belum menyelesaikan SIBSTR Triwulanan.
            </p>
        </div>
        <a href="{{ route('bps.pengingat.preview') }}" target="_blank" rel="noopener" class="pg-btn pg-btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            Lihat contoh email
        </a>
    </div>

    @unless($serverEnabled)
        <div class="pg-alert pg-alert-warn">
            <strong>Server ini tidak mengirim email.</strong>
            Pengiriman hanya aktif di server produksi (<code>SIBSTR_REMINDER_ENABLED</code>). Anda tetap dapat melihat daftar
            penerima dan mengubah pengaturan, tetapi tombol kirim dinonaktifkan.
        </div>
    @endunless

    @if($errors->any())
        <div class="pg-alert pg-alert-error">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    {{-- Summary --}}
    <div class="pg-cards">
        <div class="pg-card">
            <div class="pg-card-label">Triwulan yang sedang didata</div>
            <div class="pg-card-value">
                {{ $currentPeriod ? $periodLabel($currentPeriod['tahun'], $currentPeriod['triwulan']) : 'Belum ada yang dibuka' }}
            </div>
        </div>
        <div class="pg-card">
            <div class="pg-card-label">Pengiriman otomatis</div>
            <div class="pg-card-value">
                @if($settings->auto_enabled)
                    <span class="pg-badge pg-badge-green">Aktif</span>
                @else
                    <span class="pg-badge pg-badge-gray">Nonaktif</span>
                @endif
            </div>
            <div class="pg-card-sub">
                @if($nextRun)
                    Berikutnya: {{ $nextRun->translatedFormat('l, d M Y') }} pukul {{ $nextRun->format('H.i') }} WIB
                @elseif($settings->auto_enabled)
                    Tidak ada jadwal dalam 15 bulan ke depan
                @else
                    Aktifkan di tab Pengaturan Otomatis
                @endif
            </div>
        </div>
        <div class="pg-card">
            <div class="pg-card-label">Pengirim</div>
            <div class="pg-card-value pg-break">{{ $settings->from_name ?: config('mail.from.name') }}</div>
            <div class="pg-card-sub pg-break">
                {{ $fromAddress }}<br>
                Balasan ke: {{ $settings->reply_to ?: '— (email tidak bisa dibalas)' }}
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-1" aria-label="Tabs">
            @foreach(['kirim' => 'Kirim Manual', 'template' => 'Template Email', 'pengaturan' => 'Pengaturan Otomatis', 'riwayat' => 'Riwayat'] as $key => $label)
                <a href="{{ route('bps.pengingat.index', ['tab' => $key]) }}"
                   class="flex items-center gap-2 px-5 py-3 text-sm font-medium border-b-2 transition-colors
                          {{ $tab === $key
                              ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400'
                              : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- ─────────────── Kirim Manual ─────────────── --}}
    @if($tab === 'kirim')
        @if($periods === [])
            <div class="pg-panel pg-empty">Belum ada triwulan SIBSTR yang dibuka untuk pengisian.</div>
        @else
            @php
                $reachable = $recipients->whereNotNull('email');
                $noEmail   = $recipients->whereNull('email');
                $selectable = $reachable->filter(fn ($r) => ! in_array($sentToday[$r['user']->id] ?? null, ['queued', 'sending', 'sent'], true));
            @endphp

            <form method="GET" action="{{ route('bps.pengingat.index') }}" class="pg-panel pg-filter">
                <input type="hidden" name="tab" value="kirim">
                <div class="pg-field">
                    <label for="periode" class="pg-label">Periode</label>
                    <select id="periode" name="periode" class="pg-input">
                        @foreach($periods as $p)
                            <option value="{{ $p['tahun'] }}-{{ $p['triwulan'] }}" @selected($p['tahun'] === $tahun && $p['triwulan'] === $triwulan)>
                                {{ $periodLabel($p['tahun'], $p['triwulan']) }}
                                @if($currentPeriod === $p) (sedang didata) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="pg-field">
                    <span class="pg-label">Kirim ke responden yang</span>
                    <div class="pg-checks">
                        @foreach($statusLabels as $value => $label)
                            <label class="pg-check">
                                <input type="checkbox" name="status[]" value="{{ $value }}" @checked(in_array($value, $statuses, true))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="pg-field pg-field-end">
                    <button type="submit" class="pg-btn pg-btn-primary">Tampilkan Penerima</button>
                </div>
            </form>

            <p class="text-sm text-gray-600 dark:text-gray-400">
                <strong>{{ $recipients->count() }}</strong> responden belum menyelesaikan {{ $periodLabel($tahun, $triwulan) }}
                sesuai filter —
                <strong>{{ $reachable->count() }}</strong> punya email Blok I,
                <strong>{{ $noEmail->count() }}</strong> tanpa email yang valid.
                Perusahaan berstatus {{ collect($settings->excluded_kondisi ?? [])->map(fn ($k) => $kondisiLabels[$k] ?? $k)->implode(', ') ?: '—' }}
                tidak ditampilkan (atur di tab Pengaturan).
            </p>

            @if($reachable->isNotEmpty())
                <form method="POST" action="{{ route('bps.pengingat.send') }}" id="pg-send-form" class="pg-panel pg-flush">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="triwulan" value="{{ $triwulan }}">
                    @foreach($statuses as $s)<input type="hidden" name="status[]" value="{{ $s }}">@endforeach

                    <div class="overflow-x-auto">
                        <table class="pg-table">
                            <thead>
                                <tr>
                                    <th class="pg-col-check">
                                        <input type="checkbox" id="pg-check-all" title="Pilih semua" checked @disabled($selectable->isEmpty())>
                                    </th>
                                    <th>Perusahaan</th>
                                    <th>Penghubung</th>
                                    <th>Email Blok I</th>
                                    <th>Status Isian</th>
                                    <th>Pengingat Periode Ini</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reachable as $r)
                                    @php
                                        $uid   = $r['user']->id;
                                        $today = $sentToday[$uid] ?? null;
                                        $locked = in_array($today, ['queued', 'sending', 'sent'], true);
                                        $hist  = $history[$uid] ?? null;
                                    @endphp
                                    <tr class="{{ $locked ? 'pg-row-muted' : '' }}">
                                        <td class="pg-col-check">
                                            <input type="checkbox" name="user_ids[]" value="{{ $uid }}" class="pg-row-check"
                                                   @checked(! $locked) @disabled($locked)>
                                        </td>
                                        <td class="font-medium">{{ $r['nama_perusahaan'] ?: '-' }}</td>
                                        <td>{{ $r['penghubung'] ?: '-' }}</td>
                                        <td class="pg-break">{{ $r['email'] }}</td>
                                        <td>
                                            @if($r['status'] === 'dalam_proses')
                                                <span class="pg-badge pg-badge-amber">Dalam proses</span>
                                            @else
                                                <span class="pg-badge pg-badge-gray">Belum mulai</span>
                                            @endif
                                        </td>
                                        <td class="text-xs">
                                            @if($today === 'sent')
                                                <span class="pg-badge pg-badge-green">Sudah dikirim hari ini</span>
                                            @elseif(in_array($today, ['queued', 'sending'], true))
                                                <span class="pg-badge pg-badge-blue">Dalam antrean</span>
                                            @elseif($today === 'failed')
                                                <span class="pg-badge pg-badge-red">Gagal hari ini — bisa dikirim ulang</span>
                                            @endif
                                            @if($hist)
                                                <div class="pg-muted">{{ $hist->total }}× · terakhir {{ $wib($hist->last_sent_at)->translatedFormat('d M Y') }}</div>
                                            @elseif(! $today)
                                                <span class="pg-muted">Belum pernah</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pg-send-footer">
                        <div class="pg-field pg-field-narrow">
                            <label for="template_id" class="pg-label">Template email</label>
                            <select id="template_id" name="template_id" class="pg-input">
                                @foreach($templates as $t)
                                    <option value="{{ $t->id }}" @selected(old('template_id', $autoTemplate->id) === $t->id)>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            <div class="pg-hint">
                                <a href="{{ route('bps.pengingat.index', ['tab' => 'template']) }}" class="pg-link">Kelola template</a>
                                · periode otomatis menjadi {{ $periodLabel($tahun, $triwulan) }}
                            </div>
                        </div>
                        <div class="pg-field">
                            <label for="extra_message" class="pg-label">Pesan tambahan (opsional)</label>
                            <textarea id="extra_message" name="extra_message" rows="2" maxlength="1000" class="pg-input"
                                      placeholder="Contoh: Batas akhir pengisian 25 Oktober 2026.">{{ old('extra_message', $settings->extra_message) }}</textarea>
                            <div class="pg-hint">Ditampilkan dalam kotak di badan email. Bawaan diambil dari tab Pengaturan.</div>
                        </div>
                        <div class="pg-send-actions">
                            <button type="submit" class="pg-btn pg-btn-primary" id="pg-send-btn" @disabled(! $serverEnabled || $selectable->isEmpty())>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Kirim ke <span id="pg-send-count">{{ $selectable->count() }}</span> responden
                            </button>
                            <div class="pg-hint">Email masuk antrean dan terkirim bertahap (±{{ config('sibstr.reminder.batch_size') }} per menit).</div>
                        </div>
                    </div>
                </form>
            @endif

            @if($noEmail->isNotEmpty())
                <div class="pg-panel pg-flush">
                    <div class="pg-panel-head">
                        <h2>Tanpa email Blok I yang valid ({{ $noEmail->count() }})</h2>
                        <p>Tidak bisa dikirimi email — hubungi lewat telepon atau minta perbaiki email di Blok I.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="pg-table">
                            <thead>
                                <tr><th>Perusahaan</th><th>Penghubung</th><th>Telepon (Blok I)</th><th>Akun Login</th><th>Status Isian</th></tr>
                            </thead>
                            <tbody>
                                @foreach($noEmail as $r)
                                    <tr>
                                        <td class="font-medium">{{ $r['nama_perusahaan'] ?: '-' }}</td>
                                        <td>{{ $r['penghubung'] ?: '-' }}</td>
                                        <td>{{ $r['telepon'] ?: '-' }}</td>
                                        <td class="pg-break">{{ $r['user']->email }}</td>
                                        <td>{{ $r['status'] === 'dalam_proses' ? 'Dalam proses' : 'Belum mulai' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif

    {{-- ─────────────── Template Email ─────────────── --}}
    @elseif($tab === 'template')
        <div class="pg-toolbar">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Teks email yang bisa dipilih saat mengirim. Placeholder seperti <code>{periode}</code> terisi sesuai triwulan yang dikirim,
                jadi template yang sama berlaku untuk Triwulan I–IV.
            </p>
            <a href="{{ route('bps.pengingat.template.create') }}" class="pg-btn pg-btn-primary">+ Template Baru</a>
        </div>

        <div class="pg-panel pg-flush">
            <div class="overflow-x-auto">
                <table class="pg-table">
                    <thead>
                        <tr><th>Template</th><th>Terakhir diubah</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $t)
                            <tr>
                                <td>
                                    <div class="font-medium">
                                        {{ $t->name }}
                                        @if($autoTemplate->is($t))
                                            <span class="pg-badge pg-badge-blue">Dipakai otomatis</span>
                                        @endif
                                    </div>
                                    <div class="pg-template-snippet" title="{{ $t->subject }}">Subjek: {{ $t->subject }}</div>
                                </td>
                                <td class="text-xs">
                                    {{ $wib($t->updated_at)->translatedFormat('d M Y H.i') }}
                                    @if($t->updatedBy)<div class="pg-muted">{{ $t->updatedBy->name }}</div>@endif
                                </td>
                                <td>
                                    <div class="pg-actions">
                                        <a href="{{ route('bps.pengingat.template.edit', $t) }}" class="pg-btn pg-btn-ghost pg-btn-sm">Ubah &amp; Uji</a>
                                        <a href="{{ route('bps.pengingat.template.create', ['dari' => $t->id]) }}" class="pg-btn pg-btn-ghost pg-btn-sm">Duplikat</a>
                                        @if($templates->count() > 1)
                                            <form method="POST" action="{{ route('bps.pengingat.template.destroy', $t) }}"
                                                  onsubmit="return confirm(@js('Hapus template "' . $t->name . '"?'))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="pg-btn pg-btn-danger pg-btn-sm">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    {{-- ─────────────── Pengaturan Otomatis ─────────────── --}}
    @elseif($tab === 'pengaturan')
        @php
            $days     = array_map('intval', old('days', $settings->days ?? []));
            $targets  = old('target_statuses', $settings->target_statuses ?? []);
            $excluded = old('excluded_kondisi', $settings->excluded_kondisi ?? []);
        @endphp
        <form method="POST" action="{{ route('bps.pengingat.settings') }}" class="pg-panel pg-settings">
            @csrf
            @method('PUT')

            <section>
                <label class="pg-switch">
                    <input type="hidden" name="auto_enabled" value="0">
                    <input type="checkbox" name="auto_enabled" value="1" @checked(old('auto_enabled', $settings->auto_enabled))>
                    <span><strong>Kirim pengingat otomatis</strong><br>
                        <span class="pg-hint">Sistem mengirim sendiri pada tanggal di bawah, di bulan awal pendataan triwulan
                        (Januari, April, Juli, Oktober). Pengiriman manual tetap bisa dilakukan kapan saja.</span></span>
                </label>
            </section>

            <section>
                <h3>Tanggal pengiriman</h3>
                <p class="pg-hint">Tanggal pada bulan awal pendataan. Tanggal yang tidak ada di bulan itu (mis. 31 April) dilewati.</p>
                <div class="pg-days">
                    @for($d = 1; $d <= 31; $d++)
                        <label class="pg-day">
                            <input type="checkbox" name="days[]" value="{{ $d }}" @checked(in_array($d, $days, true))>
                            <span>{{ $d }}</span>
                        </label>
                    @endfor
                </div>
            </section>

            <section class="pg-grid-2">
                <div class="pg-field">
                    <label for="settings_template_id" class="pg-label">Template email</label>
                    <select id="settings_template_id" name="template_id" class="pg-input">
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}" @selected(old('template_id', $autoTemplate->id) === $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    <div class="pg-hint">Teks yang dipakai pengiriman otomatis; periode terisi sesuai triwulan yang sedang didata.</div>
                </div>
                <div class="pg-field">
                    <label for="send_time" class="pg-label">Jam kirim (WIB)</label>
                    <input type="time" id="send_time" name="send_time" class="pg-input" value="{{ old('send_time', $settings->send_time) }}" required>
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
                </div>
            </section>

            <section>
                <h3>Jangan kirim ke perusahaan berstatus</h3>
                <p class="pg-hint">Berdasarkan jawaban Blok II (R201) terakhir perusahaan. Berlaku juga untuk pengiriman manual.</p>
                <div class="pg-checks">
                    @foreach($kondisiLabels as $value => $label)
                        <label class="pg-check">
                            <input type="checkbox" name="excluded_kondisi[]" value="{{ $value }}" @checked(in_array($value, $excluded, true))>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="pg-grid-2">
                <div class="pg-field">
                    <label for="from_name" class="pg-label">Nama pengirim</label>
                    <input type="text" id="from_name" name="from_name" maxlength="100" class="pg-input"
                           value="{{ old('from_name', $settings->from_name) }}" placeholder="BPS Kota Batam">
                    <div class="pg-hint">Alamat pengirim: {{ $fromAddress }} (diatur di server).</div>
                </div>
                <div class="pg-field">
                    <label for="reply_to" class="pg-label">Balasan dikirim ke (Reply-To)</label>
                    <input type="email" id="reply_to" name="reply_to" maxlength="255" class="pg-input"
                           value="{{ old('reply_to', $settings->reply_to) }}" placeholder="nama@instansi.go.id">
                    <div class="pg-hint">Kotak masuk yang dipantau petugas. Kosongkan bila email tidak perlu bisa dibalas.</div>
                </div>
            </section>

            <section>
                <div class="pg-field">
                    <label for="settings_extra_message" class="pg-label">Pesan tambahan bawaan (opsional)</label>
                    <textarea id="settings_extra_message" name="extra_message" rows="3" maxlength="1000" class="pg-input"
                              placeholder="Contoh: Batas akhir pengisian tanggal 25 bulan ini.">{{ old('extra_message', $settings->extra_message) }}</textarea>
                    <div class="pg-hint">Dipakai pengiriman otomatis dan sebagai isian awal pengiriman manual.</div>
                </div>
            </section>

            <div class="pg-settings-footer">
                <span class="pg-hint">
                    @if($settings->exists)
                        Terakhir diubah {{ $wib($settings->updated_at)->translatedFormat('d M Y H.i') }} WIB
                        @if($settings->updatedBy) oleh {{ $settings->updatedBy->name }} @endif
                    @else
                        Belum pernah disimpan — nilai di atas adalah bawaan.
                    @endif
                </span>
                <button type="submit" class="pg-btn pg-btn-primary">Simpan Pengaturan</button>
            </div>
        </form>

    {{-- ─────────────── Riwayat ─────────────── --}}
    @else
        <div class="pg-panel pg-flush">
            @if($blasts->isEmpty())
                <div class="pg-empty">Belum ada pengingat yang dikirim.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="pg-table">
                        <thead>
                            <tr><th>Waktu</th><th>Jenis</th><th>Periode</th><th>Penerima</th><th>Terkirim</th><th>Antrean</th><th>Gagal</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($blasts as $b)
                                <tr>
                                    <td>{{ $wib($b->created_at)->translatedFormat('d M Y H.i') }}</td>
                                    <td>
                                        @if($b->trigger === 'otomatis')
                                            <span class="pg-badge pg-badge-blue">Otomatis</span>
                                        @else
                                            <span class="pg-badge pg-badge-gray">Manual</span>
                                            <div class="pg-muted">{{ $b->createdBy->name ?? '-' }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $periodLabel($b->tahun, $b->triwulan) }}</td>
                                    <td>{{ $b->logs_count }}</td>
                                    <td>{{ $b->sent_count }}</td>
                                    <td>{{ $b->queued_count }}</td>
                                    <td>{{ $b->failed_count }}</td>
                                    <td><a href="{{ route('bps.pengingat.blast', $b) }}" class="pg-link">Detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pg-pagination">{{ $blasts->links() }}</div>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('pg-send-form');
    if (!form) return;

    var all   = document.getElementById('pg-check-all');
    var rows  = Array.prototype.slice.call(form.querySelectorAll('.pg-row-check:not(:disabled)'));
    var count = document.getElementById('pg-send-count');
    var btn   = document.getElementById('pg-send-btn');
    var serverEnabled = @json($serverEnabled);

    function refresh() {
        var n = rows.filter(function (r) { return r.checked; }).length;
        count.textContent = n;
        btn.disabled = !serverEnabled || n === 0;
        if (all) {
            all.checked = n > 0 && n === rows.length;
            all.indeterminate = n > 0 && n < rows.length;
        }
    }

    if (all) all.addEventListener('change', function () {
        rows.forEach(function (r) { r.checked = all.checked; });
        refresh();
    });
    rows.forEach(function (r) { r.addEventListener('change', refresh); });

    form.addEventListener('submit', function (e) {
        var n = count.textContent;
        if (!confirm('Kirim email pengingat SIBSTR ke ' + n + ' responden?')) e.preventDefault();
    });

    refresh();
})();
</script>
@endpush
