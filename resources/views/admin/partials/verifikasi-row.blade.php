@foreach ($items as $item)
    @if ($item->childrenRecursive->isEmpty())
        @php
            $jawaban = $jawabans[$item->id] ?? null;
        @endphp
        <tr class="hover:bg-gray-50 align-top border-b border-gray-100">

            {{-- No --}}
            <td class="px-2 py-3 text-center text-gray-500 text-xs whitespace-nowrap" style="width: 3%">
                {{ $item->nomor }}
            </td>

            {{-- Pertanyaan --}}
            <td class="px-3 py-3 text-gray-700 text-xs leading-relaxed" style="width: 28%; padding-left: {{ ($depth * 1.25) + 0.75 }}rem">
                {{ $item->pertanyaan_kuisioner }}
            </td>

            {{-- Jawaban: tampilkan Ya / Tidak --}}
            <td class="px-2 py-3 text-center text-xs font-medium" style="width: 7%">
                @if ($jawaban?->jawaban === 'Ya' || $jawaban?->jawaban == 1 || $jawaban?->jawaban === true)
                    <span class="text-gray-700">Ya</span>
                @elseif ($jawaban?->jawaban === 'Tidak' || $jawaban?->jawaban == 0 || $jawaban?->jawaban === false)
                    <span class="text-gray-700">Tidak</span>
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </td>

            {{-- Link Bukti --}}
            <td class="px-3 py-3 text-center" style="width: 10%">
                @if ($jawaban?->links && is_array($jawaban->links))
                    <div class="flex flex-col gap-1 items-center">
                        @foreach ($jawaban->links as $link)
                            <div class="group relative">
                                <a href="{{ $link }}" target="_blank"
                                   class="text-blue-500 hover:underline text-xs break-all">
                                    {{ Str::limit($link, 20) }}
                                </a>
                                {{-- Hover Preview Link --}}
                                <div class="invisible group-hover:visible absolute z-[100] bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 p-2 bg-gray-900 text-white text-[10px] rounded shadow-xl pointer-events-none transition-all opacity-0 group-hover:opacity-100">
                                    <div class="font-semibold border-b border-gray-700 pb-1 mb-1">Link Full:</div>
                                    <div class="break-all">{{ $link }}</div>
                                    <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif ($jawaban?->links)
                    <div class="group relative">
                        <a href="{{ $jawaban->links }}" target="_blank"
                           class="text-blue-500 hover:underline text-xs break-all">
                            {{ Str::limit($jawaban->links, 25) }}
                        </a>
                        <div class="invisible group-hover:visible absolute z-[100] bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 p-2 bg-gray-900 text-white text-[10px] rounded shadow-xl pointer-events-none transition-all opacity-0 group-hover:opacity-100">
                            <div class="font-semibold border-b border-gray-700 pb-1 mb-1">Link Full:</div>
                            <div class="break-all">{{ $jawaban->links }}</div>
                            <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                        </div>
                    </div>
                @else
                    <span class="text-gray-300 text-xs">—</span>
                @endif
            </td>

            {{-- Dokumen Bukti --}}
            <td class="px-3 py-3 text-center" style="width: 10%">
                @if ($jawaban?->dokumen_path)
                    <div class="group relative inline-block">
                        <a href="{{ Storage::url($jawaban->dokumen_path) }}" target="_blank"
                           class="inline-flex items-center gap-1 text-blue-500 hover:underline text-xs font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Dokumen
                        </a>
                        {{-- Hover Preview Document --}}
                        <div class="invisible group-hover:visible absolute z-[100] bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-2 bg-white border border-gray-200 rounded-lg shadow-2xl pointer-events-none transition-all opacity-0 group-hover:opacity-100">
                            @php
                                $ext = pathinfo($jawaban->dokumen_path, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp
                            @if($isImage)
                                <img src="{{ Storage::url($jawaban->dokumen_path) }}" class="w-full h-auto rounded shadow-sm mb-2 max-h-32 object-cover">
                            @else
                                <div class="flex flex-col items-center py-4 bg-gray-50 rounded mb-2">
                                    <svg class="w-10 h-10 text-primary-main" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                    </svg>
                                    <span class="text-[10px] font-bold text-gray-500 uppercase mt-2">{{ $ext }} Document</span>
                                </div>
                            @endif
                            <div class="text-[9px] text-gray-500 text-center truncate px-1">
                                {{ basename($jawaban->dokumen_path) }}
                            </div>
                            <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-white border-b border-r border-gray-200 rotate-45"></div>
                        </div>
                    </div>
                @else
                    <span class="text-gray-300 text-xs">—</span>
                @endif
            </td>

            {{-- Verifikasi: Radio Ya / Tidak --}}
            <td class="px-2 py-3 text-center" style="width: 12%">
                <div class="flex items-center justify-center gap-2 flex-wrap">
                    <label class="inline-flex items-center gap-1 cursor-pointer text-xs text-gray-700 whitespace-nowrap">
                        <input type="radio"
                               name="verifikasi[{{ $item->id }}]"
                               value="1"
                               class="accent-primary-dark w-3.5 h-3.5"
                               {{ ($jawaban?->is_verified === true) ? 'checked' : '' }}
                               @if(auth()->user()->hasRole('Super Admin')) disabled @else @change="triggerAutoSave()" @endif>
                        Ya
                    </label>
                    <label class="inline-flex items-center gap-1 cursor-pointer text-xs text-gray-700 whitespace-nowrap">
                        <input type="radio"
                               name="verifikasi[{{ $item->id }}]"
                               value="0"
                               class="accent-primary-dark w-3.5 h-3.5"
                               {{ ($jawaban?->is_verified === false) ? 'checked' : '' }}
                               @if(auth()->user()->hasRole('Super Admin')) disabled @else @change="triggerAutoSave()" @endif>
                        Tidak
                    </label>
                </div>
            </td>

            {{-- Catatan + Upload --}}
            <td class="px-3 py-3" style="width: 30%">
                {{-- Input Catatan --}}
                <input type="text"
                       name="catatan[{{ $item->id }}]"
                       value="{{ $jawaban?->catatan_verifikasi ?? '' }}"
                       placeholder="Catatan"
                       class="w-full rounded border border-gray-300 px-2 py-1.5 text-xs text-gray-700 focus:border-primary-main focus:ring-1 focus:ring-primary-main outline-none mb-2"
                       @if(auth()->user()->hasRole('Super Admin')) readonly @else @input="triggerAutoSave()" @endif>
            </td>

            @if(auth()->user()->hasRole('Super Admin'))
                <td class="px-3 py-3 text-xs text-gray-600" style="width: 10%">
                    {{ $jawaban?->verifikator?->name ?? '-' }}
                </td>
            @endif
        </tr>

    @else
        {{-- Sub-judul --}}
        <tr class="bg-gray-50">
            <td class="py-2 font-semibold text-gray-600 text-xs" colspan="{{ auth()->user()->hasRole('Super Admin') ? 8 : 7 }}"
                style="padding-left: {{ ($depth * 1.25) + 0.75 }}rem">
                {{ $item->nomor }}. {{ $item->pertanyaan_kuisioner }}
            </td>
        </tr>

        @include('admin.partials.verifikasi-row', [
            'items'    => $item->childrenRecursive,
            'depth'    => $depth + 1,
            'jawabans' => $jawabans,
        ])
    @endif
@endforeach