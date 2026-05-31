@extends('components.layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="text-white p-8 rounded-xl border border-gray-300 w-full max-w-md shadow-lg bg-white-200">
        
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/monev.jpg') }}" alt="Logo SIMANTAP" class="h-20 object-contain">
        </div>

        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
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
                <label class="block text-sm font-semibold mb-1 text-gray-300">Email / Username</label>
                <input
                    type="text"
                    name="email"
                    value="{{ old('email') }}"
                    class="w-full px-4 py-2 border rounded-md border-gray-500 text-black placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-600 @error('email') border-red-500 @enderror"
                    placeholder="Email / Username"
                    required
                    autofocus
                >
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <!-- Kata Sandi -->
            <div>
                <label class="block text-sm font-semibold mb-1 text-gray-300">Kata Sandi</label>
                <div class="relative">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="w-full px-4 py-2 border rounded-md border-gray-500 text-black placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-600 pr-10 @error('password') border-red-500 @enderror"
                        placeholder="Kata Sandi"
                        required
                    >
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Toggle eye -->
                    <div onclick="togglePassword()" class="absolute inset-y-0 right-3 flex items-center text-red-600 cursor-pointer">
                        <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition duration-300 ease-in-out opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.042-3.368M9.88 9.88a3 3 0 104.243 4.243M3 3l18 18" />
                        </svg>
                        <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden transition duration-300 ease-in-out opacity-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                </div>
            </div>
            <!-- Remember Me -->
            <div class="flex items-center justify-between text-sm text-gray-300">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="remember" class="form-checkbox text-red-600" {{ old('remember') ? 'checked' : '' }}>
                    <span class="ml-2">Ingat saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-red-600 hover:underline">Lupa kata sandi?</a>
                @endif
            </div>
            <!-- Tombol Login -->
            <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-2 rounded-md font-semibold transition duration-300">
                Masuk
            </button>
            <!-- Link ke register -->
            <p class="text-center text-sm text-gray-300 mt-4">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-red-500 hover:text-red-700 font-semibold transition duration-300">Daftar</a>
            </p>
        </form>
    </div>
</div>

<!-- JS Toggle Password -->
<script>
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const eyeOpen = document.getElementById("eye-open");
        const eyeClosed = document.getElementById("eye-closed");

        const isHidden = passwordInput.type === "password";

        passwordInput.type = isHidden ? "text" : "password";

        if (isHidden) {
            eyeOpen.classList.remove("hidden", "opacity-0");
            eyeOpen.classList.add("opacity-100");
            eyeClosed.classList.add("opacity-0");
            setTimeout(() => eyeClosed.classList.add("hidden"), 300);
        } else {
            eyeClosed.classList.remove("hidden", "opacity-0");
            eyeClosed.classList.add("opacity-100");
            eyeOpen.classList.add("opacity-0");
            setTimeout(() => eyeOpen.classList.add("hidden"), 300);
        }
    }
</script>
@endsection
