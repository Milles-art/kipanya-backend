@extends('layouts.app')

@section('content')
<section data-wishlist-page class="bg-white">
    <div class="mx-auto w-full max-w-[1600px] px-5 pb-20 pt-10 sm:px-8 lg:px-10 lg:pt-12">
        <div class="flex flex-col gap-5 border-b border-gray-100 pb-8 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                    Saved pieces
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-950 sm:text-4xl">Wishlist</h1>
                    <span data-wishlist-page-count class="hidden rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">0</span>
                </div>
                <p class="mt-2 max-w-xl text-sm leading-6 text-gray-500">Keep the pieces you love close. Your saved items are synced to your account.</p>
            </div>

            <a href="{{ route('shop') }}" class="inline-flex w-fit items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-800 transition hover:border-gray-300 hover:bg-gray-50">
                Continue shopping
                <span aria-hidden="true">→</span>
            </a>
        </div>

        <div data-wishlist-auth class="mt-8 hidden rounded-2xl border border-gray-200 bg-gray-50 p-6 sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">Sign in to view your wishlist</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-500">Save pieces while you browse and access them again from any device.</p>
                </div>
                <a href="{{ route('login') }}" class="button-dark w-fit">Sign in</a>
            </div>
        </div>

        <div data-wishlist-loading class="mt-8">
            <div data-wishlist-skeleton class="grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-8"></div>
        </div>

        <div data-wishlist-empty class="mt-12 hidden rounded-[1.5rem] border border-gray-100 bg-gray-50 px-6 py-14 text-center sm:px-10">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm ring-1 ring-gray-100">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.8 8.9c0 5.5-8.8 10.1-8.8 10.1S3.2 14.4 3.2 8.9A4.7 4.7 0 0 1 12 6.4a4.7 4.7 0 0 1 8.8 2.5Z" />
                </svg>
            </div>
            <h2 class="mt-5 text-xl font-semibold text-gray-950">Your wishlist is empty</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">Tap the heart on any piece you love and it will appear here.</p>
            <a href="{{ route('shop') }}" class="button-dark mt-6">Explore the shop</a>
        </div>

        <div data-wishlist-grid class="mt-8 hidden grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-8"></div>
    </div>
</section>
@endsection
