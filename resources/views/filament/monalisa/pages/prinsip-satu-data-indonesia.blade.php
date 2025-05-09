<x-filament::page>
    <div class="space-y-4">
        <!-- Title -->
        {{-- <h1 class="text-2xl font-bold">1. Manajemen Perubahan - Pemenuhan</h1> --}}

        <!-- Dropdown -->
        <label for="section-select" class="block text-sm font-medium text-gray-900 dark:text-white">Pilih Aspek</label>
        <select id="section-select"
            class="block w-full mt-2 p-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-md shadow-sm"
            onchange="showSection(this.value)">
            <option value="standar-data-statistik">i. Standar Data Statistik</option>
            <option value="metadata-statistik">ii. Metadata Statistik</option>
            <option value="interoperabilitas-data">iii. Interoperabilitas Data</option>
            <option value="kode-referensi">iv. Kode Referensi dan/atau Data Induk</option>
        </select>





        <!-- Sections -->
        <div id="standar-data-statistik" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Standar Data Statistik'])
        </div>
        <div id="metadata-statistik" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Metadata Statistik'])
        </div>
        <div id="interoperabilitas-data" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Interoperabilitas Data'])
        </div>
        <div id="kode-referensi" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Kode Referensi dan/atau Data Induk'])
        </div>

    </div>

    <!-- JavaScript to toggle sections -->
    <script>
        function showSection(sectionId) {
            // Only proceed if a section is selected
            if (!sectionId) return;

            // Save selected section in localStorage
            localStorage.setItem('selectedSection', sectionId);

            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.add('hidden');
            });

            // Show the selected section
            document.getElementById(sectionId).classList.remove('hidden');
        }

        // On page load
        window.addEventListener('DOMContentLoaded', () => {
            const savedSection = localStorage.getItem('selectedSection');

            // Set the dropdown value to saved section or empty
            document.getElementById('section-select').value = savedSection || '';

            // Only show a section if there's a saved selection
            if (savedSection) {
                document.getElementById(savedSection).classList.remove('hidden');
            }
        });
    </script>
</x-filament::page>
