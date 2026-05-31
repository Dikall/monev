@extends('components.layouts.app')

@section('content')
<div class="max-w-8xl mx-auto mt-10 mb-20 px-16">
    <h1 class="text-xl font-bold mb-6">Laporan Monev</h1>

    <div class="space-y-4">
        @forelse($files as $file)
            <div class="bg-white rounded-lg shadow p-6 flex justify-between items-center">
                <div>
                    <h2 class="text-primary-dark font-semibold text-base uppercase border-b border-gray-200 pb-2">
                        {{ pathinfo($file->file_name, PATHINFO_FILENAME) }}
                    </h2>
                    <p class="text-sm mt-3">{{ $file->file_name }}</p>
                    <p class="text-sm text-gray-500">
                        {{ optional($file->created_at)->translatedFormat('d F Y') }}
                    </p>
                </div>
                <a href="{{ route('laporan.download', $file->id) }}"
                   class="bg-primary-dark hover:bg-primary-dark text-white font-semibold px-6 py-2 rounded text-sm">
                    Download
                </a>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
                Belum ada dokumen yang tersedia.
            </div>
        @endforelse
    </div>
</div>
@endsection
