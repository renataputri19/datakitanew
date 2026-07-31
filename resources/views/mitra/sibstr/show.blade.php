@extends('layouts.user-dashboard')

@section('title', 'Detail Survei SIBSTR - Mitra')
@section('description', 'Detail respons survei SIBSTR dalam mode tampilan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sibstr-detail.css') }}?v={{ filemtime(public_path('css/sibstr-detail.css')) }}">
@endpush

@section('dashboard-content')
<div class="lg:hidden mb-4">
  <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400" type="button" data-open-sidebar>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    Menu
  </button>
</div>

@include('bps.sibstr.partials.detail', [
    'downloadUrl' => route('survey.mitra.sibstr.download', $surveyResponse->id),
    'backUrl'     => route('survey.sibstr.entry'),
    'backLabel'   => 'Daftar SIBSTR',
])
@endsection

@push('scripts')
@include('bps.sibstr.partials.detail-scripts')
@endpush
