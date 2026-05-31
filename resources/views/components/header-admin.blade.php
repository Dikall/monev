<div class="w-full">
    <div class="max-w-screen-xl mx-auto flex items-center justify-between px-2 py-4">
        <!-- Logo -->
        <div class="flex items-center space-x-5">
            <img src="{{ asset('images/logo_header.png') }}" alt="Logo" class="h-12 w-auto">
        </div>

        <!-- Tombol Hamburger (mobile) -->
        <button id="menu-toggle" class="sm:hidden focus:outline-none">
            <svg class="h-6 w-6 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Menu Navigasi -->
        <nav id="menu" class="hidden sm:flex flex-col sm:flex-row sm:space-x-10 text-lg font-medium text-gray-700 absolute sm:static top-16 left-0 w-full sm:w-auto bg-white sm:bg-transparent px-6 sm:px-0 py-4 sm:py-0 shadow sm:shadow-none z-40">
            <a href="{{ route('admin/beranda') }}" class="block py-2 sm:py-0 hover:text-primary-main">Beranda</a>
            <a href="{{ route('notifications.index') }}" class="block py-2 sm:py-0 hover:text-primary-main flex items-center relative">
                Notifikasi
                @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                    <span class="ml-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-primary-main text-[10px] font-bold text-white">
                        {{ $unreadNotificationsCount }}
                    </span>
                @endif
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block py-2 sm:py-0 hover:text-primary-main">Keluar</button>
            </form>
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
