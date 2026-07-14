@extends('components.layouts.app')

@section('content')

<div class="p-6"
     x-data="{
        openTambah:false,
        openEdit:false,
        openDelete:false,

        indikatorId:null,
        indikatorNo:'',
        indikatorNama:'',
        indikatorBobot:'',
        indikatorKeterangan:'',

        indikatorTahun:'',
        indikatorKategori:'',

        bobotSums: @js($bobotSums),
        originalBobot: 0,

        kategorisAll: @js($kategoris),
        kategoris: @js($kategoris),

        filterKategori(reset = true) {
            this.kategoris = this.kategorisAll;
            if (reset) {
                this.indikatorKategori = '';
            }
        }
    }">

    {{-- HEADER --}}
    <div class="mb-8">
        <h1 class="text-xl font-bold mb-4">Daftar Indikator</h1>

        {{-- FILTER --}}
        <form method="GET" class="bg-white border shadow rounded-lg p-4 mb-6">
            <label class="block mb-2 font-medium">Pilih Tahun</label>

            <div class="flex gap-3">
                <select name="tahun_id" class="w-full border rounded-lg p-3">
                    @foreach($tahuns as $t)
                        <option value="{{ $t->id }}" {{ $tahunId == $t->id ? 'selected':'' }}>
                            {{ $t->tahun }}
                        </option>
                    @endforeach
                </select>

                <button class="px-6 bg-primary-dark text-white rounded-lg hover:bg-primary-dark">
                    Tampilkan
                </button>
            </div>
        </form>

        <div class="flex justify-end">
            <button 
                @click="
                    openTambah = true;
                    indikatorTahun = '{{ $tahunId }}';
                    indikatorKategori = '';
                    indikatorBobot = '';
                    filterKategori(true);
                "
                class="px-6 py-2 bg-primary-dark text-white rounded-lg hover:bg-primary-dark">
                Tambah Indikator
            </button>
        </div>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
             class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800 flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="text-green-600 hover:text-green-800">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
             class="mb-4 rounded-lg bg-primary-light px-4 py-3 text-primary-dark flex items-center justify-between">
            <span>{{ session('error') }}</span>
            <button @click="show = false" class="text-primary-main hover:text-primary-dark">&times;</button>
        </div>
    @endif

    {{-- TABLE --}}
    <div class="bg-white border shadow rounded-lg overflow-hidden">
        <table class="min-w-full text-sm table-auto">
            <thead class="bg-primary-dark text-white">
                <tr>
                    <th class="px-4 py-2 text-left">No</th>
                    <th class="px-4 py-2 text-left">Tahun</th>
                    <th class="px-4 py-2 text-left">Kategori</th>
                    <th class="px-4 py-2 text-left">Nama</th>
                    <th class="px-4 py-2 text-left">Bobot</th>
                    <th class="px-4 py-2 text-left">Keterangan</th>
                    <th class="px-4 py-2 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($indikators as $item)
                <tr>
                    <td class="px-4 py-2">{{ $item->no }}</td>
                    <td class="px-4 py-2">{{ $item->tahun->tahun }}</td>
                    <td class="px-4 py-2">{{ $item->kategori->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $item->nama_indikator }}</td>
                    <td class="px-4 py-2">{{ $item->bobot }}</td>
                    <td class="px-4 py-2 max-w-xs truncate">{{ $item->keterangan }}</td>

                    <td class="px-4 py-3 text-center">
                        <div class="flex flex-col gap-1 items-center">
                            {{-- EDIT --}}
                            <button 
                                @click="
                                    openEdit = true;

                                    indikatorId = {{ $item->id }};
                                    indikatorNo = '{{ $item->no }}';
                                    indikatorNama = '{{ $item->nama_indikator }}';
                                    indikatorBobot = '{{ $item->bobot }}';
                                    originalBobot = parseFloat('{{ $item->bobot }}') || 0;
                                    indikatorKeterangan = '{{ $item->keterangan }}';

                                    indikatorTahun = '{{ $item->tahun_id }}';

                                    filterKategori(false);
                                    indikatorKategori = '{{ $item->kategori_id }}';
                                "
                                class="w-28 px-4 py-1 bg-primary-dark text-white rounded hover:bg-primary-dark">
                                Edit
                            </button>

                            {{-- DELETE --}}
                            <button 
                                @click="
                                    openDelete = true;
                                    indikatorId = {{ $item->id }};
                                "
                                class="w-28 px-4 py-1 border border-primary-dark text-primary-dark rounded hover:bg-primary-light">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-6 text-center text-gray-500">
                        Belum ada data indikator
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    {{-- MODAL TAMBAH --}}
    <div x-show="openTambah" x-cloak x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">

            <div class="flex justify-between mb-6">
                <h2 class="text-2xl font-bold">Tambah Indikator</h2>
                <button @click="openTambah=false">✕</button>
            </div>

            <form action="{{ route('superadmin.indikator.store') }}" method="POST">
                @csrf

                <label class="block mb-2 font-medium">Tahun</label>
                <select name="tahun_id"
                    x-model="indikatorTahun"
                    @change="filterKategori(true)"
                    class="w-full border rounded-lg p-3 mb-4">
                    @foreach($tahuns as $t)
                        <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                    @endforeach
                </select>

                <label class="block mb-2 font-medium">Kategori</label>
                <select name="kategori_id"
                    x-model="indikatorKategori"
                    class="w-full border rounded-lg p-3 mb-4">

                    <option value="">Pilih Kategori</option>

                    <template x-for="k in kategoris" :key="k.id">
                        <option :value="k.id" x-text="k.name"></option>
                    </template>
                </select>

                <input name="no" placeholder="No"
                    class="w-full border rounded-lg p-3 mb-4">

                <input name="nama_indikator" placeholder="Nama"
                    class="w-full border rounded-lg p-3 mb-4">

                <input name="bobot" placeholder="Bobot" x-model="indikatorBobot"
                    class="w-full border rounded-lg p-3 mb-1">
                <div class="text-xs mb-5 flex justify-between px-1" x-show="indikatorTahun && indikatorKategori">
                    <span class="text-gray-500">
                        Total bobot terpasang: <strong x-text="(bobotSums[indikatorTahun + '-' + indikatorKategori] || 0) + '%'"></strong>
                    </span>
                    <span :class="((bobotSums[indikatorTahun + '-' + indikatorKategori] || 0) + (parseFloat(indikatorBobot) || 0)) > 100 ? 'text-primary-main font-bold' : 'text-green-600 font-medium'">
                        Proyeksi total: <span x-text="(((bobotSums[indikatorTahun + '-' + indikatorKategori] || 0) + (parseFloat(indikatorBobot) || 0)).toFixed(2).replace(/\.00$/, '')) + '% / 100%'"></span>
                    </span>
                </div>

                <textarea name="keterangan"
                    class="w-full border rounded-lg p-3 mb-6"></textarea>

                <div class="flex justify-end">
                    <button class="px-10 py-2 bg-primary-dark text-white rounded-lg hover:bg-primary-dark">
                        Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>


    {{-- MODAL EDIT --}}
    <div x-show="openEdit" x-cloak x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">

            <div class="flex justify-between mb-6">
                <h2 class="text-2xl font-bold">Edit Indikator</h2>
                <button @click="openEdit=false">✕</button>
            </div>

            <form :action="'/superadmin/indikator/' + indikatorId" method="POST">
                @csrf
                @method('PUT')

                <select name="tahun_id"
                    x-model="indikatorTahun"
                    @change="filterKategori(true)"
                    class="w-full border rounded-lg p-3 mb-4">
                    @foreach($tahuns as $t)
                        <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                    @endforeach
                </select>

                <select name="kategori_id"
                    x-model="indikatorKategori"
                    class="w-full border rounded-lg p-3 mb-4">
                    <template x-for="k in kategoris" :key="k.id">
                        <option :value="k.id" x-text="k.name"></option>
                    </template>
                </select>

                <input name="no" x-model="indikatorNo"
                    class="w-full border rounded-lg p-3 mb-4">

                <input name="nama_indikator" x-model="indikatorNama"
                    class="w-full border rounded-lg p-3 mb-4">

                <input name="bobot" x-model="indikatorBobot"
                    class="w-full border rounded-lg p-3 mb-1">
                <div class="text-xs mb-4 flex justify-between px-1" x-show="indikatorTahun && indikatorKategori">
                    <span class="text-gray-500">
                        Bobot indikator lain: <strong x-text="(((bobotSums[indikatorTahun + '-' + indikatorKategori] || 0) - originalBobot).toFixed(2).replace(/\.00$/, '')) + '%'"></strong>
                    </span>
                    <span :class="(((bobotSums[indikatorTahun + '-' + indikatorKategori] || 0) - originalBobot) + (parseFloat(indikatorBobot) || 0)) > 100 ? 'text-primary-main font-bold' : 'text-green-600 font-medium'">
                        Proyeksi total: <span x-text="((((bobotSums[indikatorTahun + '-' + indikatorKategori] || 0) - originalBobot) + (parseFloat(indikatorBobot) || 0)).toFixed(2).replace(/\.00$/, '')) + '% / 100%'"></span>
                    </span>
                </div>

                <textarea name="keterangan" x-model="indikatorKeterangan"
                    class="w-full border rounded-lg p-3 mb-6"></textarea>

                <div class="flex justify-end">
                    <button class="px-10 py-2 bg-primary-dark text-white rounded-lg hover:bg-primary-dark">
                        Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>


    {{-- MODAL DELETE --}}
    <div x-show="openDelete" x-cloak x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/3 p-8">

            <div class="flex justify-between mb-6">
                <h2 class="text-2xl font-bold">Hapus Indikator</h2>
                <button @click="openDelete=false">✕</button>
            </div>

            <p class="mb-8 text-lg">
                Yakin ingin menghapus indikator ini?
            </p>

            <div class="flex justify-end gap-4">
                <button @click="openDelete=false"
                    class="px-8 py-2 border border-primary-dark text-primary-dark rounded-lg">
                    Batal
                </button>

                <form :action="'/superadmin/indikator/' + indikatorId" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="tahun_id" value="{{ $tahunId }}">

                    <button class="px-8 py-2 bg-primary-dark text-white rounded-lg hover:bg-primary-dark">
                        Hapus
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>

@endsection