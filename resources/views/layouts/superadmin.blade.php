@extends('layouts.app')

@push('styles')
<!-- Reuse the user-dashboard sidebar styling for a consistent look -->
<link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
<style>
  /* Sidebar visibility + stacking context (mirrors layouts.user-dashboard) */
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
      z-index: 70;
      transform: translateX(-100%);
    }
    #dashboard-sidebar.active {
      transform: translateX(0);
    }
    #dashboard-sidebar-overlay {
      position: fixed;
      inset: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 60;
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
      top: 4rem;
      height: calc(100vh - 4rem);
      z-index: 10;
    }
  }
</style>
@endpush

@section('content')
<div class="ud-container">
  <!-- Mobile Sidebar Overlay -->
  <div id="dashboard-sidebar-overlay" class="lg:hidden" aria-hidden="true"></div>

  <div class="flex">
    @include('partials.superadmin.sidebar')

    <section class="ud-content flex-1 p-4 md:p-6" aria-live="polite">
      <!-- Mobile menu button -->
      <div class="lg:hidden mb-4">
        <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                type="button" data-open-sidebar aria-controls="dashboard-sidebar" aria-expanded="false">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
          Menu
        </button>
      </div>

      @yield('dashboard-content')
    </section>
  </div>
</div>
@endsection

@push('scripts')
<!-- Reuse the user-dashboard sidebar behaviour (off-canvas toggle, overlay) -->
<script src="{{ asset('js/user-dashboard.js') }}"></script>
@endpush
