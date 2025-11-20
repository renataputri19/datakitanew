@php
    $userType = auth()->user()->is_kominfo_user ? 'kominfo' : 'bps';
    $domains = \App\Models\MonalisaDomain::orderBy('order')->get();
    $currentRoute = request()->route()->getName();
    // Use unified routes - both user types now see the same dashboard and charts
    $dashboardRoute = 'monalisa.' . $userType . '.dashboard';
    // $chartsRoute = 'monalisa.' . $userType . '.charts'; // Commented out - Visualisasi page displays too much data
    $indicatorAnalysisRoute = 'monalisa.' . $userType . '.indicator-analysis';
    $notificationsRoute = 'monalisa.' . $userType . '.notifications.index';

    // Get unread notifications count
    $unreadNotificationsCount = \App\Models\MonalisaNotification::getUnreadCountForUser(auth()->id());
@endphp

<aside id="monalisa-sidebar" class="ud-sidebar w-64 md:w-64 flex-shrink-0" role="navigation" aria-label="MONALISA sidebar">
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
    <div class="mt-3">
      <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
        MONALISA
      </div>
      <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
        {{ $userType === 'kominfo' ? 'Self-Assessment' : 'BPS Verification' }}
      </div>
    </div>
  </div>

  <nav class="flex-1 py-4">
    <!-- Dashboard Link - Unified for both user types -->
    <a href="{{ route($dashboardRoute) }}"
       class="ud-sidebar-item {{ request()->routeIs($dashboardRoute) ? 'active' : '' }}"
       aria-current="{{ request()->routeIs($dashboardRoute) ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
      </svg>
      Dashboard
    </a>

    <!-- Charts/Visualisasi Link - Commented out because Visualisasi page displays too much data
    <a href="#"
       class="ud-sidebar-item">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z M16 17l2 2 4-4"></path>
      </svg>
      Visualisasi
    </a>
    -->

    <!-- Indicator Analysis Link - Unified for both user types -->
    <a href="{{ route($indicatorAnalysisRoute) }}"
       class="ud-sidebar-item {{ request()->routeIs($indicatorAnalysisRoute) ? 'active' : '' }}"
       aria-current="{{ request()->routeIs($indicatorAnalysisRoute) ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
      </svg>
      Analisis Indikator
    </a>

    <!-- Notifications Link - Unified for both user types -->
    <a href="{{ route($notificationsRoute) }}"
       class="ud-sidebar-item {{ request()->routeIs($notificationsRoute) ? 'active' : '' }}"
       aria-current="{{ request()->routeIs($notificationsRoute) ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
      </svg>
      <span class="flex-1">Notifikasi</span>
      @if($unreadNotificationsCount > 0)
      <span class="text-xs bg-red-500 text-white px-2 py-0.5 rounded-full font-semibold" id="sidebar-notification-badge">
        {{ $unreadNotificationsCount }}
      </span>
      @endif
    </a>

    <!-- Domain Navigation -->
    <div class="mt-4 mb-2 px-4">
      <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
        Domains
      </div>
    </div>

    @foreach($domains as $domain)
    @php
        $isDomainActive = request()->route('domainId') == $domain->id;
    @endphp
    <a href="{{ route('monalisa.' . $userType . '.domain', $domain->id) }}"
       class="ud-sidebar-item {{ $isDomainActive ? 'active' : '' }}"
       aria-current="{{ $isDomainActive ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <span class="flex-1">Domain {{ $domain->domain_number }}</span>
      <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded-full">
        {{ $domain->weight }}%
      </span>
    </a>
    @endforeach

    @if($userType === 'bps')
    <!-- BPS-specific: Assessment List -->
    <div class="mt-4 mb-2 px-4">
      <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
        Verifikasi
      </div>
    </div>
    
    <a href="{{ route('monalisa.bps.assessments') }}"
       class="ud-sidebar-item {{ request()->routeIs('monalisa.bps.assessments') ? 'active' : '' }}"
       aria-current="{{ request()->routeIs('monalisa.bps.assessments') ? 'page' : 'false' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
      </svg>
      Semua Assessment
    </a>
    @endif

    <!-- Divider and Logout -->
    <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4">
      <a href="{{ route('dashboard') }}"
         class="ud-sidebar-item">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali ke Dashboard
      </a>

      <form method="POST" action="{{ route('logout') }}" id="logout-form-monalisa">
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

