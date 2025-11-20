@extends('layouts.app')

@push('meta')
<!-- MONALISA User Type Meta Tag -->
<meta name="monalisa-user-type" content="{{ auth()->user()->is_kominfo_user ? 'kominfo' : 'bps' }}">
@endpush

@push('styles')
<!-- User Dashboard Centralized Styles (reused for consistency) -->
<link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
<!-- MONALISA Specific Styles -->
<link rel="stylesheet" href="{{ asset('css/monalisa.css') }}">
<style>
  /* Sidebar visibility + stacking context */
  #monalisa-sidebar {
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }

  /* Mobile & Tablet: off-canvas */
  @media (max-width: 1023px) {
    #monalisa-sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 280px;
      z-index: 70; /* Above sticky header */
      transform: translateX(-100%);
    }
    #monalisa-sidebar.active {
      transform: translateX(0);
    }
    #monalisa-sidebar-overlay {
      position: fixed;
      inset: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 60; /* Above header, below sidebar */
      opacity: 0;
      visibility: hidden;
      transition: opacity 300ms ease, visibility 300ms ease;
    }
    #monalisa-sidebar-overlay.active {
      opacity: 1;
      visibility: visible;
    }
  }

  /* Laptop & above: sticky beside content and below header */
  @media (min-width: 1024px) {
    #monalisa-sidebar {
      position: sticky;
      top: 4rem; /* header height (h-16) */
      height: calc(100vh - 4rem);
      z-index: 10; /* below header, above content background */
    }
  }
</style>
@endpush

@section('content')
<div class="ud-container">
  <!-- Mobile Sidebar Overlay -->
  <div id="monalisa-sidebar-overlay" class="lg:hidden" aria-hidden="true"></div>

  <div class="flex">
    @include('partials.monalisa.sidebar')

    <section class="ud-content flex-1 p-4 md:p-6" aria-live="polite">
      @yield('monalisa-content')
    </section>
  </div>
</div>
@endsection

@push('scripts')
<!-- Removed AOS Animation Library -->

<!-- MONALISA Notification System -->
<script src="{{ asset('js/monalisa-notifications.js') }}"></script>

<!-- MONALISA Dashboard JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const sidebar = document.getElementById('monalisa-sidebar');
    const overlay = document.getElementById('monalisa-sidebar-overlay');
    const openButtons = document.querySelectorAll('[data-open-sidebar]');
    const closeButtons = document.querySelectorAll('[data-close-sidebar]');

    function openSidebar() {
        sidebar?.classList.add('active');
        overlay?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar?.classList.remove('active');
        overlay?.classList.remove('active');
        document.body.style.overflow = '';
    }

    openButtons.forEach(btn => btn.addEventListener('click', openSidebar));
    closeButtons.forEach(btn => btn.addEventListener('click', closeSidebar));
    overlay?.addEventListener('click', closeSidebar);

    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar?.classList.contains('active')) {
            closeSidebar();
        }
    });
});
</script>
@endpush

