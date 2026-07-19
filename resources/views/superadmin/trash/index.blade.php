@extends('layouts.superadmin')

@section('title', 'Data Terhapus - Superadmin - DataKita')
@section('description', 'Pulihkan data survei yang telah dihapus oleh BPS')

@push('styles')
<style>
/* Self-contained so the page does not depend on a fresh Tailwind build. */
.tr-tabs { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.tr-tab {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .55rem 1rem; border-radius: .625rem;
    border: 1px solid #e5e7eb; background: #fff; color: #374151;
    font-size: .875rem; font-weight: 600; text-decoration: none;
    transition: background .15s, border-color .15s;
}
.tr-tab:hover { background: #f9fafb; }
.dark .tr-tab { background: #1f2937; border-color: #374151; color: #d1d5db; }
.dark .tr-tab:hover { background: #111827; }
.tr-tab.active { background: #2563eb; border-color: #2563eb; color: #fff; }
.dark .tr-tab.active { background: #2563eb; border-color: #2563eb; color: #fff; }
.tr-tab-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 1.5rem; padding: 0 .4rem; height: 1.375rem;
    border-radius: 9999px; background: #f3f4f6; color: #4b5563;
    font-size: .75rem; font-weight: 700;
}
.dark .tr-tab-count { background: #374151; color: #e5e7eb; }
.tr-tab.active .tr-tab-count { background: rgba(255,255,255,.25); color: #fff; }

.tr-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: .75rem; overflow: hidden;
}
.dark .tr-card { background: #1f2937; border-color: #374151; }

.tr-table { width: 100%; border-collapse: collapse; }
.tr-table thead th {
    background: #f9fafb; padding: .75rem 1rem; text-align: left;
    font-size: .75rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: .05em; color: #6b7280; border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}
.dark .tr-table thead th { background: #111827; color: #9ca3af; border-color: #374151; }
.tr-table tbody td { padding: .875rem 1rem; border-bottom: 1px solid #e5e7eb; color: #374151; font-size: .875rem; }
.dark .tr-table tbody td { border-color: #374151; color: #d1d5db; }
.tr-table tbody tr:hover { background: #f9fafb; }
.dark .tr-table tbody tr:hover { background: #111827; }
.tr-name { font-weight: 600; color: #111827; }
.dark .tr-name { color: #f9fafb; }
.tr-sub { font-size: .75rem; color: #6b7280; margin-top: .1rem; }
.dark .tr-sub { color: #9ca3af; }

.tr-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .2rem .6rem; border-radius: 9999px;
    font-size: .7rem; font-weight: 600; white-space: nowrap;
}
.tr-pill-ok { background: #d1fae5; color: #065f46; }
.dark .tr-pill-ok { background: #064e3b; color: #6ee7b7; }
.tr-pill-blocked { background: #fee2e2; color: #991b1b; }
.dark .tr-pill-blocked { background: #7f1d1d; color: #fca5a5; }

.tr-btn-restore {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .4rem .85rem; border-radius: .5rem;
    background: #16a34a; color: #fff; border: none;
    font-size: .78rem; font-weight: 600; cursor: pointer; font-family: inherit;
}
.tr-btn-restore:hover { background: #15803d; }
.tr-btn-restore:disabled { background: #d1d5db; color: #6b7280; cursor: not-allowed; }
.dark .tr-btn-restore:disabled { background: #374151; color: #9ca3af; }

.tr-btn-all {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .6rem 1.15rem; border-radius: .625rem;
    background: #16a34a; color: #fff; border: none;
    font-size: .875rem; font-weight: 600; cursor: pointer; font-family: inherit;
}
.tr-btn-all:hover { background: #15803d; }
.tr-btn-all:disabled { background: #d1d5db; color: #6b7280; cursor: not-allowed; }
.dark .tr-btn-all:disabled { background: #374151; color: #9ca3af; }

.tr-note {
    display: flex; gap: .75rem; align-items: flex-start;
    padding: .875rem 1rem; border-radius: .625rem;
    background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;
    font-size: .8rem; line-height: 1.5; margin-bottom: 1.5rem;
}
.dark .tr-note { background: rgba(30,58,138,.25); border-color: #1e40af; color: #bfdbfe; }
.tr-warn {
    padding: .75rem 1rem; border-radius: .625rem;
    background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
    font-size: .8rem; margin-bottom: 1.25rem;
}
.dark .tr-warn { background: rgba(120,53,15,.25); border-color: #b45309; color: #fcd34d; }

.tr-empty { text-align: center; padding: 3.5rem 2rem; }
.tr-search { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
.tr-input {
    flex: 1; min-width: 14rem; padding: .55rem .9rem;
    border: 1px solid #d1d5db; border-radius: .5rem;
    background: #fff; color: #111827; font-size: .875rem; font-family: inherit;
}
.dark .tr-input { background: #111827; border-color: #4b5563; color: #f3f4f6; }
.tr-btn-search {
    padding: .55rem 1.1rem; border-radius: .5rem; border: none;
    background: #2563eb; color: #fff; font-size: .875rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
}
.tr-btn-search:hover { background: #1d4ed8; }
.tr-btn-clear {
    padding: .55rem 1.1rem; border-radius: .5rem;
    border: 1px solid #d1d5db; background: #fff; color: #374151;
    font-size: .875rem; font-weight: 500; text-decoration: none;
    display: inline-flex; align-items: center;
}
.dark .tr-btn-clear { background: #1f2937; border-color: #4b5563; color: #d1d5db; }

.tr-pagination { padding: 1rem 1.25rem; border-top: 1px solid #e5e7eb; background: #f9fafb; }
.dark .tr-pagination { border-color: #374151; background: #111827; }

/* Confirmation modal */
.tr-modal-overlay {
    position: fixed; inset: 0; background: rgba(17,24,39,.6);
    display: flex; align-items: center; justify-content: center;
    padding: 1rem; z-index: 9999;
}
.tr-modal-overlay[hidden] { display: none; }
.tr-modal {
    background: #fff; border-radius: .75rem; max-width: 30rem; width: 100%;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,.25); overflow: hidden;
}
.dark .tr-modal { background: #1f2937; }
.tr-modal-body { padding: 1.5rem; display: flex; gap: 1rem; align-items: flex-start; }
.tr-modal-icon {
    width: 2.75rem; height: 2.75rem; flex-shrink: 0; border-radius: 9999px;
    background: #d1fae5; color: #16a34a;
    display: flex; align-items: center; justify-content: center;
}
.dark .tr-modal-icon { background: #064e3b; color: #6ee7b7; }
.tr-modal-title { font-size: 1.05rem; font-weight: 700; color: #111827; margin-bottom: .35rem; }
.dark .tr-modal-title { color: #f9fafb; }
.tr-modal-text { font-size: .875rem; color: #4b5563; line-height: 1.5; }
.dark .tr-modal-text { color: #d1d5db; }
.tr-modal-target {
    margin-top: .75rem; padding: .625rem .75rem; background: #f9fafb;
    border: 1px solid #e5e7eb; border-radius: .5rem;
    font-size: .875rem; font-weight: 600; color: #111827; word-break: break-word;
}
.dark .tr-modal-target { background: #111827; border-color: #374151; color: #f9fafb; }
.tr-modal-actions {
    display: flex; justify-content: flex-end; gap: .5rem;
    padding: 1rem 1.5rem; background: #f9fafb; border-top: 1px solid #e5e7eb;
}
.dark .tr-modal-actions { background: #111827; border-color: #374151; }
.tr-btn-cancel {
    padding: .5rem 1rem; border-radius: .5rem; background: #fff;
    color: #374151; border: 1px solid #d1d5db;
    font-size: .875rem; font-weight: 500; cursor: pointer; font-family: inherit;
}
.dark .tr-btn-cancel { background: #374151; color: #e5e7eb; border-color: #4b5563; }
.tr-btn-confirm {
    padding: .5rem 1rem; border-radius: .5rem; background: #16a34a;
    color: #fff; border: none; font-size: .875rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
}
.tr-btn-confirm:hover { background: #15803d; }
</style>
@endpush

@section('dashboard-content')
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-8">
        <nav class="text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('superadmin.dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Dashboard Superadmin</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700 dark:text-gray-300">Data Terhapus</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Data Terhapus</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            Data survei yang dihapus BPS tidak dibuang permanen — data disimpan di sini dan dapat dipulihkan kembali.
        </p>
    </div>

    <div class="tr-note">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            Setiap survei disimpan terpisah — memulihkan data <strong>Survei Listrik</strong> tidak memengaruhi data UB atau SIBSTR milik responden yang sama.
            Data yang dipulihkan akan langsung muncul kembali di halaman BPS dan dashboard statistik.
        </div>
    </div>

    {{-- Survey tabs --}}
    <div class="tr-tabs">
        @foreach($types as $key => $label)
        <a href="{{ route('superadmin.trash.index', ['type' => $key]) }}"
           class="tr-tab {{ $type === $key ? 'active' : '' }}">
            {{ $label }}
            <span class="tr-tab-count">{{ $counts[$key] }}</span>
        </a>
        @endforeach
    </div>

    @if($blockedTotal > 0)
    <div class="tr-warn">
        <strong>{{ $blockedTotal }} data tidak dapat dipulihkan</strong> karena respondennya sudah mengisi ulang survei untuk periode yang sama.
        Satu periode hanya boleh punya satu data aktif. Untuk memulihkan versi lama, hapus dulu data aktif periode tersebut dari halaman BPS.
    </div>
    @endif

    {{-- Search + restore all --}}
    <div class="tr-search">
        <form method="GET" action="{{ route('superadmin.trash.index') }}" style="display:flex; gap:.5rem; flex:1; flex-wrap:wrap;">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="text" name="search" value="{{ $search }}" class="tr-input"
                   placeholder="Cari nama perusahaan, nama atau email pengguna...">
            <button type="submit" class="tr-btn-search">Cari</button>
            @if($search)
            <a href="{{ route('superadmin.trash.index', ['type' => $type]) }}" class="tr-btn-clear">Reset</a>
            @endif
        </form>

        <form method="POST" action="{{ route('superadmin.trash.restore-all', ['type' => $type]) }}"
              id="form-restore-all" style="margin:0;">
            @csrf
            <button type="button" class="tr-btn-all"
                    {{ $counts[$type] === 0 ? 'disabled' : '' }}
                    onclick="trConfirmRestoreAll(@js($types[$type]), {{ $counts[$type] - $blockedTotal }})">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Pulihkan Semua
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="tr-card">
        @if($rows->count() > 0)
        <div style="overflow-x:auto;">
            <table class="tr-table">
                <thead>
                    <tr>
                        <th>Perusahaan</th>
                        <th>Pengguna</th>
                        <th>Periode</th>
                        <th>Dihapus Pada</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                    <tr>
                        <td>
                            <div class="tr-name">{{ $row->nama_perusahaan ?: '(tanpa nama perusahaan)' }}</div>
                            @if($row->nama_komersial && $row->nama_komersial !== $row->nama_perusahaan)
                            <div class="tr-sub">{{ $row->nama_komersial }}</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $row->user->name ?? '—' }}</div>
                            <div class="tr-sub">{{ $row->user->email ?? '' }}</div>
                        </td>
                        <td>{{ $row->period_label }}</td>
                        <td>
                            <div>{{ $row->deleted_at?->setTimezone('Asia/Jakarta')->format('d M Y') }}</div>
                            <div class="tr-sub">{{ $row->deleted_at?->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</div>
                        </td>
                        <td>
                            @if($row->can_restore)
                            <span class="tr-pill tr-pill-ok">Dapat dipulihkan</span>
                            @else
                            <span class="tr-pill tr-pill-blocked" title="Responden sudah mengisi ulang periode ini">
                                Terhalang data baru
                            </span>
                            @endif
                        </td>
                        <td>
                            <form method="POST"
                                  action="{{ route('superadmin.trash.restore', ['type' => $type, 'id' => $row->id]) }}"
                                  class="tr-restore-form" style="margin:0;">
                                @csrf
                                <button type="button" class="tr-btn-restore"
                                        {{ $row->can_restore ? '' : 'disabled' }}
                                        onclick="trConfirmRestore(this, @js($row->nama_perusahaan ?: ($row->user->name ?? 'Responden')), @js($row->period_label))">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Pulihkan
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="tr-pagination">{{ $rows->links() }}</div>
        @else
        <div class="tr-empty">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak Ada Data Terhapus</h3>
            <p class="text-gray-600 dark:text-gray-400">
                @if($search)
                    Tidak ada hasil yang cocok dengan pencarian.
                @else
                    Belum ada data {{ $types[$type] }} yang dihapus oleh BPS.
                @endif
            </p>
        </div>
        @endif
    </div>
</div>

{{-- Restore confirmation --}}
<div id="tr-modal" class="tr-modal-overlay" hidden role="dialog" aria-modal="true" aria-labelledby="tr-modal-title">
    <div class="tr-modal">
        <div class="tr-modal-body">
            <div class="tr-modal-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <div style="flex:1; min-width:0;">
                <h3 class="tr-modal-title" id="tr-modal-title">Pulihkan Data?</h3>
                <p class="tr-modal-text" id="tr-modal-text"></p>
                <div class="tr-modal-target" id="tr-modal-target"></div>
            </div>
        </div>
        <div class="tr-modal-actions">
            <button type="button" class="tr-btn-cancel" onclick="trCloseModal()">Batal</button>
            <button type="button" class="tr-btn-confirm" id="tr-modal-confirm">Ya, Pulihkan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var modal   = document.getElementById('tr-modal');
    var confirm = document.getElementById('tr-modal-confirm');
    var pending = null;

    function open(title, text, target, submitFn) {
        document.getElementById('tr-modal-title').textContent  = title;
        document.getElementById('tr-modal-text').textContent    = text;
        document.getElementById('tr-modal-target').textContent  = target;
        pending = submitFn;
        modal.hidden = false;
    }

    window.trConfirmRestore = function (btn, name, period) {
        var form = btn.closest('form');
        open('Pulihkan Data?',
             'Data ini akan dikembalikan dan muncul lagi di halaman BPS serta dashboard statistik.',
             name + ' — ' + period,
             function () { form.submit(); });
    };

    window.trConfirmRestoreAll = function (label, restorable) {
        var form = document.getElementById('form-restore-all');
        open('Pulihkan Semua Data?',
             restorable > 0
                ? 'Semua data ' + label + ' yang dapat dipulihkan akan dikembalikan sekaligus. Data yang terhalang akan dilewati.'
                : 'Tidak ada data yang dapat dipulihkan saat ini — semuanya terhalang oleh data baru.',
             restorable + ' data siap dipulihkan',
             function () { form.submit(); });
    };

    window.trCloseModal = function () { modal.hidden = true; pending = null; };

    confirm.addEventListener('click', function () {
        if (pending) { confirm.disabled = true; pending(); }
    });
    modal.addEventListener('click', function (e) { if (e.target === modal) trCloseModal(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) trCloseModal();
    });
})();
</script>
@endpush
