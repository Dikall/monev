@extends('components.layouts.app')

@section('content')

<div class="p-6 px-16">

    {{-- HEADER: SELAMAT DATANG --}}
    <div class="mb-8">
        <h1 class="text-xl font-semibold text-gray-800">
            Selamat Datang, {{ $publicBody->nama_badan ?? $user->name }}
        </h1>
    </div>

    {{-- CONTAINER 1: PROGRESS (Locked if inactive) --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-8 mb-8 {{ isset($tidak_aktif) && $tidak_aktif ? 'opacity-60 grayscale' : '' }}">
        <div class="flex flex-col md:flex-row items-center gap-8">
            {{-- Circular Progress --}}
            <div class="relative flex items-center justify-center">
                @if(isset($tidak_aktif) && $tidak_aktif)
                    <div class="w-32 h-32 rounded-full border-8 border-gray-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                @else
                    <svg class="w-32 h-32 transform -rotate-90">
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-gray-100" />
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent"
                                stroke-dasharray="364.42"
                                stroke-dashoffset="{{ 364.42 - (364.42 * $persen / 100) }}"
                                class="text-primary-dark transition-all duration-1000 ease-out" />
                    </svg>
                    <span class="absolute text-xl font-bold text-gray-800">{{ $persen }}%</span>
                @endif
            </div>

            {{-- Progress Info --}}
            <div class="flex-1 text-center md:text-left">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">
                    @if(isset($tidak_aktif) && $tidak_aktif)
                        Progres Kuesioner Belum Tersedia
                    @else
                        Anda Telah mengisi {{ $persen }}% kuesioner Monev {{ $tahun->tahun ?? now()->year }}
                    @endif
                </h2>
                <p class="text-sm text-gray-400">
                    @if(isset($tidak_aktif) && $tidak_aktif)
                        Silakan verifikasi akun Anda terlebih dahulu untuk mulai mengisi kuesioner.
                    @else
                        Terakhir Diperbarui: <span class="font-medium text-gray-500">{{ $terakhirDiperbarui ? \Carbon\Carbon::parse($terakhirDiperbarui)->translatedFormat('d F Y') : '-' }}</span>
                    @endif
                </p>
            </div>

            {{-- Action Button --}}
            <div class="flex-shrink-0">
                @if(isset($tidak_aktif) && $tidak_aktif)
                    <button disabled class="px-6 py-3 bg-gray-300 text-white font-bold rounded-lg cursor-not-allowed">
                        Kuesioner Terkunci
                    </button>
                @elseif($sudahSubmit)
                    <a href="{{ route('kuesioner.hasil') }}"
                   class="inline-flex items-center gap-2 px-8 py-2.5 bg-primary-dark text-white
                          font-semibold rounded-lg hover:bg-primary-dark transition-colors shadow-sm text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5 a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414 a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Lihat Hasil Penilaian
                </a>
                @elseif($isOpen)
                    <a href="{{ route('kuesioner.index') }}" class="inline-flex items-center px-6 py-3 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark transition-all shadow-sm">
                        Lanjutkan Pengisian Kuesioner
                    </a>
                @else
                    <span class="text-sm text-gray-400 italic">Periode pengisian ditutup</span>
                @endif
            </div>
        </div>
    </div>

    {{-- BOTTOM SECTION: GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        {{-- CARD: KIRI (Status Akun atau Progres Per Indikator) --}}
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-6">
            @if(isset($tidak_aktif) && $tidak_aktif)
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 bg-primary-light rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3C6.477 3 2 7.477 2 12s4.477 9 10 9 10-4.477 10-9S17.523 3 12 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Status Akun</h3>
                </div>
                <div class="py-4">
                    <p class="text-sm text-gray-600 leading-relaxed mb-4">
                        <span class="font-bold text-primary-main text-base block mb-1">Akun Belum Aktif</span>
                        Akun Anda saat ini belum diverifikasi oleh administrator. Mohon hubungi administrator Badan Publik atau Komisi Informasi untuk proses aktivasi akun.
                    </p>
                    <a href="mailto:admin@e-monev.id" class="text-sm text-primary-dark font-bold hover:underline flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Hubungi Administrator
                    </a>
                </div>
            @else
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3C6.477 3 2 7.477 2 12s4.477 9 10 9 10-4.477 10-9S17.523 3 12 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Yang Belum Diisi</h3>
                </div>
                <ul class="space-y-3">
                    @forelse($indikatorBelumLengkap as $item)
                        <li class="flex items-start gap-2 text-sm text-gray-600">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-gray-400 flex-shrink-0"></span>
                            <span>{{ $item }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-green-600 font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Semua indikator telah terisi.
                        </li>
                    @endforelse
                </ul>
            @endif
        </div>

        {{-- CARD: KANAN (Info Penting) --}}
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-6 {{ isset($tidak_aktif) && $tidak_aktif ? 'opacity-60 grayscale' : '' }}">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Info Penting</h3>
            </div>
            <ul class="space-y-3">
                <li class="flex items-start gap-2 text-sm text-gray-600">
                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-gray-400 flex-shrink-0"></span>
                    <span>Mohon lengkapi kuesioner sebelum 
                        <strong class="text-primary-dark">
                            {{ isset($tenggat) && $tenggat ? \Carbon\Carbon::parse($tenggat->waktu_nonaktif)->translatedFormat('d F Y') : 'batas waktu ditentukan' }}
                        </strong>
                    </span>
                </li>
                <li class="flex items-start gap-2 text-sm text-gray-600">
                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-gray-400 flex-shrink-0"></span>
                    <span>Pastikan data yang diinput sudah benar dan valid sebelum melakukan submit kuesioner.</span>
                </li>
                @if(isset($isOpen) && $isOpen)
                    <li class="flex items-start gap-2 text-sm text-gray-600">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-gray-400 flex-shrink-0"></span>
                        <span>Bimbingan Teknis E-Monev dapat dilihat pada menu Pedoman Monev.</span>
                    </li>
                @endif
            </ul>
        </div>

    </div>

</div>

@endsection
