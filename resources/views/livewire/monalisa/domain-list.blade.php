<div>
    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Search domains..." class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500" />
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Description</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($domains as $domain)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4">{{ $domain->name }}</td>
                        <td class="px-6 py-4">{{ Str::limit($domain->description, 100) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                            <a href="#" class="text-blue-600 hover:text-blue-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No domains found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $domains->links() }}
    </div>
</div>
