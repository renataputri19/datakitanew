@php
    /**
     * Reusable role badge.
     * Expects: $badge (red|blue|purple|amber|gray), $label (string)
     */
    $badgeMap = [
        'red'    => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
        'blue'   => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
        'amber'  => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        'gray'   => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    ];
    $cls = $badgeMap[$badge ?? 'gray'] ?? $badgeMap['gray'];
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap {{ $cls }}">{{ $label }}</span>
