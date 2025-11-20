@extends('layouts.app')

@section('title', 'Form Institusi - DataKita BPS')
@section('description', 'Form pendaftaran institusi dengan validasi client-side')

@push('styles')
<style>
    .field-required::after {
        content: ' *';
        color: #ef4444;
        font-weight: 600;
    }
    
    .error-message {
        display: none;
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    .error-message.show {
        display: block;
    }
    
    .input-error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
    
    .input-success {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
    }
    
    .form-container {
        max-width: 600px;
        margin: 0 auto;
        padding: 2rem;
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .dark .form-container {
        background: #1f2937;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 py-12 px-4 sm:px-6 lg:px-8">
    <div class="form-container">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                Form Institusi
            </h1>
            <p class="text-gray-600 dark:text-gray-300">
                Silakan isi formulir berikut. Field yang bertanda <span class="text-red-500 font-semibold">*</span> wajib diisi.
            </p>
        </div>

        <!-- Form -->
        <form id="institutionForm" novalidate>
            @csrf
            
            <!-- Jenis Field (Required) -->
            <div class="mb-6">
                <label for="jenis" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 field-required">
                    Jenis
                </label>
                <select 
                    id="jenis" 
                    name="jenis" 
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200"
                    aria-required="true"
                    aria-describedby="jenis-error"
                >
                    <option value="">Pilih Jenis Institusi</option>
                    <option value="pemerintah">Pemerintah</option>
                    <option value="swasta">Swasta</option>
                    <option value="pendidikan">Pendidikan</option>
                    <option value="organisasi">Organisasi</option>
                    <option value="lainnya">Lainnya</option>
                </select>
                <div id="jenis-error" class="error-message" role="alert">
                    Jenis institusi harus dipilih
                </div>
            </div>

            <!-- Nama Institusi Field (Required) -->
            <div class="mb-6">
                <label for="nama_institusi" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 field-required">
                    Nama Institusi
                </label>
                <input 
                    type="text" 
                    id="nama_institusi" 
                    name="nama_institusi" 
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200"
                    placeholder="Masukkan nama institusi"
                    aria-required="true"
                    aria-describedby="nama_institusi-error"
                >
                <div id="nama_institusi-error" class="error-message" role="alert">
                    Nama institusi harus diisi
                </div>
            </div>

            <!-- Alamat Field (Optional) -->
            <div class="mb-6">
                <label for="alamat" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Alamat
                </label>
                <textarea 
                    id="alamat" 
                    name="alamat" 
                    rows="3"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200"
                    placeholder="Masukkan alamat institusi (opsional)"
                ></textarea>
            </div>

            <!-- Nomor Field (Optional) -->
            <div class="mb-8">
                <label for="nomor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nomor
                </label>
                <input 
                    type="tel" 
                    id="nomor" 
                    name="nomor" 
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200"
                    placeholder="Masukkan nomor telepon (opsional)"
                >
            </div>

            <!-- Submit Button -->
            <div class="flex flex-col sm:flex-row gap-4">
                <button 
                    type="submit" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                >
                    Submit Form
                </button>
                <button 
                    type="reset" 
                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                >
                    Reset Form
                </button>
            </div>
        </form>

        <!-- Success Message -->
        <div id="success-message" class="hidden mt-6 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-medium">Form berhasil dikirim!</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('institutionForm');
    const jenisField = document.getElementById('jenis');
    const namaInstitusiField = document.getElementById('nama_institusi');
    const successMessage = document.getElementById('success-message');
    
    // Required fields
    const requiredFields = [
        { field: jenisField, errorId: 'jenis-error', message: 'Jenis institusi harus dipilih' },
        { field: namaInstitusiField, errorId: 'nama_institusi-error', message: 'Nama institusi harus diisi' }
    ];
    
    // Real-time validation
    requiredFields.forEach(({ field, errorId }) => {
        field.addEventListener('blur', function() {
            validateField(field, errorId);
        });
        
        field.addEventListener('input', function() {
            if (field.classList.contains('input-error')) {
                validateField(field, errorId);
            }
        });
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        
        // Validate all required fields
        requiredFields.forEach(({ field, errorId, message }) => {
            if (!validateField(field, errorId, message)) {
                isValid = false;
            }
        });
        
        if (isValid) {
            // Show success message
            successMessage.classList.remove('hidden');
            successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Reset form after a delay
            setTimeout(() => {
                form.reset();
                clearAllValidation();
                successMessage.classList.add('hidden');
            }, 3000);
        } else {
            // Scroll to first error
            const firstError = document.querySelector('.input-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
    
    // Reset button functionality
    form.addEventListener('reset', function() {
        setTimeout(() => {
            clearAllValidation();
            successMessage.classList.add('hidden');
        }, 10);
    });
    
    function validateField(field, errorId, customMessage = null) {
        const errorElement = document.getElementById(errorId);
        const value = field.value.trim();
        
        if (value === '') {
            showError(field, errorElement, customMessage);
            return false;
        } else {
            showSuccess(field, errorElement);
            return true;
        }
    }
    
    function showError(field, errorElement, customMessage = null) {
        field.classList.add('input-error');
        field.classList.remove('input-success');
        errorElement.classList.add('show');
        
        if (customMessage) {
            errorElement.textContent = customMessage;
        }
    }
    
    function showSuccess(field, errorElement) {
        field.classList.remove('input-error');
        field.classList.add('input-success');
        errorElement.classList.remove('show');
    }
    
    function clearAllValidation() {
        requiredFields.forEach(({ field, errorId }) => {
            const errorElement = document.getElementById(errorId);
            field.classList.remove('input-error', 'input-success');
            errorElement.classList.remove('show');
        });
    }
});
</script>
@endpush
@endsection