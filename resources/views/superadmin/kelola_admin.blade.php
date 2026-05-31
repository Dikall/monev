@extends('components.layouts.app')

@section('content')

<div
    class="p-6"
    x-data="{
        openTambah: false,
        openEdit: false,
        openSet: false,

        verifikatorId: null,
        nama: '',
        telepon: '',
        email: '',
        username: '',
        originalBodies: [],


        /* ── Multi-select state ── */
        selectedBodies: [],
        selectedKategori: '',

        allBodies: @js($bodies),
        assignedBodyIds: @js($assignedBodyIds),
        filteredBodies: [],

        openKategoriDrop: false,
        openBodyDrop: false,

        get selectedBodyObjects() {
            return this.selectedBodies
                .map(id => this.allBodies.find(b => b.id == id))
                .filter(Boolean);
        },

        /* FIX 3: filterBodies bisa dipanggil kapan saja dan langsung
                  menggunakan this.selectedKategori yang sudah terupdate */
        filterBodies() {
            if (this.selectedKategori === '' || this.selectedKategori === null) {
                this.filteredBodies = [];
                return;
            }
            this.filteredBodies = this.allBodies.filter(b => {
                const isSameKategori = String(b.kategori_id) === String(this.selectedKategori);
                // Dianggap milik orang lain jika ada di assignedBodyIds tapi TIDAK ada di originalBodies verifikator ini
                const isAssignedToOthers = this.assignedBodyIds.includes(b.id) && !this.originalBodies.includes(b.id);
                
                return isSameKategori && !isAssignedToOthers;
            });
        },

        toggleBody(id) {
            const idx = this.selectedBodies.indexOf(id);
            if (idx === -1) {
                this.selectedBodies.push(id);
            } else {
                this.selectedBodies.splice(idx, 1);
            }
        },

        removeBody(id) {
            this.selectedBodies = this.selectedBodies.filter(b => b !== id);
            this.filterBodies();
        },

        isBodySelected(id) {
            return this.selectedBodies.includes(id);
        },

        get selectedKategoriLabel() {
            if (this.selectedKategori === '') return 'Semua Kategori';
            const k = @js($kategoris).find(k => String(k.id) === String(this.selectedKategori));
            return k ? k.name : 'Semua Kategori';
        },

        /* FIX 3: setKategori sekarang langsung panggil filterBodies()
                  setelah mengubah selectedKategori */
        setKategori(val) {
            this.selectedKategori = String(val);
            this.openKategoriDrop = false;
            this.selectedBodies   = [];
            this.filterBodies();
        }
    }"
    x-init="filterBodies()"
