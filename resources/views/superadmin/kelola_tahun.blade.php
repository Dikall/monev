@extends('components.layouts.app')

@section('content')

<div class="p-6"
     x-data="{
        openTambah: false,
        openEdit: false,
        openDelete: false,
        tahunId: null,
        tahunValue: ''
     }">

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-4">Daftar Tahun</h1>

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

                Tambah Tahun
            </button>
        </div>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-700 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif


    {{-- TABLE --}}
    <div class="bg-white rounded-lg overflow-hidden shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-red-700 text-white">
                <tr>
                    <th class="px-6 py-3 text-left">No</th>
                    <th class="px-6 py-3 text-left">Tahun</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($tahuns as $item)
                <tr>
                    <td class="px-6 py-4">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4">{{ $item->tahun }}</td>
                    <td class="px-6 py-4 text-center">

                        <button 
                            @click="
                                openEdit = true;
                                tahunId = {{ $item->id }};
                                tahunValue = '{{ $item->tahun }}';
                            "
                            class="px-6 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 flex items-center gap-2 mx-auto">

                            <svg xmlns="http://www.w3.org/2000/svg" 
                                 class="h-5 w-5" fill="none" 
                                 viewBox="0 0 24 24" 
                                 stroke="currentColor">
                                <path stroke-linecap="round" 
                                      stroke-linejoin="round" 
                                      stroke-width="2" 
                                      d="M11 5h2m-1-1v2m-6 4l8-8 4 4-8 8H5v-4z"/>
                            </svg>

                            Edit
                        </button>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-6 text-center text-gray-500">
                        Belum ada data tahun
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    {{-- MODAL TAMBAH --}}
    <div x-show="openTambah" x-cloak x-transition
         class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Tambah Tahun</h2>
                <button @click="openTambah = false">✕</button>
            </div>

            <form action="{{ route('superadmin.tahun.store') }}" method="POST">
                @csrf

                <label class="block mb-2 font-medium">Tahun</label>
                <input type="number"
                       name="tahun"
                       class="w-full border rounded-lg p-3 mb-6"
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
    <div x-show="openEdit" x-cloak x-transition
         class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Edit Tahun</h2>
                <button @click="openEdit = false">✕</button>
            </div>

            <form :action="'/superadmin/tahun/' + tahunId" method="POST">
                @csrf
                @method('PUT')

                <label class="block mb-2 font-medium">Tahun</label>
                <input type="number"
                       name="tahun"
                       x-model="tahunValue"
                       class="w-full border rounded-lg p-3 mb-6"
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

</div>

@endsection
