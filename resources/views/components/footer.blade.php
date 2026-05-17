    <div class="font-poppins bg-red-700 text-white text-sm">
        <div class="container mx-auto px-16 py-6">
            <div class="grid md:grid-cols-2 gap-12">
                
                <!-- Kolom 1: Letak Geografis -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Letak Geografis</h3>
                    <div class="rounded-xl overflow-hidden shadow-2xl border border-red-600/50 w-full lg:w-[424px] h-[236px]">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7979.635005280486!2d109.338784!3d-0.038167!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e1d59929235e77b%3A0x6b772426359f1064!2sKomisi%20Informasi%20Provinsi%20Kalimantan%20Barat!5e0!3m2!1sid!2sid!4v1715695000000!5m2!1sid!2sid" 
                            class="w-full h-full border-0" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
                <!-- Kolom 2: Informasi Kontak -->
                <div class="space-y-4">
                    
                    <!-- Alamat -->
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Alamat</h3>
                        <p class=" text-white leading-relaxed">
                            {{ $appSettings['alamat'] ?? 'Komisi Informasi Provinsi Kalimantan Barat Jl. D.A.Hadi No.146, Akcaya, Kec. Pontianak Sel., Kota Pontianak, Kalimantan Barat 78121' }}
                        </p>
                    </div>

                    <!-- Alamat Email -->
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Alamat email</h3>
                        <div class=" space-y-1">
                            <p class="text-white">{{ $appSettings['email'] ?? 'komisiinformasi_provkalbar@yahoo.co.id' }}</p>
                            @if(!isset($appSettings['email']))
                                <p class="text-white">ki.kalbarprov@gmail.com</p>
                            @endif
                        </div>
                    </div>

                    <!-- Nomor Telepon -->
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Nomor Telepon</h3>
                        <div class=" space-y-1">
                            <p class="text-white">{{ $appSettings['no_telp'] ?? '081363142008 (Call Center)' }}</p>
                            @if(!isset($appSettings['no_telp']))
                                <p class="text-white">(0561) 810 3347 (Telp & Fax)</p>
                            @endif
                        </div>
                    </div>

                    <!-- Media Sosial -->
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Media Sosial</h3>
                        <div class="flex items-center space-x-3">
                            <!-- Facebook -->
                            <a href="{{ $appSettings['facebook'] ?? 'https://www.facebook.com/KI.Kalbar' }}" target="_blank" rel="noopener noreferrer" class="hover:opacity-80 transition-opacity">
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 26 26">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            <!-- Instagram -->
                            <a href="{{ $appSettings['instagram'] ?? 'https://www.instagram.com/ki_kalbar' }}" target="_blank" rel="noopener noreferrer" class="hover:opacity-80 transition-opacity">
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 26 26">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                            <!-- Twitter/X -->
                            <a href="{{ $appSettings['twitter'] ?? 'https://x.com/KI_Kalbar' }}" target="_blank" rel="noopener noreferrer" class="hover:opacity-80 transition-opacity">
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 26 26">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                            <!-- YouTube -->
                            <a href="{{ $appSettings['youtube'] ?? 'https://www.youtube.com/@komisiinformasiprovkalbar' }}" target="_blank" rel="noopener noreferrer" class="hover:opacity-80 transition-opacity">
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 26 26">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="border-t border-red-600 py-4">
            <div class="container mx-auto px-6">
                <p class="text-center text-white">
                    Copyright 2025 Komisi Informasi Provinsi Kalimantan Barat
                </p>
            </div>
        </div>
    </div>

</body>
</html>