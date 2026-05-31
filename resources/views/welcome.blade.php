@extends('components.layouts.app')

@section('content')
<div class="bg-[#fdfdfd] flex flex-col items-center min-h-screen overflow-x-hidden">

  <div class="w-full mb-10 z-0">
    <img src="{{ $appSettings['cover'] ?? asset('images/cover.png') }}" 
       alt="COVER E-MONEV KALIMANTAN BARAT" 
       class="w-full h-auto object-contain block" />
  </div>

  <div class="w-full border-b border-stone-300 py-5 mb-10 bg-white">
    <div class="max-w-7xl mx-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-y-8 gap-x-4 justify-items-center">
      <!-- 1. Jumlah Badan Publik -->
      <div class="flex flex-col items-center text-center">
        <div class="text-sm text-zinc-600 font-medium">Jumlah Badan Publik</div>
        <div class="text-2xl font-bold text-black mt-1">1</div>
      </div>

      <!-- 2. Nilai Kuesioner Badan Publik -->
      <div class="flex flex-col items-center text-center">
        <div class="text-sm text-zinc-600 font-medium">Nilai Kuesioner Badan Publik</div>
        <div class="text-2xl font-bold text-black mt-1">2</div>
      </div>

      <!-- 3. Nilai Verifikasi Kuesioner -->
      <div class="flex flex-col items-center text-center">
        <div class="text-sm text-zinc-600 font-medium">Nilai Verifikasi Kuesioner</div>
        <div class="text-2xl font-bold text-black mt-1">2</div>
      </div>

      <!-- 4. Nilai Uji Publik -->
      <div class="flex flex-col items-center text-center">
        <div class="text-sm text-zinc-600 font-medium">Nilai Uji Publik</div>
        <div class="text-2xl font-bold text-black mt-1">3</div>
      </div>

      <!-- 5. Nilai Akhir -->
      <div class="flex flex-col items-center text-center">
        <div class="text-sm text-zinc-600 font-medium">Nilai Akhir</div>
        <div class="text-2xl font-bold text-black mt-1">4</div>
      </div>
    </div>
  </div>

  
  <!-- Peta Keterbukaan -->
  <div class="w-full max-w-6xl mx-auto bg-white rounded-lg shadow-md mb-10 p-4">
      <h2 class="text-center text-xl font-bold py-4">PETA KETERBUKAAN</h2>
      
      <!-- Map div -->
      <div id="map" class="w-full h-[60vh] rounded-md relative z-0"></div>
  </div>


  <!-- Tombol Aksi -->
  <div class="flex flex-wrap justify-center gap-8 mb-20">
    <!-- Registrasi -->
    <a href="{{ route('register') }}" class="transform transition hover:-translate-y-1 w-full sm:w-[410px] flex items-center gap-3 bg-white shadow-md px-8 py-6 rounded-lg hover:bg-gray-100 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 fill-primary-dark" viewBox="0 0 24 24">
        <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
      </svg>
      <span class="font-semibold text-base">Registrasi Badan Publik</span>
    </a>

    <!-- Pengisian Kuisioner -->
    <a href="{{route('login')}}" class="transform transition hover:-translate-y-1 w-full sm:w-[410px] flex items-center gap-3 bg-white shadow-md px-8 py-6 rounded-lg hover:bg-gray-100 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 fill-primary-dark" viewBox="0 0 24 24">
        <path d="M19 3H5c-1.1 0-2 .9-2 2v14l4-4h12c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-8 9H7v-2h4v2zm6-4H7V6h10v2z"/>
      </svg>
      <span class="font-semibold text-base">Pengisian Kuesioner</span>
    </a>

    <!-- Verifikasi Kuisioner -->
    <a href="{{route('login')}}" class="transform transition hover:-translate-y-1 w-full sm:w-[300px] flex items-center gap-3 bg-white shadow-md px-8 py-6 rounded-lg hover:bg-gray-100 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 fill-primary-dark" viewBox="0 0 24 24">
        <path d="M14 2H6c-1.1 0-2 .9-2 2v16l4-4h8c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm1 10l-2.5-2.5-1.41 1.41L15 13.83 18.91 10l-1.41-1.41z"/>
      </svg>
      <span class="font-semibold text-base">Verifikasi Kuesioner</span>
    </a>
  </div>

  <!-- Alur Monitoring -->
  <div id="alur-monev" class="w-full sm:w-[95%] bg-white rounded-lg mb-10 flex justify-center">
    <img src="{{ $appSettings['alur_monev'] ?? asset('images/Frame 365.png') }}" alt="ALUR MONITORING DAN EVALUASI KETERBUKAAN INFORMASI PUBLIK" class="w-full h-auto object-contain">
  </div>

</div>
@endsection

