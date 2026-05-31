@extends('components.layouts.app')

@section('content')
<div class="max-w-12xl mx-auto mt-10 mb-20 px-6 sm:px-10 lg:px-6"
     x-data="verifikasiApp()"
     x-init="init()">

    {{-- Breadcrumb --}}
    <div class="mb-6">
        @if(auth()->user()->hasRole('Super Admin'))
            <a href="{{ route('superadmin.rekap-nilai.index') }}" class="text-sm text-primary-dark hover:text-primary-dark inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Rekap Nilai
            </a>
        @else
            <a href="{{ route('admin.list-akun', $kategori->id) }}" class="text-sm text-primary-dark hover:text-primary-dark inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        @endif
    </div>

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-800 mb-1">Rekap Nilai Pengisian Kuesioner</h1>
            <p class="text-sm text-gray-500">{{ $publicBody->nama_badan }} — {{ $kategori->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->hasRole('Super Admin'))
                @if($penilaian && $penilaian->is_published)
                    <form action="{{ route('superadmin.rekap-nilai.reset-publish', $publicBody->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan publikasi nilai ini?')">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-2 bg-primary-dark text-black px-4 py-2 rounded-lg text-sm font-semibold hover:bg-orange-700 transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reset Publish (Batalkan)
                        </button>
                    </form>
                @endif
                <a href="{{ route('superadmin.rekap-nilai.export-detail', $publicBody->id) }}" 
                   class="inline-flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Detail (Excel)
                </a>
            @endif
        </div>
    </div>

    {{-- Tabs Indikator --}}
    <div class="mb-0 border-b border-gray-200">
        <div class="flex flex-wrap gap-0">
            @foreach ($indikators as $i => $ind)
                <button @click="activeTab = {{ $ind->id }}"
                        :class="activeTab === {{ $ind->id }}
                            ? 'border-b-2 border-primary-dark text-primary-dark font-semibold'
                            : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-5 py-3 text-sm transition-colors whitespace-nowrap -mb-px">
                    {{ $ind->nama_indikator }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Deskripsi Indikator Aktif --}}
    @foreach ($indikators as $ind)
        <div x-show="activeTab === {{ $ind->id }}" x-cloak class="mt-3 mb-4">
            <p class="text-xs text-gray-500">{{ $ind->deskripsi ?? '' }}</p>
        </div>
    @endforeach

    {{-- Alert --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif

    {{-- Auto-save status --}}
    <div x-show="saveStatus !== ''" x-transition
         :class="saveStatus === 'saving' ? 'bg-yellow-100 text-yellow-800' : (saveStatus === 'saved' ? 'bg-green-100 text-green-800' : 'bg-primary-light text-primary-dark')"
         class="mb-4 rounded-lg px-4 py-2 text-sm flex items-center gap-2">
        <template x-if="saveStatus === 'saving'">
            <span>⏳ Menyimpan...</span>
        </template>
        <template x-if="saveStatus === 'saved'">
            <span>✓ Tersimpan otomatis</span>
        </template>
        <template x-if="saveStatus === 'error'">
            <span>✕ Gagal menyimpan</span>
        </template>
    </div>

    {{-- Form Verifikasi --}}
    <form method="POST" action="{{ route('admin.simpan-verifikasi', $publicBody->id) }}" id="formVerifikasi" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="public_body_id" value="{{ $publicBody->id }}">
        <input type="hidden" name="kategori_id" value="{{ $kategori->id }}">

        @foreach ($indikators as $ind)
            <div x-show="activeTab === {{ $ind->id }}" x-cloak>

                {{-- Tabel Pertanyaan --}}
                <div class="overflow-x-auto rounded-xl border border-gray-200 mb-6">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-dark text-white">
                            <tr>
                                <th class="px-3 py-3 text-center" style="width: 3%">No</th>
                                <th class="px-3 py-3 text-left" style="width: 25%">Pertanyaan</th>
                                <th class="px-3 py-3 text-center" style="width: 7%">Jawaban</th>
                                <th class="px-3 py-3 text-center" colspan="2" style="width: 20%">Bukti</th>
                                <th class="px-3 py-3 text-center" style="width: 13%">Verifikasi</th>
                                <th class="px-3 py-3 text-left" style="{{ auth()->user()->hasRole('Super Admin') ? 'width: 22%' : 'width: 32%' }}">Catatan</th>
                                @if(auth()->user()->hasRole('Super Admin'))
                                    <th class="px-3 py-3 text-left" style="width: 10%">Verifikator</th>
                                @endif
                            </tr>
                            <tr class="bg-primary-dark text-white text-xs font-normal">
                                <th class="py-1"></th>
                                <th class="py-1"></th>
                                <th class="py-1"></th>
                                <th class="px-3 py-1 text-center font-normal border-r border-primary-main" style="width: 10%">Link</th>
                                <th class="px-3 py-1 text-center font-normal" style="width: 10%">Dokumen</th>
                                <th class="py-1"></th>
                                <th class="py-1"></th>
                                @if(auth()->user()->hasRole('Super Admin'))
                                    <th class="py-1"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @if(isset($pertanyaanPerIndikator[$ind->id]))
                                @foreach ($pertanyaanPerIndikator[$ind->id] as $judul)
                                    {{-- Baris Judul --}}
                                    <tr class="bg-gray-100">
                                        <td class="px-3 py-2 font-bold text-gray-700" colspan="{{ auth()->user()->hasRole('Super Admin') ? 8 : 7 }}">
                                            {{ $judul->nomor }}. {{ $judul->pertanyaan_kuisioner }}
                                        </td>
                                    </tr>

                                    @include('admin.partials.verifikasi-row', [
                                        'items' => $judul->childrenRecursive,
                                        'depth' => 1,
                                        'jawabans' => $jawabans
                                    ])
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        {{-- Tombol Simpan --}}
        @if(!auth()->user()->hasRole('Super Admin'))
            <div class="flex justify-end mt-6">
                <button type="submit"
                        class="rounded-lg bg-primary-dark px-8 py-3 text-sm font-semibold text-white hover:bg-primary-dark transition-colors shadow-sm">
                    Simpan
                </button>
            </div>
        @endif
    </form>

</div>

@push('scripts')
<script>
function verifikasiApp() {
    return {
        activeTab: {{ $indikators->first()?->id ?? 0 }},
        saveStatus: '',
        autoSaveTimer: null,

        init() {
            this.$watch('activeTab', () => {});
            document.getElementById('formVerifikasi').addEventListener('change', () => {
                this.triggerAutoSave();
            });
        },

        triggerAutoSave() {
            clearTimeout(this.autoSaveTimer);
            this.autoSaveTimer = setTimeout(() => {
                this.doAutoSave();
            }, 3000);
        },

        async doAutoSave() {
            this.saveStatus = 'saving';
            const form = document.getElementById('formVerifikasi');
            const formData = new FormData(form);

            try {
                const response = await fetch('{{ route("admin.autosave-verifikasi") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (data.status === 'success') {
                    this.saveStatus = 'saved';
                    setTimeout(() => { this.saveStatus = ''; }, 3000);
                } else {
                    this.saveStatus = 'error';
                    setTimeout(() => { this.saveStatus = ''; }, 5000);
                }
            } catch (e) {
                this.saveStatus = 'error';
                setTimeout(() => { this.saveStatus = ''; }, 5000);
            }
        }
    };
}
</script>
@endpush

@endsection