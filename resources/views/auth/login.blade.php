@extends('components.layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white p-8 rounded-2xl border border-gray-100 w-full max-w-md shadow-xl text-gray-800">
        
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/monev.jpg') }}" alt="Logo SIMANTAP" class="h-20 object-contain">
        </div>

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Form Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <!-- Email / Username -->
            <div>
                <label class="block text-sm font-semibold mb-1 text-gray-700">Email / Username</label>
                <input
                    type="text"
                    name="email"
                    value="{{ old('email') }}"
                    class="w-full px-4 py-2 border rounded-lg border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-main focus:border-transparent @error('email') border-red-500 @enderror"
                    placeholder="Email atau Username"
                    required
                    autofocus
                >
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kata Sandi -->
            <div x-data="{ showPassword: false }">
                <label class="block text-sm font-semibold mb-1 text-gray-700">Kata Sandi</label>
                <div class="relative">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        name="password"
                        id="password"
                        class="w-full px-4 py-2 border rounded-lg border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-main focus:border-transparent pr-10 @error('password') border-red-500 @enderror"
                        placeholder="Masukkan Kata Sandi"
                        required
                    >
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Toggle eye button with smooth transition -->
                    <button 
                        type="button" 
                        @click="showPassword = !showPassword" 
                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-primary-main transition-colors duration-200 focus:outline-none"
                        title="Tampilkan/Sembunyikan Kata Sandi"
                    >
                        <!-- Icon: Eye-Off (Sembunyikan Kata Sandi) -->
                        <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.042-3.368M9.88 9.88a3 3 0 104.243 4.243M3 3l18 18" />
                        </svg>
                        <!-- Icon: Eye (Tampilkan Kata Sandi) -->
                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center text-gray-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-primary-main focus:ring-primary-main" {{ old('remember') ? 'checked' : '' }}>
                    <span class="ml-2">Ingat saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-primary-main hover:text-primary-dark font-medium transition-colors">Lupa kata sandi?</a>
                @endif
            </div>

            <!-- Tombol Login -->
            <button type="submit" class="w-full bg-primary-dark hover:bg-primary-main text-white py-2.5 rounded-lg font-semibold shadow-md hover:shadow-lg transition-all duration-200">
                Masuk
            </button>

            <!-- Link ke register -->
            <p class="text-center text-sm text-gray-600 mt-4">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-primary-main hover:text-primary-dark font-semibold transition-colors duration-200">Daftar</a>
            </p>
        </form>
    </div>
</div>
@endsection
