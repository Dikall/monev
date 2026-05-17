@extends('components.layouts.app')

@section('content')
<div class="bg-white min-h-screen">
    <div class="mx-auto max-w-6xl px-6 py-8">
        <h1 class="text-xl font-bold text-gray-900 mb-6">Notifikasi</h1>

        <div class="space-y-4">
            @forelse($notifications as $notification)
                <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-bold text-gray-800 uppercase tracking-wider">
                                {{ $notification->title }}
                            </span>
                            @if(!$notification->read_at)
                                <span class="bg-red-100 text-red-600 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Baru</span>
                            @endif
                        </div>
                        
                        <p class="text-gray-700 text-sm leading-relaxed">
                            {{ $notification->message }}
                        </p>

                        <div class="flex justify-end mt-2">
                            <span class="text-[11px] text-gray-400">
                                {{ $notification->created_at->translatedFormat('d F Y H:i:s') }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-gray-50 border border-dashed border-gray-200 rounded-xl p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C8.67 6.165 8 7.388 8 9v5.159c0 .538-.214 1.055-.595 1.436L6 17h9z" />
                    </svg>
                    <p class="text-gray-500 text-sm uppercase tracking-widest font-medium">Belum ada notifikasi</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
