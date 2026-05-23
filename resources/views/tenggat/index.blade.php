@extends('components.layouts.app')

@section('content')

<div class="p-6"
     x-data="{
        openTambah:false,
        openEdit:false,
        openDelete:false,
        tenggatId:null,
        kategoriId:null,
        kategoriName:'',
        tanggalAktif:'',
        jamAktif:'',
        tanggalNonaktif:'',
        jamNonaktif:''
     }">

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-4">Daftar Tenggat</h1>

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

                Tambah Tenggat
            </button>
        </div>
    </div>

    {{-- ALERT --}}
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

    {{-- TABLE --}}
    <div class="bg-white border rounded-lg shadow overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-red-700 text-white">
                <tr>
                    <th class="px-4 py-3 text-left">No</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-left">Waktu Aktif</th>
                    <th class="px-4 py-3 text-left">Waktu Nonaktif</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($tenggats as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-medium">{{ $item->kategori->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        {{ \Carbon\Carbon::parse($item->waktu_aktif)->format('d M Y H:i') }}
                    </td>
                    <td class="px-4 py-3">
                        {{ \Carbon\Carbon::parse($item->waktu_nonaktif)->format('d M Y H:i') }}
                    </td>
                    <td class="px-4 py-3">
                        @if($item->status == 'Aktif')
                            <span class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded-full font-medium">
                                Aktif
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs bg-gray-200 text-gray-700 rounded-full font-medium">
                                Tidak Aktif
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex flex-col gap-1 items-center">
                            {{-- EDIT --}}
                            <button
                                @click="
                                    openEdit=true;
                                    tenggatId={{ $item->id }};
                                    kategoriId={{ $item->kategori_id }};
                                    kategoriName='{{ addslashes($item->kategori->name ?? '') }}';
                                    tanggalAktif='{{ \Carbon\Carbon::parse($item->waktu_aktif)->format('Y-m-d') }}';
                                    jamAktif='{{ \Carbon\Carbon::parse($item->waktu_aktif)->format('H:i:s') }}';
                                    tanggalNonaktif='{{ \Carbon\Carbon::parse($item->waktu_nonaktif)->format('Y-m-d') }}';
                                    jamNonaktif='{{ \Carbon\Carbon::parse($item->waktu_nonaktif)->format('H:i:s') }}';
                                "
                                class="px-4 py-1 bg-red-700 text-white rounded hover:bg-red-800 w-28">
                                Edit
                            </button>

                            {{-- DELETE --}}
                            <button
                                @click="openDelete=true; tenggatId={{ $item->id }};"
                                class="px-4 py-1 border border-red-700 text-red-700 rounded hover:bg-red-50 w-28">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                        Belum ada data tenggat
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

        <div @click.away="openTambah = false"
            class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Tambah Tenggat</h2>
                <button @click="openTambah = false">✕</button>
            </div>

            <form action="{{ route('superadmin.tenggat.store') }}" method="POST">
                @csrf

                <label class="block mb-2 font-medium">
                    Kategori <span class="text-red-600">*</span>
                </label>
                <select name="kategori_id"
                    class="w-full border rounded-lg p-3 mb-6"
                    required>
                    <option value="">Pilih Kategori</option>
                    @foreach($kategoris as $kategori)
                        <option value="{{ $kategori->id }}">
                            {{ $kategori->name }}
                        </option>
                    @endforeach
                </select>

                <label class="block mb-2 font-medium">Waktu Aktif</label>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <input type="date" name="tanggal_aktif"
                        class="border rounded-lg p-3" required>

                    <input type="text" name="jam_aktif"
                        id="jam_aktif_tambah"
                        placeholder="HH:MM:SS"
                        class="border rounded-lg p-3" required>
                </div>

                <label class="block mb-2 font-medium">Waktu Nonaktif</label>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <input type="date" name="tanggal_nonaktif"
                        class="border rounded-lg p-3" required>

                    <input type="text" name="jam_nonaktif"
                        id="jam_nonaktif_tambah"
                        placeholder="HH:MM:SS"
                        class="border rounded-lg p-3" required>
                </div>

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
            class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">

            <div @click.away="openEdit = false"
                class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Edit Tenggat</h2>
                    <button @click="openEdit = false">✕</button>
                </div>

            <form :action="'/superadmin/tenggat/' + tenggatId" method="POST">
                @csrf
                @method('PUT')

                <label class="block mb-2 font-medium">Kategori</label>

                <input type="hidden" name="kategori_id" :value="kategoriId">

                <input type="text"
                    x-model="kategoriName"
                    class="w-full border rounded-lg p-3 mb-6 bg-gray-100"
                    readonly>

                <label class="block mb-2 font-medium">Waktu Aktif</label>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <input type="date" name="tanggal_aktif"
                        x-model="tanggalAktif"
                        class="border rounded-lg p-3" required>

                    <input type="text" name="jam_aktif"
                        id="jam_aktif_edit"
                        x-model="jamAktif"
                        placeholder="HH:MM:SS"
                        class="border rounded-lg p-3" required>
                </div>

                <label class="block mb-2 font-medium">Waktu Nonaktif</label>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <input type="date" name="tanggal_nonaktif"
                        x-model="tanggalNonaktif"
                        class="border rounded-lg p-3" required>

                    <input type="text" name="jam_nonaktif"
                        id="jam_nonaktif_edit"
                        x-model="jamNonaktif"
                        placeholder="HH:MM:SS"
                        class="border rounded-lg p-3" required>
                </div>

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

        <div @click.away="openDelete = false"
            class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Hapus Tenggat</h2>
                <button @click="openDelete = false">✕</button>
            </div>

            <p class="mb-8 text-lg">
                Apakah Anda yakin ingin menghapus tenggat ini?
            </p>

            <div class="flex justify-end gap-4">
                <button @click="openDelete = false"
                    class="px-8 py-2 border border-red-700 text-red-700 rounded-lg">
                    Batal
                </button>

                <form :action="'/superadmin/tenggat/' + tenggatId" method="POST">
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function force24Hour(input) {
                input.setAttribute('lang', 'id-ID');
                if (input._x_model === undefined) {
                    input.addEventListener('change', function () {
                        const val = this.value;
                        if (val && val.includes('M')) {
                            const [time, period] = val.split(' ');
                            let [hours, minutes, seconds] = time.split(':');
                            hours = parseInt(hours);
                            if (period === 'PM' && hours !== 12) hours += 12;
                            if (period === 'AM' && hours === 12) hours = 0;
                            this.value = `${String(hours).padStart(2,'0')}:${minutes}:${seconds || '00'}`;
                        }
                    });
                }
            }

            document.querySelectorAll('input[type="time"]').forEach(force24Hour);

            const observer = new MutationObserver(() => {
                document.querySelectorAll('input[type="time"]').forEach(force24Hour);
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>

</div>

@endsection