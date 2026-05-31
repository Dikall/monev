@php
    $jawaban     = $jawabans[$pertanyaan->id] ?? null;
    $disabled    = $isClosed || !$isOpen;
    $indentClass = $indent ?? 'pl-8';

    // Nilai jawaban: cast ke int agar perbandingan konsisten
    $nilaiJawaban = $jawaban ? (int) $jawaban->jawaban : null;

    // Links: array dari model
    $links = ($jawaban && !empty($jawaban->links) && is_array($jawaban->links)) ? $jawaban->links : [];
    if (empty($links)) $links = [''];

    // Nama file dokumen yang sudah ada
    $namaFile = $jawaban?->dokumen_path ? basename($jawaban->dokumen_path) : '';
@endphp

<tr class="hover:bg-gray-50 transition-colors"
    x-data="{ 
        jawaban: '{{ $nilaiJawaban }}',
        links: {{ json_encode($links) }},
        hasFile: {{ $jawaban?->dokumen_path ? 'true' : 'false' }},
        fileName: '{{ $namaFile }}'
    }">

    {{-- NOMOR --}}
    <td class="px-4 py-3 text-gray-600 align-top {{ $indentClass }}">
        {{ $pertanyaan->nomor }}
    </td>

    {{-- PERTANYAAN --}}
    <td class="px-4 py-3 text-gray-700 align-top leading-relaxed">
        {{ $pertanyaan->pertanyaan_kuisioner }}
    </td>

    {{-- PILIHAN JAWABAN --}}
    <td class="px-4 py-3 align-top">
        <div class="flex items-center gap-3 mt-1">

            {{-- Ya --}}
            <label class="flex items-center gap-1
                {{ $disabled ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' }}">
                <input type="radio"
                       name="jawaban[{{ $pertanyaan->id }}]"
                       value="1"
                       x-model="jawaban"
                       @checked($nilaiJawaban === 1)
                       @disabled($disabled)
                       class="text-primary-dark focus:ring-primary-dark">
                <span class="text-sm text-gray-700">Ya</span>
            </label>

            {{-- Tidak --}}
            <label class="flex items-center gap-1
                {{ $disabled ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' }}">
                <input type="radio"
                       name="jawaban[{{ $pertanyaan->id }}]"
                       value="0"
                       x-model="jawaban"
                       @checked($nilaiJawaban === 0 && $jawaban !== null)
                       @disabled($disabled)
                       class="text-primary-dark focus:ring-primary-dark">
                <span class="text-sm text-gray-700">Tidak</span>
            </label>

        </div>
    </td>

    {{-- DATA PENDUKUNG: LINK --}}
    <td class="px-3 py-3 align-top border-l border-gray-100">
        <div class="space-y-2">
            <template x-for="(link, index) in links" :key="index">
                <div class="flex items-center gap-1 mb-1">
                    <input type="text"
                           :name="'links[' + {{ $pertanyaan->id }} + '][]'"
                           x-model="links[index]"
                           placeholder="Masukkan Link..."
                           @disabled($disabled)
                           class="flex-1 border border-gray-200 rounded-md px-3 py-1.5 text-xs text-gray-700
                                  placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-main
                                  {{ $disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white' }}">
                    
                    @if(!$disabled)
                    <button type="button" @click="links.splice(index, 1)" x-show="links.length > 1"
                            class="text-primary-main hover:text-primary-dark p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    @endif
                </div>
            </template>
            
            @if(!$disabled)
            <button type="button" @click="links.push('')"
                    class="text-[10px] font-bold text-primary-dark hover:text-primary-dark flex items-center gap-1 mt-1 bg-primary-light px-2 py-1 rounded">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Link
            </button>
            @endif

            {{-- Warning untuk Jawaban YA tanpa Bukti --}}
            <div x-show="jawaban == '1' && !hasFile && !links.some(l => l && l.trim() !== '')"
                 class="mt-2 p-2 bg-primary-light border border-primary-light rounded-md">
                <p class="text-[10px] text-primary-main font-bold leading-tight">
                    ⚠ Wajib mengisi Link atau Upload Dokumen jika menjawab YA agar terhitung dalam progres.
                </p>
            </div>
        </div>
    </td>

    {{-- DATA PENDUKUNG: UPLOAD DOKUMEN --}}
    <td class="px-3 py-3 align-top border-l border-gray-100">

        <div class="flex items-start gap-2">

            @if(!$disabled)
            <label class="flex-shrink-0 cursor-pointer">
                <div class="flex items-center gap-1.5 bg-primary-dark hover:bg-primary-dark text-white
                            text-xs font-medium px-3 py-1.5 rounded-md transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Pilih File
                </div>
                <input type="file"
                       name="dokumen[{{ $pertanyaan->id }}]"
                       accept="application/pdf"
                       class="hidden"
                       @change="fileName = $event.target.files[0]?.name || ''; hasFile = !!$event.target.files[0]">
            </label>
            @endif

            <div class="min-w-0">
                <p class="text-xs text-gray-500 break-all"
                   x-text="fileName || 'Tidak ada file dipilih'"></p>
                <p class="text-xs text-primary-main mt-0.5">Maksimum ukuran file 2MB</p>

                @if($jawaban?->dokumen_path)
                <a href="{{ Storage::url($jawaban->dokumen_path) }}"
                   target="_blank"
                   class="text-xs text-primary-dark underline hover:text-primary-dark mt-1 block">
                    Lihat Dokumen
                </a>
                @endif
            </div>

        </div>
    </td>

</tr>