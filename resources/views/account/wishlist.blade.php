@extends('layouts.app')

@section('content')
<div class="w-full px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
        @include('components.account.sidebar')

        <section data-wishlist-page class="min-w-0">
            <div class="flex flex-col gap-5 border-b border-emerald-950/10 pb-8 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                        Saved pieces
                    </div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold tracking-tight text-black sm:text-4xl">Wishlist</h1>
                        <span data-wishlist-page-count class="hidden rounded-full bg-emerald-50/70 px-2.5 py-1 text-xs font-semibold text-black">0</span>
                    </div>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-black">Keep the pieces you love close. Your saved items are synced to your account.</p>
                </div>

                <a href="{{ route('shop') }}" class="kp-button-secondary w-fit">
                    Continue shopping
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <div data-wishlist-auth class="mt-8 hidden rounded-2xl border border-emerald-950/12 bg-emerald-50/50 p-6 sm:p-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-black">Sign in to view your wishlist</h2>
                        <p class="mt-1 text-sm leading-6 text-black">Save pieces while you browse and access them again from any device.</p>
                    </div>
                    <a href="{{ route('login') }}" class="button-dark w-fit">Sign in</a>
                </div>
            </div>

            <div data-wishlist-loading class="mt-8">
                <div data-wishlist-skeleton class="grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 lg:gap-8"></div>
            </div>

            <div data-wishlist-empty class="mt-12 hidden rounded-[1.5rem] border border-emerald-950/10 bg-emerald-50/50 px-6 py-14 text-center sm:px-10">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white text-emerald-700 shadow-sm ring-1 ring-emerald-950/10">
                    <x-tabler-heart size="24" stroke-width="1.8" />
                </div>
                <h2 class="mt-5 text-xl font-semibold text-black">Your wishlist is empty</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-black">Tap the heart on any piece you love and it will appear here.</p>
                <a href="{{ route('shop') }}" class="button-dark mt-6">Explore the shop</a>
            </div>

            <div data-wishlist-grid class="mt-8 hidden grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 lg:gap-8"></div>
        </section>
    </div>
</div>
@endsection
