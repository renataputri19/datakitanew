{{-- The Kirim tab. Rendered inside index.blade.php, and on its own (?partial=1)
     when a choice in step 1 changes, so the page swaps it in without reloading. --}}
@php
    $periodLabel ??= fn ($tahun, $triwulan) => 'Triwulan ' . \App\Support\ReminderTemplate::roman($triwulan) . ' ' . $tahun;
    $wib ??= fn ($dt) => \Illuminate\Support\Carbon::parse($dt)->setTimezone('Asia/Jakarta');
    $joinWith ??= fn (array $items, string $last = 'dan') => count($items) > 1
        ? implode(', ', array_slice($items, 0, -1)) . " {$last} " . $items[count($items) - 1]
        : (string) ($items[0] ?? '');

    // One row per email address; sends and history are tracked per address too.
    $reachable   = $recipients->whereNotNull('email');
    $noEmail     = $recipients->whereNull('email');
    $emailKey    = fn ($r) => mb_strtolower($r['email']);
    $isLocked    = fn ($r) => in_array($sentToday[$emailKey($r)] ?? null, ['queued', 'sending', 'sent'], true);
    $selectable  = $reachable->reject($isLocked);
    $lockedCount = $reachable->count() - $selectable->count();
    $sharedCount = $reachable->filter(fn ($r) => count($r['companies']) > 1)->count();
    $periodText  = $periodLabel($tahun, $triwulan);
    $periodKey   = $tahun . '-' . $triwulan;

    // Each source chip links to the same page with that source toggled;
    // `pilih` marks the list as chosen, so unticking every chip sticks.
    $toggleUrl = fn ($key) => route('bps.pengingat.index', ['tab' => 'kirim', 'periode' => $periodKey, 'pilih' => 1,
        'sumber' => in_array($key, $sources, true) ? array_values(array_diff($sources, [$key])) : [...$sources, $key]]);

    $excludedText = $joinWith(collect($settings->excluded_kondisi ?? [])
        ->map(fn ($k) => mb_strtolower($kondisiLabels[$k] ?? $k))->all(), 'atau');

    // A merged address stands for several companies; count the companies.
    $companyCount = $recipients->sum(fn ($r) => max(1, count($r['companies'])));
@endphp

