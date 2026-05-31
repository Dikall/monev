@extends('components.layouts.app')

@section('content')
<div class="max-w-12xl mx-auto mt-10 mb-20 px-6 sm:px-10 lg:px-16"
     x-data="{
        searchMengisi: '',
        searchTidakMengisi: '',
        pageMengisi: 1,
        pageTidakMengisi: 1,
        perPage: 10,
        
        itemsMengisi: {{ json_encode($bodiesMengisi) }},
        itemsTidak: {{ json_encode($bodiesTidakMengisi) }},

        get filteredMengisi() {
            return this.itemsMengisi.filter(i => 
                this.searchMengisi === '' || 
                i.nama_badan.toLowerCase().includes(this.searchMengisi.toLowerCase())
            );
        },

        get filteredTidak() {
            return this.itemsTidak.filter(i => 
                this.searchTidakMengisi === '' || 
                i.nama_badan.toLowerCase().includes(this.searchTidakMengisi.toLowerCase())
            );
        },

        shouldShowMengisi(id) {
            let index = this.filteredMengisi.findIndex(i => i.id === id);
            if (index === -1) return false;
            let start = (this.pageMengisi - 1) * this.perPage;
            return index >= start && index < start + this.perPage;
        },

        shouldShowTidak(id) {
            let index = this.filteredTidak.findIndex(i => i.id === id);
            if (index === -1) return false;
            let start = (this.pageTidakMengisi - 1) * this.perPage;
            return index >= start && index < start + this.perPage;
        }
     }"
     x-init="$watch('searchMengisi', () => pageMengisi = 1); $watch('searchTidakMengisi', () => pageTidakMengisi = 1);">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <a href="{{ route('admin/beranda') }}" class="text-sm text-red-700 hover:text-red-800 inline-flex items-center gap-1 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Beranda
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">
                    List Akun — {{ $kategori->name }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">Tahun {{ $tahun->tahun }}</p>
            </div>
            {{-- Tombol Unduh Rekap Excel --}}
            <a href="{{ route('admin.export-list-akun', $kategori->id) }}"
               id="btn-unduh-rekap-excel"
               class="inline-flex items-center gap-2 rounded-lg bg-red-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-800 active:scale-95 transition-all duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Unduh Semua Rekap
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- ====================================================================
         CONTAINER 1: REKAPAN NILAI — MENGISI KUESIONER
         ==================================================================== --}}
    <div class="mb-10">
        <div class="bg-green-50 border border-grey-200 rounded-xl p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-4">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Rekapan Nilai — Sudah Submit
                        <span class="ml-2 inline-flex items-center rounded-full bg-green-200 px-2.5 py-0.5 text-xs font-medium text-green-800">
                            {{ count($bodiesMengisi) }}
                        </span>
                    </h2>
                    <a href="{{ route('admin.export-list-akun', ['kategori' => $kategori->id, 'type' => 'mengisi']) }}" class="inline-flex items-center gap-1 rounded bg-green-100 px-2.5 py-1.5 text-xs font-semibold text-green-800 hover:bg-green-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Excel
                    </a>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Search --}}
                    <div class="relative">
                        <input type="text"
                               x-model="searchMengisi"
                               placeholder="Cari nama badan publik..."
                               class="w-64 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none">
                        <svg class="absolute right-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        <span class="text-xs text-gray-500">Tampilkan:</span>
                        <select x-model.number="perPage" @change="pageMengisi = 1; pageTidakMengisi = 1" class="rounded-lg border border-gray-300 px-2 py-2 text-sm focus:ring-red-500 focus:border-red-500 bg-white">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs text-gray-500">data</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-sm">
                    <thead class="bg-red-700 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left whitespace-nowrap">Nama Badan Publik</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap">Nama Responden</th>
                            @foreach ($indikators as $ind)
                                <th class="px-4 py-3 text-center whitespace-nowrap" title="{{ $ind->nama_indikator }} (Bobot: {{ $ind->bobot }})">
                                    {{ $ind->nama_indikator }}
                                    <br><span class="text-xs font-normal opacity-80">({{ $ind->bobot }})</span>
                                </th>
                            @endforeach
                            <th class="px-4 py-3 text-center whitespace-nowrap">Nilai Presentasi</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Total</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Total Score</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y bg-white">
                        @forelse ($bodiesMengisi as $row)
                            <tr x-show="shouldShowMengisi({{ $row['id'] }})"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $row['nama_badan'] }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $row['nama_responden'] }}</td>
                                @foreach ($indikators as $ind)
                                    <td class="px-4 py-3 text-center font-semibold">
                                        {{ $row['nilai_per_indikator'][$ind->id] ?? 0 }}
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-center">
                                    @if($row['nilai_presentasi'] !== null)
                                        <span class="font-semibold text-blue-700">{{ $row['nilai_presentasi'] }}</span>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Belum diisi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ $row['total_kuesioner'] }}</td>
                                <td class="px-4 py-3 text-center font-bold">
                                    @if($row['total_score'] !== null)
                                        <span class="text-green-700">{{ $row['total_score'] }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Publish Nilai --}}
                                        <form method="POST" action="{{ route('admin.publish-nilai', $row['body']->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            @if(!$row['can_publish'] && !$row['is_published'])
                                                <button type="button" disabled
                                                        class="rounded px-3 py-1.5 text-xs font-semibold border-2 border-gray-400 bg-gray-400 text-white cursor-not-allowed"
                                                        title="Verifikasi belum selesai atau nilai presentasi belum diisi">
                                                    Publish
                                                </button>
                                            @else
                                                <button type="submit"
                                                        class="rounded px-3 py-1.5 text-xs font-semibold transition-colors
                                                            {{ $row['is_published']
                                                                ? 'bg-red-700 text-white hover:bg-white hover:text-red-700 border-2 border-red-700'
                                                                : 'bg-red-700 text-white hover:bg-white hover:text-red-700 border-2 border-red-700' }}"
                                                        title="{{ $row['is_published'] ? 'Unpublish Nilai' : 'Publish Nilai' }}">
                                                    {{ $row['is_published'] ? 'Published' : 'Publish' }}
                                                </button>
                                            @endif
                                        </form>

                                        {{-- Lakukan Verifikasi --}}
                                        <a href="{{ route('admin.verifikasi', $row['body']->id) }}"
                                           class="rounded bg-white px-3 py-1.5 text-xs font-semibold border-2 border-red-700 text-red-700 hover:bg-red-700 hover:text-white transition-colors">
                                            Verifikasi
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + count($indikators) }}" class="py-8 text-center text-gray-400">
                                    Belum ada badan publik yang mengisi kuesioner
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Mengisi --}}
            <div class="mt-4 flex items-center justify-between" x-show="filteredMengisi.length > 0">
                <div class="text-xs text-gray-500">
                    Menampilkan <span x-text="Math.min((pageMengisi - 1) * perPage + 1, filteredMengisi.length)"></span> 
                    sampai <span x-text="Math.min(pageMengisi * perPage, filteredMengisi.length)"></span> 
                    dari <span x-text="filteredMengisi.length"></span> data
                </div>
                <div class="flex items-center gap-1">
                    <button @click="pageMengisi--" :disabled="pageMengisi === 1" 
                            class="px-2 py-1 text-xs border rounded disabled:opacity-30 hover:bg-gray-100">
                        Prev
                    </button>
                    <template x-for="p in Math.ceil(filteredMengisi.length / perPage)">
                        <button @click="pageMengisi = p" 
                                :class="pageMengisi === p ? 'bg-red-700 text-white' : 'hover:bg-gray-100'"
                                class="px-2 py-1 text-xs border rounded transition-colors" 
                                x-text="p"></button>
                    </template>
                    <button @click="pageMengisi++" :disabled="pageMengisi === Math.ceil(filteredMengisi.length / perPage)" 
                            class="px-2 py-1 text-xs border rounded disabled:opacity-30 hover:bg-gray-100">
                        Next
                    </button>
                </div>
            </div>

            {{-- Keterangan pembobotan --}}
            <div class="mt-3 text-xs text-gray-500">
                <strong>Keterangan:</strong> Total Score = (Total × 70%) + (Nilai Presentasi × 30%)
            </div>
        </div>
    </div>


    {{-- ====================================================================
         CONTAINER 2: REKAPAN NILAI — TIDAK MENGISI KUESIONER
         ==================================================================== --}}
    <div class="mb-10">
        <div class="bg-red-50 border border-red-200 rounded-xl p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-4">
                    <h2 class="text-lg font-semibold text-red-800">
                        Rekapan Nilai — Tidak Mengisi Kuesioner
                        <span class="ml-2 inline-flex items-center rounded-full bg-red-200 px-2.5 py-0.5 text-xs font-medium text-red-800">
                            {{ count($bodiesTidakMengisi) }}
                        </span>
                    </h2>
                    <a href="{{ route('admin.export-list-akun', ['kategori' => $kategori->id, 'type' => 'tidak']) }}" class="inline-flex items-center gap-1 rounded bg-red-100 px-2.5 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Excel
                    </a>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Search --}}
                    <div class="relative">
                        <input type="text"
                               x-model="searchTidakMengisi"
                               placeholder="Cari nama badan publik..."
                               class="w-64 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none">
                        <svg class="absolute right-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        <span class="text-xs text-gray-500">Tampilkan:</span>
                        <select x-model.number="perPage" @change="pageMengisi = 1; pageTidakMengisi = 1" class="rounded-lg border border-gray-300 px-2 py-2 text-sm focus:ring-red-500 focus:border-red-500 bg-white">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs text-gray-500">data</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-sm">
                    <thead class="bg-red-700 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left whitespace-nowrap">Nama Badan Publik</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap">Nama Responden</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Status</th>
                            @foreach ($indikators as $ind)
                                <th class="px-4 py-3 text-center whitespace-nowrap" title="{{ $ind->nama_indikator }} (Bobot: {{ $ind->bobot }})">
                                    {{ $ind->nama_indikator }}
                                    <br><span class="text-xs font-normal opacity-80">({{ $ind->bobot }})</span>
                                </th>
                            @endforeach
                            <th class="px-4 py-3 text-center whitespace-nowrap">Nilai Presentasi</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Total</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Total Score</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y bg-white">
                        @forelse ($bodiesTidakMengisi as $row)
                            <tr x-show="shouldShowTidak({{ $row['id'] }})"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $row['nama_badan'] }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $row['nama_responden'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        // Cek apakah ada jawaban (meski belum submit)
                                        $hasAnyJawaban = collect($row['nilai_per_indikator'])->sum() > 0;
                                    @endphp
                                    @if($hasAnyJawaban)
                                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                            Sedang Mengisi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                            Belum Mulai
                                        </span>
                                    @endif
                                </td>
                                @foreach ($indikators as $ind)
                                    <td class="px-4 py-3 text-center font-semibold text-gray-700">
                                        {{ $row['nilai_per_indikator'][$ind->id] ?? 0 }}
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-center">
                                    @if($row['nilai_presentasi'] !== null)
                                        <span class="font-semibold text-blue-700">{{ $row['nilai_presentasi'] }}</span>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Belum diisi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ $row['total_kuesioner'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($row['total_score'] !== null)
                                        <span class="font-bold text-red-700">{{ $row['total_score'] }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Publish Nilai --}}
                                        <form method="POST" action="{{ route('admin.publish-nilai', $row['body']->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            @if(!$row['can_publish'] && !$row['is_published'])
                                                <button type="button" disabled
                                                        class="rounded px-3 py-1.5 text-xs font-semibold border-2 border-gray-400 bg-gray-400 text-white cursor-not-allowed"
                                                        title="Verifikasi belum selesai atau nilai presentasi belum diisi">
                                                    Publish
                                                </button>
                                            @else
                                                <button type="submit"
                                                        class="rounded px-3 py-1.5 text-xs font-semibold transition-colors
                                                            {{ $row['is_published']
                                                                ? 'bg-red-700 text-white hover:bg-white hover:text-red-700 border-2 border-red-700'
                                                                : 'bg-red-700 text-white hover:bg-white hover:text-red-700 border-2 border-red-700' }}"
                                                        title="{{ $row['is_published'] ? 'Unpublish Nilai' : 'Publish Nilai' }}">
                                                    {{ $row['is_published'] ? 'Published' : 'Publish' }}
                                                </button>
                                            @endif
                                        </form>

                                        {{-- Lakukan Verifikasi --}}
                                        <a href="{{ route('admin.verifikasi', $row['body']->id) }}"
                                           class="rounded bg-white px-3 py-1.5 text-xs font-semibold border-2 border-red-700 text-red-700 hover:bg-red-700 hover:text-white transition-colors">
                                            Verifikasi
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + count($indikators) }}" class="py-8 text-center text-gray-400">
                                    Semua badan publik sudah mengisi kuesioner
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Tidak --}}
            <div class="mt-4 flex items-center justify-between" x-show="filteredTidak.length > 0">
                <div class="text-xs text-gray-500">
                    Menampilkan <span x-text="Math.min((pageTidakMengisi - 1) * perPage + 1, filteredTidak.length)"></span> 
                    sampai <span x-text="Math.min(pageTidakMengisi * perPage, filteredTidak.length)"></span> 
                    dari <span x-text="filteredTidak.length"></span> data
                </div>
                <div class="flex items-center gap-1">
                    <button @click="pageTidakMengisi--" :disabled="pageTidakMengisi === 1" 
                            class="px-2 py-1 text-xs border rounded disabled:opacity-30 hover:bg-gray-100">
                        Prev
                    </button>
                    <template x-for="p in Math.ceil(filteredTidak.length / perPage)">
                        <button @click="pageTidakMengisi = p" 
                                :class="pageTidakMengisi === p ? 'bg-red-700 text-white' : 'hover:bg-gray-100'"
                                class="px-2 py-1 text-xs border rounded transition-colors" 
                                x-text="p"></button>
                    </template>
                    <button @click="pageTidakMengisi++" :disabled="pageTidakMengisi === Math.ceil(filteredTidak.length / perPage)" 
                            class="px-2 py-1 text-xs border rounded disabled:opacity-30 hover:bg-gray-100">
                        Next
                    </button>
                </div>
            </div>

            {{-- Keterangan pembobotan --}}
            <div class="mt-3 text-xs text-gray-500">
                <strong>Keterangan:</strong> Total Score = (Total × 70%) + (Nilai Presentasi × 30%)
            </div>
        </div>
    </div>

</div>
@endsection
