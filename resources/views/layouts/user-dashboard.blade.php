@extends('layouts.app')

@push('styles')
<!-- User Dashboard Centralized Styles -->
<link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}?v={{ filemtime(public_path('css/user-dashboard.css')) }}">
<style>
  /* Sidebar visibility + stacking context */
  #dashboard-sidebar {
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }

  /* Mobile & Tablet: off-canvas */
  @media (max-width: 1023px) {
    #dashboard-sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 280px;
      z-index: 70; /* Above sticky header */
      transform: translateX(-100%);
    }
    #dashboard-sidebar.active {
      transform: translateX(0);
    }
    #dashboard-sidebar-overlay {
      position: fixed;
      inset: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 60; /* Above header, below sidebar */
      opacity: 0;
      visibility: hidden;
      transition: opacity 300ms ease, visibility 300ms ease;
    }
    #dashboard-sidebar-overlay.active {
      opacity: 1;
      visibility: visible;
    }
  }

  /* Laptop & above: sticky beside content and below header */
  @media (min-width: 1024px) {
    #dashboard-sidebar {
      position: sticky;
      top: 4rem; /* header height (h-16) */
      height: calc(100vh - 4rem);
      z-index: 10; /* below header, above content background */
      transition: width 300ms ease, opacity 200ms ease;
    }

    /* Collapsed: sidebar slides away so the content area gets full width */
    html.ud-sidebar-collapsed #dashboard-sidebar {
      width: 0 !important;
      opacity: 0;
      overflow: hidden;
      border-right-width: 0;
      pointer-events: none;
    }
  }

  /* Reveal the header burger only where a sidebar exists to collapse.
     This stylesheet is loaded solely by the dashboard layout, and below 1024px
     the off-canvas sidebar is driven by each page's own "Menu" button instead. */
  @media (min-width: 1024px) {
    #ud-header-sidebar-toggle {
      display: inline-flex;
    }
  }
</style>
<script>
  // Applied before first paint so a collapsed sidebar never flashes open.
  (function () {
    try {
      if (localStorage.getItem('ud-sidebar-collapsed') === '1') {
        document.documentElement.classList.add('ud-sidebar-collapsed');
      }
    } catch (e) {}
  })();
</script>
@endpush

@section('content')
<div class="ud-container">
  <!-- Mobile Sidebar Overlay -->
  <div id="dashboard-sidebar-overlay" class="lg:hidden" aria-hidden="true"></div>

  <div class="flex">
    @include('partials.user-dashboard.sidebar')

    <section class="ud-content flex-1 min-w-0 p-4 md:p-6" aria-live="polite">
      @yield('dashboard-content')
    </section>
  </div>
</div>
@endsection

@push('scripts')
<!-- User Dashboard Centralized JavaScript -->
<script src="{{ asset('js/user-dashboard.js') }}?v={{ filemtime(public_path('js/user-dashboard.js')) }}"></script>
@endpush