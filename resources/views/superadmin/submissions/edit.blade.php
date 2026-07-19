@extends('layouts.superadmin')

@section('title', 'Edit Data Submission - Superadmin - DataKita')

@section('dashboard-content')
<div class="max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="mb-8">
        <nav class="text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="{{ route('superadmin.dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Dashboard</a>
            <span class="mx-1">/</span>
            <a href="{{ route('superadmin.submissions.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Data Submission</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700 dark:text-gray-300">Edit</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Edit Data Submission</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Perbarui data submission survei SIBSTR.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <p class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">Terdapat kesalahan pada input:</p>
            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('superadmin.submissions.update', $submission) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $submission->nama) }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="jabatan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jabatan <span class="text-red-500">*</span></label>
                    <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $submission->jabatan) }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="no_hp" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. HP <span class="text-red-500">*</span></label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $submission->no_hp) }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $submission->email) }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="perusahaan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Perusahaan <span class="text-red-500">*</span></label>
                    <input type="text" id="perusahaan" name="perusahaan" value="{{ old('perusahaan', $submission->perusahaan) }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="jenis_perusahaan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenis Perusahaan <span class="text-red-500">*</span></label>
                    <select id="jenis_perusahaan" name="jenis_perusahaan" required
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Jenis Perusahaan</option>
                        <option value="industri" {{ old('jenis_perusahaan', $submission->jenis_perusahaan) == 'industri' ? 'selected' : '' }}>Industri</option>
                        <option value="non-industri" {{ old('jenis_perusahaan', $submission->jenis_perusahaan) == 'non-industri' ? 'selected' : '' }}>Non-Industri</option>
                    </select>
                </div>
            </div>

            <div class="mt-5">
                <label for="alamat" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat</label>
                <textarea id="alamat" name="alamat" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('alamat', $submission->alamat) }}</textarea>
            </div>

            @if($submission->file_paths && count($submission->file_paths) > 0)
                <div class="mt-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">File yang Diupload</label>
                    <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-2">
                        @foreach($submission->file_paths as $filePath)
                            @php $filename = basename($filePath); @endphp
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <a href="{{ route('superadmin.download.file', $filename) }}" target="_blank"
                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 break-all">{{ $filename }}</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('superadmin.submissions.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Batal</a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
