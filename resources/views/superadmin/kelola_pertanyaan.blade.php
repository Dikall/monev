@extends('components.layouts.app')

@section('content')

<div class="p-6"
    x-data="pertanyaanComponent()"
    x-init="
        @if(old('mode') === 'tambah') openTambah = true @endif
        @if(old('mode') === 'edit')   openEdit   = true @endif
        kategoris = kategorisAll;
        @if(request('tahun_id'))
            tahun = '{{ request('tahun_id') }}';
            kategori = '{{ request('kategori_id') }}';
            if (kategori) {
                indikators = indikatorsAll.filter(i => i.kategori_id == kategori && i.tahun_id == tahun);
                indikator = '{{ request('indikator_id') }}';
            }
        @endif
    ">

    {{-- ================= FILTER ================= --}}
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="GET">
            <div class="grid grid-cols-3 gap-4 mb-4">

                <select name="tahun_id" x-model="tahun" @change="filterKategori"
                    class="border p-3 rounded">
                    <option value="">Tahun</option>
                    @foreach($tahuns as $t)
                        <option value="{{ $t->id }}" {{ request('tahun_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->tahun }}
                        </option>
                    @endforeach
                </select>

                <select name="kategori_id" x-model="kategori" @change="filterIndikator"
                    :disabled="!tahun" class="border p-3 rounded">
                    <option value="">Kategori</option>
                    <template x-for="k in kategoris" :key="k.id">
                        <option :value="k.id" x-text="k.name"></option>
                    </template>
                </select>

                <select name="indikator_id" x-model="indikator"
                    :disabled="!kategori" class="border p-3 rounded">
                    <option value="">Indikator</option>
                    <template x-for="i in indikators" :key="i.id">
                        <option :value="i.id" x-text="i.nama_indikator"></option>
                    </template>
                </select>

            </div>
            <button class="w-full bg-primary-dark text-white p-3 rounded">Tampilkan</button>
        </form>
    </div>

    {{-- ================= ALERT ================= --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-primary-light border border-primary-main text-primary-dark rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- Alert baris yang dilewati saat import --}}
    @if(session('import_errors'))
        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-400 text-yellow-800 rounded">
            <p class="font-semibold mb-2">⚠ Beberapa baris dilewati saat import:</p>
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Info peringatan bobot (data tetap tersimpan) --}}
    @if(session('import_warnings'))
        <div class="mb-4 p-4 bg-primary-light border border-primary-light text-primary-dark rounded">
            <p class="font-semibold mb-2">ℹ️ Catatan pembobotan (data tetap tersimpan):</p>
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach(session('import_warnings') as $warn)
                    <li>{{ $warn }}</li>
                @endforeach
            </ul>
            <p class="text-xs mt-2 text-primary-main">Total bobot per indikator yang melebihi 100 tetap akan dihitung sebagai 100 saat rekap nilai.</p>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-semibold text-slate-800">Data Pertanyaan</h1>
        <div class="flex gap-2">
            <button @click="openImport = true"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V4m0 8l-3-3m3 3l3-3"/>
                </svg>
                Import Excel
            </button>
            <button @click="openTambah = true; resetForm();"
                class="bg-primary-dark hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-medium transition-colors">
                + Tambah Pertanyaan
            </button>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-primary-dark text-white font-medium">
                <tr>
                    <th class="p-3 text-left font-medium">Tahun</th>
                    <th class="p-3 text-left font-medium">Kategori</th>
                    <th class="p-3 text-left font-medium">Indikator</th>
                    <th class="p-3 text-left font-medium">Level</th>
                    <th class="p-3 text-left font-medium w-16">No</th>
                    <th class="p-3 text-left font-medium">Pertanyaan</th>
                    <th class="p-3 text-center font-medium w-20">Bobot</th>
                    <th class="p-3 text-center font-medium w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-600">
                @forelse($pertanyaans as $p)
                <tr class="transition-colors hover:bg-slate-50/50
                    outline outline-1 outline-slate-200
                    {{ $p->level === 'judul'    ? 'bg-primary-light/40 text-primary-dark font-medium' : '' }}
                    {{ $p->level === 'subjudul' ? 'bg-primary-light/10 text-slate-800' : '' }}
                    {{ $p->level === 'pertanyaan' ? 'bg-white text-slate-600' : '' }}
                ">
                    <td class="p-3 whitespace-nowrap">{{ $p->tahun->tahun }}</td>
                    <td class="p-3 max-w-[160px]">
                        <div class="line-clamp-2 leading-relaxed break-words" title="{{ $p->kategori->name }}">
                            {{ $p->kategori->name }}
                        </div>
                    </td>
                    <td class="p-3 max-w-[220px]">
                        <div class="line-clamp-2 leading-relaxed break-words" title="{{ $p->indikator->nama_indikator }}">
                            {{ $p->indikator->nama_indikator }}
                        </div>
                    </td>
                    <td class="p-3 whitespace-nowrap">
                        @if($p->level === 'judul')
                            <span class="bg-primary-light text-primary-dark px-2 py-0.5 rounded text-xs font-medium">Judul</span>
                        @elseif($p->level === 'subjudul')
                            <span class="bg-orange-100 text-orange-800 px-2 py-0.5 rounded text-xs font-medium">Sub Judul</span>
                        @else
                            <span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-xs">Pertanyaan</span>
                        @endif
                    </td>
                    <td class="p-3 font-mono">
                        <span class="{{ $p->level === 'subjudul' ? 'pl-4' : '' }} {{ $p->level === 'pertanyaan' ? 'pl-8' : '' }}">
                            {{ $p->nomor }}
                        </span>
                    </td>
                    <td class="p-3">
                        <div class="line-clamp-2 leading-relaxed {{ $p->level === 'subjudul' ? 'pl-4 text-slate-800' : '' }} {{ $p->level === 'pertanyaan' ? 'pl-8 text-slate-600' : '' }}" title="{{ $p->pertanyaan_kuisioner }}">
                            {{ $p->pertanyaan_kuisioner }}
                        </div>
                    </td>
                    <td class="p-3 text-center font-medium">
                        {{ $p->level === 'pertanyaan' ? $p->bobot : ' ' }}
                    </td>
                    <td class="p-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button
                                @click='
                                    openEdit      = true;
                                    pertanyaanId  = {{ $p->id }};
                                    tahunForm     = "{{ $p->tahun_id }}";
                                    filterKategoriForm(false);
                                    kategoriForm  = "{{ $p->kategori_id }}";
                                    filterIndikatorForm(false);
                                    indikatorForm = "{{ $p->indikator_id }}";
                                    levelForm     = "{{ $p->level }}";
                                    parent_id     = "{{ $p->parent_id ?? '' }}";
                                    nomor         = "{{ $p->nomor }}";
                                    pertanyaan    = `{{ addslashes($p->pertanyaan_kuisioner) }}`;
                                    bobot         = "{{ $p->bobot }}";
                                '
                                class="bg-primary-dark hover:bg-primary-dark text-white px-2.5 py-1 rounded text-xs font-medium transition-colors">
                                Edit
                            </button>
                            <button @click="openDelete = true; pertanyaanDeleteId = {{ $p->id }}"
                                class="border border-primary-dark text-primary-dark hover:bg-primary-light px-2.5 py-1 rounded text-xs font-medium transition-colors">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="p-6 text-center text-gray-500">Belum ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- =================== MODAL IMPORT EXCEL =================== --}}
    <div x-show="openImport" x-cloak
        class="fixed inset-0 bg-black/40 overflow-y-auto z-50">
        <div class="flex items-start justify-center min-h-screen py-10 px-4">
            <div class="bg-white w-[620px] p-6 rounded-xl shadow-lg">

                <div class="flex justify-between items-center mb-5">
                    <h2 class="text-xl font-bold">Import Pertanyaan dari Excel</h2>
                    <button @click="openImport = false; resetImportForm()">✕</button>
                </div>

                {{-- Panduan ringkas --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-5 text-sm text-blue-800 space-y-1.5">
                    <p class="font-semibold text-blue-900">📋 Panduan Singkat</p>
                    <p>
                        <span class="font-medium">1.</span>
                        Download template → semua dropdown (Tahun, Kategori, Indikator, Level) sudah terisi dari database.
                    </p>
                    <p>
                        <span class="font-medium">2.</span>
                        Sheet <strong>Ref Parent</strong> berisi daftar Judul &amp; Sub Judul yang sudah ada — gunakan kolomnya sebagai acuan isi <em>Parent Nomor</em>.
                    </p>
                    <p>
                        <span class="font-medium">3.</span>
                        Kolom <strong>Parent Nomor</strong> (E): pilih dari dropdown atau ketik nomor parent persis seperti yang ada (mis: <code class="bg-blue-100 px-1 rounded">I</code>, <code class="bg-blue-100 px-1 rounded">A</code>).
                    </p>
                    <p>
                        <span class="font-medium">4.</span>
                        Urutan baris penting: <strong>Judul → Sub Judul → Pertanyaan</strong>.
                    </p>
                    <p>
                        <span class="font-medium">5.</span>
                        Total bobot per indikator sebaiknya = 100. Kolom <strong>Nomor</strong> opsional — otomatis jika dikosongkan.
                    </p>
                </div>

                {{-- Tombol download template --}}
                <div class="flex items-center gap-3 mb-5 p-3 border border-dashed border-green-400 rounded-lg bg-green-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-600 flex-shrink-0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2m3 2v-4m3 4v-6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-green-800 text-sm">Download Template Excel</p>
                        <p class="text-xs text-green-600">
                            Dropdown Tahun, Kategori, Indikator, Level, dan referensi Parent sudah terisi otomatis dari database
                        </p>
                    </div>
                    <a href="{{ route('superadmin.pertanyaan.import.template') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm whitespace-nowrap flex-shrink-0">
                        ⬇ Download
                    </a>
                </div>

                {{-- Form upload --}}
                <form action="{{ route('superadmin.pertanyaan.import') }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    <label class="font-semibold block mb-2">Upload File Excel</label>

                    {{-- Drop zone --}}
                    <div class="border-2 border-dashed rounded-lg p-6 text-center mb-4 transition-colors"
                         :class="importFileName ? 'border-green-400 bg-green-50' : 'border-gray-300'"
                         @dragover.prevent="$el.classList.add('border-blue-400','bg-primary-light')"
                         @dragleave.prevent="$el.classList.remove('border-blue-400','bg-primary-light')"
                         @drop.prevent="handleFileDrop($event); $el.classList.remove('border-blue-400','bg-primary-light')">
                        <div x-show="!importFileName">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-10 h-10 mx-auto text-gray-400 mb-2"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-gray-500 text-sm mb-1">Drag &amp; drop file di sini, atau</p>
                                <label class="cursor-pointer text-green-700 font-semibold text-sm underline">
                                    klik untuk pilih file
                                    <input type="file" name="file_excel" accept=".xlsx,.xls"
                                           class="hidden" x-ref="fileInput"
                                           @change="handleFileSelect($event)">
                                </label>
                                <p class="text-xs text-gray-400 mt-2">Format: .xlsx atau .xls · Maks. 5 MB</p>
                            </div>
                        </div>
 
                        <div x-show="importFileName" style="display: none;">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-10 h-10 mx-auto text-green-500 mb-2"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                <p class="text-green-700 font-semibold text-sm" x-text="importFileName"></p>
                                <p class="text-xs text-green-600 mb-2">File siap diimport</p>
                                <button type="button" @click="resetImportForm()"
                                    class="text-xs text-primary-main underline">Ganti file</button>
                            </div>
                        </div>
                    </div>

                    @error('file_excel')
                        <p class="text-primary-main text-sm mb-3">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-end gap-3 mt-2">
                        <button type="button"
                            @click="openImport = false; resetImportForm()"
                            class="px-5 py-2 border border-gray-300 rounded text-gray-600 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit"
                            :disabled="!importFileName"
                            :class="importFileName
                                ? 'bg-green-600 hover:bg-green-700 cursor-pointer'
                                : 'bg-gray-300 cursor-not-allowed'"
                            class="text-white px-6 py-2 rounded flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V4m0 8l-3-3m3 3l3-3"/>
                            </svg>
                            Proses Import
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- ===================== MODAL TAMBAH ===================== --}}
    <div x-show="openTambah" x-cloak
        class="fixed inset-0 bg-black/40 overflow-y-auto z-50">
        <div class="flex items-start justify-center min-h-screen py-10 px-4">
            <div class="bg-white w-[800px] p-6 rounded-xl shadow-lg">

                <div class="flex justify-between mb-6">
                    <h2 class="text-xl font-bold">Tambah Pertanyaan</h2>
                    <button @click="openTambah=false; resetForm()">✕</button>
                </div>

                <form action="{{ route('superadmin.pertanyaan.store') }}" method="POST">
                    @csrf

                    <label class="font-semibold block mb-1">Tahun</label>
                    <select name="tahun_id" x-model="tahunForm" @change="filterKategoriForm()"
                        class="w-full border p-3 rounded mb-4">
                        <option value="">Pilih Tahun</option>
                        @foreach($tahuns as $t)
                            <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                        @endforeach
                    </select>

                    <label class="font-semibold block mb-1">Kategori</label>
                    <select name="kategori_id" x-model="kategoriForm" @change="filterIndikatorForm()"
                        class="w-full border p-3 rounded mb-4">
                        <option value="">Pilih Kategori</option>
                        <template x-for="k in kategoris" :key="k.id">
                            <option :value="k.id" x-text="k.name"></option>
                        </template>
                    </select>

                    <label class="font-semibold block mb-1">Indikator</label>
                    <select name="indikator_id" x-model="indikatorForm"
                        @change="indikatorForm = $event.target.value; parent_id = '';"
                        class="w-full border p-3 rounded mb-4">
                        <option value="">Pilih Indikator</option>
                        <template x-for="i in indikators" :key="i.id">
                            <option :value="i.id" x-text="i.nama_indikator"></option>
                        </template>
                    </select>

                    <label class="font-semibold block mb-1">Level</label>
                    <select name="level" x-model="levelForm"
                        @change="levelForm = $event.target.value; parent_id = '';"
                        class="w-full border p-3 rounded mb-4">
                        <option value="judul">Judul (I)</option>
                        <option value="subjudul">Sub Judul (A)</option>
                        <option value="pertanyaan">Pertanyaan (1)</option>
                    </select>

                    <div x-show="levelForm === 'subjudul'" class="mb-4">
                        <label class="font-semibold block mb-1">
                            Parent Judul <span class="text-primary-main">*</span>
                        </label>
                        <select x-model="parent_id" class="w-full border p-3 rounded">
                            <option value="">-- Pilih Judul --</option>
                            <template x-for="p in parentJudulFiltered" :key="p.id">
                                <option :value="p.id" x-text="p.nomor + ' - ' + p.pertanyaan_kuisioner"></option>
                            </template>
                        </select>
                        <p class="text-xs text-primary-main mt-1"
                           x-show="levelForm === 'subjudul' && !parent_id">
                            Wajib pilih Judul sebagai parent.
                        </p>
                    </div>

                    <div x-show="levelForm === 'pertanyaan'" class="mb-4">
                        <label class="font-semibold block mb-1">Parent Sub Judul</label>
                        <select x-model="parent_id" class="w-full border p-3 rounded">
                            <option value="">-- Tanpa Sub Judul --</option>
                            <template x-for="p in parentSubJudulFiltered" :key="p.id">
                                <option :value="p.id" x-text="p.nomor + ' - ' + p.pertanyaan_kuisioner"></option>
                            </template>
                        </select>
                    </div>

                    <input type="hidden" name="parent_id" :value="parent_id">

                    <label class="font-semibold block mb-1">Nomor</label>
                    <input name="nomor" x-model="nomor"
                        class="w-full border p-3 rounded mb-4"
                        placeholder="Contoh: I, A, 1">

                    <label class="font-semibold block mb-1">
                        <span x-show="levelForm==='judul'">Judul</span>
                        <span x-show="levelForm==='subjudul'">Sub Judul</span>
                        <span x-show="levelForm==='pertanyaan'">Pertanyaan</span>
                    </label>
                    <textarea name="pertanyaan_kuisioner" x-model="pertanyaan"
                        class="w-full border p-3 rounded mb-4" rows="3"></textarea>

                    <div x-show="levelForm === 'pertanyaan'">
                        <div class="flex justify-between items-center mb-1">
                            <label class="font-semibold">Bobot Soal</label>
                            <div class="text-sm"
                                :class="totalBobotSekarang > 100 ? 'text-primary-main font-bold' : 'text-gray-600'">
                                Total bobot indikator ini:
                                <span x-text="totalBobotSekarang"></span> / 100
                                <span x-show="totalBobotSekarang > 100"> ⚠ Melebihi 100!</span>
                            </div>
                        </div>
                        <input name="bobot" x-model="bobot"
                            @input="bobotInput = parseInt($event.target.value) || 0"
                            class="w-full border p-3 rounded mb-1"
                            placeholder="Total bobot semua soal dalam indikator = 100">
                        <p class="text-xs text-gray-500 mb-4">
                            Bobot aktual = (<span x-text="bobot || 0"></span> / 100) × bobot indikator
                        </p>
                    </div>

                    <div class="flex justify-end">
                        <button
                            :disabled="levelForm === 'pertanyaan' && totalBobotSekarang > 100"
                            :class="(levelForm === 'pertanyaan' && totalBobotSekarang > 100)
                                ? 'bg-gray-400 cursor-not-allowed'
                                : 'bg-primary-dark hover:bg-primary-dark'"
                            class="text-white px-6 py-2 rounded">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL EDIT ===================== --}}
    <div x-show="openEdit" x-cloak
        class="fixed inset-0 bg-black/40 overflow-y-auto z-50">
        <div class="flex items-start justify-center min-h-screen py-10 px-4">
            <div class="bg-white w-[800px] p-6 rounded-xl shadow-lg">

                <div class="flex justify-between mb-6">
                    <h2 class="text-xl font-bold">Edit Pertanyaan</h2>
                    <button @click="openEdit=false">✕</button>
                </div>

                <form :action="'/superadmin/pertanyaan/' + pertanyaanId" method="POST">
                    @csrf
                    @method('PUT')

                    <label class="font-semibold block mb-1">Tahun</label>
                    <select name="tahun_id" x-model="tahunForm" @change="filterKategoriForm()"
                        class="w-full border p-3 rounded mb-4">
                        @foreach($tahuns as $t)
                            <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                        @endforeach
                    </select>

                    <label class="font-semibold block mb-1">Kategori</label>
                    <select name="kategori_id" x-model="kategoriForm" @change="filterIndikatorForm()"
                        class="w-full border p-3 rounded mb-4">
                        <template x-for="k in kategoris" :key="k.id">
                            <option :value="k.id" x-text="k.name"></option>
                        </template>
                    </select>

                    <label class="font-semibold block mb-1">Indikator</label>
                    <select name="indikator_id" x-model="indikatorForm"
                        @change="indikatorForm = $event.target.value; parent_id = '';"
                        class="w-full border p-3 rounded mb-4">
                        <template x-for="i in indikators" :key="i.id">
                            <option :value="i.id" x-text="i.nama_indikator"></option>
                        </template>
                    </select>

                    <label class="font-semibold block mb-1">Level</label>
                    <select name="level" x-model="levelForm"
                        @change="levelForm = $event.target.value; parent_id = '';"
                        class="w-full border p-3 rounded mb-4">
                        <option value="judul">Judul (I)</option>
                        <option value="subjudul">Sub Judul (A)</option>
                        <option value="pertanyaan">Pertanyaan (1)</option>
                    </select>

                    <div x-show="levelForm === 'subjudul'" class="mb-4">
                        <label class="font-semibold block mb-1">
                            Parent Judul <span class="text-primary-main">*</span>
                        </label>
                        <select x-model="parent_id" class="w-full border p-3 rounded">
                            <option value="">-- Pilih Judul --</option>
                            <template x-for="p in parentJudulFiltered" :key="p.id">
                                <option :value="p.id" x-text="p.nomor + ' - ' + p.pertanyaan_kuisioner"></option>
                            </template>
                        </select>
                    </div>

                    <div x-show="levelForm === 'pertanyaan'" class="mb-4">
                        <label class="font-semibold block mb-1">Parent Sub Judul</label>
                        <select x-model="parent_id" class="w-full border p-3 rounded">
                            <option value="">-- Tanpa Sub Judul --</option>
                            <template x-for="p in parentSubJudulFiltered" :key="p.id">
                                <option :value="p.id" x-text="p.nomor + ' - ' + p.pertanyaan_kuisioner"></option>
                            </template>
                        </select>
                    </div>

                    <input type="hidden" name="parent_id" :value="parent_id">

                    <label class="font-semibold block mb-1">Nomor</label>
                    <input name="nomor" x-model="nomor" class="w-full border p-3 rounded mb-4">

                    <label class="font-semibold block mb-1">
                        <span x-show="levelForm==='judul'">Judul</span>
                        <span x-show="levelForm==='subjudul'">Sub Judul</span>
                        <span x-show="levelForm==='pertanyaan'">Pertanyaan</span>
                    </label>
                    <textarea name="pertanyaan_kuisioner" x-model="pertanyaan"
                        class="w-full border p-3 rounded mb-4" rows="3"></textarea>

                    <div x-show="levelForm === 'pertanyaan'">
                        <label class="font-semibold block mb-1">Bobot Soal</label>
                        <input name="bobot" x-model="bobot" class="w-full border p-3 rounded mb-4">
                    </div>

                    <div class="flex justify-end">
                        <button class="bg-primary-dark text-black px-6 py-2 rounded hover:bg-primary-dark">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL DELETE ===================== --}}
    <div x-show="openDelete" x-cloak
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl w-11/12 md:w-1/2 p-8">
            <h2 class="text-2xl font-bold mb-6">Hapus Data</h2>
            <p class="mb-8 text-lg">Apakah Anda yakin ingin menghapus data ini?</p>
            <div class="flex justify-end gap-4">
                <button @click="openDelete = false"
                    class="px-8 py-2 border border-primary-dark text-primary-dark rounded-lg">
                    Batal
                </button>
                <form :action="'/superadmin/pertanyaan/' + pertanyaanDeleteId" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="tahun_id" value="{{ request('tahun_id') }}">
                    <input type="hidden" name="kategori_id" value="{{ request('kategori_id') }}">
                    <input type="hidden" name="indikator_id" value="{{ request('indikator_id') }}">
                    <button type="submit"
                        class="px-8 py-2 bg-primary-dark text-white rounded-lg hover:bg-primary-dark">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function pertanyaanComponent() {
    return {
        openTambah: false,
        openEdit:   false,
        openDelete: false,
        openImport: false,

        pertanyaanId:       null,
        pertanyaanDeleteId: null,

        // Filter bar
        tahun:    '',
        kategori: '',
        indikator:'',

        // Modal form
        tahunForm:    '',
        kategoriForm: '',
        indikatorForm:'',
        levelForm:    'judul',
        parent_id:    '',
        nomor:        '',
        pertanyaan:   '',
        bobot:        '',
        bobotInput:   0,

        // Import
        importFileName: '',

        // Data dari blade
        kategorisAll:      @js($kategoris),
        indikatorsAll:     @js($indikators),
        parentJudulAll:    @js($parentJudul),
        parentSubJudulAll: @js($parentSubJudul),
        bobotPerIndikator: @js($bobotPerIndikator),

        kategoris:  @js($kategoris),
        indikators: [],

        get totalBobotAwal()    { return parseInt(this.bobotPerIndikator[this.indikatorForm] ?? 0); },
        get totalBobotSekarang(){ return this.totalBobotAwal + parseInt(this.bobotInput || 0); },

        get parentJudulFiltered() {
            if (!this.indikatorForm) return [];
            return this.parentJudulAll.filter(p => p.indikator_id == this.indikatorForm);
        },
        get parentSubJudulFiltered() {
            if (!this.indikatorForm) return [];
            return this.parentSubJudulAll.filter(p => p.indikator_id == this.indikatorForm);
        },

        filterKategori() {
            this.kategoris  = this.kategorisAll;
            this.kategori   = '';
            this.indikator  = '';
            this.indikators = [];
        },
        filterIndikator() {
            this.indikators = this.indikatorsAll.filter(i => i.kategori_id == this.kategori && i.tahun_id == this.tahun);
            this.indikator  = '';
        },
        filterKategoriForm(reset = true) {
            this.kategoris = this.kategorisAll;
            if (reset) { this.kategoriForm = ''; this.indikators = []; this.indikatorForm = ''; this.parent_id = ''; }
        },
        filterIndikatorForm(reset = true) {
            this.indikators = this.indikatorsAll.filter(i => i.kategori_id == this.kategoriForm && i.tahun_id == this.tahunForm);
            if (reset) { this.indikatorForm = ''; this.parent_id = ''; }
        },

        resetForm() {
            this.tahunForm = ''; this.kategoriForm = ''; this.indikatorForm = '';
            this.levelForm = 'judul'; this.parent_id = ''; this.nomor = '';
            this.pertanyaan = ''; this.bobot = ''; this.bobotInput = 0;
            this.kategoris = this.kategorisAll; this.indikators = [];
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) this.importFileName = file.name;
        },
        handleFileDrop(event) {
            const file = event.dataTransfer.files[0];
            if (!file) return;
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['xlsx','xls'].includes(ext)) {
                alert('Format tidak didukung. Gunakan .xlsx atau .xls');
                return;
            }
            const dt = new DataTransfer();
            dt.items.add(file);
            this.$refs.fileInput.files = dt.files;
            this.importFileName = file.name;
        },
        resetImportForm() {
            this.importFileName = '';
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
        },
    }
}
</script>

@endsection