@extends('components.layouts.app')

@section('content')

<div class="p-6 px-16">

    {{-- HEADER KEMBALI --}}
    <div class="mb-8">
        <a href="{{ route('badanpublik/beranda') }}"
           class="inline-flex items-center gap-1 text-primary-dark font-semibold hover:text-primary-dark text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
    </div>

    {{-- AKUN BELUM AKTIF --}}
    @if(isset($tidak_aktif) && $tidak_aktif)
        <div class="flex flex-col items-center justify-center py-32 text-center">
            <div class="bg-white rounded-2xl shadow-md border border-primary-light px-12 py-16 max-w-lg">
                <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-primary-dark"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M12 3C6.477 3 2 7.477 2 12s4.477 9 10 9
                                 10-4.477 10-9S17.523 3 12 3z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Akun Belum Aktif</h2>
                <p class="text-gray-500 leading-relaxed">
                    Lakukan Verifikasi Akun Terlebih Dahulu untuk dapat mengakses halaman
                    kuesioner ini. Silakan hubungi administrator untuk mengaktifkan akun Anda.
                </p>
            </div>
        </div>

    @else

    {{-- ALERT --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-primary-light border border-primary-light text-primary-dark px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- KARTU UTAMA --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">

        {{-- INFO RESPONDEN --}}
        <div class="px-8 py-6 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                Informasi Responden
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-12">

                <div class="flex flex-col gap-1">
                    <span class="text-xs text-gray-400 font-medium">Nama Responden</span>
                    <span class="text-sm font-semibold text-gray-800">
                        {{ $user->nama_responden }}
                    </span>
                </div>

                <div class="flex flex-col gap-1">
                    <span class="text-xs text-gray-400 font-medium">Nama Badan Publik</span>
                    <span class="text-sm font-semibold text-gray-800">
                        {{ $publicBody->nama_badan ?? '-' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1">
                    <span class="text-xs text-gray-400 font-medium">Kategori</span>
                    <span class="text-sm font-semibold text-gray-800">
                        {{ $kategoriAktif->name ?? '-' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1">
                    <span class="text-xs text-gray-400 font-medium">Batas Waktu</span>
                    <span class="text-sm font-semibold">
                        @if($tenggat)
                            @if($isClosed)
                                <div class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                                    <svg class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6
                                                a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span>
                                        Periode pengisian telah berakhir pada
                                        <strong>
                                            {{ \Carbon\Carbon::parse($tenggat->waktu_nonaktif)
                                                ->translatedFormat('d F Y H:i') }}
                                        </strong>.
                                    </span>
                                </div>
                            @elseif($isOpen)
                                <div class="flex items-center gap-3 text-sm font-semibold text-green-800">
                                    <svg class="w-5 h-5 text-green-800 flex-shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>
                                        Periode pengisian aktif hingga
                                        <em>
                                            <strong>
                                                {{ \Carbon\Carbon::parse($tenggat->waktu_nonaktif)
                                                    ->translatedFormat('d F Y H:i') }}
                                            </strong>
                                        </em>
                                    </span>
                                </div>
                            @else
                                <div class="flex items-center gap-3 text-sm font-semibold text-yellow-700">
                                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>
                                        Periode pengisian belum dibuka. Akan dibuka pada
                                        <strong>
                                            {{ \Carbon\Carbon::parse($tenggat->waktu_aktif)
                                                ->translatedFormat('d F Y H:i') }}
                                        </strong>.
                                    </span>
                                </div>
                            @endif
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg text-sm">
                                Belum ada tenggat pengisian untuk kategori ini.
                            </div>
                        @endif
                    </span>
                </div>

            </div>
        </div>

        {{-- PROGRESS PENGISIAN --}}
        @if($isOpen || $isClosed || $sudahSubmit)
        <div class="px-8 py-5 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">
                Progress Pengisian
            </h3>

            @php
                $persen = $totalPertanyaan > 0
                    ? round(($totalDijawab / $totalPertanyaan) * 100)
                    : 0;
            @endphp

            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-primary-dark h-2.5 rounded-full transition-all duration-500"
                             style="width: {{ $persen }}%"></div>
                    </div>
                </div>
                <span class="text-sm font-semibold text-gray-700 whitespace-nowrap">
                    {{ $totalDijawab }} / {{ $totalPertanyaan }} pertanyaan
                    ({{ $persen }}%)
                </span>
            </div>

            @if($sudahSubmit)
                <div class="mt-3 flex items-center gap-2 text-green-700 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                    Kuesioner telah di-submit. Jawaban telah dikunci.
                </div>
            @endif
        </div>
        @endif

        {{-- TOMBOL AKSI --}}
        <div class="px-8 py-6 flex items-center justify-end gap-4">

            @if($sudahSubmit)
                {{-- Setelah submit: tampilkan tombol Hasil Penilaian saja --}}
                <a href="{{ route('kuesioner.hasil') }}"
                   class="inline-flex items-center gap-2 px-8 py-2.5 bg-primary-dark text-white
                          font-semibold rounded-lg hover:bg-primary-dark transition-colors shadow-sm text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5
                                 a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414
                                 a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Hasil Penilaian
                </a>

            @elseif($isOpen)
                {{-- Belum submit & periode aktif: Edit + Submit --}}
                <a href="{{ route('kuesioner.index') }}"
                   class="inline-flex items-center gap-2 px-8 py-2.5 border-2 border-primary-dark
                          text-primary-dark font-semibold rounded-lg hover:bg-primary-light
                          transition-colors text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828
                                 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0
                                 011.172-2.828z"/>
                    </svg>
                    Edit Jawaban
                </a>

                {{-- Tombol Submit — buka modal konfirmasi --}}
                <button type="button"
                        onclick="document.getElementById('modalSubmit').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-8 py-2.5 bg-primary-dark text-white
                               font-semibold rounded-lg hover:bg-primary-dark transition-colors shadow-sm text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                    Submit
                </button>

            @else
                {{-- Periode belum/sudah tutup & belum submit --}}
                <span class="text-sm text-gray-400 italic">
                    Tidak tersedia — periode pengisian
                    {{ $isClosed ? 'telah berakhir' : 'belum dibuka' }}.
                </span>
            @endif

        </div>

    </div>

    @endif {{-- end akun aktif --}}

</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- MODAL KONFIRMASI SUBMIT                               --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div id="modalSubmit"
     class="hidden fixed inset-0 z-50 flex items-center justify-center"
     x-data>

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"
         onclick="document.getElementById('modalSubmit').classList.add('hidden')"></div>

    {{-- Panel --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Submit Kuesioner</h3>
            <button type="button"
                    onclick="document.getElementById('modalSubmit').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-6">
            <p class="text-gray-700 text-base leading-relaxed">
                Apakah Anda yakin ingin melakukan Submit Kuesioner?
            </p>
            <p class="text-sm text-gray-500 mt-2">
                Setelah di-submit, jawaban akan dikunci dan tidak dapat diubah kembali.
            </p>
        </div>

        {{-- Footer Tombol --}}
        <div class="px-6 pb-6 flex items-center justify-center gap-4">
            {{-- Batal --}}
            <button type="button"
                    onclick="document.getElementById('modalSubmit').classList.add('hidden')"
                    class="px-8 py-2.5 border-2 border-primary-dark text-primary-dark font-bold
                           rounded-xl hover:bg-primary-light transition-colors text-sm">
                Batal
            </button>

            {{-- Ya — POST ke route submit --}}
            <form action="{{ route('kuesioner.submit') }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-10 py-2.5 bg-primary-dark text-white font-bold rounded-xl
                               hover:bg-primary-dark transition-colors shadow-sm text-sm">
                    Ya
                </button>
            </form>
        </div>

    </div>
</div>

@endsection