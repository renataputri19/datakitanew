@extends('layouts.app')

@push('styles')
<!-- User Dashboard Centralized Styles -->
<link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
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
    }
  }
</style>
@endpush

@section('content')
<div class="ud-container">
  <!-- Mobile Sidebar Overlay -->
  <div id="dashboard-sidebar-overlay" class="lg:hidden" aria-hidden="true"></div>

  <div class="flex">
    @include('partials.user-dashboard.sidebar')

    <section class="ud-content flex-1 p-4 md:p-6" aria-live="polite">
      @yield('dashboard-content')
    </section>
  </div>
</div>
@endsection

@push('scripts')
<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<!-- User Dashboard Centralized JavaScript -->
<script src="{{ asset('js/user-dashboard.js') }}"></script>
@endpush