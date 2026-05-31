@extends('components.layouts.app')

@section('content')

<div class="p-6"
     x-data="{
        openTambah: false,
        openEdit: false,
        openDelete: false,
        kategoriId: null,
        kategoriNama: ''
     }">

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-4">Daftar Kategori</h1>

        <div class="flex justify-end">
            <button 
                @click="openTambah = true"
                class="px-6 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 flex items-center gap-2">

                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="h-5 w-5" fill="none" 
                     viewBox="0 0 24 24" 
                     stroke="currentColor">
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          stroke-width="2" 
                          d="M12 4v16m8-8H4"/>
                </svg>

                Tambah Kategori
            </button>
        </div>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div id="successAlert" class="mb-4 bg-green-100 text-green-700 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- TABLE --}}
    <div class="bg-white border shadow rounded-lg overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-red-700 text-white">
                <tr>
                    <th class="px-6 py-3 text-left">No</th>
                    <th class="px-6 py-3 text-left">Nama Kategori</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($kategoris as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4 font-medium">{{ $item->name }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex flex-col gap-1 items-center">
                            {{-- EDIT --}}
                            <button 
                                @click="
                                    openEdit = true;
                                    kategoriId = {{ $item->id }};
                                    kategoriNama = '{{ addslashes($item->name) }}';
                                "
                                class="w-28 px-4 py-1 bg-red-700 text-white rounded hover:bg-red-800">
                                Edit
                            </button>

                            {{-- DELETE --}}
                            <button 
                                @click="
                                    openDelete = true;
                                    kategoriId = {{ $item->id }};
                                "
                                class="w-28 px-4 py-1 border border-red-700 text-red-700 rounded hover:bg-red-50">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-6 text-center text-gray-500">
                        Belum ada data kategori
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    {{-- MODAL TAMBAH --}}
        <div x-show="openTambah"
        x-cloak
        x-transition
        class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Tambah Kategori</h2>
                <button @click="openTambah = false">✕</button>
            </div>

            <form action="{{ route('superadmin.kategori.store') }}" method="POST">
                @csrf

                <label class="block mb-2 font-medium">
                    Nama Kategori
                </label>
                <input type="text"
                    name="name"
                    class="w-full border rounded-lg p-3 mb-6 focus:outline-none focus:ring-1 focus:ring-red-500"
                    placeholder="Contoh: Pemerintah Kabupaten/Kota"
                    required>

                <div class="flex justify-end">
                    <button type="submit"
                        class="px-10 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- MODAL EDIT --}}
    <div x-show="openEdit"
        x-cloak
        x-transition
        class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Edit Kategori</h2>
                <button @click="openEdit = false">✕</button>
            </div>

            <form :action="'/superadmin/kategori/' + kategoriId" method="POST">
                @csrf
                @method('PUT')

                <label class="block mb-2 font-medium">
                    Nama Kategori <span class="text-red-600">*</span>
                </label>
                <input type="text"
                    name="name"
                    x-model="kategoriNama"
                    class="w-full border rounded-lg p-3 mb-6 focus:outline-none focus:ring-1 focus:ring-red-500"
                    required>

                <div class="flex justify-end">
                    <button type="submit"
                        class="px-10 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>



    {{-- MODAL DELETE --}}
    <div x-show="openDelete"
        x-cloak
        x-transition
        class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Hapus Kategori</h2>
                <button @click="openDelete = false">✕</button>
            </div>

            <p class="mb-8 text-lg">
                Apakah Anda yakin ingin menghapus kategori ini?
                Badan publik yang terkait dengan kategori ini juga akan terpengaruh.
            </p>

            <div class="flex justify-end gap-4">
                <button @click="openDelete = false"
                    class="px-8 py-2 border border-red-700 text-red-700 rounded-lg">
                    Batal
                </button>

                <form :action="'/superadmin/kategori/' + kategoriId" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-8 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
