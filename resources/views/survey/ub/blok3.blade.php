@extends('layouts.user-dashboard')

@section('title', 'Survei UB — Blok III: Keterangan Pemberi Jawaban')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<style>
.ub-card{background:#fff;border-radius:1rem;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:1.75rem 2rem;margin-bottom:1.25rem;}
.dark .ub-card{background:#1f2937;border-color:#374151;}
.ub-section-title{font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#3b82f6;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid #dbeafe;}
.dark .ub-section-title{color:#93c5fd;border-color:#1e3a5f;}
.ub-label{display:block;font-size:.8125rem;font-weight:600;color:#374151;margin-bottom:.35rem;}
.dark .ub-label{color:#d1d5db;}
.ub-input{width:100%;border:1px solid #d1d5db;border-radius:.625rem;padding:.55rem .85rem;font-size:.875rem;color:#111827;background:#fff;transition:border-color .15s;}
.ub-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15);}
.dark .ub-input{background:#111827;border-color:#4b5563;color:#f9fafb;}
.ub-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.ub-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:2rem;}
@media(max-width:768px){.ub-grid-2,.ub-grid-3{grid-template-columns:1fr;}}
.ub-stepper{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.ub-step.done{background:#dcfce7;color:#15803d;border:1.5px solid #bbf7d0;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.ub-step.active{background:#dbeafe;color:#1d4ed8;border:1.5px solid #bfdbfe;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.ub-step.pending{background:#f3f4f6;color:#9ca3af;border:1.5px solid #e5e7eb;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.dark .ub-step.done{background:#14532d;color:#86efac;border-color:#166534;}
.dark .ub-step.active{background:#1e3a5f;color:#93c5fd;border-color:#1d4ed8;}
.dark .ub-step.pending{background:#374151;color:#6b7280;border-color:#4b5563;}
.person-col-header{font-size:.75rem;font-weight:700;text-align:center;padding:.5rem;background:#f8fafc;border-radius:.5rem .5rem 0 0;border:1px solid #e5e7eb;margin-bottom:.5rem;color:#374151;}
.dark .person-col-header{background:#1e293b;border-color:#374151;color:#d1d5db;}
.person-col{border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem;background:#fafafa;}
.dark .person-col{background:#111827;border-color:#374151;}
.ub-required{color:#ef4444;margin-left:.2rem;}
.ub-err-msg{font-size:.73rem;color:#ef4444;margin-top:.3rem;}
.ub-input.input-error{border-color:#ef4444 !important;}
</style>
@endpush

@section('dashboard-content')
<div class="lg:hidden mb-4">
  <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400" type="button" data-open-sidebar>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>Menu
  </button>
</div>

<div class="ud-page-header">
  <div class="ud-page-header-content">
    <h1 class="ud-page-title">Blok III — Keterangan Pemberi Jawaban</h1>
    <p class="ud-page-description">SE2026-L.UB · Identitas PPL, PML, dan Responden — Penyelesaian Survei</p>
  </div>
  <a href="{{ route('survey.ub.blok2') }}" class="ud-btn ud-btn-secondary text-sm hidden sm:inline-flex shrink-0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Kembali
  </a>
</div>

<div class="flex gap-5 items-start mt-4">
@include('survey.ub.partials.sidebar')
<div class="flex-1 min-w-0">

{{-- Session error from server-side redirect (non-AJAX fallback) --}}
@if(session('error'))
<div class="mb-4 px-5 py-4 rounded-2xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-sm text-red-700 dark:text-red-300 flex items-start gap-3">
  <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
  <span>{{ session('error') }}</span>
</div>
@endif

{{-- Summary check --}}
<div class="bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-700 rounded-2xl px-6 py-4 text-sm text-green-800 dark:text-green-300 mb-5">
  <div class="flex items-center gap-2 font-semibold mb-1">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    Semua blok data perusahaan telah dilengkapi!
  </div>
  <p>Lengkapi identitas petugas dan responden di bawah ini, lalu klik <strong>Selesaikan Survei</strong> untuk mengirimkan data.</p>
</div>

<div id="autosave-status" class="autosave-status hidden" style="margin-bottom:.75rem;"><span id="autosave-text"></span></div>

<form id="survey-form" method="POST" action="{{ ($editMode ?? false) ? route('survey.ub.edit.blok3.finish') : route('survey.ub.blok3.finish') }}" novalidate>
@csrf

<div class="ub-card">
  <p class="ub-section-title">BLOK III : KETERANGAN PEMBERI JAWABAN</p>
  <div class="ub-grid-3">

@php
$readonlyCls = 'ub-input bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed select-none';
$todayVal    = $today ?? now()->format('Y-m-d');
@endphp

    {{-- PPL — read-only, filled by BPS --}}
    <div class="person-col">
      <p class="person-col-header">PPL (Petugas Pencacah Lapangan)</p>
      <p class="text-[10px] text-gray-400 dark:text-gray-500 mb-2 italic">Diisi oleh petugas BPS</p>
      <div class="space-y-3">
        <div>
          <label class="ub-label text-gray-400 dark:text-gray-500">1. Nama</label>
          <input class="{{ $readonlyCls }}" value="{{ $response->ppl_nama ?? '' }}" readonly tabindex="-1" placeholder="—">
        </div>
        <div>
          <label class="ub-label text-gray-400 dark:text-gray-500">2. NIP/NMS</label>
          <input class="{{ $readonlyCls }}" value="{{ $response->ppl_nip ?? '' }}" readonly tabindex="-1" placeholder="—">
        </div>
        <div>
          <label class="ub-label text-gray-400 dark:text-gray-500">3. Nomor HP/Telepon</label>
          <input class="{{ $readonlyCls }}" value="{{ $response->ppl_telepon ?? '' }}" readonly tabindex="-1" placeholder="—">
        </div>
        <div>
          <label class="ub-label text-gray-400 dark:text-gray-500">4. E-mail</label>
          <input class="{{ $readonlyCls }}" value="{{ $response->ppl_email ?? '' }}" readonly tabindex="-1" placeholder="—">
        </div>
        <div>
          <label class="ub-label text-gray-400 dark:text-gray-500">5. Tanggal Pelaksanaan</label>
          <input name="ppl_tanggal" type="date" class="{{ $readonlyCls }}" value="{{ old('ppl_tanggal', $response->ppl_tanggal?->format('Y-m-d') ?? $todayVal) }}" readonly tabindex="-1">
        </div>
      </div>
    </div>

    {{-- PML — read-only, filled by BPS --}}
    <div class="person-col">
      <p class="person-col-header">PML (Pengawas Mula Lapangan)</p>
      <p class="text-[10px] text-gray-400 dark:text-gray-500 mb-2 italic">Diisi oleh petugas BPS</p>
      <div class="space-y-3">
        <div>
          <label class="ub-label text-gray-400 dark:text-gray-500">1. Nama</label>
          <input class="{{ $readonlyCls }}" value="{{ $response->pml_nama ?? '' }}" readonly tabindex="-1" placeholder="—">
        </div>
        <div>
          <label class="ub-label text-gray-400 dark:text-gray-500">2. NIP/NMS</label>
          <input class="{{ $readonlyCls }}" value="{{ $response->pml_nip ?? '' }}" readonly tabindex="-1" placeholder="—">
        </div>
        <div>
          <label class="ub-label text-gray-400 dark:text-gray-500">3. Nomor HP/Telepon</label>
          <input class="{{ $readonlyCls }}" value="{{ $response->pml_telepon ?? '' }}" readonly tabindex="-1" placeholder="—">
        </div>
        <div>
          <label class="ub-label text-gray-400 dark:text-gray-500">4. E-mail</label>
          <input class="{{ $readonlyCls }}" value="{{ $response->pml_email ?? '' }}" readonly tabindex="-1" placeholder="—">
        </div>
        <div>
          <label class="ub-label text-gray-400 dark:text-gray-500">5. Tanggal Pelaksanaan</label>
          <input name="pml_tanggal" type="date" class="{{ $readonlyCls }}" value="{{ old('pml_tanggal', $response->pml_tanggal?->format('Y-m-d') ?? $todayVal) }}" readonly tabindex="-1">
        </div>
      </div>
    </div>

    {{-- Responden — name & email auto-filled from profile, NIP & telepon required --}}
    <div class="person-col">
      <p class="person-col-header">Responden</p>
      <div class="space-y-3">
        <div>
          <label class="ub-label">1. Nama</label>
          <input class="{{ $readonlyCls }}" value="{{ old('resp_nama', $response->resp_nama ?? $user->name ?? '') }}" readonly tabindex="-1">
          <input type="hidden" name="resp_nama" value="{{ old('resp_nama', $response->resp_nama ?? $user->name ?? '') }}">
        </div>
        <div>
          <label class="ub-label">2. NIP/NMS <span class="ub-required">*</span></label>
          <input name="resp_nip" id="resp_nip" class="ub-input" value="{{ old('resp_nip',$response->resp_nip) }}" placeholder="NIP atau jabatan">
          <div class="ub-err-msg" data-field="resp_nip"></div>
        </div>
        <div>
          <label class="ub-label">3. Nomor HP/Telepon <span class="ub-required">*</span></label>
          <input name="resp_telepon" id="resp_telepon" class="ub-input" value="{{ old('resp_telepon',$response->resp_telepon) }}" placeholder="08xx-xxxx-xxxx">
          <div class="ub-err-msg" data-field="resp_telepon"></div>
        </div>
        <div>
          <label class="ub-label">4. E-mail</label>
          <input class="{{ $readonlyCls }}" value="{{ old('resp_email', $response->resp_email ?? $user->email ?? '') }}" readonly tabindex="-1">
          <input type="hidden" name="resp_email" value="{{ old('resp_email', $response->resp_email ?? $user->email ?? '') }}">
        </div>
        <div>
          <label class="ub-label">5. Tanggal Pelaksanaan</label>
          <input name="resp_tanggal" type="date" class="ub-input" value="{{ old('resp_tanggal', $response->resp_tanggal?->format('Y-m-d') ?? $todayVal) }}">
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Confirmation box --}}
<div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-700 rounded-2xl px-6 py-5 mb-5">
  <div class="flex items-start gap-3">
    <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <div>
      <p class="font-semibold text-amber-800 dark:text-amber-300 text-sm mb-1">Konfirmasi Penyelesaian Survei</p>
      <p class="text-xs text-amber-700 dark:text-amber-400">
        Dengan menyelesaikan survei ini, Anda menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan.
        Kerahasiaan data dijamin oleh <strong>UU No. 16 Tahun 1997 Pasal 21</strong>.
      </p>
      <label class="flex items-center gap-2 mt-2 cursor-pointer">
        <input type="checkbox" id="confirmCheck" class="w-4 h-4 accent-amber-600">
        <span class="text-xs font-semibold text-amber-800 dark:text-amber-300">Saya menyatakan bahwa data yang diisikan adalah benar</span>
      </label>
    </div>
  </div>
</div>

<div class="flex items-center justify-between mt-2 mb-8">
  <a href="{{ route('survey.ub.blok2') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Kembali ke Blok II
  </a>
  <button type="submit" id="finishBtn" disabled
    class="inline-flex items-center gap-2 px-7 py-3 rounded-xl bg-green-600 hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold shadow-md transition text-sm">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    Selesaikan Survei UB
  </button>
</div>
</form>
</div>
</div>

{{-- Cross-block validation modal (mirrors SIBSTR Blok 6 modal) --}}
<div id="survey-modal-overlay"
     style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.45);
            backdrop-filter:blur(2px);align-items:center;justify-content:center;padding:1rem;">
    <div id="survey-modal-box"
         style="background:#fff;border-radius:1rem;padding:2rem 2rem 1.5rem;max-width:28rem;width:100%;
                box-shadow:0 20px 60px rgba(0,0,0,0.25);position:relative;animation:modalIn .18s ease-out;">
        <div style="display:flex;align-items:flex-start;gap:0.875rem;margin-bottom:1.25rem;">
            <span id="survey-modal-icon" style="font-size:1.75rem;line-height:1;flex-shrink:0;margin-top:0.1rem;"></span>
            <div style="flex:1;min-width:0;">
                <p id="survey-modal-title"
                   style="font-size:1rem;font-weight:700;color:#1f2937;margin:0 0 0.35rem;"></p>
                <p id="survey-modal-body"
                   style="font-size:0.875rem;color:#4b5563;margin:0;line-height:1.5;white-space:pre-line;"></p>
            </div>
        </div>
        <div id="survey-modal-progress-wrap" style="display:none;margin-bottom:1rem;">
            <div style="height:4px;background:#e5e7eb;border-radius:99px;overflow:hidden;">
                <div id="survey-modal-progress-bar"
                     style="height:100%;width:100%;border-radius:99px;transition:width linear;"></div>
            </div>
            <p id="survey-modal-countdown"
               style="font-size:0.75rem;color:#9ca3af;margin:0.4rem 0 0;text-align:right;"></p>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:0.5rem;">
            <button id="survey-modal-cancel"
                    style="display:none;padding:0.5rem 1rem;border-radius:0.5rem;border:1px solid #d1d5db;
                           background:#fff;color:#374151;font-size:0.875rem;font-weight:600;cursor:pointer;">
                Batal
            </button>
            <button id="survey-modal-confirm"
                    style="padding:0.5rem 1.25rem;border-radius:0.5rem;border:none;
                           font-size:0.875rem;font-weight:700;cursor:pointer;color:#fff;transition:opacity .15s;">
                OK
            </button>
        </div>
    </div>
</div>
<style>
@keyframes modalIn {
    from { opacity:0; transform:scale(.94) translateY(8px); }
    to   { opacity:1; transform:scale(1)  translateY(0);    }
}
</style>

@push('scripts')
<script>
window.surveyRoutes = {
    autoSave: '{{ route("survey.ub.blok3.autosave") }}',
    saveAll:  '{{ ($editMode ?? false) ? route("survey.ub.edit.blok3.finish") : route("survey.ub.blok3.finish") }}',
    status:   '{{ route("survey.ub.blok3.status") }}',
    nextBlok: '{{ route("survey.ub.entry") }}',
    showGuidanceNearSubmit: false
};

function showSurveyModal(opts) {
    const overlay  = document.getElementById('survey-modal-overlay');
    const icon     = document.getElementById('survey-modal-icon');
    const title    = document.getElementById('survey-modal-title');
    const body     = document.getElementById('survey-modal-body');
    const confirm  = document.getElementById('survey-modal-confirm');
    const cancel   = document.getElementById('survey-modal-cancel');
    const progWrap = document.getElementById('survey-modal-progress-wrap');
    const progBar  = document.getElementById('survey-modal-progress-bar');
    const countdown= document.getElementById('survey-modal-countdown');
    if (!overlay) return;

    const palette = {
        warning : { icon: '⚠️',  bg: '#fbbf24' },
        success : { icon: '✅',  bg: '#10b981' },
        error   : { icon: '❌',  bg: '#ef4444' },
    };
    const p = palette[opts.type] || palette.error;

    icon.textContent    = p.icon;
    title.textContent   = opts.title  || '';
    body.textContent    = opts.body   || '';
    confirm.textContent = opts.confirmText || 'OK';
    confirm.style.background = p.bg;

    if (opts.cancelText) {
        cancel.textContent = opts.cancelText;
        cancel.style.display = '';
    } else {
        cancel.style.display = 'none';
    }

    let timer = null;
    const delay = opts.redirectDelay ?? 3000;
    if (opts.redirectUrl && !opts.onConfirm) {
        progWrap.style.display = '';
        progBar.style.background = p.bg;
        progBar.style.transitionDuration = delay + 'ms';
        countdown.textContent = Math.ceil(delay / 1000) + ' detik…';
        void progBar.offsetWidth;
        progBar.style.width = '0%';
        let remaining = delay;
        timer = setInterval(() => {
            remaining -= 1000;
            if (remaining <= 0) { clearInterval(timer); close(); window.location.href = opts.redirectUrl; }
            else countdown.textContent = Math.ceil(remaining / 1000) + ' detik…';
        }, 1000);
    } else {
        progWrap.style.display = 'none';
    }

    function close() { clearInterval(timer); overlay.style.display = 'none'; }

    confirm.onclick = () => { close(); if (opts.onConfirm) opts.onConfirm(); else if (opts.redirectUrl) window.location.href = opts.redirectUrl; };
    cancel.onclick  = () => { close(); if (opts.onCancel) opts.onCancel(); };
    overlay.style.display = 'flex';
}

window.showCrossBlockModal = function(result) {
    const btn      = document.getElementById('finishBtn');
    const origHTML = btn ? btn.getAttribute('data-orig-html') : null;
    showSurveyModal({
        type        : 'warning',
        title       : 'Isian Sebelumnya Belum Lengkap',
        body        : (result.message || 'Terdapat isian yang belum lengkap.') +
                      '\n\nKlik \u201cLengkapi Sekarang\u201d untuk menuju ke halaman yang perlu dilengkapi.',
        confirmText : 'Lengkapi Sekarang',
        cancelText  : 'Nanti',
        redirectUrl : result.redirect,
        redirectDelay: 5000,
        onCancel    : function() {
            if (btn) {
                btn.disabled = false;
                if (origHTML) btn.innerHTML = origHTML;
            }
        },
    });
};

(function(){
  const check = document.getElementById('confirmCheck');
  const btn   = document.getElementById('finishBtn');
  if (btn) btn.setAttribute('data-orig-html', btn.innerHTML);
  check.addEventListener('change', () => { btn.disabled = !check.checked; });

  document.getElementById('survey-form').addEventListener('submit', function(e){
    e.preventDefault();
    const form         = this;

    // Helper: show/clear inline error messages like blok1d
    function setErr(field, msg) {
      const el = form.querySelector('[data-field="' + field + '"]');
      const inp = form.querySelector('[name="' + field + '"]');
      if (el)  el.textContent = msg;
      if (inp) inp.classList.toggle('input-error', !!msg);
    }
    function clearErrs() { ['resp_nip','resp_telepon'].forEach(f => setErr(f,'')); }

    // Client-side validation — inline errors, no alert()
    clearErrs();
    const nipField     = form.querySelector('[name="resp_nip"]');
    const teleponField = form.querySelector('[name="resp_telepon"]');
    let hasError = false;
    if (!nipField?.value.trim()) {
      setErr('resp_nip', 'NIP/NMS responden wajib diisi.');
      nipField?.focus();
      hasError = true;
    }
    if (!teleponField?.value.trim()) {
      setErr('resp_telepon', 'Nomor HP/Telepon responden wajib diisi.');
      if (!hasError) teleponField?.focus();
      hasError = true;
    }
    if (hasError) return;

    // Clear errors on input
    ['resp_nip','resp_telepon'].forEach(function(f) {
      form.querySelector('[name="'+f+'"]')?.addEventListener('input', function() { setErr(f,''); }, {once:true});
    });
    const origHTML = btn.getAttribute('data-orig-html');
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Menyimpan…';

    fetch(form.action, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form),
    })
    .then(function(res) { return res.json().then(function(data){ return { ok: res.ok, status: res.status, data: data }; }); })
    .then(function(r) {
      if (r.ok && r.data.success) {
        window.location.href = r.data.redirect || '{{ route("survey.ub.entry") }}';
        return;
      }
      // Cross-block validation failure
      if (r.status === 422 && r.data.redirect) {
        if (typeof window.showCrossBlockModal === 'function') {
          window.showCrossBlockModal(r.data);
        } else {
          alert(r.data.message || 'Ada blok yang belum lengkap.');
          window.location.href = r.data.redirect;
        }
        return;
      }
      // Other error
      btn.disabled = false;
      btn.innerHTML = origHTML;
      alert(r.data.message || 'Terjadi kesalahan. Silakan coba lagi.');
    })
    .catch(function() {
      btn.disabled = false;
      btn.innerHTML = origHTML;
      alert('Gagal menghubungi server. Periksa koneksi internet Anda.');
    });
  });
})();
</script>
<script src="{{ asset('js/survey.js') }}"></script>
@endpush
@endsection