>

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-4">Kelola Verifikator</h1>

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

                Tambah Verifikator
            </button>
        </div>
    </div>

    {{-- Alert sukses --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- TABEL --}}
    <div class="overflow-hidden rounded-xl border bg-white shadow">
        <table class="w-full text-sm">
            <thead class="bg-red-700 text-white">
                <tr>
                    <th class="px-4 py-3 text-left">Peran</th>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Username</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-left">Badan Publik</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($users as $item)
                    <tr>
                        <td class="px-4 py-3">{{ $item->getRoleNames()->first() }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                        <td class="px-4 py-3">{{ $item->username }}</td>
                        <td class="px-4 py-3">{{ $item->email }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1">
                                @foreach ($item->publicBodies->unique('kategori_id') as $body)
                                    <span class="w-fit rounded bg-gray-100 px-2 py-1 text-xs">
                                        {{ $body->kategori->name ?? '-' }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1">
                                @foreach ($item->publicBodies as $body)
                                    <span class="w-fit rounded bg-gray-200 px-2 py-1 text-xs">
                                        {{ $body->nama_badan }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1">
                                {{-- Edit --}}
                                <button
                                    @click="
                                        openEdit      = true;
                                        verifikatorId = {{ $item->id }};
                                        nama          = '{{ addslashes($item->name) }}';
                                        telepon       = '{{ addslashes($item->telepon ?? '') }}';
                                        email         = '{{ $item->email }}';
                                        username      = '{{ $item->username }}';
                                    "
                                    class="w-full rounded bg-leafy px-4 py-2 text-xs text-white text-center"
                                >Edit</button>

                                {{-- Set Badan Publik --}}
                                <button
                                    @click="
                                        openSet          = true;
                                        verifikatorId    = {{ $item->id }};
                                        selectedBodies   = {{ $item->publicBodies->pluck('id') }};
                                        originalBodies   = {{ $item->publicBodies->pluck('id') }};
                                        selectedKategori = '{{ $item->publicBodies->first()?->kategori_id ?? '' }}';
                                        openBodyDrop     = false;
                                        openKategoriDrop = false;
                                        filterBodies();
                                    "
                                    class="w-full rounded bg-yellow-500 px-4 py-2 text-xs text-white text-center"
                                >Set</button>

                                {{-- Hapus --}}
                                <form
                                    action="{{ route('superadmin.verifikator.destroy', $item->id) }}"
                                    method="POST"
                                    class="w-full"
                                    onsubmit="return confirm('Hapus verifikator ini?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="w-full rounded bg-red-700 px-4 py-2 text-xs text-white text-center">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-gray-500">
                            Belum ada data
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    {{-- ================================================================
         MODAL: TAMBAH VERIFIKATOR
         FIX 1: Hapus required public_body_ids dari validasi backend,
                sehingga modal ini cukup kirim data akun saja.
         ================================================================ --}}
    <div
        x-show="openTambah"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="openTambah = false"
    >
        <div class="w-[600px] rounded-xl bg-white p-8 shadow-xl">
            <div class="mb-6 flex justify-between">
                <h2 class="text-lg font-bold">Tambah Verifikator</h2>
                <button @click="openTambah = false">✕</button>
            </div>

            <form action="{{ route('superadmin.verifikator.store') }}" method="POST">
                @csrf

                <input
                    type="text"
                    name="name"
                    placeholder="Nama"
                    required
                    class="mb-3 w-full rounded border p-3"
                >
                <input
                    type="text"
                    name="telepon"
                    placeholder="No HP"
                    class="mb-3 w-full rounded border p-3"
                >
                <input
                    type="text"
                    name="email"
                    placeholder="Email"
                    class="mb-3 w-full rounded border p-3"
                >
                <input
                    type="text"
                    name="username"
                    placeholder="Username"
                    required
                    class="mb-3 w-full rounded border p-3"
                >
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                    class="mb-6 w-full rounded border p-3"
                >

                <div class="text-right">
                    <button class="rounded bg-red-700 px-6 py-2 text-white">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- ================================================================
         MODAL: EDIT VERIFIKATOR
         FIX 2: Tambahkan password_confirmation, action pakai @method PUT
         ================================================================ --}}
    <div
        x-show="openEdit"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="openEdit = false"
    >
        <div class="w-[600px] rounded-xl bg-white p-8 shadow-xl">
            <div class="mb-6 flex justify-between">
                <h2 class="text-lg font-bold">Edit Verifikator</h2>
                <button @click="openEdit = false">✕</button>
            </div>

            {{-- FIX 2: gunakan template literal untuk action URL --}}
            <form x-bind:action="`{{ url('superadmin/verifikator') }}/${verifikatorId}`" method="POST">
                @csrf
                @method('PUT')

                <input
                    type="text"
                    name="name"
                    x-model="nama"
                    placeholder="Nama"
                    class="mb-3 w-full rounded border p-3"
                >   
                <input
                    type="text"
                    name="telepon"
                    x-model="telepon"
                    placeholder="No HP"
                    class="mb-3 w-full rounded border p-3"
                >
                <input
                    type="text"
                    name="email"
                    x-model="email"
                    placeholder="Email"
                    required
                    class="mb-3 w-full rounded border p-3"
                >
                <input
                    type="text"
                    name="username"
                    x-model="username"
                    placeholder="Username"
                    required
                    class="mb-3 w-full rounded border p-3"
                >
                <input
                    type="password"
                    name="password"
                    placeholder="Password baru (kosongkan jika tidak diganti)"
                    class="mb-3 w-full rounded border p-3"
                >
                <input
                    type="password"
                    name="password_confirmation"
                    placeholder="Konfirmasi Password"
                    class="mb-6 w-full rounded border p-3"
                >

                <div class="text-right">
                    <button class="rounded bg-red-700 px-6 py-2 text-white">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- ================================================================
         MODAL: SET BADAN PUBLIK
         FIX 3: Panggil filterBodies() saat modal dibuka dan saat
                setKategori() dipanggil (sudah ada di setKategori).
         ================================================================ --}}
    <div
        x-show="openSet"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="openSet = false"
    >
        <div class="w-[650px] rounded-xl bg-white p-8 shadow-xl">
            <div class="mb-1 flex items-center justify-between">
                <h2 class="text-lg font-bold">Set Badan Publik</h2>
                <button @click="openSet = false" class="text-xl text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <hr class="mb-6">

            <form x-bind:action="`{{ url('superadmin/verifikator/set') }}/${verifikatorId}`" method="POST">
                @csrf

                {{-- Hidden inputs untuk selectedBodies --}}
                <template x-for="id in selectedBodies" :key="id">
                    <input type="hidden" name="public_body_ids[]" :value="id">
                </template>

                {{-- Kategori Dropdown --}}
                <label class="mb-2 block font-medium text-gray-700">Kategori Badan Publik</label>
                <div class="relative mb-5" x-data @click.outside="openKategoriDrop = false">
                    <button
                        type="button"
                        @click="openKategoriDrop = !openKategoriDrop"
                        class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 hover:border-gray-400 focus:outline-none"
                    >
                        <span class="flex flex-wrap gap-2">
                            <template x-if="selectedKategori !== ''">
                                <span class="inline-flex items-center gap-1 rounded-md bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                    <span x-text="selectedKategoriLabel"></span>
                                    <button
                                        type="button"
                                        @click.stop="setKategori('')"
                                        class="font-bold text-red-500 hover:text-red-700"
                                    >×</button>
                                </span>
                            </template>
                            <template x-if="selectedKategori === ''">
                                <span class="text-gray-400">Pilih Kategori</span>
                            </template>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        x-show="openKategoriDrop"
                        x-cloak
                        class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg"
                    >
                        <ul class="max-h-48 overflow-y-auto py-1 text-sm">
                            @foreach ($kategoris as $kategori)
                                <li>
                                    <button
                                        type="button"
                                        @click="setKategori('{{ $kategori->id }}')"
                                        class="w-full px-4 py-2 text-left hover:bg-gray-100"
                                        :class="String(selectedKategori) === '{{ $kategori->id }}' ? 'font-semibold text-red-700' : 'text-gray-700'"
                                    >{{ $kategori->name }}</button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Nama Badan Publik Multi-Select --}}
                <label class="mb-2 block font-medium text-gray-700">Nama Badan Publik</label>
                <div class="relative mb-6" x-data @click.outside="openBodyDrop = false">
                    <div
                        @click="selectedKategori !== '' && (openBodyDrop = !openBodyDrop)"
                        class="min-h-[80px] w-full rounded-lg border border-gray-300 bg-white px-3 py-2"
                        :class="selectedKategori !== '' ? 'cursor-pointer hover:border-gray-400' : 'cursor-not-allowed bg-gray-50 opacity-60'"
                    >
                        <div class="flex flex-col gap-2">
                            <template x-for="body in selectedBodyObjects" :key="body.id">
                                <div class="flex items-center justify-between gap-1 rounded-md bg-red-100 px-3 py-2 text-xs font-medium text-red-800">
                                    <span x-text="body.nama_badan"></span>
                                    <button
                                        type="button"
                                        @click.stop="removeBody(body.id)"
                                        class="font-bold text-red-400 hover:text-red-700"
                                    >×</button>
                                </div>
                            </template>
                            <template x-if="selectedKategori === ''">
                                <span class="text-sm text-gray-400">Pilih kategori terlebih dahulu...</span>
                            </template>
                            <template x-if="selectedKategori !== '' && selectedBodies.length === 0">
                                <span class="text-sm text-gray-400">Pilih badan publik...</span>
                            </template>
                        </div>
                        <div class="absolute right-3 top-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    <div
                        x-show="openBodyDrop && selectedKategori !== ''"
                        x-cloak
                        class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg"
                    >
                        <ul class="max-h-52 overflow-y-auto py-1 text-sm">
                            <template x-if="filteredBodies.length === 0">
                                <li class="px-4 py-3 text-center text-gray-400">
                                    Tidak ada badan publik tersedia
                                </li>
                            </template>
                            <template x-for="body in filteredBodies" :key="body.id">
                                <li>
                                    <button
                                        type="button"
                                        @click.stop="toggleBody(body.id)"
                                        class="flex w-full items-center gap-3 px-4 py-2 text-left hover:bg-gray-50"
                                        :class="isBodySelected(body.id) ? 'bg-red-50' : ''"
                                    >
                                        <span
                                            class="flex h-4 w-4 shrink-0 items-center justify-center rounded border"
                                            :class="isBodySelected(body.id) ? 'border-red-700 bg-red-700' : 'border-gray-300 bg-white'"
                                        >
                                            <svg
                                                x-show="isBodySelected(body.id)"
                                                class="h-3 w-3 text-white"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                        <span
                                            x-text="body.nama_badan"
                                            :class="isBodySelected(body.id) ? 'font-medium text-red-800' : 'text-gray-700'"
                                        ></span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                        <div class="border-t px-4 py-2 text-xs text-gray-500">
                            <span x-text="selectedBodies.length"></span> badan publik dipilih
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button
                        type="submit"
                        class="rounded-lg bg-red-700 px-8 py-3 font-semibold text-white hover:bg-red-800"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection