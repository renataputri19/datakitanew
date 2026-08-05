@if(session('success'))
    <div class="mb-6 rounded-md border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4">
        <p class="text-sm text-green-800 dark:text-green-300">{{ session('success') }}</p>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 rounded-md border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4">
        <p class="text-sm text-red-800 dark:text-red-300">{{ session('error') }}</p>
    </div>
@endif

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
