@extends('components.layouts.app')

@section('content')
<div class="max-w-12xl mx-auto mt-10 mb-20 px-6 sm:px-10 lg:px-16">

    <div class="bg-green-100 p-4 rounded-xl mb-4">
        {{-- Header Selamat Datang --}}
        <h1 class="text-xl font-semibold text-gray-800 mb-2">
            Selamat Datang, {{ $admin->name }}
        </h1>

        {{-- Judul Kategori Kuesioner --}}
        <h2 class="text-base font-semibold text-gray-700">Kategori Kuesioner</h2>
    </div>

    {{-- Tombol Download --}}
    <div class="flex justify-end gap-3 mb-6">
        {{-- Export Excel Terformat (baru) --}}
        <a href="{{ route('admin.export-excel-formatted') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-primary-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-dark transition-colors duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download Excel (.xlsx)
        </a>
    </div>

    {{-- Alert --}}
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-primary-light px-4 py-3 text-primary-dark">
            {{ session('error') }}
        </div>
    @endif

    {{-- List Kategori --}}
    @if(!empty($kategoriStats))
        <div class="space-y-4">
            @foreach ($kategoriStats as $stat)
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 py-5 border-b border-gray-200">

                    {{-- Nama Kategori --}}
                    <div class="flex-1 min-w-0 pr-4">
                        <h3 class="text-base font-semibold text-gray-800 uppercase leading-snug">
                            {{ $stat['kategori']->name }}
                        </h3>
                    </div>

                    {{-- Statistik --}}
                    <div class="flex items-center gap-8 flex-shrink-0">

                        {{-- Total Akun yang harus diverifikasi --}}
                        <div class="border border-gray-300 rounded-lg px-4 py-3 min-w-[190px]">
                            <p class="text-xs text-gray-500 mb-1">Total Akun yang harus diverifikasi</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $stat['total_verifikasi'] }}</p>
                        </div>

                        {{-- Jumlah Akun yang sudah mengisi --}}
                        <div class="border border-gray-300 rounded-lg px-4 py-3 min-w-[190px]">
                            <p class="text-xs text-gray-500 mb-1">Jumlah Akun yang sudah mengisi tahun {{ $tahun->tahun ?? now()->year }}</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $stat['total_mengisi'] }}</p>
                        </div>

                        {{-- Tombol List Akun --}}
                        <a href="{{ route('admin.list-akun', $stat['kategori']->id) }}"
                           class="inline-flex items-center justify-center rounded-lg bg-primary-dark px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark transition-colors duration-200 whitespace-nowrap">
                            List Akun
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-16">
            <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3"/>
            </svg>
            <p class="text-gray-500 text-sm">Belum ada kategori badan publik yang di-assign kepada Anda.</p>
        </div>
    @endif

</div>
@endsection
