@extends('components.layouts.app')

@section('content')
<div class="flex justify-center items-center min-h-screen bg-gray-100">
    <div class="w-full bg-white shadow-lg rounded-lg p-8 md:px-20">
        <h2 class="text-xl font-bold text-red-700 mb-6">
            Registrasi Badan Publik
        </h2>

        {{-- ERROR MESSAGE --}}
        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Data Badan Publik --}}
            <h3 class="text-lg font-semibold text-gray-800">Data Badan Publik</h3>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Kategori Badan Publik*
                    </label>
                    <select name="kategori_id" id="kategori_id"
                        class="w-full h-10 px-4 border rounded-lg
                        @error('kategori_id') border-red-500 @else border-gray-400 @enderror"
                        required>
                        <option value="">Pilih Kategori</option>
                        @foreach($kategoris as $kategori)
                        <option value="{{ $kategori->id }}"
                        {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                        {{ $kategori->name }}
                        </option>
                        @endforeach
                    </select>

                    @error('kategori_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Nama Badan Publik*
                    </label>
                    <select name="public_body_id" id="public_body_id"
                        class="w-full h-10 px-4 border border-gray-400 rounded-lg shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                        required>
                        <option value="">Pilih kategori dulu</option>
                    </select>
                </div>
            </div>

            {{-- Data Responden --}}
            <h3 class="text-lg font-semibold text-gray-800">Data Responden</h3>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Nama Responden*
                    </label>
                    <input type="text" name="nama_responden" value="{{ old('nama_responden') }}"
                        class="w-full h-10 px-4 border border-gray-400 rounded-lg shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                        required>
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        No Telepon / HP*
                    </label>
                    <input type="text" name="nohp_responden" value="{{ old('nohp_responden') }}"
                        class="w-full h-10 px-4 border border-gray-400 rounded-lg shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                        required>
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Jabatan*
                    </label>
                    <input type="text" name="jabatan_responden" value="{{ old('jabatan_responden') }}"
                        class="w-full h-10 px-4 border border-gray-400 rounded-lg shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                        required>
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Email*
                    </label>
                    <input type="email" name="email_responden" value="{{ old('email_responden') }}"
                        class="w-full h-10 px-4 border border-gray-400 rounded-lg shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                        required>
                </div>
            </div>

            {{-- Informasi Akun --}}
            <h3 class="text-lg font-semibold text-gray-800">
                Informasi Akun (digunakan untuk Login)
            </h3>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Username*
                    </label>
                    <input type="text"
                        name="username"
                        value="{{ old('username') }}"
                        class="w-full h-10 px-4 border rounded-lg
                        @error('username') border-red-500 @else border-gray-400 @enderror"
                        required>

                        @error('username')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <input type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full h-10 px-4 border rounded-lg
                        @error('email') border-red-500 @else border-gray-400 @enderror">

                        @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Kata Sandi*
                    </label>
                    <input type="password"
                        name="password"
                        class="w-full h-10 px-4 border rounded-lg
                        @error('password') border-red-500 @else border-gray-400 @enderror"
                        required>

                        @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Ulangi Kata Sandi*
                    </label>
                    <input type="password" name="password_confirmation"
                        class="w-full h-10 px-4 border border-gray-400 rounded-lg shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                        required>
                </div>
            </div>

            <div class="text-right">
                <button type="submit"
                    class="bg-red-700 hover:bg-red-800 text-white font-semibold py-2 px-8 rounded-lg shadow">
                    Daftar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kategoriSelect = document.getElementById('kategori_id');
    const publicBodySelect = document.getElementById('public_body_id');
    const oldPublicBodyId = "{{ old('public_body_id') }}";

    function loadPublicBodies(categoryId, selectedId = null) {
        if (!categoryId) {
            publicBodySelect.innerHTML = '<option value="">Pilih kategori dulu</option>';
            return;
        }

        publicBodySelect.innerHTML = '<option value="">Loading...</option>';

        fetch('/get-public-bodies/' + categoryId)
            .then(response => response.json())
            .then(data => {
                publicBodySelect.innerHTML = '<option value="">Nama Badan Publik</option>';

                if (data.length === 0) {
                    publicBodySelect.innerHTML = '<option value="">Semua badan sudah terdaftar</option>';
                    return;
                }

                data.forEach(function(item) {
                    const selected = (selectedId && item.id == selectedId) ? 'selected' : '';
                    publicBodySelect.innerHTML += `<option value="${item.id}" ${selected}>${item.nama_badan}</option>`;
                });
            })
            .catch(error => {
                console.error(error);
                publicBodySelect.innerHTML = '<option value="">Terjadi kesalahan</option>';
            });
    }

    kategoriSelect.addEventListener('change', function () {
        loadPublicBodies(this.value);
    });

    if (kategoriSelect.value) {
        loadPublicBodies(kategoriSelect.value, oldPublicBodyId);
    }
});
</script>

@endsection