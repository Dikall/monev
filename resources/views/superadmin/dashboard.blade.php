@extends('components.layouts.app')

@section('content')
<div class="bg-white min-h-screen">

    <div class="bg-white px-6 py-8">
        <div class="mx-auto max-w-6xl">
            <form action="{{ route('superadmin.dashboard') }}" method="GET">
                <div class="border border-gray-300 rounded-lg p-6 flex flex-col gap-4">

                    <!-- Input Tahun -->
                    <select name="tahun_id" id="tahun_id"
                        class="w-full bg-transparent border border-gray-300 rounded-lg px-4 py-3 text-black text-base focus:outline-none focus:border-gray-400 cursor-pointer appearance-none">
                        @foreach($tahuns as $t)
                            <option value="{{ $t->id }}" 
                                class="bg-white text-black"
                                {{ $selectedTahunId == $t->id ? 'selected' : '' }}>
                                {{ $t->tahun }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Tombol Submit -->
                    <button type="submit"
                        class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold text-base py-3 rounded-lg transition-colors duration-150">
                        Tampilkan Kategori
                    </button>

                </div>
            </form>
        </div>
    </div>

    <!-- Category List -->
    <div class="mx-auto max-w-6xl px-6 py-4">
        <div class="divide-y divide-gray-100">
            @foreach($stats as $item)
            <div class="flex flex-col lg:flex-row lg:items-center justify-between py-8 gap-6">

                <!-- Category Name -->
                <div class="lg:w-2/5">
                    <h2 class="text-sm font-semibold text-gray-800 uppercase leading-snug tracking-wide">
                        {{ $item->kategori }}
                    </h2>
                </div>

                <!-- Stats Cards -->
                <div class="lg:w-3/5 grid grid-cols-3 gap-3">

                    <!-- Akun Terdaftar -->
                    <div class="border border-gray-200 rounded-xl p-4 flex flex-col gap-3">
                        <span class="text-xs text-gray-400">Akun Terdaftar</span>
                        <span class="text-2xl font-bold text-gray-800">{{ $item->terdaftar }}</span>
                    </div>

                    <!-- Terverifikasi -->
                    <div class="border border-gray-200 rounded-xl p-4 flex flex-col gap-3">
                        <span class="text-xs text-gray-400">Terverifikasi</span>
                        <span class="text-2xl font-bold text-gray-800">{{ $item->terverifikasi }}</span>
                    </div>

                    <!-- Sudah Mengisi -->
                    <div class="border border-gray-200 rounded-xl p-4 flex flex-col gap-3">
                        <span class="text-xs text-gray-400">Sudah mengisi</span>
                        <span class="text-2xl font-bold text-gray-800">{{ $item->sudah_mengisi }}</span>
                    </div>

                </div>
            </div>
            @endforeach
        </div>

        @if(count($stats) == 0)
        <div class="mt-20 text-center">
            <p class="text-gray-400 text-sm uppercase tracking-widest">Belum ada data kategori tersedia</p>
        </div>
        @endif
    </div>

</div>
@endsection