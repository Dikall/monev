@extends('components.layouts.app')

@section('content')

<div class="p-6 px-16">

    {{-- HEADER KEMBALI --}}
    <div class="mb-8">
        <a href="{{ route('kuesioner.tab') }}"
           class="inline-flex items-center gap-1 text-primary-dark font-semibold hover:text-primary-dark text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
    </div>

    <h2 class="text-primary-dark font-bold text-lg mb-6">Hasil Penilaian</h2>

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
                    Lakukan Verifikasi Akun Terlebih Dahulu untuk dapat mengakses halaman ini.
                    Silakan hubungi administrator untuk mengaktifkan akun Anda.
                </p>
            </div>
        </div>

    @else

    {{-- INFORMASI BADAN PUBLIK & SKOR TOTAL --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-2xl overflow-hidden mb-8">
        <div class="p-8">
            <div class="flex flex-col lg:flex-row gap-10 lg:divide-x lg:divide-gray-100">
                
                {{-- Kiri: Informasi Responden --}}
                <div class="flex-1">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                        Informasi Responden
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-10">
                        <div class="space-y-1">
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Nama Badan Publik</p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $publicBody->nama_badan ?? '-' }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Nama Badan Publik</p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $kategoriAktif->name  ?? '-' }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Nama Badan Publik</p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $tahun->tahun ?? '-' }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Nama Badan Publik</p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $user->nama_responden }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Kanan: Skor Total (Jika sudah dinilai) --}}
                @if($sudahDinilai && $penilaian)
                <div class="lg:pl-10 lg:w-[400px] flex-shrink-0">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                        Skor Total Penilaian
                    </h3>

                    <div class="flex items-center gap-6">
                        {{-- Gauge Skor --}}
                        <div class="relative w-24 h-24 flex-shrink-0">
                            <svg viewBox="0 0 120 120" class="w-full h-full -rotate-90">
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#f1f5f9" stroke-width="12"/>
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#3783ED" stroke-width="12"
                                        stroke-dasharray="{{ round(($penilaian->skor_total / 100) * 339.29, 2) }} 339.29"
                                        stroke-linecap="round" class="transition-all duration-1000 ease-out"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-xl font-bold text-gray-900 leading-none">
                                    {{ number_format($penilaian->skor_total, 2) }}
                                </span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter mt-0.5">Skor</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <p class="text-2xl font-bold text-primary-dark leading-tight uppercase">
                                {{ $penilaian->predikat ?? '-' }}
                            </p>
                            <p class="text-[10px] font-medium text-gray-500 italic">
                                Sesuai Standar Keterbukaan Informasi Publik
                            </p>
                        </div>
                    </div>

                    @if($penilaian->catatan)
                        <div class="mt-6 p-3 bg-primary-light border-l-4 border-primary-dark rounded-r-lg">
                            <p class="text-[10px] font-black text-primary-dark uppercase tracking-widest mb-1">Catatan Verifikator</p>
                            <p class="text-xs text-primary-dark leading-relaxed italic">"{{ $penilaian->catatan }}"</p>
                        </div>
                    @endif
                </div>
                @else
                <div class="lg:pl-10 lg:w-[400px] flex-shrink-0 flex items-center justify-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <div class="text-center p-6">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Menunggu Penilaian</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- PENILAIAN BELUM ADA ALERT --}}
    @if(!$sudahDinilai)
        <div class="bg-primary-light border border-blue-100 rounded-2xl p-8 mb-8 flex items-start gap-4">
            <div class="w-10 h-10 bg-leafy text-black rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-blue-900 mb-1">Proses Verifikasi Sedang Berlangsung</h3>
                <p class="text-sm text-blue-800 leading-relaxed max-w-2xl">
                    Kuesioner Anda telah berhasil dikirim. Saat ini tim verifikator sedang melakukan penilaian terhadap jawaban dan bukti yang Anda berikan. 
                    Hasil skor rincian akan muncul di bawah ini setelah proses verifikasi selesai.
                </p>
            </div>
        </div>
    @else

        {{-- Tabel per Indikator --}}
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden mb-6">
            <div class="px-8 py-5 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">
                    Rincian Per Indikator
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-primary-dark text-white">
                            <th class="px-6 py-3 text-left font-semibold">No</th>
                            <th class="px-6 py-3 text-left font-semibold">Indikator</th>
                            <th class="px-6 py-3 text-center font-semibold">Dijawab Ya</th>
                            <th class="px-6 py-3 text-center font-semibold">Dijawab Tidak</th>
                            <th class="px-6 py-3 text-center font-semibold">Bobot Tercapai</th>
                            <th class="px-6 py-3 text-center font-semibold">Total Bobot</th>
                            <th class="px-6 py-3 text-center font-semibold">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($ringkasanPerIndikator as $index => $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-gray-600 font-medium">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 text-gray-800">
                                    {{ $item['indikator']->nama_indikator }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8
                                                 bg-green-100 text-green-700 rounded-full font-semibold text-xs">
                                        {{ $item['dijawab_ya'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8
                                                 bg-primary-light text-primary-dark rounded-full font-semibold text-xs">
                                        {{ $item['dijawab_tidak'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-700 font-medium">
                                    {{ $item['bobot_ya'] }}
                                </td>
                                <td class="px-6 py-4 text-center text-gray-500">
                                    {{ $item['total_bobot'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $persen = $item['persentase'];
                                        $colorClass = $persen >= 80 ? 'text-green-700 bg-green-50 border-green-200'
                                            : ($persen >= 50 ? 'text-yellow-700 bg-yellow-50 border-yellow-200'
                                            : 'text-primary-dark bg-primary-light border-primary-light');
                                    @endphp
                                    <span class="inline-block px-2.5 py-1 rounded-full border text-xs font-semibold {{ $colorClass }}">
                                        {{ $persen }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">
                                    Tidak ada data indikator.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @endif
    {{-- end sudahDinilai --}}

    @endif
    {{-- end akun aktif --}}

</div>

@endsection