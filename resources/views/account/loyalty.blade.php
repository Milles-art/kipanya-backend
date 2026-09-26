@extends('layouts.app')
@section('content')
<div class="w-full px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
        @include('components.account.sidebar')

        <div data-loyalty-page class="min-w-0">
            <div class="border-b border-emerald-950/12 pb-6">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-black">
                    <x-tabler-gift size="14" />
                    Rewards
                </p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-black sm:text-3xl">Loyalty &amp; referrals</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-black">Rewards are being prepared for KP Wear. This page will become your home for points, referral rewards, and member benefits.</p>
            </div>

            <section class="mt-6 overflow-hidden rounded-3xl bg-black p-6 text-white shadow-sm sm:p-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/50">Your points balance</p>
                        <p class="mt-2 text-4xl font-bold">{{ $loyalty->points ?? 0 }}</p>
                    </div>
                    <p class="text-sm text-white/60">KP Rewards</p>
                </div>
            </section>

            <section class="mt-6 rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-sm font-semibold text-black">Points history</h2>
                @if($loyalty->transactions->isNotEmpty())
                <ul class="mt-3 divide-y divide-emerald-950/10">
                    @foreach($loyalty->transactions as $transaction)
                    <li class="flex items-center justify-between gap-3 py-3">
                        <div>
                            <p class="text-sm font-medium text-black">{{ $transaction->description }}</p>
                            <p class="mt-0.5 text-xs text-black">{{ $transaction->created_at?->format('j M Y') }} · {{ ucfirst($transaction->type) }}</p>
                        </div>
                        <span class="font-semibold text-emerald-700">{{ $transaction->points > 0 ? '+' : '' }}{{ $transaction->points }}</span>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="mt-3 text-sm leading-6 text-black">No points activity yet. Your points balance and history will appear here.</p>
                @endif
            </section>

            <section class="mt-6 overflow-hidden rounded-3xl bg-black p-6 text-white shadow-sm sm:p-8">
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

            <section class="mt-6 rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-50/70">
                        <x-tabler-clock size="18" class="text-black" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-black">Rewards programme coming soon</h2>
                        <p class="mt-1 text-sm leading-6 text-black">Your account is ready for the loyalty experience. Points balances, redemption, referral codes, and referral history will appear here once the rewards system is connected.</p>
                    </div>
                </div>
            </section>

            <section class="mt-6 grid gap-4 sm:grid-cols-2">
                <a href="{{ route('shop') }}" class="group rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-black hover:shadow-md sm:p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50/70">
                            <x-tabler-shopping-bag size="18" class="text-black" />
                        </div>
                        <span class="text-black transition group-hover:translate-x-1">→</span>
                    </div>
                    <h2 class="mt-5 text-sm font-semibold text-black">Shop KP Wear</h2>
                    <p class="mt-1 text-sm leading-6 text-black">Explore the latest pieces and build your wardrobe.</p>
                </a>

                <a href="{{ route('contact') }}" class="group rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-black hover:shadow-md sm:p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50/70">
                            <x-tabler-message-circle size="18" class="text-black" />
                        </div>
                        <span class="text-black transition group-hover:translate-x-1">→</span>
                    </div>
                    <h2 class="mt-5 text-sm font-semibold text-black">Need help?</h2>
                    <p class="mt-1 text-sm leading-6 text-black">Contact KP Wear for questions about your account or orders.</p>
                </a>
            </section>
        </div>
    </div>
</div>
@endsection
