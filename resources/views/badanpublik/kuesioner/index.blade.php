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

    {{-- NOTIFIKASI AKUN BELUM AKTIF --}}
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

    {{-- INDIKATOR STATUS AUTO-SAVE --}}
    @if($isOpen && !$isClosed)
    <div id="autosave-status"
         class="mb-4 hidden items-center gap-2 text-xs text-gray-500 justify-end">
        <svg id="autosave-spinner" class="w-3.5 h-3.5 animate-spin text-gray-400 hidden"
             fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <span id="autosave-text"></span>
    </div>
    @endif

    {{-- TAB INDIKATOR --}}
    <div class="bg-white border shadow-sm rounded-lg overflow-hidden mb-6">
        <div class="flex border-b overflow-x-auto">
            @forelse($indikators as $ind)
                <a href="{{ route('kuesioner.index', ['indikator_id' => $ind->id]) }}"
                   class="px-5 py-4 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
                          {{ $ind->id == $indikatorId
                              ? 'border-primary-dark text-primary-dark'
                              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ $ind->nama_indikator }}
                </a>
            @empty
                <span class="px-5 py-4 text-sm text-gray-400 italic">Belum ada indikator.</span>
            @endforelse
        </div>

        @if($indikatorAktif)
            <div class="px-6 py-3 text-sm text-gray-500 bg-white">
                {{ $indikatorAktif->keterangan
                    ?? '<span class="italic text-gray-400">Tidak ada keterangan.</span>' }}
            </div>
        @endif
    </div>

    {{-- STATUS TENGGAT --}}
    @if($tenggat)
        @if($isClosed)
            <div class="mb-4 bg-gray-100 border border-gray-300 text-gray-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none"
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
                    Jawaban dikunci dan tidak dapat diubah.
                </span>
            </div>
        @elseif($isOpen)
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>
                    Periode pengisian aktif hingga
                    <strong>
                        {{ \Carbon\Carbon::parse($tenggat->waktu_nonaktif)
                            ->translatedFormat('d F Y H:i') }}
                    </strong>.
                </span>
            </div>
        @else
            <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-600 flex-shrink-0" fill="none"
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
        <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg text-sm">
            Belum ada tenggat pengisian untuk kategori ini.
        </div>
    @endif

    {{-- FORM KUESIONER --}}
    <form id="form-kuesioner"
          action="{{ route('kuesioner.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="kategori_id"  value="{{ $kategoriId }}">
        <input type="hidden" name="indikator_id" value="{{ $indikatorId }}">

        <div class="bg-white border shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">

                <thead>
                    <tr class="bg-primary-dark text-white">
                        <th class="px-4 py-3 text-left w-16">No</th>
                        <th class="px-4 py-3 text-left">Pertanyaan</th>
                        <th class="px-4 py-3 text-left w-40">Pilihan Jawaban</th>
                        <th class="px-4 py-3 text-center border-l border-primary-main w-48 text-xs font-semibold">Link</th>
                        <th class="px-4 py-3 text-center border-l border-primary-main w-64 text-xs font-semibold">Upload Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">

                    @forelse($pertanyaans as $judul)

                        {{-- ── JUDUL (I, II, III) ── --}}
                        <tr class="bg-primary-light border-t-2 border-primary-light">
                            <td class="px-4 py-3 font-bold text-primary-dark align-top">
                                {{ $judul->nomor }}
                            </td>
                            <td colspan="4" class="px-4 py-3 font-bold text-primary-dark uppercase tracking-wide">
                                {{ $judul->pertanyaan_kuisioner }}
                            </td>
                        </tr>

                        @if($judul->children->isEmpty())
                            <tr>
                                <td colspan="5" class="pl-10 py-3 text-xs text-gray-400 italic">
                                    Tidak ada isi untuk judul ini.
                                </td>
                            </tr>
                        @else
                            @foreach($judul->childrenRecursive as $child)

                                @php
                                    $childIsSubJudul = $child->level === 'subjudul'
                                        || ($child->level === null && $child->is_parent);
                                    $childIsPertanyaan = $child->level === 'pertanyaan'
                                        || ($child->level === null && !$child->is_parent);
                                @endphp

                                @if($childIsSubJudul)

                                    {{-- ── SUB JUDUL (A, B, C) ── --}}
                                    <tr class="bg-gray-50 border-t border-gray-200">
                                        <td class="px-4 py-2 pl-10 font-semibold text-gray-700 align-top">
                                            {{ $child->nomor }}
                                        </td>
                                        <td colspan="4" class="px-4 py-2 font-semibold text-gray-700 italic">
                                            {{ $child->pertanyaan_kuisioner }}
                                        </td>
                                    </tr>

                                    @if($child->children->isEmpty())
                                        <tr>
                                            <td colspan="5" class="pl-16 py-2 text-xs text-gray-400 italic">
                                                Tidak ada pertanyaan di sub judul ini.
                                            </td>
                                        </tr>
                                    @else
                                        @foreach($child->childrenRecursive as $pertanyaan)
                                            @include('badanpublik.kuesioner._baris_pertanyaan', [
                                                'pertanyaan' => $pertanyaan,
                                                'jawabans'   => $jawabans,
                                                'isClosed'   => $isClosed,
                                                'isOpen'     => $isOpen,
                                                'indent'     => 'pl-16',
                                            ])
                                        @endforeach
                                    @endif

                                @elseif($childIsPertanyaan)

                                    {{-- ── PERTANYAAN LANGSUNG (tanpa sub judul) ── --}}
                                    @include('badanpublik.kuesioner._baris_pertanyaan', [
                                        'pertanyaan' => $child,
                                        'jawabans'   => $jawabans,
                                        'isClosed'   => $isClosed,
                                        'isOpen'     => $isOpen,
                                        'indent'     => 'pl-10',
                                    ])

                                @endif

                            @endforeach
                        @endif

                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                Belum ada pertanyaan untuk indikator ini.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- TOMBOL SIMPAN --}}
        @if($isOpen && !$isClosed)
            <div class="flex justify-end mt-6">
                <button type="submit"
                    class="px-10 py-2.5 bg-primary-dark text-white font-semibold rounded-lg
                           hover:bg-primary-dark transition-colors shadow-sm">
                    Simpan
                </button>
            </div>
        @endif

    </form>

    @endif {{-- end akun aktif --}}

