@extends('components.layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Kelola Tampilan</h1>
        <p class="text-sm text-gray-600">Sesuaikan tampilan dan informasi kontak aplikasi E-Monev.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('superadmin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Branding Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Branding & Media
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo Komisi Informasi</label>
                        @if(isset($settings['logo']))
                            <div class="mb-2">
                                <img src="{{ $settings['logo'] }}" alt="Logo" class="h-20 w-auto rounded border p-1">
                            </div>
                        @endif
                        <input type="file" name="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                        <p class="mt-1 text-xs text-gray-500">Rekomendasi ukuran: 300x100px. Max: 2MB.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Foto Sampul (Homepage)</label>
                        @if(isset($settings['cover']))
                            <div class="mb-2">
                                <img src="{{ $settings['cover'] }}" alt="Cover" class="w-full h-32 object-cover rounded border">
                            </div>
                        @endif
                        <input type="file" name="cover" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                        <p class="mt-1 text-xs text-gray-500">Rekomendasi resolusi: 1920x1080px. Max: 5MB.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Alur Monev (Homepage)</label>
                        @if(isset($settings['alur_monev']))
                            <div class="mb-2">
                                <img src="{{ $settings['alur_monev'] }}" alt="Alur Monev" class="w-full h-32 object-cover rounded border">
                            </div>
                        @endif
                        <input type="file" name="alur_monev" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                        <p class="mt-1 text-xs text-gray-500">Rekomendasi format landscape. Max: 5MB.</p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Informasi Kontak
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea name="alamat" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">{{ $settings['alamat'] ?? '' }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No Telepon</label>
                            <input type="text" name="no_telp" value="{{ $settings['no_telp'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ $settings['email'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                        <input type="url" name="website" value="{{ $settings['website'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" placeholder="https://example.com">
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:col-span-2">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Media Sosial
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                        <input type="url" name="facebook" value="{{ $settings['facebook'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" placeholder="https://facebook.com/yourpage">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                        <input type="url" name="instagram" value="{{ $settings['instagram'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" placeholder="https://instagram.com/yourprofile">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Twitter (X)</label>
                        <input type="url" name="twitter" value="{{ $settings['twitter'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" placeholder="https://twitter.com/yourhandle">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">YouTube</label>
                        <input type="url" name="youtube" value="{{ $settings['youtube'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" placeholder="https://youtube.com/yourchannel">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-red-700 hover:bg-red-800 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition duration-200 transform hover:scale-105">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
