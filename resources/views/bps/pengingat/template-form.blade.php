@extends('layouts.bps')

@section('title', ($template->exists ? 'Ubah' : 'Buat') . ' Template Pengingat - BPS Dashboard')

@push('styles')
@include('bps.pengingat.partials.styles')
@endpush

@section('content')
<div class="pg">
    <div>
        <a href="{{ route('bps.pengingat.index', ['tab' => 'template']) }}" class="pg-link pg-back">
            @include('bps.pengingat.partials.icon', ['name' => 'back'])
            Kembali ke daftar template
        </a>
        <h1 class="pg-title">
            {{ $template->exists ? 'Ubah Template: ' . $template->name : 'Template Email Baru' }}
        </h1>
        <p class="pg-subtitle">
            Tulis teks biasa. Placeholder seperti <code>{periode}</code> diisi otomatis per perusahaan dan per triwulan,
            jadi satu template bisa dipakai untuk Triwulan I sampai IV.
            @if($isAutoTemplate)
                <span class="pg-badge pg-badge-blue">Dipakai pengiriman otomatis</span>
            @endif
        </p>
    </div>

    @if($errors->any())
        <div class="pg-alert pg-alert-error" role="alert">
            @include('bps.pengingat.partials.icon', ['name' => 'warn'])
            <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        </div>
    @endif

    <form method="POST" id="pg-template-form"
          action="{{ $template->exists ? route('bps.pengingat.template.update', $template) : route('bps.pengingat.template.store') }}">
        @csrf
        @if($template->exists) @method('PUT') @endif

        <div class="pg-editor">
            {{-- ── Teks ── --}}
            <div class="pg-panel pg-editor-main">
                <div class="pg-field">
                    <label for="name" class="pg-label">Nama template</label>
                    <input type="text" id="name" name="name" maxlength="100" class="pg-input"
                           value="{{ old('name', $template->name) }}" placeholder="mis. Pengingat standar, Pengingat batas akhir">
                    <div class="pg-hint">Hanya untuk BPS — tidak terlihat oleh responden.</div>
                </div>

                <div class="pg-field">
                    <label for="subject" class="pg-label">Subjek email</label>
                    <input type="text" id="subject" name="subject" maxlength="200" class="pg-input pg-template-input" required
                           value="{{ old('subject', $template->subject) }}">
                </div>

                <div class="pg-field">
                    <label for="body" class="pg-label">Isi email</label>
                    <textarea id="body" name="body" rows="20" maxlength="5000" class="pg-input pg-mono pg-template-input" required>{{ old('body', $template->body) }}</textarea>
                    <div class="pg-hint">
                        Baris kosong = paragraf baru · Enter = baris baru · <code>**teks**</code> = tebal ·
                        <code>{tombol}</code> = posisi tombol "Isi Survei SIBSTR" (bila tidak ada, tombol di akhir).
                        Kotak "pesan tambahan" (bila diisi saat kirim) muncul tepat di atas tombol.
                    </div>
                </div>

                <div class="pg-editor-actions">
                    <button type="submit" class="pg-btn pg-btn-primary">Simpan Template</button>
                    <button type="submit" class="pg-btn pg-btn-ghost" formnovalidate
                            formaction="{{ route('bps.pengingat.template.preview') }}" formtarget="_blank">
                        Pratinjau
                    </button>
                    <span class="pg-hint">Pratinjau dan email uji memakai teks seperti yang sedang diketik, belum perlu disimpan.</span>
                </div>
            </div>

            {{-- ── Placeholder & uji ── --}}
            <div class="pg-editor-side">
                <div class="pg-panel">
                    <h3 class="pg-side-title">Placeholder</h3>
                    <p class="pg-hint">Klik untuk menyisipkan di posisi kursor (subjek atau isi).</p>
                    <ul class="pg-placeholders">
                        @foreach($placeholders as $key => $desc)
                            <li>
                                <button type="button" class="pg-chip" data-insert="{{ '{' . $key . '}' }}">{{ '{' . $key . '}' }}</button>
                                <span class="pg-hint">{{ $desc }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="pg-panel">
                    <h3 class="pg-side-title">Kirim email uji</h3>
                    <p class="pg-hint">Dikirim hanya ke alamat di bawah, dengan subjek bertanda [UJI COBA]. Tidak ke responden.</p>

                    <div class="pg-field">
                        <label for="test_email" class="pg-label">Kirim ke</label>
                        <input type="email" id="test_email" name="test_email" class="pg-input"
                               value="{{ old('test_email', auth()->user()->email) }}">
                    </div>

                    <details class="pg-more">
                        <summary>
                            Ubah contoh data <span class="pg-optional">(opsional)</span>
                            @include('bps.pengingat.partials.icon', ['name' => 'chevron', 'class' => 'pg-chevron'])
                        </summary>
                        <div class="pg-more-body">
                            <div class="pg-field">
                                <label for="test_periode" class="pg-label">Contoh periode</label>
                                <select id="test_periode" name="test_periode" class="pg-input">
                                    @foreach($testPeriods as $p)
                                        @php $value = $p['tahun'] . '-' . $p['triwulan']; @endphp
                                        <option value="{{ $value }}" @selected(old('test_periode', $defaultPeriod) === $value)>
                                            {{ \App\Support\ReminderTemplate::periodLabel($p['tahun'], $p['triwulan']) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="pg-field">
                                <label for="test_status" class="pg-label">Contoh status isian</label>
                                <select id="test_status" name="test_status" class="pg-input">
                                    @foreach($statusLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(old('test_status', 'belum_mulai') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="pg-field">
                                <label for="test_nama" class="pg-label">Contoh nama perusahaan</label>
                                <input type="text" id="test_nama" name="test_nama" maxlength="200" class="pg-input"
                                       value="{{ old('test_nama', 'PT Contoh Industri Batam') }}">
                            </div>
                            <div class="pg-field">
                                <label for="test_penghubung" class="pg-label">Contoh penghubung</label>
                                <input type="text" id="test_penghubung" name="test_penghubung" maxlength="200" class="pg-input"
                                       value="{{ old('test_penghubung', 'Nama Penghubung') }}">
                            </div>
                            <div class="pg-field">
                                <label for="test_pesan" class="pg-label">Contoh pesan tambahan (opsional)</label>
                                <textarea id="test_pesan" name="test_pesan" rows="2" maxlength="1000" class="pg-input">{{ old('test_pesan', $settings->extra_message) }}</textarea>
                            </div>
                        </div>
                    </details>

                    <button type="submit" class="pg-btn pg-btn-primary pg-btn-block" formnovalidate
                            formaction="{{ route('bps.pengingat.template.test') }}">
                        @include('bps.pengingat.partials.icon', ['name' => 'mail'])
                        Kirim Email Uji
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var fields = Array.prototype.slice.call(document.querySelectorAll('.pg-template-input'));
    var target = document.getElementById('body');

    // Remember whichever of subject/body was used last, so a chip goes there.
    fields.forEach(function (f) { f.addEventListener('focus', function () { target = f; }); });

    document.querySelectorAll('.pg-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var text  = chip.getAttribute('data-insert');
            var start = target.selectionStart || 0;
            var end   = target.selectionEnd || 0;
            target.value = target.value.slice(0, start) + text + target.value.slice(end);
            target.focus();
            target.selectionStart = target.selectionEnd = start + text.length;
        });
    });
})();
</script>
@endpush
