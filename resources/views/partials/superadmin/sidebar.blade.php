@php
    $subActive     = request()->routeIs('superadmin.submissions.*');
    $usersActive   = request()->routeIs('superadmin.users.*');
    $compActive    = request()->routeIs('superadmin.companies.*');
    $dashActive    = request()->routeIs('superadmin.dashboard');
    $trashActive   = request()->routeIs('superadmin.trash.*');

    // Badge for how many survey submissions are sitting in the recycle bin.
    $trashCount = \App\Models\SurveyResponse::onlyTrashed()->where('survey_type', 'sibstr')->count()
        + \App\Models\UbSurveyResponse::onlyTrashed()->count()
        + \App\Models\ListrikSurveyResponse::onlyTrashed()->count();
@endphp
<aside id="dashboard-sidebar" class="ud-sidebar w-64 md:w-64 flex-shrink-0" role="navigation" aria-label="Superadmin sidebar">
  <div class="p-6 border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img src="{{ asset('img/Logo BPS 1.png') }}" alt="Logo BPS" class="h-9 w-auto" loading="lazy">
      </div>
      <button class="md:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" type="button" data-close-sidebar aria-label="Tutup sidebar">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <div class="mt-4">
      <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 text-xs font-semibold">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        Superadmin
      </span>
      <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200 truncate">{{ Auth::user()->name }}</p>
    </div>
  </div>

  <nav class="flex-1 py-4">
    <a href="{{ route('superadmin.dashboard') }}"
       class="ud-sidebar-item {{ $dashActive ? 'active' : '' }}"
       aria-current="{{ $dashActive ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
      </svg>
      Dashboard
    </a>

    <a href="{{ route('superadmin.submissions.index') }}"
       class="ud-sidebar-item {{ $subActive ? 'active' : '' }}"
       aria-current="{{ $subActive ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      Data Submission SIBSTR
    </a>

    <a href="{{ route('superadmin.users.index') }}"
       class="ud-sidebar-item {{ $usersActive ? 'active' : '' }}"
       aria-current="{{ $usersActive ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
      </svg>
      Manajemen Pengguna
    </a>

    <a href="{{ route('superadmin.companies.index') }}"
       class="ud-sidebar-item {{ $compActive ? 'active' : '' }}"
       aria-current="{{ $compActive ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
      </svg>
      Data Perusahaan
    </a>

    <a href="{{ route('superadmin.trash.index') }}"
       class="ud-sidebar-item {{ $trashActive ? 'active' : '' }}"
       aria-current="{{ $trashActive ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
      </svg>
      Data Terhapus
      @if($trashCount > 0)
      <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-semibold">{{ $trashCount }}</span>
      @endif
    </a>

    <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4">
      <a href="{{ route('home') }}" class="ud-sidebar-item">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        Ke Situs Utama
      </a>
      <form method="POST" action="{{ route('logout') }}" id="logout-form-superadmin">
        @csrf
        <button type="submit" class="ud-sidebar-item w-full text-left text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-900/20" data-no-loading>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          Logout
        </button>
      </form>
    </div>
  </nav>
</aside>
