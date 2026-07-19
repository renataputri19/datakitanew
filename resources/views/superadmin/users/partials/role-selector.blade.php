@php
    /**
     * Radio-card role selector (single, exclusive choice).
     * Expects: $definitions (array), $selected (current role key|null)
     */
    $selected = $selected ?? old('role');
@endphp
<div class="grid sm:grid-cols-2 gap-3">
    @foreach($definitions as $key => $def)
        <label class="relative block cursor-pointer">
            <input type="radio" name="role" value="{{ $key }}" class="peer sr-only" {{ $selected === $key ? 'checked' : '' }}>

            {{-- Unchecked / checked indicators: both are direct siblings of the input
                 so Tailwind's peer-checked variant applies correctly. --}}
            <span class="absolute top-4 right-4 w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600 peer-checked:hidden"></span>
            <span class="absolute top-4 right-4 w-5 h-5 rounded-full bg-blue-500 hidden peer-checked:flex items-center justify-center">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </span>

            <div class="h-full rounded-xl border border-gray-200 dark:border-gray-700 p-4 transition-all
                        hover:border-gray-300 dark:hover:border-gray-600
                        peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 peer-checked:ring-1 peer-checked:ring-blue-500">
                <div class="pr-7">
                    @include('superadmin.partials.role-badge', ['badge' => $def['badge'], 'label' => $def['label']])
                </div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $def['description'] }}</p>
                <p class="mt-1.5 text-xs font-mono text-gray-400 dark:text-gray-500">Area: {{ $def['area'] }}</p>
            </div>
        </label>
    @endforeach
</div>
@error('role')
    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
@enderror