<div class="pg-stack" id="pg-kirim" data-url="{{ route('bps.pengingat.index') }}" data-period-key="{{ $periodKey }}">
    {{-- Step 1 --}}
    <section class="pg-step">
        <div class="pg-step-head">
            <span class="pg-step-num">1</span>
            <div>
                <h2>Pilih triwulan dan sumber data</h2>
                <p>Daftar penerima di bawah langsung menyesuaikan pilihan Anda.</p>
            </div>
            <span class="pg-loading" aria-hidden="true">Memuat…</span>
        </div>
        <div class="pg-step-body">
            <div class="pg-choice">
                <span class="pg-label">Triwulan yang diingatkan</span>
                @if(count($periods) <= 4)
                    <div class="pg-segmented">
                        @foreach($periods as $p)
                            @php $key = $p['tahun'] . '-' . $p['triwulan']; @endphp
                            <a href="{{ route('bps.pengingat.index', ['tab' => 'kirim', 'periode' => $key]) }}" data-pg-period="{{ $key }}"
                               class="pg-seg {{ $key === $periodKey ? 'is-active' : '' }}" @if($key === $periodKey) aria-current="true" @endif>
                                {{ $periodLabel($p['tahun'], $p['triwulan']) }}
                                @if($currentPeriod === $p)<span class="pg-seg-note">sedang didata</span>@endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <form method="GET" action="{{ route('bps.pengingat.index') }}">
                        <input type="hidden" name="tab" value="kirim">
                        <label for="periode" class="pg-sr">Triwulan</label>
                        <select id="periode" name="periode" class="pg-input pg-select-auto" data-pg-period>
                            @foreach($periods as $p)
                                <option value="{{ $p['tahun'] }}-{{ $p['triwulan'] }}" @selected($p['tahun'] . '-' . $p['triwulan'] === $periodKey)>
                                    {{ $periodLabel($p['tahun'], $p['triwulan']) }}@if($currentPeriod === $p) (sedang didata)@endif
                                </option>
                            @endforeach
                        </select>
                        <noscript><button type="submit" class="pg-btn pg-btn-ghost">Tampilkan</button></noscript>
                    </form>
                @endif
            </div>

            <div class="pg-choice">
                <span class="pg-label" id="pg-sources-label">Ambil responden dari data <span class="pg-optional">(boleh pilih lebih dari satu)</span></span>
                <div class="pg-sources" role="group" aria-labelledby="pg-sources-label">
                    @foreach($sourceOptions as $key => $label)
                        @php
                            $on    = in_array($key, $sources, true);
                            $count = $sourceCounts[$key] ?? 0;
                            $started = $key === \App\Support\SibstrReminder::SOURCE_STARTED;
                            [$y, $q] = $started ? [$tahun, $triwulan] : array_map('intval', explode('-', $key));
                        @endphp
                        <a href="{{ $toggleUrl($key) }}" data-pg-source="{{ $key }}" role="checkbox" aria-checked="{{ $on ? 'true' : 'false' }}"
                           class="pg-source {{ $on ? 'is-active' : '' }} {{ $started ? 'pg-source-started' : '' }} {{ $count === 0 ? 'is-zero' : '' }}">
                            <span class="pg-checkbox" aria-hidden="true"></span>
                            <span class="pg-source-text">
                                <span class="pg-source-title">{{ $q === 0 ? 'Tahunan ' . $y : $periodLabel($y, $q) }}</span>
                                <span class="pg-source-sub">{{ $started ? 'Sudah mulai, belum selesai' : 'Sudah selesai mengisi' }}</span>
                            </span>
                            <span class="pg-source-count" title="{{ $count }} perusahaan dari data ini belum menyelesaikan {{ $periodText }}">{{ $count }}</span>
                        </a>
                    @endforeach
                </div>
                <p class="pg-hint">
                    Angka = perusahaan dari data itu yang belum menyelesaikan {{ $periodText }}.
                    @if($excludedText !== '')
                        Perusahaan berstatus {{ $excludedText }} (Blok II) tidak diikutkan.
                        <a href="{{ route('bps.pengingat.index', ['tab' => 'pengaturan']) }}" class="pg-link">Ubah</a>
                    @endif
                </p>
            </div>

            <div class="pg-result" aria-live="polite">
                @if($sources === [])
                    Centang minimal satu sumber data.
                @else
                    <span><b>{{ $companyCount }}</b> perusahaan</span>
                    <span class="pg-result-arrow" aria-hidden="true">→</span>
                    <span><b>{{ $reachable->count() }}</b> alamat email</span>
                    @php
                        $notes = array_filter([
                            $companyCount > $recipients->count() ? 'perusahaan dengan email yang sama digabung' : null,
                            $noEmail->isNotEmpty() ? $noEmail->count() . ' tanpa email yang valid' : null,
                        ]);
                    @endphp
                    @if($notes)
                        <span class="pg-hint">{{ implode(' · ', $notes) }}</span>
                    @endif
                @endif
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('bps.pengingat.send') }}" id="pg-send-form" class="pg-stack" data-period="{{ $periodText }}">
        @csrf
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        <input type="hidden" name="triwulan" value="{{ $triwulan }}">
        @foreach($sources as $s)<input type="hidden" name="sumber[]" value="{{ $s }}">@endforeach

        {{-- Step 2 --}}
        <section class="pg-step">
            <div class="pg-step-head">
                <span class="pg-step-num">2</span>
                <div>
                    <h2>Periksa penerima</h2>
                    <p>Semua sudah dicentang. Hapus centang perusahaan yang tidak perlu diingatkan.</p>
                </div>
            </div>

            @if($reachable->isEmpty())
                <div class="pg-empty">
                    @if($sources === [])
                        @include('bps.pengingat.partials.icon', ['name' => 'list'])
                        <p class="pg-empty-title">Belum ada sumber data yang dipilih</p>
                        <p>Centang minimal satu sumber di atas, misalnya Selesai Triwulan II.</p>
                    @elseif($recipients->isEmpty())
                        @include('bps.pengingat.partials.icon', ['name' => 'party'])
                        <p class="pg-empty-title">Tidak ada yang perlu diingatkan</p>
                        <p>Semua responden pada pilihan ini sudah menyelesaikan {{ $periodText }}.</p>
                    @else
                        @include('bps.pengingat.partials.icon', ['name' => 'inbox'])
                        <p class="pg-empty-title">Tidak ada yang bisa dikirimi email</p>
                        <p>Responden di bawah belum punya email Blok I yang valid. Hubungi lewat telepon.</p>
                    @endif
                </div>
            @else
                <div class="pg-table-tools">
                    <div class="pg-search">
                        @include('bps.pengingat.partials.icon', ['name' => 'search'])
                        <label for="pg-search" class="pg-sr">Cari penerima</label>
                        <input type="search" id="pg-search" class="pg-input" autocomplete="off"
                               placeholder="Cari perusahaan, penghubung, atau email…">
                    </div>
                    <div class="pg-selected">
                        <strong data-selected-count>{{ $selectable->count() }}</strong> dari {{ $reachable->count() }} alamat email dipilih
                    </div>
                </div>
                <div class="pg-table-scroll">
                    <table class="pg-table">
                        <thead>
                            <tr>
                                <th class="pg-col-check">
                                    <input type="checkbox" id="pg-check-all" aria-label="Pilih semua" checked @disabled($selectable->isEmpty())>
                                </th>
                                <th>Perusahaan</th>
                                <th>Email Blok I</th>
                                <th>Dari data</th>
                                <th>Status isian</th>
                                <th>Sudah diingatkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reachable as $r)
                                @php
                                    $uid    = $r['user']->id;
                                    $today  = $sentToday[$emailKey($r)] ?? null;
                                    $locked = $isLocked($r);
                                    $hist   = $history[$emailKey($r)] ?? null;
                                @endphp
                                <tr data-row data-search="{{ mb_strtolower(implode(' ', $r['companies']) . ' ' . $r['penghubung'] . ' ' . $r['email']) }}"
                                    class="{{ $locked ? 'pg-row-muted' : 'pg-row-click' }}">
                                    <td class="pg-col-check">
                                        <input type="checkbox" name="user_ids[]" value="{{ $uid }}" class="pg-row-check"
                                               aria-label="Pilih {{ $r['nama_perusahaan'] ?: $r['email'] }}"
                                               @checked(! $locked) @disabled($locked)>
                                    </td>
                                    <td>
                                        @forelse($r['companies'] as $company)
                                            <div class="pg-strong">{{ $company }}</div>
                                        @empty
                                            <div class="pg-strong">-</div>
                                        @endforelse
                                        @if(count($r['companies']) > 1)
                                            <span class="pg-badge pg-badge-blue">1 email untuk {{ count($r['companies']) }} perusahaan</span>
                                        @endif
                                        @if($r['penghubung'])<div class="pg-muted">{{ $r['penghubung'] }}</div>@endif
                                    </td>
                                    <td class="pg-break">{{ $r['email'] }}</td>
                                    <td>
                                        <div class="pg-tags">
                                            @foreach(array_intersect($sources, $r['sources']) as $s)
                                                <span class="pg-tag">{{ \App\Support\SibstrReminder::sourceTag($s, $triwulan) }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        @if($r['status'] === 'dalam_proses')
                                            <span class="pg-badge pg-badge-amber">Sudah mulai</span>
                                        @else
                                            <span class="pg-badge pg-badge-gray">Belum mulai</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($today === 'sent')
                                            <span class="pg-badge pg-badge-green">Terkirim hari ini</span>
                                        @elseif(in_array($today, ['queued', 'sending'], true))
                                            <span class="pg-badge pg-badge-blue">Dalam antrean</span>
                                        @elseif($today === 'failed')
                                            <span class="pg-badge pg-badge-red">Gagal hari ini, bisa dikirim ulang</span>
                                        @endif
                                        @if($hist)
                                            <div class="pg-muted">{{ $hist->total }}× · terakhir {{ $wib($hist->last_sent_at)->translatedFormat('d M Y') }}</div>
                                        @elseif(! $today)
                                            <span class="pg-muted">Belum pernah</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="pg-no-match" hidden>
                                <td colspan="6" class="pg-empty-sm">Tidak ada penerima yang cocok dengan pencarian.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @if($lockedCount > 0 || $sharedCount > 0)
                    <p class="pg-table-note">
                        @if($sharedCount > 0)
                            {{ $sharedCount }} alamat email dipakai lebih dari satu perusahaan; tiap alamat menerima satu email yang menyebut semua perusahaannya.
                        @endif
                        @if($lockedCount > 0)
                            {{ $lockedCount }} alamat sudah dikirimi pengingat {{ $periodText }} hari ini, jadi tidak bisa dipilih lagi.
                        @endif
                    </p>
                @endif
            @endif

            @if($noEmail->isNotEmpty())
                <details class="pg-disclosure">
                    <summary>
                        @include('bps.pengingat.partials.icon', ['name' => 'warn'])
                        <span>
                            <strong>{{ $noEmail->count() }} responden tidak bisa dikirimi email</strong> karena email Blok I-nya
                            kosong atau tidak valid. Klik untuk melihat nomor teleponnya.
                        </span>
                        @include('bps.pengingat.partials.icon', ['name' => 'chevron', 'class' => 'pg-chevron'])
                    </summary>
                    <div class="pg-table-scroll">
                        <table class="pg-table">
                            <thead>
                                <tr><th>Perusahaan</th><th>Telepon (Blok I)</th><th>Akun login</th><th>Status isian</th></tr>
                            </thead>
                            <tbody>
                                @foreach($noEmail as $r)
                                    <tr>
                                        <td>
                                            <div class="pg-strong">{{ $r['nama_perusahaan'] ?: '-' }}</div>
                                            @if($r['penghubung'])<div class="pg-muted">{{ $r['penghubung'] }}</div>@endif
                                        </td>
                                        <td class="pg-nowrap">{{ $r['telepon'] ?: '-' }}</td>
                                        <td class="pg-break">{{ $r['user']->email }}</td>
                                        <td>{{ $r['status'] === 'dalam_proses' ? 'Sudah mulai' : 'Belum mulai' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        </section>

        {{-- Step 3 --}}
        @if($reachable->isNotEmpty())
            @php
                $previewUrl = route('bps.pengingat.preview', array_filter([
                    'template'     => old('template_id', $autoTemplate->id),
                    'periode'      => $tahun . '-' . $triwulan,
                    'pesan'        => (string) old('extra_message', $settings->extra_message),
                    'dalam_proses' => $sources === [\App\Support\SibstrReminder::SOURCE_STARTED] ? 1 : null,
                ], fn ($v) => $v !== null));
            @endphp
            <section class="pg-step">
                <div class="pg-step-head">
                    <span class="pg-step-num">3</span>
                    <div>
                        <h2>Pilih isi email, lalu kirim</h2>
                        <p>Cek dulu lewat Pratinjau. Periode otomatis menjadi {{ $periodText }}.</p>
                    </div>
                </div>
                <div class="pg-step-body">
                    <div class="pg-grid-2">
                        <div class="pg-field">
                            <label for="template_id" class="pg-label">Template email</label>
                            <select id="template_id" name="template_id" class="pg-input">
                                @foreach($templates as $t)
                                    <option value="{{ $t->id }}" @selected(old('template_id', $autoTemplate->id) === $t->id)>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            <div class="pg-hint">
                                Teks utama email.
                                <a href="{{ route('bps.pengingat.index', ['tab' => 'template']) }}" class="pg-link">Kelola template</a>
                            </div>
                        </div>
                        <div class="pg-field">
                            <label for="extra_message" class="pg-label">Pesan tambahan <span class="pg-optional">(opsional)</span></label>
                            <textarea id="extra_message" name="extra_message" rows="2" maxlength="1000" class="pg-input"
                                      placeholder="Contoh: Batas akhir pengisian 25 Oktober 2026.">{{ old('extra_message', $settings->extra_message) }}</textarea>
                            <div class="pg-hint">Tampil dalam kotak tersendiri di atas tombol "Isi Survei". Kosongkan bila tidak perlu.</div>
                        </div>
                    </div>
                </div>
                <div class="pg-sendbar">
                    <div class="pg-sendbar-text">
                        <strong><span data-selected-count>{{ $selectable->count() }}</span> alamat email</strong>
                        akan menerima pengingat {{ $periodText }}.
                        <span class="pg-hint">
                            @if($serverEnabled)
                                Email masuk antrean dan terkirim bertahap dalam beberapa menit.
                            @else
                                Tombol kirim nonaktif karena server ini tidak mengirim email.
                            @endif
                        </span>
                    </div>
                    <div class="pg-sendbar-actions">
                        <a href="{{ $previewUrl }}" id="pg-preview-link" target="_blank" rel="noopener" class="pg-btn pg-btn-ghost">
                            @include('bps.pengingat.partials.icon', ['name' => 'eye'])
                            Pratinjau
                        </a>
                        <button type="submit" class="pg-btn pg-btn-primary pg-btn-lg" id="pg-send-btn"
                                @disabled(! $serverEnabled || $selectable->isEmpty())>
                            @include('bps.pengingat.partials.icon', ['name' => 'mail'])
                            Kirim Pengingat
                        </button>
                    </div>
                </div>
            </section>
        @endif
    </form>
</div>
