<!-- resources/views/components/navbar.blade.php -->
<div class="w-full">
    <div class="max-w-screen-xl mx-auto flex items-center justify-between px-2 py-4">
        <!-- Kiri: Logo & Judul -->
        <div class="flex items-center space-x-5">
            <img src="{{ $appSettings['logo'] ?? asset('images/Logo_header.png') }}" alt="Logo" class="h-12 w-auto">
        </div>

        <!-- Tombol Hamburger (hanya tampil di layar kecil) -->
        <button id="menu-toggle" class="sm:hidden focus:outline-none">
            <svg class="h-6 w-6 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Menu Navigasi -->
        <nav id="menu" class="hidden sm:flex flex-col sm:flex-row sm:space-x-10 text-lg font-medium text-gray-700 absolute sm:static top-16 left-0 w-full sm:w-auto bg-white sm:bg-transparent px-6 sm:px-0 py-4 sm:py-0 shadow sm:shadow-none z-40">
            <a href="/" class="font-inter block py-2 sm:py-0 border-b-2 sm:border-none border-primary-main sm:hover:text-primary-main">Beranda</a>
            <a href="/#alur-monev" class="font-inter block py-2 sm:py-0 hover:text-primary-main">Alur</a>

            <!-- Publikasi dengan Submenu -->
            <div class="relative group">
                <a href="#" class="font-inter block py-2 sm:py-0 hover:text-primary-main flex items-center">
                    Publikasi
                    <svg class="ml-1 h-4 w-4 transform group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
                <!-- Submenu -->
                <div class="absolute opacity-0 invisible group-hover:opacity-100 group-hover:visible transform scale-95 group-hover:scale-100 transition-all duration-300 bg-white shadow-md mt-2 rounded-md w-48 z-50">
                    <a href="/laporan-monev" class="block px-4 py-2 text-sm hover:bg-gray-100">Laporan Monev</a>
                    <a href="/kuesioner-monev" class="block px-4 py-2 text-sm hover:bg-gray-100">Kuesioner</a>
                    <a href="/pedoman-monev" class="block px-4 py-2 text-sm hover:bg-gray-100">Pedoman Monev</a>
                </div>
            </div>

            <a href="{{route('login')}}" class="font-inter block py-2 sm:py-0 hover:text-primary-main">Masuk</a>
        </nav>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('menu-toggle');
        const menu = document.getElementById('menu');

        toggle.addEventListener('click', function () {
            menu.classList.toggle('hidden');
        });
    });
</script>
@endpush