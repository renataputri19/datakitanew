@extends('layouts.bps')

@section('title', 'Detail Survei SIBSTR – BPS Dashboard')
@section('description', 'Detail respons Survei SIBSTR dalam mode tampilan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sibstr-detail.css') }}?v={{ filemtime(public_path('css/sibstr-detail.css')) }}">
@endpush

@section('content')
@include('bps.sibstr.partials.detail', [
    'downloadUrl' => route('bps.sibstr.download', $surveyResponse->id),
    'backUrl'     => route('bps.sibstr.index'),
    'backLabel'   => 'Daftar SIBSTR',
])
@endsection

@push('scripts')
@include('bps.sibstr.partials.detail-scripts')
@endpush
