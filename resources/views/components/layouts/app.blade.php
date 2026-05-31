<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Monev Komisi Informasi Kalimantan Barat</title>

    <!-- Favicon / Ikon Tab Browser (Mempunyai logo tersendiri, terpisah dari logo header) -->
    <link rel="shortcut icon" href="{{ $appSettings['favicon'] ?? asset('favicon.ico') }}" type="image/x-icon">

    <!-- Styles -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Audiowide&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Audiowide&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

{{--
    ✅ KUNCI PERBAIKAN: x-data="{ sidebarOpen: true }" dipasang di <body>
    Dengan ini, state sidebarOpen bisa diakses oleh SEMUA elemen di dalam body,
    termasuk sidebar (untuk toggle) dan main content (untuk margin kiri).
    Sebelumnya x-data ada di dalam sidebar component saja, sehingga
    main content tidak bisa membaca nilai sidebarOpen → konten tertimpa sidebar.
--}}
<body class="overflow-x-hidden font-inter bg-white text-gray-900" x-data="{ sidebarOpen: true }">

    <!-- Wrapper seluruh halaman -->
    <div class="flex flex-col min-h-screen w-screen">

        {{-- ===== SIDEBAR (hanya Super Admin) ===== --}}
        @auth
            @if(auth()->user()->hasRole('Super Admin'))
                @include('components.sidebar')
            @endif
        @endauth

        {{-- ===== HEADER ===== --}}
        @guest
            <header class="sticky top-0 z-[999] bg-white shadow-md w-full h-20">
                @include('components.header-general')
            </header>
        @endguest

        @auth
            @if(auth()->user()->hasRole('Badan Publik'))
                <header class="sticky top-0 z-[999] bg-white shadow-md w-full h-20">
                    @include('components.header-bp')
                </header>
            @elseif(auth()->user()->hasRole('Admin'))
                <header class="sticky top-0 z-[999] bg-white shadow-md w-full h-20">
                    @include('components.header-admin')
                </header>
            @endif
        @endauth

        {{-- ===== MAIN CONTENT ===== --}}
        <main class="flex-grow">
            {{--
                ✅ :class binding di sini bisa bekerja karena sidebarOpen
                sudah ada di scope body (x-data di atas).
                ml-72 = 288px (lebar sidebar expanded, w-72)
                ml-20 = 80px  (lebar sidebar collapsed, w-20)
                Hanya Super Admin yang mendapat margin kiri.
            --}}
            <div class="transition-all duration-300
                @auth
                    @if(auth()->user()->hasRole('Super Admin'))
                        " :class="sidebarOpen ? 'ml-72' : 'ml-20'
                    @endif
                @endauth
                ">
                @yield('content')
            </div>
        </main>

        {{-- ===== FOOTER ===== --}}
        @guest
            @include('components.footer')
        @endguest

        @auth
            @include('components.footer-auth')
        @endauth

    </div>

    <!-- ===== SCRIPTS ===== -->

    {{--
        ✅ Alpine.js hanya di-load SATU kali.
        Kode asli memiliki dua import Alpine (cdn.min.js & //unpkg.com/alpinejs)
        yang menyebabkan konflik. Salah satunya dihapus.
    --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        inter: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            dark: '#1860C4',
                            main: '#3783ED',
                            light: '#C7DFFF',
                        },
                        neutral: {
                            darkGrey: '#525252',
                            grey: '#C6C6C6',
                            lightGrey: '#EBEBEB',
                            black: '#1B1B1B',
                            white: '#FFFFFF',
                        }
                    }
                },
            },
        };
    </script>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

    <script>
        // ✅ Cek elemen map ada dulu sebelum inisialisasi
        // Mencegah error JS di halaman yang tidak memiliki peta
        if (document.getElementById('map')) {
            const map = L.map('map').setView([0.3630254114686059, 113.73327448321643], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            const drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            const drawControl = new L.Control.Draw({
                edit: { featureGroup: drawnItems },
                draw: {
                    polygon: true,
                    marker: false,
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    circlemarker: false
                }
            });
            map.addControl(drawControl);

            fetch('/geojson')
                .then(res => res.json())
                .then(data => {
                    data.forEach(feature => {
                        const layer = L.geoJSON(feature, {
                            style: {
                                color: feature.properties.color || 'blue',
                                fillOpacity: 0.5
                            },
                            onEachFeature: function (feature, layer) {
                                layer.bindPopup(
                                    `<b>${feature.properties.name}</b><br>Kategori: ${feature.properties.kategori}`
                                    + `<br><button onclick="deleteFeature(${feature.properties.id})">Hapus</button>`
                                );
                                drawnItems.addLayer(layer);
                            }
                        });
                    });
                });

            map.on(L.Draw.Event.CREATED, function (e) {
                const layer = e.layer;
                const name     = prompt("Nama area:");
                const color    = prompt("Warna (misal 'red' atau '#ff0000'):");
                const kategori = prompt("Kategori / Tingkat Risiko:");

                if (!name || !color) {
                    alert("Nama dan warna wajib diisi!");
                    return;
                }

                const geojson = layer.toGeoJSON();
                geojson.properties = {};

                fetch('/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: name,
                        color: color,
                        kategori: kategori,
                        geojson: JSON.stringify(geojson)
                    })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                });
            });

            function deleteFeature(id) {
                if (!confirm("Yakin ingin menghapus area ini?")) return;

                fetch(`/delete/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                });
            }
        }
    </script>

    @stack('scripts')
</body>
</html>