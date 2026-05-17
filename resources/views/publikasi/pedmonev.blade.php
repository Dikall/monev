@extends('components.layouts.app')

@section('content')
<div class="max-w-8xl mx-auto mt-10 mb-20 px-6">
    <h1 class="text-xl font-bold mb-6">Pedoman Monev</h1>
    <div class="flex items-center mb-6">
        <button onclick="openModal()" class="ml-auto flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Dokumen
        </button>
    </div>

<!-- Card Items -->
    <div class="space-y-4">
        @foreach ($pedomen as $item)
        <div class="bg-white rounded-lg shadow p-6 flex justify-between items-center">
            <div>
                <h2 class="text-red-700 font-semibold text-base uppercase border-b border-gray-200 pb-2">
                    {{ $item->file_name }}
                </h2>
                <p class="text-sm mt-3">
                    <a href="{{ asset('files/pedoman/' . $item->file_data) }}" target="_blank" class="text-blue-600 underline">
                        {{ basename($item->file_data) }}
                    </a>
                </p>
                <p class="text-sm text-gray-500">{{ $item->created_at->translatedFormat('d F Y') }}</p>
            </div>
            <form action="{{ route('pedmonev.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                @csrf
                @method('DELETE')
                <button type="button"
                        onclick="openDeleteModal('{{ route('pedmonev.destroy', $item->id) }}')"
                        class="bg-red-700 hover:bg-red-800 text-white font-semibold px-6 py-2 rounded text-sm">
                    Hapus
                </button>
            </form>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $pedomen->links() }}
    </div>
</div>

<!-- Modal Upload -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-6">
        <h2 class="text-center text-lg font-semibold mb-4">Tambah Dokumen</h2>
        <hr class="mb-4">

        <form action="{{ route('pedmonev.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- Dropzone Style -->
            <div class="flex items-center justify-center w-full mb-4">
                <label for="fileInput" class="flex flex-col items-center justify-center w-full h-64 border-2 border-red-700 border-dashed rounded-lg cursor-pointer bg-red-50 hover:bg-red-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                        </svg>
                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> atau drag and drop</p>
                        <p class="text-xs text-gray-500">PDF, XLS, XLSX (Maks. 5MB)</p>
                    </div>
                    <input id="fileInput" type="file" name="dokumen" class="hidden" required />
                </label>
            </div>

            <!-- Preview File -->
            <div id="filePreview" class="hidden mt-4 flex items-center justify-between p-3 bg-gray-50 rounded border">
                <div class="flex items-center gap-3" id="filePreviewContent"></div>
                <button type="button" id="removeFile" class="text-red-600 hover:text-red-800">
                    <!-- Icon Hapus -->
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>


            <hr class="mb-4">

            <div class="flex justify-center gap-4">
                <button type="button" onclick="closeModal()" class="border border-red-700 text-red-800 px-6 py-2 rounded hover:bg-red-50">
                    Batal
                </button>
                <button type="submit" class="bg-red-700 text-white px-6 py-2 rounded hover:bg-red-800">
                    Tambah
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 text-center">
        <h2 class="text-lg font-semibold mb-4">Konfirmasi Hapus</h2>
        <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin menghapus dokumen ini?</p>

        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex justify-center gap-4">
                <button type="button" onclick="closeDeleteModal()" class="border border-red-700 text-red-800 px-6 py-2 rounded hover:bg-red-50">
                    Batal
                </button>
                <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-6 py-2 rounded">
                    Hapus
                </button>
            </div>
        </form>
    </div>
</div>


<script>
const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('filePreview');
    const previewContent = document.getElementById('filePreviewContent');
    const dropzoneContent = document.getElementById('dropzoneContent');
    const removeBtn = document.getElementById('removeFile');

    fileInput.addEventListener('change', function(event) {
        const file = event.target.files[0];

        if (file) {
            // Tentukan ikon sesuai jenis file
            let icon;
            const ext = file.name.split('.').pop().toLowerCase();
            if (ext === 'pdf') {
                icon = `<svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0L..."/></svg>`;
            } else if (['xls','xlsx'].includes(ext)) {
                icon = `<svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0L..."/></svg>`;
            } else {
                icon = `<svg class="w-6 h-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0L..."/></svg>`;
            }

            // Isi preview
            previewContent.innerHTML = `${icon}<span class="text-sm">${file.name}</span>`;
            previewContainer.classList.remove('hidden');
            dropzoneContent.style.display = 'none';
        } else {
            previewContainer.classList.add('hidden');
            dropzoneContent.style.display = 'flex';
        }
    });

    // Tombol Hapus
    removeBtn.addEventListener('click', function() {
        fileInput.value = ""; // reset input
        previewContainer.classList.add('hidden');
        dropzoneContent.style.display = 'flex';
        previewContent.innerHTML = '';
    });

    function openModal() {
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('modal').classList.add('flex');
    }
    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
        document.getElementById('modal').classList.remove('flex');
    }

    function openDeleteModal(actionUrl) {
        document.getElementById('deleteForm').action = actionUrl;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('deleteModal').classList.remove('flex');
    }
</script>

@endsection
