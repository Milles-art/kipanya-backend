@extends('layouts.app')
@section('content')
<div data-wishlist-page class="mx-auto max-w-7xl px-4 pb-20 pt-12 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between gap-4 border-b border-gray-200 pb-7">
        <div>
            <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">
                <x-tabler-heart size="14" />
                Saved
            </p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">Wishlist</h1>
            <p class="mt-2 text-sm text-gray-500">Your saved pieces, synced to your account.</p>
        </div>
        <a href="{{ route('shop') }}" class="hidden text-sm font-medium text-gray-700 underline underline-offset-4 hover:text-black sm:inline-block">Browse shop</a>
    </div>

    <div data-wishlist-empty class="hidden py-24 text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 ring-8 ring-gray-50">
            <x-tabler-heart size="26" class="text-gray-400" />
        </div>
        <h2 class="mt-4 text-xl font-semibold text-gray-900">Nothing saved yet</h2>
        <p class="mt-2 text-sm text-gray-500">Tap the heart on anything you love to save it here.</p>
        <a href="{{ route('shop') }}" class="button-dark mt-6 inline-flex items-center gap-2 rounded-full px-6">
            Browse shop <span aria-hidden="true">→</span>
        </a>
    </div>

    <div data-wishlist-grid class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-6"></div>
</div>
@endsection
