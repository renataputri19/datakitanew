<div class="p-6 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-200 rounded-md shadow">


    <!-- Slider for Indicator Navigation -->
    {{-- part 1 --}}

    {{-- @if (count($indicators) > 1)
        <div class="p-6 border rounded-lg bg-gray-100 dark:bg-gray-800 mb-6">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100 text-center">
                Pilih Indikator dari Aspek {{ $selectedCategory }}
            </h1>
            <div
                class="mt-6 mb-6 flex overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 dark:scrollbar-thumb-gray-700 dark:scrollbar-track-gray-800 gap-4 border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                @foreach ($indicators as $index => $indicator)
                    <button wire:click="selectIndicator({{ $index }})"
                        class="flex-shrink-0 px-4 py-2 rounded-md text-sm transition text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600
                        {{ $currentIndicator['id'] === $indicator['id'] ? 'bg-gray-200 dark:bg-gray-800' : 'bg-blue-500' }}">
                        {{ $indicator['indikator'] }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif --}}


    @if (count($indicators) > 1)
        <div class="bg-gray-100 dark:bg-gray-800 border rounded-lg  shadow-lg border overflow-hidden mb-6">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    Pilih Indikator
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Aspek {{ $selectedCategory }}
                </p>
            </div>

            <!-- Table Section -->
            <div class="p-6">
                <div class="relative">
                    <!-- Container with flexible wrap -->
                    <div class="flex flex-wrap gap-3 ">
                        @foreach ($indicators as $index => $indicator)
                            <button wire:click="selectIndicator({{ $index }})"
                                class="px-4 py-2.5 rounded-full text-sm font-medium
                                       transition-all duration-200 ease-in-out
                                       focus:outline-none focus:ring-2 focus:ring-offset-2 
                                       {{ $currentIndicator['id'] === $indicator['id']
                                           ? 'bg-white text-primary-800 ring-2 ring-primary-500 dark:bg-gray-600 dark:text-primary-100'
                                           : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-600' }}">
                                <div class="flex items-center space-x-2">
                                    <span>{{ $indicator['indikator'] }}</span>
                                    @if ($currentIndicator['id'] === $indicator['id'])
                                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif


    {{-- <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-gray-100">
                Pilih Indikator
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Aspek {{ $selectedCategory }}
            </p>
        </div>

        <!-- Indicator Pills Container -->
        <div class="p-6">
            <div class="relative">
                <!-- Scroll Shadow Indicators -->
                <div
                    class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-white to-transparent dark:from-gray-800 z-10 pointer-events-none">
                </div>
                <div
                    class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white to-transparent dark:from-gray-800 z-10 pointer-events-none">
                </div>

                <!-- Scrollable Container -->
                <div
                    class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent 
                          dark:scrollbar-thumb-gray-600 dark:scrollbar-track-transparent 
                          -mx-2 px-2">
                    <div class="flex gap-3 py-2">
                        @foreach ($indicators as $index => $indicator)
                            <button wire:click="selectIndicator({{ $index }})"
                                class="flex-shrink-0 px-4 py-2.5 rounded-full text-sm font-medium
                                       transition-all duration-200 ease-in-out
                                       focus:outline-none focus:ring-2 focus:ring-offset-2 
                                       {{ $currentIndicator['id'] === $indicator['id']
                                           ? 'bg-primary-100 text-primary-800 ring-2 ring-primary-500 dark:bg-primary-900 dark:text-primary-100'
                                           : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}">
                                <div class="flex items-center space-x-2">
                                    <span class="whitespace-nowrap">{{ $indicator['indikator'] }}</span>
                                    @if ($currentIndicator['id'] === $indicator['id'])
                                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Optional: Navigation Hints -->
            <div class="mt-4 flex justify-center">
                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    Geser untuk melihat lebih banyak indikator
                </p>
            </div>
        </div>
    </div> --}}




    <div
        class="bg-gray-100 dark:bg-gray-800 border rounded-lg shadow-lg border overflow-hidden mb-6 @if (count($indicators) > 1) mt-6 @endif">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                {{ $currentIndicator['indikator'] ?? 'Pilih Indikator' }}
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Kelola tingkat indikator dan unggah dokumen pendukung
            </p>
        </div>

        <!-- Table Section -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Update Tingkat Section -->
                <div class="p-6 bg-white dark:bg-gray-700 rounded-xl shadow-lg transition hover:shadow-2xl">
                    <h2 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Pilih Tingkat</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Klik pada tingkat yang diinginkan untuk memperbarui secara otomatis.
                    </p>
                    <div class="flex items-center gap-2 flex-wrap mb-4">
                        @foreach (range(1, 5) as $level)
                            <button wire:click="saveTingkat({{ $level }})"
                                class="px-4 py-2 rounded-md text-sm transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 
                                {{ $currentIndicator['tingkat'] == $level ? 'bg-primary-500 text-white shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600' }}">
                                {{ $level }}
                            </button>
                        @endforeach
                    </div>

                    @if (session()->has('message'))
                        <p class="mt-6 text-sm text-green-500">{{ session('message') }}</p>
                    @endif
                </div>


                <!-- Upload Files Section -->
                <div class="p-6 bg-white dark:bg-gray-700 rounded-xl shadow-lg transition hover:shadow-2xl">
                    <h2 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Unggah Dokumen</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Pilih dokumen untuk diunggah atau seret dan lepaskan. Anda dapat mengunggah beberapa dokumen
                        sekaligus.
                    </p>

                    <!-- Context Selection Dropdown -->
                    <div class="mb-4">
                        <label for="context" class="block text-sm font-medium text-gray-800 dark:text-gray-200">Pilih
                            Tahapan</label>
                        <select id="context" wire:model="selectedContext"
                            class="block w-full p-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="pembinaan">Pembinaan</option>
                            <option value="interviu">Interviu</option>
                            <option value="visitasi">Visitasi</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <!-- File Upload Section -->
                        <div class="col-span-2">
                            @if (!empty($uploadedFiles))
                                <!-- File Details Section -->
                                <label class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">
                                    Dokumen Terunggah:
                                </label>
                                <ul>
                                    @foreach ($uploadedFiles as $file)
                                        <li class="text-sm text-gray-800 dark:text-gray-200">
                                            {{ $file->getClientOriginalName() }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <!-- File Input Section -->
                                <label for="files"
                                    class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">
                                    Pilih Dokumen:
                                </label>
                                <input type="file" id="files" wire:model="uploadedFiles" multiple
                                    class="block w-full p-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            @endif

                            <!-- Loading Indicator -->
                            <div wire:loading wire:target="uploadedFiles" class="text-sm text-blue-500 mt-2">
                                Mengunggah dokumen, harap tunggu...
                            </div>
                        </div>

                        <!-- Upload Button -->
                        <div class="flex justify-end">
                            <button type="button" wire:click="saveFiles"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium
            text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500
            disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                                wire:loading.attr="disabled">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Unggah Dokumen
                            </button>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>


    {{-- <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                {{ $currentIndicator['indikator'] ?? 'Select an Indicator' }}
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Manage indicator levels and upload supporting documents
            </p>
        </div>

        <!-- Main Content -->
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Level Selection Card -->
                <div class="bg-white dark:bg-gray-700 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Level Selection</h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Select the appropriate level for this indicator
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Level:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-100">
                                    {{ $currentIndicator['tingkat'] ?? 'Not Set' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex flex-wrap gap-3">
                                @foreach (range(1, 5) as $level)
                                    <button 
                                        wire:click="saveTingkat({{ $level }})"
                                        class="inline-flex items-center justify-center h-12 w-12 rounded-lg text-sm font-medium transition-all duration-150
                                               {{ $currentIndicator['tingkat'] == $level 
                                                   ? 'bg-primary-500 text-white shadow-sm' 
                                                   : 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-500 border border-gray-300 dark:border-gray-500' }}
                                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                    >
                                        {{ $level }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @if (session()->has('message'))
                            <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg">
                                <p class="text-sm text-green-700 dark:text-green-300">
                                    {{ session('message') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- File Upload Card -->
                <div class="bg-white dark:bg-gray-700 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Document Upload</h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Upload supporting documents for this indicator
                                </p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- File Input Area -->
                            @if (!empty($uploadedFiles))
                                <div class="space-y-3">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Selected Files
                                    </label>
                                    <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                                        @foreach ($uploadedFiles as $file)
                                            <li class="py-3 flex items-center justify-between">
                                                <span class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $file->getClientOriginalName() }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    Ready to upload
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <div class="relative">
                                    <label for="files" 
                                           class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Choose Files
                                    </label>
                                    <div class="mt-1 flex justify-center px-6 py-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                        <div class="text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="mt-4 flex text-sm text-gray-600 dark:text-gray-400">
                                                <label for="files"
                                                       class="relative cursor-pointer bg-white dark:bg-gray-600 rounded-md font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                                    <span>Upload files</span>
                                                    <input id="files" 
                                                           type="file" 
                                                           wire:model="uploadedFiles" 
                                                           multiple 
                                                           class="sr-only">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Any file format up to 10MB
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Upload Progress -->
                            <div wire:loading wire:target="uploadedFiles" 
                                 class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm text-blue-700 dark:text-blue-300">
                                        Uploading files, please wait...
                                    </span>
                                </div>
                            </div>

                            <!-- Upload Button -->
                            <div class="flex justify-end">
                                <button type="button" 
                                        wire:click="saveFiles"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium
                                               text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500
                                               disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                                        wire:loading.attr="disabled">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Upload Files
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Current Indicator Title -->
    {{-- part 2 --}}

    <!-- Tingkat Section -->
    {{-- <div class="p-6 border rounded-lg bg-gray-100 dark:bg-gray-800 mb-6 mt-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">
            Indikator: {{ $currentIndicator['indikator'] ?? 'Select an Indicator' }}
        </h1>


        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6 mb-6">
            <!-- Update Tingkat Section -->
            <div class="p-6 bg-white dark:bg-gray-700 rounded-xl shadow-lg transition hover:shadow-2xl">
                <h2 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Pilih Tingkat</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Click on the desired level to update the "Tingkat" automatically.
                </p>
                <div class="flex items-center gap-2 flex-wrap mb-4">
                    @foreach (range(1, 5) as $level)
                        <button wire:click="saveTingkat({{ $level }})"
                            class="px-4 py-2 rounded-md text-sm transition w-12 text-center text-gray-800 dark:text-gray-200
                            {{ $currentIndicator['tingkat'] == $level ? 'bg-gray-400 dark:bg-gray-900 hover:bg-gray-400 dark:hover:bg-gray-700' : 'bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            {{ $level }}
                        </button>
                    @endforeach
                </div>

                @if (session()->has('message'))
                    <p class="mt-6 text-sm text-green-500">{{ session('message') }}</p>
                @endif
            </div>


            <!-- Upload Files Section -->
            <div class="p-6 bg-white dark:bg-gray-700 rounded-xl shadow-lg transition hover:shadow-2xl">
                <h2 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Upload Files</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Select files to upload. You can upload multiple files at once.
                </p>
                <div class="grid grid-cols-3 gap-4">
                    <!-- File Upload Section -->
                    <div class="col-span-2">
                        @if (!empty($uploadedFiles))
                            <!-- File Details Section -->
                            <label class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">
                                Uploaded Files:
                            </label>
                            <ul>
                                @foreach ($uploadedFiles as $file)
                                    <li class="text-sm text-gray-800 dark:text-gray-200">
                                        {{ $file->getClientOriginalName() }}</li>
                                @endforeach
                            </ul>
                        @else
                            <!-- File Input Section -->
                            <label for="files"
                                class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">
                                Select Files:
                            </label>
                            <input type="file" id="files" wire:model="uploadedFiles" multiple
                                class="block w-full p-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @endif

                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="uploadedFiles" class="text-sm text-blue-500 mt-2">
                            Uploading files, please wait...
                        </div>
                    </div>

                    <!-- Upload Button -->
                    <div class="flex items-end">
                        <button type="button" wire:click="saveFiles"
                            class="w-full px-6 py-2 bg-green-500 text-gray-800 dark:text-gray-200 rounded-md hover:bg-green-600 focus:ring-2 focus:ring-green-300 bg-gray-300  dark:bg-gray-900  hover:bg-gray-300 dark:hover:bg-gray-600"
                            wire:loading.attr="disabled">
                            Upload Files
                        </button>
                    </div>


                </div>
            </div>

        </div>
    </div> --}}





    <div class="bg-gray-100 dark:bg-gray-800 border rounded-lg  shadow-lg border overflow-hidden mb-6 mt-6">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Manajemen Penilaian</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Tinjau dan kelola hasil penilaian indikator
            </p>
        </div>

        <!-- Table Section -->
        <!-- Main Content -->
        <div class="p-6">
            @if ($currentIndicator)
                <!-- Indicator Card -->
                <div class="bg-white dark:bg-gray-700 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
                    <!-- Header with Status -->
                    <div class="p-6 bg-white dark:bg-gray-700 rounded-xl shadow-lg transition hover:shadow-2xl">
                        <!-- Header with Domain Name and Status -->
                        <div
                            class="flex items-center justify-between border-b border-gray-300 dark:border-gray-600 pb-4 mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">
                                    {{ $currentIndicator['indikator'] }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @if (is_null($currentIndicator['disetujui']))
                                        Menunggu Penilaian
                                        <span class="text-yellow-500 font-medium">• ⚠ Menunggu</span>
                                    @elseif ($currentIndicator['disetujui'] == 0)
                                        Tidak Disetujui
                                        <span class="text-red-500 font-medium">• ✖ Tidak Disetujui</span>
                                    @elseif ($currentIndicator['disetujui'] == 1)
                                        Disetujui
                                        <span class="text-green-500 font-medium">• ✔ Disetujui</span>
                                    @endif
                                </p>

                            </div>
                            <button wire:key="indicator-{{ $currentIndicator['id'] }}"
                                class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-sm rounded-full">
                                Penilai: {{ $currentIndicator['user']['name'] ?? 'Belum Dinilai' }}
                            </button>




                        </div>

                        <!-- Domain Details -->
                        <div class="mb-2">
                            <div>
                                <span class="fs-6 text-gray-600 dark:text-gray-400 font-medium">Tingkat (User):</span>
                                <span class="fs-6 text-gray-800 dark:text-gray-100 font-medium">
                                    {{ $currentIndicator['tingkat'] ?? 'Belum Dinilai' }}
                                </span>
                            </div>
                            <div>
                                <span class="fs-6 text-gray-600 dark:text-gray-400 font-medium">Tingkat TPB
                                    (Admin):</span>
                                <span class="fs-6 text-gray-800 dark:text-gray-100 font-medium">
                                    {{ $currentIndicator['tingkat_tpb'] ?? 'Belum Dinilai' }}
                                </span>
                            </div>
                            <div>
                                <span class="fs-6 text-gray-600 dark:text-gray-400 font-medium">Alasan:</span>
                                <span class="fs-6 text-gray-800 dark:text-gray-100 whitespace-normal font-medium">
                                    {{ $currentIndicator['reasons'] ?? 'Tidak Ada Alasan' }}
                                </span>
                            </div>
                        </div>


                        @if ($isAdmin)
                            <!-- Admin Actions -->
                            <div class="mt-4 p-4 bg-gray-200 dark:bg-gray-600 rounded-lg shadow-md">
                                <!-- Update Tingkat TPB -->
                                <div class="mb-4">
                                    <label for="tingkat-tpb-{{ $currentIndicator['id'] }}"
                                        class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                        Tingkat TPB:
                                    </label>
                                    <div class="flex gap-2">
                                        @foreach (range(1, 5) as $tingkat)
                                            <button
                                                wire:click="updateTingkatTpb({{ $currentIndicator['id'] }}, {{ $tingkat }})"
                                                class="px-3 py-2 rounded-md text-sm transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 
                                {{ $currentIndicator['tingkat_tpb'] == $tingkat
                                    ? 'bg-primary-500 text-white shadow-sm'
                                    : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600' }}">
                                                {{ $tingkat }}
                                            </button>
                                        @endforeach
                                    </div>
                                    {{-- <div class="flex flex-wrap gap-2">
                                        @foreach (range(1, 5) as $tingkat)
                                            <button
                                                wire:click="updateTingkatTpb({{ $currentIndicator['id'] }}, {{ $tingkat }})"
                                                class="inline-flex items-center justify-center h-10 w-10 rounded-lg text-sm font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500
                                                       {{ $currentIndicator['tingkat_tpb'] == $tingkat
                                                           ? 'bg-primary-500 text-white shadow-sm'
                                                           : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600' }}">
                                                {{ $tingkat }}
                                            </button>
                                        @endforeach
                                    </div> --}}
                                </div>

                                <!-- Provide Reason -->
                                <div>
                                    <label for="reason-{{ $currentIndicator['id'] }}"
                                        class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2 mt-2">
                                        Masukkan Alasan:
                                    </label>
                                    <textarea id="reason-{{ $currentIndicator['id'] }}" wire:model.lazy="currentIndicator.reasons"
                                        class="w-full mb-2 p-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Enter your reason here..."></textarea>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-4 flex gap-4">
                                    <!-- Approve Button -->
                                    <button wire:click="saveReasonAndStatus({{ $currentIndicator['id'] }}, 1)"
                                        class="px-3 py-2 text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-900 font-semibold rounded-md shadow-md transition hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Setujui
                                    </button>
                                    <!-- Disapprove Button -->
                                    <button wire:click="saveReasonAndStatus({{ $currentIndicator['id'] }}, 0)"
                                        class="px-3 py-2 text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-900 font-semibold rounded-md shadow-md transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Tolak
                                    </button>
                                </div>


                            </div>
                        @endif

                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada indikator yang
                        dipilih</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih indikator untuk melihat detail
                        penilaiannya.</p>
                </div>
            @endif
        </div>
    </div>


    {{-- <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Assessment Management</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Review and manage indicator assessments
            </p>
        </div>

        <!-- Main Content -->
        <div class="p-6">
            @if ($currentIndicator)
                <!-- Indicator Card -->
                <div class="bg-white dark:bg-gray-700 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
                    <!-- Header with Status -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $currentIndicator['indikator'] }}
                                    </h2>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $currentIndicator['disetujui'] ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100' }}">
                                        @if ($currentIndicator['disetujui'])
                                            <svg class="mr-1.5 h-2 w-2 text-green-400" fill="currentColor"
                                                viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3" />
                                            </svg>
                                            Approved
                                        @else
                                            <svg class="mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor"
                                                viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3" />
                                            </svg>
                                            Pending
                                        @endif
                                    </span>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200">
                                    ID: {{ $currentIndicator['id'] }}
                                </span>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">User
                                        Level:</span>
                                    <span
                                        class="text-sm text-gray-900 dark:text-gray-100">{{ $currentIndicator['tingkat'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">TPB Level
                                        (Admin):</span>
                                    <span
                                        class="text-sm text-gray-900 dark:text-gray-100">{{ $currentIndicator['tingkat_tpb'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Reason:</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $currentIndicator['reasons'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($isAdmin)
                        <!-- Admin Controls -->
                        <div class="p-6 bg-gray-50 dark:bg-gray-600/50">
                            <div class="space-y-6">
                                <!-- TPB Level Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        TPB Level Selection
                                    </label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach (range(1, 5) as $tingkat)
                                            <button
                                                wire:click="updateTingkatTpb({{ $currentIndicator['id'] }}, {{ $tingkat }})"
                                                class="inline-flex items-center justify-center h-10 w-10 rounded-lg text-sm font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500
                                                       {{ $currentIndicator['tingkat_tpb'] == $tingkat
                                                           ? 'bg-primary-500 text-white shadow-sm'
                                                           : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600' }}">
                                                {{ $tingkat }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Reason Input -->
                                <div>
                                    <label for="reason-{{ $currentIndicator['id'] }}"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Assessment Reason
                                    </label>
                                    <textarea id="reason-{{ $currentIndicator['id'] }}" wire:model.lazy="currentIndicator.reasons" rows="3"
                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm text-gray-900 dark:text-gray-100 
                                               bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                               placeholder-gray-400 dark:placeholder-gray-500 transition-colors duration-150"
                                        placeholder="Provide your assessment reasoning here..."></textarea>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <button wire:click="saveReasonAndStatus({{ $currentIndicator['id'] }}, 1)"
                                        class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium
                                               text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500
                                               transition-colors duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Approve Assessment
                                    </button>
                                    <button wire:click="saveReasonAndStatus({{ $currentIndicator['id'] }}, 0)"
                                        class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium
                                               text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500
                                               transition-colors duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Disapprove Assessment
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No indicator selected</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose an indicator to view its assessment
                        details</p>
                </div>
            @endif
        </div>
    </div> --}}



    <!-- Domain Details -->
    {{-- part 3 --}}
    {{-- <div class="p-6 border rounded-lg bg-gray-100 dark:bg-gray-800 mb-6 mt-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Penilaian</h1>

        @if ($currentIndicator)
            <div class="p-6 bg-white dark:bg-gray-700 rounded-xl shadow-lg transition hover:shadow-2xl mt-6 mb-6">
                <!-- Header with Domain Name and Status -->
                <div class="flex items-center justify-between border-b border-gray-300 dark:border-gray-600 pb-4 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                            {{ $currentIndicator['indikator'] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $currentIndicator['disetujui'] ? 'Approved' : 'Pending' }}
                            <span
                                class="{{ $currentIndicator['disetujui'] ? 'text-green-500' : 'text-yellow-500' }} font-medium">
                                • {{ $currentIndicator['disetujui'] ? '✔ Approved' : '⚠ Pending' }}
                            </span>
                        </p>
                    </div>
                    <button class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-sm rounded-full">
                        ID: {{ $currentIndicator['id'] }}
                    </button>
                </div>

                <!-- Domain Details -->
                <div class="mb-2">
                    <div>
                        <span class="fs-6 text-gray-600 dark:text-gray-400 font-medium">Tingkat (User):</span>
                        <span class="fs-6 text-gray-800 dark:text-gray-100 font-medium">
                            {{ $currentIndicator['tingkat'] ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="fs-6 text-gray-600 dark:text-gray-400 font-medium">Tingkat TPB (Admin):</span>
                        <span class="fs-6 text-gray-800 dark:text-gray-100 font-medium">
                            {{ $currentIndicator['tingkat_tpb'] ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="fs-6 text-gray-600 dark:text-gray-400 font-medium">Reason:</span>
                        <span class="fs-6 text-gray-800 dark:text-gray-100 whitespace-normal font-medium">
                            {{ $currentIndicator['reasons'] ?? 'N/A' }}
                        </span>
                    </div>
                </div>


                @if ($isAdmin)
                    <!-- Admin Actions -->
                    <div class="mt-4 p-4 bg-gray-200 dark:bg-gray-600 rounded-lg shadow-md">
                        <!-- Update Tingkat TPB -->
                        <div class="mb-4">
                            <label for="tingkat-tpb-{{ $currentIndicator['id'] }}"
                                class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                Tingkat TPB:
                            </label>
                            <div class="flex gap-2">
                                @foreach (range(1, 5) as $tingkat)
                                    <button
                                        wire:click="updateTingkatTpb({{ $currentIndicator['id'] }}, {{ $tingkat }})"
                                        class="px-3 py-2 rounded-md text-sm transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 
                        {{ $currentIndicator['tingkat_tpb'] == $tingkat
                            ? 'bg-gray-400 dark:bg-gray-900 hover:bg-gray-400 dark:hover:bg-gray-700'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                                        {{ $tingkat }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Provide Reason -->
                        <div>
                            <label for="reason-{{ $currentIndicator['id'] }}"
                                class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                Provide a Reason:
                            </label>
                            <textarea id="reason-{{ $currentIndicator['id'] }}" wire:model.lazy="currentIndicator.reasons"
                                class="w-full mb-2 p-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter your reason here..."></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 flex gap-4">
                            <!-- Approve Button -->
                            <button wire:click="saveReasonAndStatus({{ $currentIndicator['id'] }}, 1)"
                                class="px-3 py-2 text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-900 font-semibold rounded-md shadow-md transition hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Approve
                            </button>
                            <!-- Disapprove Button -->
                            <button wire:click="saveReasonAndStatus({{ $currentIndicator['id'] }}, 0)"
                                class="px-3 py-2 text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-900 font-semibold rounded-md shadow-md transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Disapprove
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400">No indicator selected.</p>
        @endif
    </div> --}}






    <!-- Enhanced Files Table -->
    {{-- part 4 --}}
    {{-- <div class="p-6 border rounded-lg bg-gray-100 dark:bg-gray-800 mb-6 mt-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Files</h1>
        <div class="overflow-x-auto">
            @if ($currentIndicator)
                <table class="table-auto w-full border-collapse border border-gray-300 dark:border-gray-700 mt-6 mb-6">
                    <thead class="bg-gray-200 dark:bg-gray-800">
                        <tr>
                            <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                File</th>
                            <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                Status</th>
                            <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                Reason</th>
                            <th class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $files = \App\Models\File::where('domain_id', $currentIndicator['id'])->get();
                        @endphp
                        @foreach ($files as $file)
                            <tr class="bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <!-- File Column -->
                                <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-800 dark:text-gray-300">
                                    <a href="{{ asset('storage/' . $file->file_path) }}" class="text-blue-500">
                                        {{ $file->original_name }}
                                    </a>
                                </td>

                                <!-- Status Column -->
                                <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-800 dark:text-gray-300">
                                    <span class="{{ $file->hasil ? 'text-green-500' : 'text-yellow-500' }}">
                                        {{ $file->hasil ? 'Approved' : 'Pending' }}
                                    </span>
                                </td>

                                <!-- Reason Column -->
                                <td class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-800 dark:text-gray-300">
                                    {{ $file->reasons ?? 'N/A' }}
                                </td>

                                <!-- Actions Column -->
                                <td class="border border-gray-300 dark:border-gray-700 px-4 py-2">
                                    <div class="space-y-2">
                                        <!-- Update File Button -->
                                        <label for="update-file-{{ $file->id }}"
                                            class="block px-3 py-2 bg-yellow-500 text-gray-800 dark:text-gray-300 rounded-md cursor-pointer text-center bg-gray-300  dark:bg-gray-700  hover:bg-gray-300 dark:hover:bg-gray-800">
                                            Update
                                        </label>
                                        <input type="file" id="update-file-{{ $file->id }}"
                                            wire:model="updatedFiles.{{ $file->id }}" class="hidden">
                                        @if (isset($updatedFiles[$file->id]))
                                            <button wire:click="updateFile({{ $file->id }})"
                                                class="block w-full px-3 py-2 bg-green-500 text-gray-800 dark:text-gray-300 rounded-md bg-gray-300  dark:bg-gray-700  hover:bg-gray-300 dark:hover:bg-gray-800">
                                                Save
                                            </button>
                                        @endif

                                        <!-- Delete File Button -->
                                        <button onclick="confirmDelete({{ $file->id }})"
                                            class="block w-full px-3 py-2 bg-red-500 text-gray-800 dark:text-gray-300 rounded-md bg-gray-300  dark:bg-gray-700  hover:bg-gray-300 dark:hover:bg-gray-800">
                                            Delete
                                        </button>

                                        <!-- Provide Reason Button -->
                                        @if ($isAdmin)
                                            <button
                                                onclick="openReasonModal('file', {{ $file->id }}, '{{ $file->reasons }}', {{ $file->hasil }})"
                                                class="block w-full px-3 py-2 bg-blue-500 text-gray-800 dark:text-gray-300 rounded-md bg-gray-300  dark:bg-gray-700  hover:bg-gray-300 dark:hover:bg-gray-800">
                                                Provide Reason
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 dark:text-gray-400">No indicator selected or no files available.</p>
            @endif
        </div>
    </div> --}}

    {{-- <div class="p-6 border rounded-lg bg-gray-100 dark:bg-gray-800 mb-6 mt-6"> --}}
    {{-- <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-8"> --}}
    {{-- <div class="max-w-7xl mx-auto p-6 border rounded-lg bg-gray-100 dark:bg-gray-800 mb-6 mt-6"> --}}
    <div class="bg-gray-100 dark:bg-gray-800 border rounded-lg  shadow-lg border overflow-hidden mb-6 mt-6">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Manajemen Dokumen</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Kelola dan pantau dokumen yang telah diunggah
            </p>
        </div>

        <!-- Table Section -->
        <div class="p-6">
            @if ($currentIndicator)
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full">
                        <thead class="bg-gray-200 dark:bg-gray-800">
                            <tr>
                                <th
                                    class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                    Dokumen
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                    Status
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                    Alasan
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @php
                                $files = \App\Models\File::where('domain_id', $currentIndicator['id'])->get();
                            @endphp
                            @foreach ($files as $file)
                                <tr
                                    class="bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                    <!-- File Column -->
                                    <td
                                        class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-800 dark:text-gray-300">
                                        <a href="{{ asset('storage/' . $file->file_path) }}"
                                            class="text-blue-500 hover:underline">
                                            {{ $file->original_name }}
                                        </a>
                                    </td>

                                    <!-- Status Column DISINI ADA PERBAIKAN NANTI-->
                                    <td
                                        class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-800 dark:text-gray-300">
                                        @if (is_null($file->hasil))
                                            <span class="text-yellow-500">
                                                Menunggu Persetujuan
                                            </span>
                                        @elseif ($file->hasil == 1)
                                            <span class="text-green-500">
                                                Disetujui
                                            </span>
                                        @else
                                            <span class="text-red-500">
                                                Ditolak
                                            </span>
                                        @endif
                                    </td>


                                    <!-- Reason Column -->
                                    <td
                                        class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-800 dark:text-gray-300">
                                        {{ $file->reasons ?? 'Tidak Ada Alasan' }}
                                    </td>

                                    <!-- Actions Column -->
                                    <td class="border border-gray-300 dark:border-gray-700 p-3 text-center">
                                        <div class="flex items-center justify-center gap-3">
                                            <!-- Update File Button Group -->
                                            <div class="relative group">
                                                <label for="update-file-{{ $file->id }}"
                                                    class="inline-flex items-center gap-2 px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded-lg transition-all duration-200 hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:outline-none"
                                                    role="button" tabindex="0">
                                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                    </svg>
                                                    <span>Perbarui</span>
                                                </label>
                                                <input type="file" id="update-file-{{ $file->id }}"
                                                    wire:model="updatedFiles.{{ $file->id }}" class="hidden"
                                                    aria-label="Update file">
                                                <!-- File selection feedback tooltip -->
                                                <div
                                                    class="absolute left-0 -top-8 hidden group-focus-within:block bg-gray-800 text-gray-800 dark:text-gray-300 text-sm px-2 py-1 rounded">
                                                    Choose a file to update
                                                </div>
                                            </div>

                                            <!-- Delete Action Group -->
                                            <div class="relative">
                                                @if ($confirmingDelete === $file->id)
                                                    <!-- Delete Confirmation State -->
                                                    <div class="flex items-center gap-2 p-1 rounded-lg shadow-sm animate-fade-in"
                                                        role="alertdialog" aria-label="Confirm deletion">
                                                        <button wire:click="deleteFile({{ $file->id }})"
                                                            class="inline-flex items-center gap-2 px-3 py-2 bg-primary-500 text-gray-800 dark:text-gray-300 rounded-md transition-all duration-200 hover:bg-red-600 focus:ring-2 focus:ring-red-400 focus:outline-none">
                                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            <span>Ya, Lanjutkan</span>
                                                        </button>
                                                        <button wire:click="$set('confirmingDelete', null)"
                                                            class="inline-flex items-center gap-2 px-3 py-2 bg-primary-500 text-gray-800 dark:text-gray-300 rounded-md transition-all duration-200 hover:bg-gray-600 focus:ring-2 focus:ring-gray-400 focus:outline-none">
                                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                            <span>Batal</span>
                                                        </button>
                                                    </div>
                                                @else
                                                    <!-- Initial Delete Button -->
                                                    <button wire:click="$set('confirmingDelete', {{ $file->id }})"
                                                        class="inline-flex items-center gap-2 px-3 py-2 bg-primary-500 text-gray-800 dark:text-gray-300 rounded-lg transition-all duration-200 hover:bg-red-600 focus:ring-2 focus:ring-red-400 focus:ring-offset-2 focus:outline-none">
                                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        <span>Hapus</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada dokumen tersedia
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih indikator untuk melihat dokumen
                        terkait</p>
                </div>
            @endif
        </div>
    </div>
    {{-- </div> --}}
    {{-- </div> --}}
    {{-- </div> --}}


    {{-- <div class="p-6 border rounded-lg bg-gray-100 dark:bg-gray-800 mb-6 mt-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Files</h1>
        <div class="overflow-x-auto">
            @if ($currentIndicator)
                <table class="table-auto w-full border-collapse border border-gray-300 dark:border-gray-700 mt-6 mb-6">
                    <thead class="bg-gray-200 dark:bg-gray-800">
                        <tr>
                            <th
                                class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                File
                            </th>
                            <th
                                class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                Status
                            </th>
                            <th
                                class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                Reason
                            </th>
                            <th
                                class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-900 dark:text-gray-100">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $files = \App\Models\File::where('domain_id', $currentIndicator['id'])->get();
                        @endphp
                        @foreach ($files as $file)
                            <tr class="bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <!-- File Column -->
                                <td
                                    class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-800 dark:text-gray-300">
                                    <a href="{{ asset('storage/' . $file->file_path) }}"
                                        class="text-blue-500 hover:underline">
                                        {{ $file->original_name }}
                                    </a>
                                </td>

                                <!-- Status Column -->
                                <td
                                    class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-800 dark:text-gray-300">
                                    <span class="{{ $file->hasil ? 'text-green-500' : 'text-yellow-500' }}">
                                        {{ $file->hasil ? 'Approved' : 'Pending' }}
                                    </span>
                                </td>

                                <!-- Reason Column -->
                                <td
                                    class="border border-gray-300 dark:border-gray-700 px-4 py-2 text-gray-800 dark:text-gray-300">
                                    {{ $file->reasons ?? 'N/A' }}
                                </td>

                                <!-- Actions Column -->
                                <td class="border border-gray-300 dark:border-gray-700 p-3 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <!-- Update File Button Group -->
                                        <div class="relative group">
                                            <label for="update-file-{{ $file->id }}"
                                                class="inline-flex items-center gap-2 px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded-lg transition-all duration-200 hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:outline-none"
                                                role="button" tabindex="0">
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                </svg>
                                                <span>Update</span>
                                            </label>
                                            <input type="file" id="update-file-{{ $file->id }}"
                                                wire:model="updatedFiles.{{ $file->id }}" class="hidden"
                                                aria-label="Update file">
                                            <!-- File selection feedback tooltip -->
                                            <div
                                                class="absolute left-0 -top-8 hidden group-focus-within:block bg-gray-800 text-gray-800 dark:text-gray-300 text-sm px-2 py-1 rounded">
                                                Choose a file to update
                                            </div>
                                        </div>

                                        <!-- Delete Action Group -->
                                        <div class="relative">
                                            @if ($confirmingDelete === $file->id)
                                                <!-- Delete Confirmation State -->
                                                <div class="flex items-center gap-2 p-1 bg-gray-200 dark:bg-gray-700 rounded-lg shadow-sm animate-fade-in"
                                                    role="alertdialog" aria-label="Confirm deletion">
                                                    <button wire:click="deleteFile({{ $file->id }})"
                                                        class="inline-flex items-center gap-2 px-3 py-2 bg-red-500 text-gray-800 dark:text-gray-300 rounded-md transition-all duration-200 hover:bg-red-600 focus:ring-2 focus:ring-red-400 focus:outline-none">
                                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        <span>Confirm</span>
                                                    </button>
                                                    <button wire:click="$set('confirmingDelete', null)"
                                                        class="inline-flex items-center gap-2 px-3 py-2 bg-gray-500 text-gray-800 dark:text-gray-300 rounded-md transition-all duration-200 hover:bg-gray-600 focus:ring-2 focus:ring-gray-400 focus:outline-none">
                                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                        <span>Cancel</span>
                                                    </button>
                                                </div>
                                            @else
                                                <!-- Initial Delete Button -->
                                                <button wire:click="$set('confirmingDelete', {{ $file->id }})"
                                                    class="inline-flex items-center gap-2 px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded-lg transition-all duration-200 hover:bg-red-600 focus:ring-2 focus:ring-red-400 focus:ring-offset-2 focus:outline-none">
                                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <span>Delete</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 dark:text-gray-400">No indicator selected or no files available.</p>
            @endif
        </div>
    </div> --}}

    <script>
        function showInlineDelete(fileId) {
            document.getElementById(`inline-delete-${fileId}`).classList.remove('hidden');
        }

        function hideInlineDelete(fileId) {
            document.getElementById(`inline-delete-${fileId}`).classList.add('hidden');
        }

        function confirmDeleteInline(fileId) {
            // Call the Livewire method to delete the file
            @this.call('deleteFile', fileId);

            // Hide the confirmation box
            hideInlineDelete(fileId);
        }
    </script>







    @if ($isAdmin)
        {{-- <div class="p-6 border rounded-lg bg-gray-100 dark:bg-gray-800 mb-6 mt-6"> --}}
        <div class="bg-gray-100 dark:bg-gray-800 border rounded-lg  shadow-lg border overflow-hidden mb-6 mt-6">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    Manajemen Tinjauan Dokumen
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Pilih dokumen untuk ditinjau dan berikan status persetujuan.
                </p>
            </div>

            <!-- Main Content -->
            <div class="p-6 space-y-6">
                <!-- File Selection -->
                <div class="space-y-2">
                    <label for="file-select" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                        Pilih Dokumen untuk Ditinjau
                    </label>
                    <div class="relative">
                        <select id="file-select" wire:model.lazy="selectedFileId"
                            class="appearance-none block w-full px-3 py-2.5 text-base border border-gray-300 dark:border-gray-600 rounded-lg 
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                       focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-400
                                       transition duration-150 ease-in-out">
                            <option value="">Pilih dokumen...</option>
                            @foreach (\App\Models\File::where('domain_id', $currentIndicator['id'])->get() as $file)
                                <option value="{{ $file->id }}" class="py-2">
                                    {{ $file->original_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($selectedFileId && $selectedFile)
                    <!-- Review Section -->
                    <div class="space-y-4 animate-fadeIn">
                        <div class="space-y-2">
                            <label for="reason-{{ $selectedFileId }}"
                                class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Komentar Tinjauan
                            </label>
                            <textarea id="reason-{{ $selectedFileId }}" wire:model.lazy="selectedFileReason" rows="4"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg
                                       dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-200
                                       focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                       placeholder-gray-400 dark:placeholder-gray-500
                                       transition duration-150 ease-in-out"
                                placeholder="Silakan berikan komentar rinci terkait keputusan tinjauan Anda..."></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-4">
                            <button wire:click="saveFileReasonAndStatusInline(1)"
                                class="inline-flex justify-center items-center px-4 py-2.5 border border-transparent
                                       text-sm font-medium rounded-lg text-gray-800 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 hover:bg-emerald-700
                                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500
                                       dark:focus:ring-offset-gray-800 transition-colors duration-150 ease-in-out
                                       shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Setujui Dokumen
                            </button>
                            <button wire:click="saveFileReasonAndStatusInline(0)"
                                class="inline-flex justify-center items-center px-4 py-2.5 border border-transparent
                                       text-sm font-medium rounded-lg text-gray-800 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 hover:bg-red-700
                                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500
                                       dark:focus:ring-offset-gray-800 transition-colors duration-150 ease-in-out
                                       shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Tolak Dokumen
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        </div>
        {{-- </div> --}}
    @endif




    <!-- Modal -->
    {{-- <div id="reasonModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 hidden">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md shadow-lg w-1/3">
            <h3 id="modalTitle" class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Provide Reason</h3>
            <textarea id="reasonTextarea"
                class="w-full p-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 rounded-md mb-4"></textarea>
            <div class="flex justify-center gap-4">
                <button onclick="closeReasonModal()" 
                    class="px-4 py-2 rounded-md transition text-gray-800 dark:text-gray-100 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button onclick="saveReasonAndStatus(1)" 
                    class="px-4 py-2 rounded-md transition text-gray-800 dark:text-gray-100 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-700">
                    Approve
                </button>
                <button onclick="saveReasonAndStatus(0)" 
                    class="px-4 py-2 rounded-md transition text-gray-800 dark:text-gray-100 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-700">
                    Disapprove
                </button>
            </div>
        </div>
    </div> --}}

    <!-- Delete Confirmation Modal -->
    {{-- <div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 hidden">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md shadow-lg w-1/3">
            <h3 class="text-lg font-bold mb-4 text-center text-gray-800 dark:text-gray-100">
                Are you sure you want to delete this file?
            </h3>
            <div class="flex justify-center gap-4"> <!-- Added gap-4 for consistent spacing -->
                <button onclick="closeDeleteModal()"
                    class="px-4 py-2 rounded-md transition text-gray-800 dark:text-gray-100 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button onclick="confirmDeleteAction()"
                    class="px-4 py-2 rounded-md transition text-gray-800 dark:text-gray-100 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-700">
                    Delete
                </button>
            </div>

        </div>
    </div> --}}



    <script>
        let modalType = null; // Either 'domain' or 'file'
        let modalId = null; // The ID of the domain or file
        let currentIndicatorTitle = "{{ $currentIndicator['indikator'] ?? 'N/A' }}";

        function openReasonModal(type, id, reason = '', status = null) {
            modalType = type;
            modalId = id;

            // Update modal title based on the type
            const titlePrefix = type === 'domain' ? 'Domain' : 'File';
            document.getElementById('modalTitle').textContent =
                `Provide Reason for ${titlePrefix}: ${currentIndicatorTitle}`;

            // Populate reason textarea
            document.getElementById('reasonTextarea').value = reason || '';

            // Show modal
            document.getElementById('reasonModal').classList.remove('hidden');
        }

        function closeReasonModal() {
            // Clear modal data
            modalType = null;
            modalId = null;
            document.getElementById('reasonTextarea').value = ''; // Clear reason
            document.getElementById('reasonModal').classList.add('hidden'); // Hide modal
        }

        function saveReasonAndStatus(status) {
            const reason = document.getElementById('reasonTextarea').value;

            // Call Livewire method based on modal type
            if (modalType === 'domain') {
                @this.call('saveDomainReasonAndStatus', modalId, reason, status);
            } else if (modalType === 'file') {
                @this.call('saveFileReasonAndStatus', modalId, reason, status);
            }

            // Close modal after action
            closeReasonModal();
        }

        let deleteFileId = null; // ID of the file to delete

        function confirmDelete(fileId) {
            deleteFileId = fileId;
            document.getElementById('deleteModal').classList.remove('hidden'); // Show delete confirmation modal
        }

        function closeDeleteModal() {
            deleteFileId = null;
            document.getElementById('deleteModal').classList.add('hidden'); // Hide delete confirmation modal
        }

        function confirmDeleteAction() {
            if (deleteFileId) {
                @this.call('deleteFile', deleteFileId);
            }
            closeDeleteModal(); // Close modal after delete
        }
    </script>

</div>