</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- AUTO-SAVE SCRIPT                                              --}}
{{-- Menyimpan jawaban radio + links via AJAX sebelum halaman      --}}
{{-- ditutup / berpindah (visibilitychange + beforeunload)         --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
@if($isOpen && !$isClosed)
<script>
(function () {
    'use strict';

    const AUTOSAVE_URL  = "{{ route('kuesioner.autosave') }}";
    const CSRF_TOKEN    = "{{ csrf_token() }}";
    const KATEGORI_ID   = "{{ $kategoriId }}";
    const INDIKATOR_ID  = "{{ $indikatorId }}";

    const statusEl  = document.getElementById('autosave-status');
    const spinnerEl = document.getElementById('autosave-spinner');
    const textEl    = document.getElementById('autosave-text');

    let isDirty    = false;  // ada perubahan sejak terakhir disimpan
    let isSaving   = false;
    let autoTimer  = null;

    // ── Tandai form sudah berubah ────────────────────────────────
    function markDirty() {
        isDirty = true;
        showStatus('Ada perubahan belum disimpan…', false);

        // Auto-save 3 detik setelah berhenti mengetik / memilih
        clearTimeout(autoTimer);
        autoTimer = setTimeout(doAutoSave, 3000);
    }

    // ── Tampilkan status ─────────────────────────────────────────
    function showStatus(msg, loading) {
        statusEl.classList.remove('hidden');
        statusEl.classList.add('flex');
        textEl.textContent = msg;
        spinnerEl.classList.toggle('hidden', !loading);
    }

    function hideStatus() {
        statusEl.classList.add('hidden');
        statusEl.classList.remove('flex');
    }

    // ── Kumpulkan data form (hanya radio & text, bukan file) ─────
    function collectFormData() {
        const form     = document.getElementById('form-kuesioner');
        const payload  = new URLSearchParams();

        payload.append('_token',       CSRF_TOKEN);
        payload.append('kategori_id',  KATEGORI_ID);
        payload.append('indikator_id', INDIKATOR_ID);

        // Radio buttons (jawaban[id] = 0/1)
        form.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
            payload.append(radio.name, radio.value);
        });

        // Text inputs (links[id])
        form.querySelectorAll('input[type="text"][name^="links["]').forEach(input => {
            if (input.value.trim()) {
                payload.append(input.name, input.value.trim());
            }
        });

        return payload;
    }

    // ── Kirim AJAX auto-save ─────────────────────────────────────
    async function doAutoSave(isUnloading = false) {
        if (!isDirty || isSaving) return;

        isSaving = true;
        if (!isUnloading) showStatus('Menyimpan otomatis…', true);

        const payload = collectFormData();

        try {
            if (isUnloading && navigator.sendBeacon) {
                // sendBeacon: andalan saat halaman mau ditutup
                // Harus pakai Blob agar bisa set Content-Type
                const blob = new Blob([payload.toString()], {
                    type: 'application/x-www-form-urlencoded',
                });
                navigator.sendBeacon(AUTOSAVE_URL, blob);
                isDirty  = false;
                isSaving = false;
                return;
            }

            const resp = await fetch(AUTOSAVE_URL, {
                method:  'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type':     'application/x-www-form-urlencoded',
                },
                body:        payload.toString(),
                keepalive:   true,   // tetap jalan walau tab pindah
            });

            const json = await resp.json();

            if (json.status === 'success') {
                isDirty = false;
                showStatus('Tersimpan otomatis ✓', false);
                setTimeout(hideStatus, 3000);
            } else {
                showStatus('Gagal menyimpan otomatis.', false);
            }
        } catch (e) {
            showStatus('Gagal menyimpan otomatis.', false);
        } finally {
            isSaving = false;
        }
    }

    // ── Pasang listener pada form ────────────────────────────────
    function attachListeners() {
        const form = document.getElementById('form-kuesioner');
        if (!form) return;

        // Radio buttons
        form.querySelectorAll('input[type="radio"]').forEach(el => {
            el.addEventListener('change', markDirty);
        });

        // Text inputs (links)
        form.querySelectorAll('input[type="text"][name^="links["]').forEach(el => {
            el.addEventListener('input', markDirty);
        });

        // Saat tombol Simpan ditekan → batalkan auto-save, biarkan form submit biasa
        form.addEventListener('submit', () => {
            clearTimeout(autoTimer);
            isDirty = false;
        });
    }

    // ── Simpan saat tab disembunyikan (pindah tab / minimize) ────
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden' && isDirty) {
            doAutoSave(true);   // pakai sendBeacon
        }
    });

    // ── Simpan saat halaman akan ditutup / navigate away ─────────
    window.addEventListener('beforeunload', (e) => {
        if (isDirty) {
            doAutoSave(true);   // pakai sendBeacon
            // Tidak perlu dialog konfirmasi — cukup simpan diam-diam
        }
    });

    // ── Simpan saat pindah halaman via klik link ─────────────────
    document.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('click', () => {
            if (isDirty) doAutoSave(true);
        });
    });

    // ── Init ─────────────────────────────────────────────────────
    attachListeners();

})();
</script>
@endif

@endsection