<style>
    .sidebar-menu {
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .sidebar-menu::-webkit-scrollbar {
        display: none;
    }
    .sidebar-item {
        transition: background 0.15s ease, color 0.15s ease;
    }
    .sidebar-item:hover {
        background: rgba(255, 255, 255, 0.13);
        color: white;
    }
    .sidebar-item.active {
        background: rgba(255, 255, 255, 0.18);
        color: white;
    }
    .sidebar-dropdown-header {
        transition: background 0.15s ease;
    }
    .sidebar-dropdown-header:hover {
        background: rgba(255, 255, 255, 0.10);
    }
    .sidebar-sub-item {
        transition: background 0.12s ease, color 0.12s ease;
    }
    .sidebar-sub-item:hover {
        background: rgba(255, 255, 255, 0.10);
        color: white;
    }
    .sidebar-arrow {
        transition: transform 0.2s ease;
    }
</style>

{{--
    ✅ TIDAK ada x-data di sini.
    Sidebar ini memakai sidebarOpen dari x-data di <body> pada app.blade.php.
    Jika x-data ditambahkan di sini, Alpine akan membuat scope baru yang
    terpisah dari body, sehingga main content tidak bisa membaca sidebarOpen.
--}}
<div :class="sidebarOpen ? 'w-72' : 'w-20'"
     class="bg-primary-dark text-white flex flex-col transition-all duration-300 fixed top-0 left-0 h-screen z-50 shadow-xl">

    {{-- ===== HEADER: Teks & Hamburger ===== --}}
    <div class="flex items-center justify-between px-4 py-5 border-b border-white border-opacity-15">

        {{-- Teks saat expanded --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="overflow-hidden">
            <p class="text-base font-bold text-white leading-tight tracking-wide">E-Monev</p>
            <p class="text-xs text-white text-opacity-50 leading-tight">Superadmin Panel</p>
        </div>

        {{-- Hamburger --}}
        <button @click="sidebarOpen = !sidebarOpen"
                :class="sidebarOpen ? '' : 'mx-auto'"
                class="p-1.5 rounded-lg hover:bg-white hover:bg-opacity-15 transition focus:outline-none flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    {{-- ===== MENU NAVIGASI ===== --}}
    <nav class="sidebar-menu flex-1 px-3 py-4 space-y-0.5">

        {{-- Beranda --}}
        <a href="{{ route('superadmin.dashboard') }}"
           :class="sidebarOpen ? 'justify-start px-3' : 'justify-center px-0'"
           class="sidebar-item flex items-center gap-3 py-2.5 rounded-xl text-sm text-white text-opacity-75 font-medium">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7m-9 14v-7h4v7m5-5h2a2 2 0 002-2v-5a2 2 0 00-.586-1.414l-8-8a2 2 0 00-2.828 0l-8 8A2 2 0 002 10v5a2 2 0 002 2h2"/>
            </svg>
            <span x-show="sidebarOpen" class="whitespace-nowrap">Beranda</span>
        </a>

        {{-- Section: Manajemen Data --}}
        <p x-show="sidebarOpen" class="text-xs font-semibold text-white text-opacity-40 uppercase tracking-widest px-3 pt-4 pb-1">
            Manajemen Data
        </p>
        <div x-show="!sidebarOpen" class="border-t border-white border-opacity-10 my-2"></div>

        {{-- Master Data --}}
        <div x-data="{ open: false }">
            <button @click="sidebarOpen ? open = !open : null"
                    :class="sidebarOpen ? 'justify-start px-3' : 'justify-center px-0'"
                    class="sidebar-dropdown-header flex items-center gap-3 w-full py-2.5 rounded-xl text-sm text-white text-opacity-75 font-medium">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Master Data</span>
                <svg x-show="sidebarOpen" :class="open ? 'rotate-180' : ''"
                     class="sidebar-arrow ml-auto w-4 h-4 text-white text-opacity-50 flex-shrink-0"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open && sidebarOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="pl-11 pr-2 pb-1 space-y-0.5 mt-0.5">
                <a href="{{ route('superadmin.tahun.index') }}" class="sidebar-sub-item block px-3 py-2 rounded-lg text-xs text-white text-opacity-60">Tahun</a>
                <a href="{{ route('superadmin.kategori.index') }}" class="sidebar-sub-item block px-3 py-2 rounded-lg text-xs text-white text-opacity-60">Kategori</a>
                <a href="{{ route('superadmin.tenggat.index') }}" class="sidebar-sub-item block px-3 py-2 rounded-lg text-xs text-white text-opacity-60">Tenggat Kuesioner</a>
                <a href="{{ route('superadmin.bpublik.index') }}" class="sidebar-sub-item block px-3 py-2 rounded-lg text-xs text-white text-opacity-60">Badan Publik</a>
            </div>
        </div>

        {{-- Master Kuesioner --}}
        <div x-data="{ open: false }">
            <button @click="sidebarOpen ? open = !open : null"
                    :class="sidebarOpen ? 'justify-start px-3' : 'justify-center px-0'"
                    class="sidebar-dropdown-header flex items-center gap-3 w-full py-2.5 rounded-xl text-sm text-white text-opacity-75 font-medium">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Master Kuesioner</span>
                <svg x-show="sidebarOpen" :class="open ? 'rotate-180' : ''"
                     class="sidebar-arrow ml-auto w-4 h-4 text-white text-opacity-50 flex-shrink-0"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open && sidebarOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="pl-11 pr-2 pb-1 space-y-0.5 mt-0.5">
                <a href="{{ route('superadmin.indikator.index') }}" class="sidebar-sub-item block px-3 py-2 rounded-lg text-xs text-white text-opacity-60">Indikator</a>
                <a href="{{ route('superadmin.pertanyaan.index') }}" class="sidebar-sub-item block px-3 py-2 rounded-lg text-xs text-white text-opacity-60">Pertanyaan</a>
            </div>
        </div>

        {{-- Section: Pengelolaan --}}
        <p x-show="sidebarOpen" class="text-xs font-semibold text-white text-opacity-40 uppercase tracking-widest px-3 pt-4 pb-1">
            Pengelolaan
        </p>
        <div x-show="!sidebarOpen" class="border-t border-white border-opacity-10 my-2"></div>

        {{-- Kelola Akun --}}
        <div x-data="{ open: false }">
            <button @click="sidebarOpen ? open = !open : null"
                    :class="sidebarOpen ? 'justify-start px-3' : 'justify-center px-0'"
                    class="sidebar-dropdown-header flex items-center gap-3 w-full py-2.5 rounded-xl text-sm text-white text-opacity-75 font-medium">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Kelola Akun</span>
                <svg x-show="sidebarOpen" :class="open ? 'rotate-180' : ''"
                     class="sidebar-arrow ml-auto w-4 h-4 text-white text-opacity-50 flex-shrink-0"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open && sidebarOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="pl-11 pr-2 pb-1 space-y-0.5 mt-0.5">
                <a href="{{ route('superadmin.verifikator.index') }}" class="sidebar-sub-item block px-3 py-2 rounded-lg text-xs text-white text-opacity-60">Admin Verifikator</a>
                <a href="{{ route('superadmin.akunbpublik.index') }}" class="sidebar-sub-item block px-3 py-2 rounded-lg text-xs text-white text-opacity-60">Badan Publik</a>
            </div>
        </div>

        {{-- Kelola Publikasi --}}
        <div x-data="{ open: false }">
            <button @click="sidebarOpen ? open = !open : null"
                    :class="sidebarOpen ? 'justify-start px-3' : 'justify-center px-0'"
                    class="sidebar-dropdown-header flex items-center gap-3 w-full py-2.5 rounded-xl text-sm text-white text-opacity-75 font-medium">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V4m-4 4l4-4 4 4"/>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Kelola Publikasi</span>
                <svg x-show="sidebarOpen" :class="open ? 'rotate-180' : ''"
                     class="sidebar-arrow ml-auto w-4 h-4 text-white text-opacity-50 flex-shrink-0"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open && sidebarOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="pl-11 pr-2 pb-1 space-y-0.5 mt-0.5">
                <a href="{{ route('publikasi/kuemonev') }}" class="sidebar-sub-item block px-3 py-2 rounded-lg text-xs text-white text-opacity-60">Kuesioner</a>
                <a href="{{ route('publikasi/lapmonev') }}" class="sidebar-sub-item block px-3 py-2 rounded-lg text-xs text-white text-opacity-60">Laporan Monev</a>
                <a href="{{ route('publikasi/pedmonev') }}" class="sidebar-sub-item block px-3 py-2 rounded-lg text-xs text-white text-opacity-60">Pedoman Monev</a>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-white border-opacity-10 my-2 mx-1"></div>

        {{-- Rekap Nilai --}}
        <a href="{{ route('superadmin.rekap-nilai.index') }}"
           :class="sidebarOpen ? 'justify-start px-3' : 'justify-center px-0'"
           class="sidebar-item flex items-center gap-3 py-2.5 rounded-xl text-sm text-white text-opacity-75 font-medium">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
            </svg>
            <span x-show="sidebarOpen" class="whitespace-nowrap">Rekap Nilai</span>
        </a>

        {{-- Kelola Tampilan --}}
        <a href="{{ route('superadmin.settings.index') }}"
           :class="sidebarOpen ? 'justify-start px-3' : 'justify-center px-0'"
           class="sidebar-item flex items-center gap-3 py-2.5 rounded-xl text-sm text-white text-opacity-75 font-medium {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="sidebarOpen" class="whitespace-nowrap">Kelola Tampilan</span>
        </a>
        
        {{-- Notifikasi --}}
        <a href="{{ route('notifications.index') }}"
           :class="sidebarOpen ? 'justify-start px-3' : 'justify-center px-0'"
           class="sidebar-item flex items-center gap-3 py-2.5 rounded-xl text-sm text-white text-opacity-75 font-medium relative {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <div class="relative">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C8.67 6.165 8 7.388 8 9v5.159c0 .538-.214 1.055-.595 1.436L6 17h9z"/>
                </svg>
                {{-- Badge when sidebar is collapsed --}}
                @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                    <div x-show="!sidebarOpen" class="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-primary-main text-[9px] font-bold text-white border-2 border-primary-dark">
                        {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                    </div>
                @endif
            </div>
            
            <div x-show="sidebarOpen" class="flex items-center justify-between w-full overflow-hidden">
                <span class="whitespace-nowrap">Notifikasi</span>
                @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white bg-opacity-20 text-[10px] font-bold text-white">
                        {{ $unreadNotificationsCount }}
                    </span>
                @endif
            </div>
        </a>
        <div x-show="!sidebarOpen" class="border-t border-white border-opacity-10 my-2"></div>
    </nav>

    {{-- ===== FOOTER: Logout ===== --}}
    <div class="px-3 py-4 border-t border-white border-opacity-15">
        <a href="{{ route('logout') }}"
           :class="sidebarOpen ? 'justify-start px-3' : 'justify-center px-0'"
           class="sidebar-item flex items-center gap-3 py-2.5 rounded-xl text-sm text-white text-opacity-75 font-medium"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span x-show="sidebarOpen" class="whitespace-nowrap">Keluar</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>

</div>