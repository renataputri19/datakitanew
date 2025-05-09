<x-filament::page>
    <div class="space-y-4">
        <!-- Title -->
        {{-- <h1 class="text-2xl font-bold">1. Manajemen Perubahan - Pemenuhan</h1> --}}

        <!-- Dropdown -->
        <label for="section-select" class="block text-sm font-medium text-gray-900 dark:text-white">Pilih Aspek</label>
        <select id="section-select"
            class="block w-full mt-2 p-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-md shadow-sm"
            onchange="showSection(this.value)">
            <option value="relevansi">i. Relevansi</option>
            <option value="akurasi">ii. Akurasi</option>
            <option value="aktualitas-dan-ketepatan-waktu">iii. Aktualitas dan Ketepatan Waktu</option>
            <option value="aksesibilitas">iv. Aksesibilitas</option>
            <option value="keterbandingan-dan-konsistensi">v. Keterbandingan dan Konsistensi</option>
        </select>





        <!-- Sections -->
        <div id="relevansi" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Relevansi'])
        </div>
        <div id="akurasi" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Akurasi'])
        </div>
        <div id="aktualitas-dan-ketepatan-waktu" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Aktualitas dan Ketepatan Waktu'])
        </div>
        <div id="aksesibilitas" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Aksesibilitas'])
        </div>
        <div id="keterbandingan-dan-konsistensi" class="section hidden">
            @livewire('domain-template', ['selectedCategory' => 'Keterbandingan dan Konsistensi'])
        </div>

    </div>

    <!-- JavaScript to toggle sections -->
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
