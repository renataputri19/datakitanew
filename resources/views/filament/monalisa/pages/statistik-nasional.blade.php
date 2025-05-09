<x-filament::page>
    <div class="space-y-4">
        <!-- Title -->
        {{-- <h1 class="text-2xl font-bold">1. Manajemen Perubahan - Pemenuhan</h1> --}}

        <!-- Dropdown -->
        <label for="section-select" class="block text-sm font-medium text-gray-900 dark:text-white">Pilih Aspek</label>
        <select id="section-select"
            class="block w-full mt-2 p-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-md shadow-sm"
            onchange="showSection(this.value)">
            <option value="pemanfaatan-data">i. Pemanfaatan Data Statistik</option>
            <option value="pengelolaan-kegiatan">ii. Pengelolaan Kegiatan Statistik</option>
            <option value="penguatan-ssn">iii. Penguatan SSN Berkelanjutan</option>
        </select>





        <!-- Sections -->
        <div id="pemanfaatan-data" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Pemanfaatan Data Statistik'])
        </div>
        <div id="pengelolaan-kegiatan" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Pengelolaan Kegiatan Statistik'])
        </div>
        <div id="penguatan-ssn" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Penguatan SSN Berkelanjutan'])
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
