{{--
    Validation errors only.

    session('success') and session('error') are already rendered by
    layouts.bps just above @yield('content') — repeating them here showed
    every flash message twice.
--}}
@if($errors->any())
    <div class="mb-6 rounded-md border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4">
        <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-2">Periksa kembali isian berikut:</p>
        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
