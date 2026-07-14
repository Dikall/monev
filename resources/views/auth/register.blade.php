@extends('components.layouts.app')

@section('content')
<div class="flex justify-center items-center min-h-screen bg-gray-100">
    <div class="w-full bg-white shadow-lg rounded-lg p-8 md:px-20">
        <h2 class="text-xl font-bold text-primary-dark mb-6">
            Registrasi Badan Publik
        </h2>

        {{-- ERROR MESSAGE --}}
        @if ($errors->any())
        <div class="bg-primary-light border border-primary-main text-primary-dark px-4 py-3 rounded mb-6">
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

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Data Badan Publik --}}
            <h3 class="text-lg font-semibold text-gray-800">Data Badan Publik</h3>

            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Kategori Badan Publik
                    </label>
                    <select name="kategori_id" id="kategori_id"
                        class="w-full h-10 px-4 border rounded-lg
                        @error('kategori_id') border-primary-main @else border-gray-400 @enderror"
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
                    <p class="text-primary-main text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Nama Badan Publik
                    </label>
                    <select name="public_body_id" id="public_body_id"
                        class="w-full h-10 px-4 border border-gray-400 rounded-lg shadow-sm focus:border-primary-main focus:ring focus:ring-primary-light"
                        required>
                        <option value="">Pilih kategori dulu</option>
                    </select>
                </div>
            </div>

            {{-- Data Responden --}}
            <h3 class="text-lg font-semibold text-gray-800">Data Responden</h3>

            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Nama Responden
                    </label>
                    <input type="text" name="nama_responden" value="{{ old('nama_responden') }}"
                        class="w-full h-10 px-4 border border-gray-400 rounded-lg shadow-sm focus:border-primary-main focus:ring focus:ring-primary-light"
                        required>
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        No Telepon / HP Responden
                    </label>
                    <input type="text" name="nohp_responden" value="{{ old('nohp_responden') }}"
                        class="w-full h-10 px-4 border border-gray-400 rounded-lg shadow-sm focus:border-primary-main focus:ring focus:ring-primary-light"
                        required>
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Jabatan Responden
                    </label>
                    <input type="text" name="jabatan_responden" value="{{ old('jabatan_responden') }}"
                        class="w-full h-10 px-4 border border-gray-400 rounded-lg shadow-sm focus:border-primary-main focus:ring focus:ring-primary-light"
                        required>
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Email Responden
                    </label>
                    <input type="email" name="email_responden" value="{{ old('email_responden') }}"
                        class="w-full h-10 px-4 border border-gray-400 rounded-lg shadow-sm focus:border-primary-main focus:ring focus:ring-primary-light"
                        required>
                </div>
            </div>

            {{-- Informasi Akun --}}
            <h3 class="text-lg font-semibold text-gray-800">
                Informasi Akun (digunakan untuk Login)
            </h3>

            <div class="grid md:grid-cols-2 gap-8">
                <div class="md:col-span-2">
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Username / Email
                    </label>
                    <input type="text"
                        name="username_email"
                        value="{{ old('username_email') }}"
                        class="w-full h-10 px-4 border rounded-lg
                        @error('username_email') border-primary-main @else border-gray-400 @enderror"
                        placeholder="Masukkan Username atau Alamat Email Anda"
                        required>

                        @error('username_email')
                        <p class="text-primary-main text-sm mt-1">{{ $message }}</p>
                        @enderror
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <input type="password"
                            id="password"
                            name="password"
                            class="w-full h-10 pl-4 pr-10 border rounded-lg
                            @error('password') border-primary-main @else border-gray-400 @enderror"
                            required>
                        <button type="button" 
                                onclick="togglePasswordVisibility('password', 'eye-icon-pass')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none"
                                style="top: 50%; transform: translateY(-50%);">
                            <svg id="eye-icon-pass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-red-500 mt-1">Kata sandi minimal 8 karakter.</p>

                    @error('password')
                    <p class="text-primary-main text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-lg font-medium text-gray-700 mb-1">
                        Ulangi Kata Sandi
                    </label>
                    <div class="relative">
                        <input type="password" 
                            id="password_confirmation"
                            name="password_confirmation"
                            class="w-full h-10 pl-4 pr-10 border border-gray-400 rounded-lg shadow-sm focus:border-primary-main focus:ring focus:ring-primary-light"
                            required>
                        <button type="button" 
                                onclick="togglePasswordVisibility('password_confirmation', 'eye-icon-confirm')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none"
                                style="top: 50%; transform: translateY(-50%);">
                            <svg id="eye-icon-confirm" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit"
                    class="bg-primary-dark hover:bg-primary-dark text-white font-semibold py-2 px-8 rounded-lg shadow">
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

function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const svg = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        svg.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
        `;
    } else {
        input.type = 'password';
        svg.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;
    }
}
</script>

@endsection