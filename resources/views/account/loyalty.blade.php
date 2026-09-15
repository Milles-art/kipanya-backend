@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">
        @include('components.account-sidebar')

        <div data-loyalty-page class="min-w-0">
            <div class="border-b border-gray-200 pb-6">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    <x-tabler-gift size="14" />
                    Rewards
                </p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Loyalty &amp; referrals</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">Rewards are being prepared for KP Wear. This page will become your home for points, referral rewards, and member benefits.</p>
            </div>

            <section class="mt-6 overflow-hidden rounded-3xl bg-gray-950 p-6 text-white shadow-sm sm:p-8">
                <div class="max-w-2xl">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10">
                        <x-tabler-sparkles size="21" />
                    </div>
                    <p class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-white/50">KP Rewards</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">More than just what you wear.</h2>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-white/65">Shop, stay connected with KP Wear, and unlock benefits as the rewards programme rolls out.</p>
                </div>
                <div class="mt-7 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <x-tabler-diamond size="18" class="text-white/70" />
                        <p class="mt-3 text-sm font-medium">Earn points</p>
                        <p class="mt-1 text-xs leading-5 text-white/50">Points will be added to qualifying purchases.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <x-tabler-users size="18" class="text-white/70" />
                        <p class="mt-3 text-sm font-medium">Refer friends</p>
                        <p class="mt-1 text-xs leading-5 text-white/50">Share KP Wear and earn when referrals qualify.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <x-tabler-gift size="18" class="text-white/70" />
                        <p class="mt-3 text-sm font-medium">Member benefits</p>
                        <p class="mt-1 text-xs leading-5 text-white/50">Exclusive rewards and offers will appear here.</p>
                    </div>
                </div>
            </section>

            <section class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-gray-100">
                        <x-tabler-clock size="18" class="text-gray-600" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Rewards programme coming soon</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-500">Your account is ready for the loyalty experience. Points balances, redemption, referral codes, and referral history will appear here once the rewards system is connected.</p>
                    </div>
                </div>
            </section>

            <section class="mt-6 grid gap-4 sm:grid-cols-2">
                <a href="{{ route('shop') }}" class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-md sm:p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100">
                            <x-tabler-shopping-bag size="18" class="text-gray-600" />
                        </div>
                        <span class="text-gray-400 transition group-hover:translate-x-1">→</span>
                    </div>
                    <h2 class="mt-5 text-sm font-semibold text-gray-900">Shop KP Wear</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-500">Explore the latest pieces and build your wardrobe.</p>
                </a>

                <a href="{{ route('contact') }}" class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-md sm:p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100">
                            <x-tabler-message-circle size="18" class="text-gray-600" />
                        </div>
                        <span class="text-gray-400 transition group-hover:translate-x-1">→</span>
                    </div>
                    <h2 class="mt-5 text-sm font-semibold text-gray-900">Need help?</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-500">Contact KP Wear for questions about your account or orders.</p>
                </a>
            </section>
        </div>
    </div>
</div>
@endsection
