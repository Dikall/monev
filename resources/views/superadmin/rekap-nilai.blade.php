@extends('components.layouts.app')

@section('content')
<div class="mx-auto mt-10 mb-10 px-2 sm:px-10 lg:px-6"
     x-data="{
        showModalBobot: false,
        showModalPresentasi: false,
        selectedBody: null,
        selectedBodyId: null,
        currentNilaiPresentasi: 0,
        selectedFileName: '',

        search: '{{ request('search') }}',
        sortOrder: 'asc',
        filterKualifikasi: '',
        page: 1,
        perPage: 10,
        items: {{ json_encode($bodies) }},

        get filteredItems() {
            let filtered = this.items.filter(i => {
                const matchesSearch = this.search === '' || i.nama_badan.toLowerCase().includes(this.search.toLowerCase());
                const matchesQual = this.filterKualifikasi === '' || i.kualifikasi === this.filterKualifikasi;
                return matchesSearch && matchesQual;
            });

            if (this.sortOrder === 'asc') {
                filtered.sort((a, b) => a.nama_badan.localeCompare(b.nama_badan));
            } else if (this.sortOrder === 'desc') {
                filtered.sort((a, b) => b.nama_badan.localeCompare(a.nama_badan));
            }
            
            return filtered;
        },

        shouldShow(id) {
            let index = this.filteredItems.findIndex(i => i.id === id);
            if (index === -1) return false;
            let start = (this.page - 1) * this.perPage;
            return index >= start && index < start + this.perPage;
        },

        openPresentasiModal(body) {
            this.selectedBody = body;
            this.selectedBodyId = body.id;
            this.currentNilaiPresentasi = body.nilai_presentasi || 0;
            this.selectedFileName = '';
            this.showModalPresentasi = true;
        }
     }">

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-green-100 border border-green-200 px-6 py-4 text-green-800 flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-xl bg-red-100 border border-red-200 px-6 py-4 text-red-800 flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-red-100 border border-red-200 px-6 py-4 text-red-800 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="font-bold">Terjadi Kesalahan!</span>
            </div>
            <ul class="list-disc list-inside text-sm ml-8">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter Section --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm mb-8">
        <form action="{{ route('superadmin.rekap-nilai.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div>
                <select name="tahun_id" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:ring-red-500 focus:border-red-500 text-gray-500">
                    @foreach($tahuns as $t)
                        <option value="{{ $t->id }}" {{ (request('tahun_id') == $t->id || (!request('tahun_id') && $tahunAktif && $tahunAktif->id == $t->id)) ? 'selected' : '' }}>
                            {{ $t->tahun }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="kategori_id" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:ring-red-500 focus:border-red-500 text-gray-500">
                    <option value="">Pilih Kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="tanggal_publish" value="{{ request('tanggal_publish') }}"
                       placeholder="dd/mm/yy"
                       class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:ring-red-500 focus:border-red-500 text-gray-500">
            </div>
            <div>
                <button type="submit" class="w-full bg-red-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-800 transition-colors shadow-md">
                    Tampilkan Pertanyaan
                </button>
            </div>
        </form>
    </div>

    {{-- Table Section --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        {{-- Table Header Bar --}}
        <div class="px-6 pt-5 pb-4 flex items-end justify-between gap-4">
            {{-- Kiri: Judul + Search --}}
            <div class="flex flex-col gap-2">
                <h2 class="text-base font-bold text-gray-800">Rekap Nilai Badan Publik</h2>
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Cari badan publik..."
                           class="w-72 rounded-lg border border-gray-300 pl-9 pr-4 py-2 text-sm focus:ring-red-500 focus:border-red-500">
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            {{-- Kanan: Set Bobot --}}
            <button @click="showModalBobot = true"
                    class="inline-flex items-center gap-2 bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-800 transition-all shadow-sm whitespace-nowrap mb-0.5">
                Set Bobot Nilai
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-red-700 text-white text-xs font-bold">
                    <tr>
                        <th class="px-4 py-3 whitespace-nowrap">Nama Badan Publik</th>
                        <th class="px-4 py-3 whitespace-nowrap">Nama Responden</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Nilai SAQ</th>

                        @if(request('kategori_id'))
                            @foreach($indikators as $ind)
                                <th class="px-4 py-3 text-center whitespace-nowrap">
                                    Indikator {{ $ind->no }}
                                </th>
                            @endforeach
                            <th class="px-4 py-3 text-center whitespace-nowrap">Nilai Juri</th>
                        @endif

                        <th class="px-4 py-3 text-center whitespace-nowrap">Presentasi</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Total</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Total Score</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Kualifikasi</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Waktu Publish</th>
                        <th class="px-3 py-3 text-center whitespace-nowrap w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($bodies as $row)
                        <tr x-show="shouldShow({{ $row['id'] }})" x-transition
                            class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 font-medium text-gray-900 max-w-[180px]">{{ $row['nama_badan'] }}</td>
                            <td class="px-4 py-4 text-gray-600 whitespace-nowrap">{{ $row['nama_responden'] }}</td>
                            <td class="px-4 py-4 text-center font-semibold text-gray-800">{{ $row['nilai_saq'] }}</td>

                            @if(request('kategori_id'))
                                @foreach($indikators as $ind)
                                    <td class="px-4 py-4 text-center text-gray-600">
                                        {{ $row['nilai_per_ind'][$ind->id] ?? 0 }}
                                    </td>
                                @endforeach
                                <td class="px-4 py-4 text-center text-gray-600">{{ $row['nilai_juri'] ?? 0 }}</td>
                            @endif

                            <td class="px-4 py-4 text-center">
                                @if($row['nilai_presentasi'] !== null)
                                    <span class="inline-flex items-center gap-1 text-gray-800 font-semibold">
                                        {{ $row['nilai_presentasi'] }}
                                        @if($row['file_bukti'])
                                            <a href="{{ asset('storage/' . $row['file_bukti']) }}" target="_blank" title="Lihat Bukti">
                                                <svg class="w-4 h-4 text-blue-500 hover:text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.414a6 6 0 108.486 8.486L20.5 13"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">0</span>
                                @endif
                            </td>

                            {{-- Total (sama dengan nilai SAQ jika belum ada presentasi) --}}
                            <td class="px-4 py-4 text-center text-gray-700 font-semibold">{{ $row['total_score'] }}</td>

                            <td class="px-4 py-4 text-center font-bold text-gray-900">{{ $row['total_score'] }}</td>

                            <td class="px-4 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-[11px] font-semibold {{ $row['bg_class'] }}">
                                    {{ $row['kualifikasi'] }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                @if($row['is_published'] && $row['waktu_publish'] != '-')
                                    <span class="text-gray-700 font-medium text-xs">{{ $row['waktu_publish'] }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-center">
                                <div class="flex flex-col items-center gap-1.5">
                                    <a href="{{ route('admin.verifikasi', $row['id']) }}"
                                       class="w-full px-2.5 py-1 text-[11px] font-semibold text-white bg-red-700 rounded-md hover:bg-red-800 transition-colors text-center leading-tight whitespace-nowrap">
                                        Detail Verifikasi
                                    </a>
                                    <button @click="openPresentasiModal({{ json_encode($row) }})"
                                            class="w-full px-2.5 py-1 text-[11px] font-semibold text-red-700 border border-red-700 rounded-md hover:bg-red-50 transition-colors text-center leading-tight whitespace-nowrap">
                                        Tambah Presentasi
                                    </button>
                                    @if($row['is_published'])
                                        <form action="{{ route('superadmin.rekap-nilai.reset-publish', $row['id']) }}" method="POST" class="w-full" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan publikasi nilai ini?')">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full px-2.5 py-1 text-[11px] font-semibold text-orange-600 border border-orange-400 rounded-md hover:bg-orange-50 transition-colors leading-tight whitespace-nowrap">
                                                Unpublish
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 11 + (request('kategori_id') ? count($indikators) + 1 : 0) }}" class="px-6 py-12 text-center text-gray-400 italic">
                                Tidak ada data yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="text-xs text-gray-500">
                Menampilkan <span x-text="Math.min((page - 1) * perPage + 1, filteredItems.length)"></span>
                sampai <span x-text="Math.min(page * perPage, filteredItems.length)"></span>
                dari <span x-text="filteredItems.length"></span> data
            </div>
            <div class="flex items-center gap-1">
                <button @click="page--" :disabled="page === 1"
                        class="p-2 border rounded-lg disabled:opacity-30 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <template x-for="p in Math.ceil(filteredItems.length / perPage)">
                    <button @click="page = p"
                            :class="page === p ? 'bg-red-700 text-white border-red-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-red-50'"
                            class="px-3 py-1 text-xs border rounded-lg transition-all font-semibold"
                            x-text="p"></button>
                </template>
                <button @click="page++" :disabled="page === Math.ceil(filteredItems.length / perPage)"
                        class="p-2 border rounded-lg disabled:opacity-30 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal: Set Bobot Nilai --}}
    <div x-show="showModalBobot"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-cloak>
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl" @click.away="showModalBobot = false">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Set Bobot Nilai</h3>
                <button @click="showModalBobot = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('superadmin.rekap-nilai.update-bobot') }}" method="POST" class="px-6 py-6">
                @csrf
                <input type="hidden" name="tahun_id" value="{{ $tahunAktif->id ?? '' }}">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bobot Nilai SAQ (%)</label>
                        <input type="number" name="bobot_saq" value="{{ $tahunAktif->bobot_saq ?? '' }}"
                               placeholder="0,00"
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-red-500 focus:border-red-500 placeholder-gray-400" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bobot Nilai Presentasi/Visitasi (%)</label>
                        <input type="number" name="bobot_presentasi" value="{{ $tahunAktif->bobot_presentasi ?? '' }}"
                               placeholder="0,00"
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-red-500 focus:border-red-500 placeholder-gray-400" required>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="px-8 py-3 bg-red-700 text-white rounded-xl font-semibold hover:bg-red-800 shadow-md transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Nilai Presentasi/Visitasi --}}
    <div x-show="showModalPresentasi"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-cloak>
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl" @click.away="showModalPresentasi = false">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Nilai Presentasi/Visitasi</h3>
                <button @click="showModalPresentasi = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form :action="'{{ route('superadmin.rekap-nilai.update-presentasi', ':id') }}'.replace(':id', selectedBodyId)"
                  method="POST" enctype="multipart/form-data" class="px-6 py-6">
                @csrf
                <input type="hidden" name="tahun_id" value="{{ $tahunAktif->id ?? '' }}">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Presentasi/Visitasi</label>
                        <input type="number" step="0.01" name="nilai_presentasi" x-model="currentNilaiPresentasi"
                               placeholder="0,00"
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-red-500 focus:border-red-500 placeholder-gray-400" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dokumen Presentasi</label>
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center gap-2 px-5 py-3 bg-red-700 text-white rounded-xl text-sm font-semibold cursor-pointer hover:bg-red-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Pilih File
                                <input type="file" name="file_bukti" class="hidden"
                                       @change="selectedFileName = $event.target.files[0]?.name || ''">
                            </label>
                            <div class="text-sm text-gray-500">
                                <span x-text="selectedFileName || 'Tidak ada file yang dipilih'"></span>
                                <p class="text-xs text-red-500 mt-0.5">*maksimal ukuran file 2MB</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="px-8 py-3 bg-red-700 text-white rounded-xl font-semibold hover:bg-red-800 shadow-md transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection