<aside id="dashboard-sidebar" class="ud-sidebar w-64 md:w-64 flex-shrink-0" role="navigation" aria-label="Dashboard sidebar">
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
  </div>

  <nav class="flex-1 py-4">
    <a href="{{ route('dashboard') }}"
       class="ud-sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
       aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
      </svg>
      Dashboard
    </a>

    <a href="{{ route('dashboard.apps') }}"
       class="ud-sidebar-item {{ request()->routeIs('dashboard.apps') ? 'active' : '' }}"
       aria-current="{{ request()->routeIs('dashboard.apps') ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
      </svg>
      Aplikasi
    </a>

    @php
      $sibstrActive = request()->routeIs('survey.sibstr.*')
                   || request()->routeIs('dashboard.surveys.sibstr.*');
    @endphp
    <a href="{{ route('survey.sibstr.entry') }}"
       class="ud-sidebar-item {{ $sibstrActive ? 'active' : '' }}"
       aria-current="{{ $sibstrActive ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9 9 0 1020.945 13H11V3.055z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 8.072A9 9 0 1111 3v9h9.488z"></path>
      </svg>
      Survei SIBSTR
    </a>

    <a href="{{ route('dashboard.profile') }}"
       class="ud-sidebar-item {{ request()->routeIs('dashboard.profile') ? 'active' : '' }}"
       aria-current="{{ request()->routeIs('dashboard.profile') ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
      </svg>
      Profil Saya
    </a>

    <a href="{{ route('dashboard.news') }}"
       class="ud-sidebar-item {{ request()->routeIs('dashboard.news') ? 'active' : '' }}"
       aria-current="{{ request()->routeIs('dashboard.news') ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
      </svg>
      Berita
    </a>

    <a href="{{ route('dashboard.videos') }}"
       class="ud-sidebar-item {{ request()->routeIs('dashboard.videos') ? 'active' : '' }}"
       aria-current="{{ request()->routeIs('dashboard.videos') ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 002 2v8a2 2 0 002 2z"></path>
      </svg>
      Video
    </a>

    <a href="{{ route('dashboard.settings') }}"
       class="ud-sidebar-item {{ request()->routeIs('dashboard.settings') ? 'active' : '' }}"
       aria-current="{{ request()->routeIs('dashboard.settings') ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
      </svg>
      Pengaturan
    </a>

    <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4">
      <form method="POST" action="{{ route('logout') }}" id="logout-form">
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