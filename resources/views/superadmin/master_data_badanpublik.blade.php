@extends('components.layouts.app')

@section('content')

<div class="p-6"
     x-data="{
        openTambah: false,
        openEdit: false,
        openDelete: false,
        bodyId: null,
        bodyNama: '',
        bodyKategori: '',
        search: ''
     }">

    <div class="mb-8">
        <h1 class="text-xl font-bold mb-4">
            Daftar Badan Publik Provinsi Kalimantan Barat
        </h1>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <input 
                    type="text"
                    x-model="search"
                    placeholder="Filter Badan Publik..."
                    class="w-full md:w-64 border rounded-lg p-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-700"
                >
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <span class="text-xs text-gray-500">Tampilkan:</span>
                    <select onchange="window.location.href = this.value" class="border rounded-lg p-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-red-700">
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => 10, 'page' => 1]) }}" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => 25, 'page' => 1]) }}" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => 50, 'page' => 1]) }}" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => 100, 'page' => 1]) }}" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="text-xs text-gray-500">data</span>
                </div>
            </div>

            <button 
                @click="openTambah = true"
                class="px-6 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 flex items-center gap-2 w-full md:w-auto justify-center">

                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="h-5 w-5" fill="none" 
                     viewBox="0 0 24 24" 
                     stroke="currentColor">
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          stroke-width="2" 
                          d="M12 4v16m8-8H4"/>
                </svg>

                Tambah Badan Publik
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-700 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white border shadow rounded-lg overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-red-700 text-white">
                <tr>
                    <th class="px-6 py-3 text-left">Kategori</th>
                    <th class="px-6 py-3 text-left">Nama Badan Publik</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($publicBodies as $item)
                <tr x-show="'{{ strtolower($item->nama_badan) }}'.includes(search.toLowerCase())"
                    class="hover:bg-gray-50">

                    <td class="px-6 py-4">
                        {{ $item->kategori->name ?? '-' }}
                    </td>

                    <td class="px-6 py-4 font-medium">
                        {{ $item->nama_badan }}
                    </td>

                    <td class="px-6 py-4 text-center">
                        @if($item->is_registered)
                            <span class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded-full font-medium">
                                Terdaftar
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs bg-gray-100 text-gray-600 rounded-full font-medium">
                                Belum Daftar
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-center">
                        <div class="flex flex-col gap-1 items-center">
                            <button
                                @click="
                                    openEdit = true;
                                    bodyId = {{ $item->id }};
                                    bodyNama = '{{ addslashes($item->nama_badan) }}';
                                    bodyKategori = '{{ $item->kategori_id }}';
                                "
                                class="w-28 px-4 py-1 bg-red-700 text-white rounded hover:bg-red-800">
                                Edit
                            </button>

                            <button
                                @click="
                                    openDelete = true;
                                    bodyId = {{ $item->id }};
                                "
                                class="w-28 px-4 py-1 border border-red-700 text-red-700 rounded hover:bg-red-50">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                        Belum ada data
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 flex flex-col sm:flex-row justify-between items-center gap-4 border-t text-sm text-gray-600">
            <div>
                Menampilkan {{ $publicBodies->firstItem() ?? 0 }} sampai {{ $publicBodies->lastItem() ?? 0 }} dari {{ $publicBodies->total() }} data
            </div>
            <div class="flex items-center gap-1 text-sm">

                {{-- PREV --}}
                @if ($publicBodies->onFirstPage())
                    <span class="px-3 py-1 border rounded text-gray-400">Prev</span>
                @else
                    <a href="{{ $publicBodies->previousPageUrl() }}"
                    class="px-3 py-1 border rounded hover:bg-gray-100">
                    Prev
                    </a>
                @endif

                {{-- NUMBER --}}
                @for ($i = max(1, $publicBodies->currentPage() - 2); 
                    $i <= min($publicBodies->lastPage(), $publicBodies->currentPage() + 2); 
                    $i++)

                @if ($i == $publicBodies->currentPage())
                    <span class="px-3 py-1 bg-red-700 text-white rounded">
                        {{ $i }}
                    </span>
                @else
                    <a href="{{ $publicBodies->url($i) }}"
                    class="px-3 py-1 border rounded hover:bg-gray-100">
                    {{ $i }}
                    </a>
                @endif

                @endfor

                {{-- NEXT --}}
                @if ($publicBodies->hasMorePages())
                    <a href="{{ $publicBodies->nextPageUrl() }}"
                    class="px-3 py-1 border rounded hover:bg-gray-100">
                    Next
                    </a>
                @else
                    <span class="px-3 py-1 border rounded text-gray-400">Next</span>
                @endif

            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div x-show="openTambah" x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">
            <div class="flex justify-between mb-6">
                <h2 class="text-2xl font-bold">Tambah Badan Publik</h2>
                <button @click="openTambah=false">✕</button>
            </div>
            <form action="{{ route('superadmin.bpublik.store') }}" method="POST">
                @csrf

                <label class="block mb-2 font-medium">
                    Kategori <span class="text-red-600">*</span>
                </label>
                <select name="kategori_id" class="w-full border p-3 mb-4 rounded-lg" required>
                    <option value="">Pilih Kategori</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}">
                            {{ $kategori->name }}
                        </option>
                    @endforeach
                </select>

                <label class="block mb-2 font-medium">
                    Nama Badan Publik <span class="text-red-600">*</span>
                </label>
                <input type="text" name="nama_badan"
                       class="w-full border p-3 mb-4 rounded-lg"
                       placeholder="Nama Badan Publik"
                       required>

                <div class="text-right">
                    <button class="px-6 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div x-show="openEdit" x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">
            <div class="flex justify-between mb-6">
                <h2 class="text-2xl font-bold">Edit Badan Publik</h2>
                <button @click="openEdit=false">✕</button>
            </div>

            <form :action="'/superadmin/bpublik/' + bodyId" method="POST">
                @csrf
                @method('PUT')

                <label class="block mb-2 font-medium">Kategori</label>
                <select name="kategori_id"
                        x-model="bodyKategori"
                        class="w-full border p-3 mb-4 rounded-lg">
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}">
                            {{ $kategori->name }}
                        </option>
                    @endforeach
                </select>

                <label class="block mb-2 font-medium">Nama Badan Publik</label>
                <input type="text"
                       name="nama_badan"
                       x-model="bodyNama"
                       class="w-full border p-3 mb-4 rounded-lg">

                <div class="text-right">
                    <button class="px-6 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DELETE --}}
    <div x-show="openDelete" x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">
            <h2 class="text-xl font-bold mb-4">Hapus Data Badan Publik?</h2>
            <p class="text-gray-600 mb-6 text-sm">
                Data badan publik akan dihapus dari master data.
                Akun yang sudah terdaftar tidak akan ikut terhapus.
            </p>

            <div class="flex justify-end gap-4">
                <button @click="openDelete=false"
                        class="px-4 py-2 border border-red-700 text-red-700 rounded-lg hover:bg-red-50">
                    Batal
                </button>

                <form :action="'/superadmin/bpublik/' + bodyId" method="POST">
                    @csrf
                    @method('DELETE')

                    <button class="px-4 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection