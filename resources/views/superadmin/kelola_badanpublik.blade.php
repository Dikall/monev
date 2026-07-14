@extends('components.layouts.app')

@section('content')

<div class="p-6"
     x-data="{
        openDetail: false,
        openDelete: false,
        openResetPassword: false,

        selectedUser: null,
        newPassword: '',
        confirmPassword: '',
        showNewPassword: false,
        showConfirmPassword: false,
        passwordError: '',

        searchQuery: '',

        openReset(user) {
            this.selectedUser = user;
            this.newPassword = '';
            this.confirmPassword = '';
            this.passwordError = '';
            this.showNewPassword = false;
            this.showConfirmPassword = false;
            this.openResetPassword = true;
        },

        validatePasswords() {
            if (this.newPassword.length < 8) {
                this.passwordError = 'Password minimal 8 karakter.';
                return false;
            }
            if (this.newPassword !== this.confirmPassword) {
                this.passwordError = 'Konfirmasi password tidak cocok.';
                return false;
            }
            this.passwordError = '';
            return true;
        },

        setDetail(user) {
            this.selectedUser = user;
            this.openDetail = true;
        },

        get isAktif() {
            return this.selectedUser && this.selectedUser.is_aktif;
        }
     }">

    {{-- HEADER --}}
    <div class="mb-8">
        <h1 class="text-xl font-bold mb-4">Kelola Badan Publik</h1>

        {{-- FILTER KATEGORI --}}
        <form method="GET" action="{{ route('superadmin.akunbpublik.index') }}"
              class="bg-white border shadow rounded-lg p-4 mb-6">

            <div class="flex gap-3">
                <select name="kategori_id" class="w-full border rounded-lg p-3">
                    <option value="">Pilih Kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}" {{ $kategoriId == $k->id ? 'selected' : '' }}>
                            {{ $k->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                        class="px-6 bg-primary-dark text-white rounded-lg hover:bg-primary-dark whitespace-nowrap">
                    Tampilkan Badan Publik
                </button>
            </div>
        </form>

        {{-- HEADER TABEL --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
            <div class="flex flex-wrap items-center gap-4">
                <h2 class="text-lg font-semibold">Daftar Badan Publik</h2>
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <span class="text-xs text-gray-500">Tampilkan:</span>
                    <select onchange="window.location.href = this.value" class="border rounded-lg p-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-primary-dark">
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => 10, 'page' => 1]) }}" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => 25, 'page' => 1]) }}" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => 50, 'page' => 1]) }}" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => 100, 'page' => 1]) }}" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="text-xs text-gray-500">data</span>
                </div>
            </div>

            {{-- SEARCH --}}
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </span>
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Filter"
                    class="border rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-dark w-64"
                />
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white border shadow rounded-lg overflow-x-auto">
        <table class="min-w-[1200px] text-sm table-auto">
            <thead class="bg-primary-dark text-white">
                <tr>
                    <th class="px-4 py-3 text-left">Nama Badan Publik</th>
                    <th class="px-4 py-3 text-left">Website</th>
                    <th class="px-4 py-3 text-left">Telepon Badan Publik</th>
                    <th class="px-4 py-3 text-left">Email Badan Publik</th>
                    <th class="px-4 py-3 text-left">Nama Responden</th>
                    <th class="px-4 py-3 text-left">No HP Responden</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            Status
                            <a href="
                                @if($status === 'aktif')
                                    {{ request()->fullUrlWithQuery(['status' => 'nonaktif', 'page' => 1]) }}
                                @elseif($status === 'nonaktif')
                                    {{ request()->fullUrlWithQuery(['status' => null, 'page' => 1]) }}
                                @else
                                    {{ request()->fullUrlWithQuery(['status' => 'aktif', 'page' => 1]) }}
                                @endif
                            " title="Filter status" class="opacity-70 hover:opacity-100 transition-opacity">
                                @if($status === 'aktif')
                                    <svg class="w-4 h-4 text-yellow-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                    </svg>
                                @elseif($status === 'nonaktif')
                                    <svg class="w-4 h-4 text-yellow-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4M8 15l4 4 4-4"/>
                                    </svg>
                                @endif
                            </a>
                        </div>
                    </th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y" id="table-body">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 badan-publik-row"
                    data-nama="{{ strtolower(optional($user->publicBody)->nama_badan ?? '') }}">

                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900">{{ optional($user->publicBody)->nama_badan ?? '-' }}</div>
                        @if ($user->created_at && $user->created_at->diffInDays(now()) < 1)
                            <div class="text-[11px] text-blue-400 italic mt-0.5">Baru daftar</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ $user->website }}" target="_blank"
                           class="text-blue-600 hover:underline">
                            {{ $user->website ?? '-' }}
                        </a>
                    </td>
                    <td class="px-4 py-3">{{ $user->telepon ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $user->email ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $user->nama_responden ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $user->nohp_responden ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $user->email_responden ?? '-' }}</td>

                    <td class="px-4 py-3 text-center">
                        @if($user->is_aktif)
                            <span class="text-green-600 font-semibold">Aktif</span>
                        @else
                            <span class="text-primary-main font-semibold">Nonaktif</span>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-center">
                        <div class="flex flex-col gap-1 items-center">

                            {{-- DETAIL --}}
                            <button
                                @click="setDetail({{ json_encode([
                                    'id'              => $user->id,
                                    'is_aktif'        => $user->is_aktif,
                                    'nama'            => optional($user->publicBody)->nama_badan ?? '-',
                                    'website'         => $user->website,
                                    'alamat'          => $user->alamat,
                                    'telepon'         => $user->telepon,
                                    'nama_responden'  => $user->nama_responden,
                                    'jabatan'         => $user->jabatan_responden,
                                    'nohp_responden'  => $user->nohp_responden,
                                    'email_responden' => $user->email_responden,
                                    'nama_ppid'       => $user->nama_ppid,
                                    'nohp_ppid'       => $user->nohp_ppid,
                                    'email_ppid'      => $user->email_ppid,
                                    'email_login'     => $user->email,
                                ]) }})"
                                class="w-32 px-4 py-1 bg-primary-dark text-white rounded hover:bg-primary-dark">
                                Detail
                            </button>

                            {{-- RESET PASSWORD --}}
                            <button
                                @click="openReset({{ json_encode(['id' => $user->id, 'nama' => optional($user->publicBody)->nama_badan ?? $user->name]) }})"
                                class="w-32 px-4 py-1 border border-primary-dark text-primary-dark rounded hover:bg-primary-light">
                                Reset Password
                            </button>

                            {{-- HAPUS --}}
                            <button
                                @click="
                                    selectedUser = {{ json_encode(['id' => $user->id, 'nama' => $user->name]) }};
                                    openDelete = true;
                                "
                                class="w-32 px-4 py-1 bg-primary-dark text-white rounded hover:bg-primary-dark">
                                Hapus
                            </button>

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-6 text-center text-gray-500">
                        Belum ada data badan publik
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="p-4 flex flex-col sm:flex-row justify-between items-center gap-4 border-t text-sm text-gray-600">
            <div>
                Menampilkan {{ $users->firstItem() ?? 0 }} sampai {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data
            </div>
            <div class="flex items-center gap-1 text-sm">
                {{-- PREV --}}
                @if ($users->onFirstPage())
                    <span class="px-3 py-1 border rounded text-gray-400">Prev</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="px-3 py-1 border rounded hover:bg-gray-100">Prev</a>
                @endif

                {{-- NUMBER --}}
                @for ($i = max(1, $users->currentPage() - 2); $i <= min($users->lastPage(), $users->currentPage() + 2); $i++)
                    @if ($i == $users->currentPage())
                        <span class="px-3 py-1 bg-primary-dark text-white rounded">{{ $i }}</span>
                    @else
                        <a href="{{ $users->url($i) }}" class="px-3 py-1 border rounded hover:bg-gray-100">{{ $i }}</a>
                    @endif
                @endfor

                {{-- NEXT --}}
                @if ($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="px-3 py-1 border rounded hover:bg-gray-100">Next</a>
                @else
                    <span class="px-3 py-1 border rounded text-gray-400">Next</span>
                @endif
            </div>
        </div>
    </div>


    {{-- ===================== MODAL DETAIL ===================== --}}
    <div x-show="openDetail" x-cloak x-transition
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

        <div class="bg-white rounded-xl w-11/12 md:w-[480px] p-8 max-h-[90vh] overflow-y-auto">

            {{-- Header --}}
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Detail Akun Badan Publik</h2>
                <button @click="openDetail = false" class="text-gray-500 hover:text-gray-700 text-lg">✕</button>
            </div>
            <hr class="mb-6">

            <template x-if="selectedUser">
                <div class="space-y-6 text-sm">

                    {{-- Badan Publik --}}
                    <div>
                        <p class="font-bold text-base mb-3">Badan Publik</p>
                        <div class="space-y-2">
                            <div class="flex gap-2">
                                <span class="w-44 text-gray-600 shrink-0">Nama Badan Publik</span>
                                <span class="shrink-0">:</span>
                                <span x-text="selectedUser.nama" class="font-medium"></span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-44 text-gray-600 shrink-0">Website</span>
                                <span class="shrink-0">:</span>
                                <span x-text="selectedUser.website || '-'" class="break-all"></span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-44 text-gray-600 shrink-0">Alamat Badan Publik</span>
                                <span class="shrink-0">:</span>
                                <span x-text="selectedUser.alamat || '-'"></span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-44 text-gray-600 shrink-0">Telepon/fax</span>
                                <span class="shrink-0">:</span>
                                <span x-text="selectedUser.telepon || '-'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Data Responden --}}
                    <div>
                        <p class="font-bold text-base mb-3">Data Responden</p>
                        <div class="space-y-2">
                            <div class="flex gap-2">
                                <span class="w-44 text-gray-600 shrink-0">Nama Responden</span>
                                <span class="shrink-0">:</span>
                                <span x-text="selectedUser.nama_responden || '-'"></span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-44 text-gray-600 shrink-0">Jabatan</span>
                                <span class="shrink-0">:</span>
                                <span x-text="selectedUser.jabatan || '-'"></span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-44 text-gray-600 shrink-0">Nomor HP</span>
                                <span class="shrink-0">:</span>
                                <span x-text="selectedUser.nohp_responden || '-'"></span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-44 text-gray-600 shrink-0">Email Login</span>
                                <span class="shrink-0">:</span>
                                <span x-text="selectedUser.email_login || '-'" class="break-all"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Data PPID --}}
                    <div>
                        <p class="font-bold text-base mb-3">Data PPID</p>
                        <div class="space-y-2">
                            <div class="flex gap-2">
                                <span class="w-44 text-gray-600 shrink-0">Nama PPID</span>
                                <span class="shrink-0">:</span>
                                <span x-text="selectedUser.nama_ppid || '-'"></span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-44 text-gray-600 shrink-0">Nomor HP PPID</span>
                                <span class="shrink-0">:</span>
                                <span x-text="selectedUser.nohp_ppid || '-'"></span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-44 text-gray-600 shrink-0">Email PPID</span>
                                <span class="shrink-0">:</span>
                                <span x-text="selectedUser.email_ppid || '-'" class="break-all"></span>
                            </div>
                        </div>
                    </div>

                </div>
            </template>

            {{-- Tombol Verifikasi / Nonaktifkan --}}
            <template x-if="selectedUser">
                <div class="mt-8 flex justify-end">

                    {{-- Jika belum aktif → Verifikasi Akun --}}
                    <template x-if="!selectedUser.is_aktif">
                        <form :action="'{{ url('superadmin/akunbpublik') }}/' + selectedUser.id + '/aktifkan'" method="POST">
                            @csrf
                            @method('PATCH')
                            <button
                                class="px-8 py-2 bg-primary-dark text-white rounded-lg hover:bg-primary-dark font-medium">
                                Verifikasi Akun
                            </button>
                        </form>
                    </template>

                    {{-- Jika sudah aktif → Nonaktifkan Akun --}}
                    <template x-if="selectedUser.is_aktif">
                        <form :action="'{{ url('superadmin/akunbpublik') }}/' + selectedUser.id + '/nonaktifkan'" method="POST">
                            @csrf
                            @method('PATCH')
                            <button
                                class="px-8 py-2 bg-primary-dark text-white rounded-lg hover:bg-primary-dark font-medium">
                                Nonaktifkan Akun
                            </button>
                        </form>
                    </template>

                </div>
            </template>

        </div>
    </div>


    {{-- ===================== MODAL RESET PASSWORD ===================== --}}
    <div x-show="openResetPassword" x-cloak x-transition
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

        <div class="bg-white rounded-xl w-11/12 md:w-[480px] p-8">

            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Reset Password</h2>
                <button @click="openResetPassword = false" class="text-gray-500 hover:text-gray-700 text-lg">✕</button>
            </div>
            <hr class="mb-6">

            <p class="mb-6 text-sm text-gray-600">
                Atur password baru untuk akun
                <span class="font-semibold text-gray-800" x-text="selectedUser ? selectedUser.nama : ''"></span>.
            </p>

            <form :action="'{{ url('superadmin/akunbpublik') }}/' + (selectedUser ? selectedUser.id : '') + '/reset-password'"
                  method="POST"
                  @submit.prevent="if(validatePasswords()) $el.submit()">
                @csrf
                @method('PATCH')

                <div class="space-y-4">

                    {{-- Password Baru --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password Baru <span class="text-primary-main">*</span>
                        </label>
                        <input
                            type="text"
                            name="password"
                            x-model="newPassword"
                            placeholder="Masukkan password baru"
                            class="w-full border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-main"
                            required
                        />
                    </div>

                    {{-- Ulangi Password Baru --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Ulangi Password Baru <span class="text-primary-main">*</span>
                        </label>
                        <input
                            type="text"
                            name="password_confirmation"
                            x-model="confirmPassword"
                            placeholder="Ulangi password baru"
                            class="w-full border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-main"
                            required
                        />
                    </div>

                    {{-- Error Message --}}
                    <p x-show="passwordError" x-text="passwordError"
                       class="text-sm text-primary-main font-medium"></p>

                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" @click="openResetPassword = false"
                        class="px-6 py-2 border border-primary-dark text-primary-dark rounded-lg hover:bg-primary-light text-sm font-medium">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-primary-dark text-white rounded-lg hover:bg-primary-dark text-sm font-medium">
                        Simpan Password
                    </button>
                </div>

            </form>

        </div>
    </div>


    {{-- ===================== MODAL DELETE ===================== --}}
    <div x-show="openDelete" x-cloak x-transition
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

        <div class="bg-white rounded-xl w-11/12 md:w-1/3 p-8">

            <div class="flex justify-between mb-6">
                <h2 class="text-2xl font-bold">Hapus Badan Publik</h2>
                <button @click="openDelete = false">✕</button>
            </div>

            <p class="mb-8 text-lg">
                Yakin ingin menghapus akun
                <span class="font-semibold" x-text="selectedUser ? selectedUser.nama : ''"></span>?
                Tindakan ini tidak dapat dibatalkan.
            </p>

            <div class="flex justify-end gap-4">
                <button @click="openDelete = false"
                    class="px-8 py-2 border border-primary-dark text-primary-dark rounded-lg hover:bg-primary-light">
                    Batal
                </button>

                <form :action="'{{ url('superadmin/akunbpublik') }}/' + (selectedUser ? selectedUser.id : '')"
                      method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="px-8 py-2 bg-primary-dark text-white rounded-lg hover:bg-primary-dark">
                        Hapus
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>

{{-- Script search filter --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.querySelector('[x-model="searchQuery"]');
        if (!searchInput) return;

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('.badan-publik-row');

            rows.forEach(row => {
                const nama = row.getAttribute('data-nama') || '';
                row.style.display = nama.includes(query) ? '' : 'none';
            });
        });
    });
</script>

@endsection