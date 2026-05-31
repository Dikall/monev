@extends('components.layouts.app')

@section('content')
<div class="profile-bp p-6" x-data="{ openEdit: false }">
    <!-- Card Atas -->
    <div class="bg-red-700 text-white rounded-lg p-6 flex items-center gap-6 mb-6">
        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center text-red-700 font-bold text-xl">
            {{ strtoupper(substr($user->name ?? $user->nama_responden, 0, 1)) }}
        </div>
        <div>
            <h1 class="text-xl font-bold">{{ $user->name ?? $user->nama_responden }}</h1>
            <p>{{ $user->email }}</p>
            <p class="text-sm opacity-80">OPD Tingkat Kabupaten/Kota Se-Kalimantan Barat</p>
        </div>
    </div>

    <!-- Badan Publik -->
    <h1 class="font-semibold text-xl mb-3">Badan Publik</h1>
    <div class="bg-white border shadow rounded-lg p-5 mb-4 space-y-1">
        <p><strong>Badan Publik:</strong> {{ $user->name }}</p>
        <p><strong>Website:</strong> <a href="{{ $user->website }}" class="text-blue-600" target="_blank">{{ $user->website }}</a></p>
        <p><strong>No Telepon / Fax:</strong> {{ $user->telepon }}</p>
        <p><strong>Email Badan Publik:</strong> {{ $user->email }}</p>
        <p><strong>Alamat:</strong> {{ $user->alamat }}</p>
    </div>

    <!-- Responden -->
    <h1 class="font-semibold text-xl mb-3">Responden</h1>
    <div class="bg-white border shadow rounded-lg p-5 mb-4 space-y-1">
        <p><strong>Nama Responden:</strong> {{ $user->nama_responden }}</p>
        <p><strong>No Handphone:</strong> {{ $user->nohp_responden }}</p>
        <p><strong>Jabatan:</strong> {{ $user->jabatan_responden }}</p>
        <p><strong>Email Responden:</strong> {{ $user->email_responden }}</p>
    </div>

    <!-- PPID -->
    <h1 class="font-semibold text-xl mb-3">PPID</h1>
    <div class="bg-white border shadow rounded-lg p-5 mb-4 space-y-1">
        <p><strong>Nama PPID:</strong> {{ $user->nama_ppid }}</p>
        <p><strong>No Handphone:</strong> {{ $user->nohp_ppid }}</p>
        <p><strong>Email PPID:</strong> {{ $user->email_ppid }}</p>
    </div>

    <!-- Tombol Edit -->
    <div class="flex justify-end">
        <button @click="openEdit = true" 
            class="px-12 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 flex items-center gap-2">
            Edit
        </button>
    </div>

    <!-- Modal Edit Profile -->
    <div x-show="openEdit" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
        <div class="bg-white rounded-lg w-11/12 md:w-2/3 lg:w-1/2 p-6 max-h-screen overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">Edit Profil</h2>

            <form action="{{ route('profile.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- BADAN PUBLIK -->
                <h3 class="font-semibold text-lg mb-2">Badan Publik</h3>
                <div class="mb-4">
                    <label class="block mb-1">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border rounded p-2">
                </div>
                <div class="mb-4">
                    <label class="block mb-1">Alamat</label>
                    <input type="text" name="alamat" value="{{ old('alamat', $user->alamat) }}" class="w-full border rounded p-2">
                </div>
                <div class="mb-4">
                    <label class="block mb-1">No Telepon</label>
                    <input type="text" name="telepon" value="{{ old('telepon', $user->telepon) }}" class="w-full border rounded p-2">
                </div>
                <div class="mb-6">
                    <label class="block mb-1">Website</label>
                    <input type="text" name="website" value="{{ old('website', $user->website) }}" class="w-full border rounded p-2">
                </div>

                <!-- RESPONDEN -->
                <h3 class="font-semibold text-lg mb-2">Responden</h3>
                <div class="mb-4">
                    <label class="block mb-1">Nama Responden</label>
                    <input type="text" name="nama_responden" value="{{ old('nama_responden', $user->nama_responden) }}" class="w-full border rounded p-2">
                </div>
                <div class="mb-4">
                    <label class="block mb-1">Jabatan</label>
                    <input type="text" name="jabatan_responden" value="{{ old('jabatan_responden', $user->jabatan_responden) }}" class="w-full border rounded p-2">
                </div>
                <div class="mb-4">
                    <label class="block mb-1">No Handphone</label>
                    <input type="text" name="nohp_responden" value="{{ old('nohp_responden', $user->nohp_responden) }}" class="w-full border rounded p-2">
                </div>
                <div class="mb-6">
                    <label class="block mb-1">Email Responden</label>
                    <input type="email" name="email_responden" value="{{ old('email_responden', $user->email_responden) }}" class="w-full border rounded p-2">
                </div>

                <!-- PPID -->
                <h3 class="font-semibold text-lg mb-2">PPID</h3>
                <div class="mb-4">
                    <label class="block mb-1">Nama PPID</label>
                    <input type="text" name="nama_ppid" value="{{ old('nama_ppid', $user->nama_ppid) }}" class="w-full border rounded p-2">
                </div>
                <div class="mb-4">
                    <label class="block mb-1">No Handphone</label>
                    <input type="text" name="nohp_ppid" value="{{ old('nohp_ppid', $user->nohp_ppid) }}" class="w-full border rounded p-2">
                </div>
                <div class="mb-6">
                    <label class="block mb-1">Email PPID</label>
                    <input type="email" name="email_ppid" value="{{ old('email_ppid', $user->email_ppid) }}" class="w-full border rounded p-2">
                </div>

                <!-- BUTTONS -->
                <div class="flex justify-end gap-2">
                    <button type="button" @click="openEdit = false" 
                        class="px-4 py-2 border rounded hover:bg-gray-100">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
