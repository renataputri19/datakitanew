@extends('layouts.app')

@section('title', 'Setting Antrian - DataKita')
@section('description', 'Halaman pengaturan antrian tamu BPS Kota Batam')

@push('styles')
<style>
    .setting-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .setting-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .setting-header {
        padding: 1.5rem;
        background-color: #f59e0b;
        color: white;
    }

    .dark .setting-header {
        background-color: #d97706;
    }

    .setting-body {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        background-color: white;
    }

    .dark .form-input {
        background-color: #374151;
        border-color: #4b5563;
        color: white;
    }

    .form-input:focus {
        outline: none;
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
    }

    .dark .form-input:focus {
        border-color: #d97706;
        box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.2);
    }

    .color-input {
        width: 100%;
        height: 40px;
        padding: 0;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
    }

    .loket-list {
        margin-top: 1rem;
    }

    .loket-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        padding: 0.75rem;
        background-color: #f3f4f6;
        border-radius: 0.375rem;
    }

    .dark .loket-item {
        background-color: #374151;
    }

    .loket-input {
        flex: 1;
        margin-right: 0.5rem;
    }

    .loket-button {
        padding: 0.5rem;
        border-radius: 0.375rem;
        background-color: #ef4444;
        color: white;
        border: none;
        cursor: pointer;
    }

    .loket-button:hover {
        background-color: #dc2626;
    }

    .add-loket-button {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background-color: #10b981;
        color: white;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        font-weight: 500;
    }

    .add-loket-button:hover {
        background-color: #059669;
    }

    .save-button {
        display: inline-flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        background-color: #f59e0b;
        color: white;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        font-weight: 600;
        font-size: 1rem;
    }

    .save-button:hover {
        background-color: #d97706;
    }

    .logo-preview {
        width: 100px;
        height: 100px;
        object-fit: contain;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        background-color: white;
        margin-top: 0.5rem;
    }

    .dark .logo-preview {
        background-color: #374151;
        border-color: #4b5563;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8 md:px-6 md:py-12">
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('antrian.index') }}" class="inline-flex items-center text-amber-600 dark:text-amber-400 hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="mb-8 text-center" data-aos="fade-up">
        <h1 class="text-3xl font-bold tracking-tight mb-2">Setting Antrian</h1>
        <p class="text-gray-500 dark:text-gray-400">Konfigurasi sistem antrian</p>
    </div>

    <div class="setting-container" data-aos="fade-up" data-aos-delay="100">
        <div class="setting-card bg-white dark:bg-gray-800">
            <div class="setting-header">
                <h2 class="text-2xl font-bold">Pengaturan Antrian</h2>
            </div>
            <div class="setting-body">
                <form id="settingForm">
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200">Pengaturan Loket</h3>

                        <div id="loketList" class="loket-list">
                            <!-- Loket items will be generated dynamically -->
                        </div>

                        <button type="button" id="addLoket" class="add-loket-button mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Tambah Loket
                        </button>
                    </div>

                    <div class="mt-8 text-center">
                        <button type="submit" id="saveSettings" class="save-button">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const settingForm = document.getElementById('settingForm');
        const loketList = document.getElementById('loketList');
        const addLoket = document.getElementById('addLoket');

        // Initialize loket list
        let loketData = [];

        // Load existing lokets
        loadLokets();

        // Handle add loket button
        addLoket.addEventListener('click', function() {
            const newLoketNumber = loketData.length > 0
                ? Math.max(...loketData.map(loket => parseInt(loket.no_loket))) + 1
                : 1;

            // Add new loket to database
            fetch("{{ route('antrian.api.add-loket') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    no_loket: newLoketNumber.toString(),
                    nama_loket: `Loket ${newLoketNumber}`
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload lokets
                    loadLokets();
                } else {
                    alert(data.message || 'Gagal menambahkan loket');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menambahkan loket');
            });
        });

        // Handle form submission
        settingForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate form
            if (loketData.length === 0) {
                alert('Minimal harus ada satu loket');
                return;
            }

            // Prepare data
            const formData = {
                lokets: loketData
            };

            // Save settings
            fetch("{{ route('antrian.api.setting') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pengaturan loket berhasil disimpan');
                    // Reload lokets
                    loadLokets();
                } else {
                    alert(data.message || 'Gagal menyimpan pengaturan loket');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silahkan coba lagi.');
            });
        });

        // Function to load lokets
        function loadLokets() {
            fetch("{{ route('antrian.api.loket') }}")
                .then(response => response.json())
                .then(data => {
                    loketData = data.map(loket => ({
                        id: loket.id, // Include the ID
                        no_loket: loket.no_loket,
                        nama_loket: loket.nama_loket
                    }));

                    if (loketData.length === 0) {
                        // If no lokets exist, create default ones in the database
                        const defaultLokets = [
                            { no_loket: "1", nama_loket: "Loket 1" },
                            { no_loket: "2", nama_loket: "Loket 2" },
                            { no_loket: "3", nama_loket: "Loket 3" }
                        ];

                        // Create default lokets
                        const promises = defaultLokets.map(loket =>
                            fetch("{{ route('antrian.api.add-loket') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(loket)
                            })
                        );

                        Promise.all(promises)
                            .then(() => {
                                // Reload lokets after creating defaults
                                loadLokets();
                            })
                            .catch(error => {
                                console.error('Error creating default lokets:', error);
                            });
                    } else {
                        renderLoketList();
                    }
                })
                .catch(error => {
                    console.error('Error loading lokets:', error);
                    alert('Terjadi kesalahan saat memuat data loket');
                });
        }

        // Function to render loket list
        function renderLoketList() {
            loketList.innerHTML = '';

            loketData.forEach((loket, index) => {
                const loketItem = document.createElement('div');
                loketItem.className = 'loket-item';
                loketItem.innerHTML = `
                    <input type="hidden" value="${loket.id || ''}" id="loket-id-${index}">
                    <div class="loket-input mr-2">
                        <input type="text" class="form-input" value="${loket.no_loket}" placeholder="No Loket" onchange="updateLoketNo(${index}, this.value)">
                    </div>
                    <div class="loket-input">
                        <input type="text" class="form-input" value="${loket.nama_loket}" placeholder="Nama Loket" onchange="updateLoketNama(${index}, this.value)">
                    </div>
                    <button type="button" class="loket-button ml-2" onclick="removeLoket(${index}, '${loket.id || ''}')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                `;

                loketList.appendChild(loketItem);
            });
        }

        // Make functions available globally
        window.updateLoketNo = function(index, value) {
            loketData[index].no_loket = value;
        };

        window.updateLoketNama = function(index, value) {
            loketData[index].nama_loket = value;
        };

        window.removeLoket = function(index, id) {
            if (id) {
                // Delete from database if it exists
                fetch("{{ route('antrian.api.delete-loket') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove from local array
                        loketData.splice(index, 1);
                        renderLoketList();
                    } else {
                        alert(data.message || 'Gagal menghapus loket');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus loket');
                });
            } else {
                // Just remove from local array if it doesn't exist in database yet
                loketData.splice(index, 1);
                renderLoketList();
            }
        };
    });
</script>
@endpush
