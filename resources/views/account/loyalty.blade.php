@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">

        @include('account._account-sidebar')

        <div data-loyalty-page>
            <div class="border-b border-gray-200 pb-6">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    <x-tabler-gift size="14" />
                    Rewards
                </p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Loyalty &amp; referrals</h1>
                <p class="mt-2 text-sm text-gray-500">Earn rewards for shopping with us and sharing with friends.</p>
            </div>

            {{-- Points balance --}}
            <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-950 p-6 text-white shadow-sm sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-white/60">Your balance</p>
                        <p data-points-balance class="mt-2 text-4xl font-bold tracking-tight">0 <span class="text-lg font-medium text-white/60">pts</span></p>
                        <p data-points-value class="mt-1 text-sm text-white/60">≈ 0 TZS in discounts</p>
                    </div>
                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-white/10">
                        <x-tabler-diamond size="20" />
                    </div>
                </div>
                <button data-redeem-points type="button" class="mt-5 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-medium text-gray-950 hover:bg-gray-100">
                    Redeem at checkout <span aria-hidden="true">→</span>
                </button>
            </div>

            {{-- Referral --}}
            <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50">
                        <x-tabler-users size="18" class="text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Invite friends, earn rewards</p>
                        <p class="text-sm text-gray-500">Share your code — you both get points when they place their first order.</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-col gap-3 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Your referral code</p>
                        <p data-referral-code class="mt-0.5 text-lg font-bold tracking-wider text-gray-900">KP-XXXXXX</p>
                    </div>
                    <div class="flex gap-2">
                        <button data-copy-referral type="button" class="flex items-center gap-1.5 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                            <x-tabler-copy size="15" />
                            Copy
                        </button>
                        <button data-share-referral type="button" class="flex items-center gap-1.5 rounded-full bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                            <x-tabler-share size="15" />
                            Share
                        </button>
                    </div>
                </div>
            </div>

            {{-- Referral history --}}
            <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">Referral history</p>
                    <span data-referral-count class="text-xs text-gray-500">0 friends joined</span>
                </div>

                <div data-referral-empty class="mt-5 flex flex-col items-center py-10 text-center">
                    <x-tabler-users size="24" class="text-gray-300" />
                    <p class="mt-3 text-sm text-gray-500">No referrals yet — share your code to get started.</p>
                </div>

                <div data-referral-list class="mt-5 hidden space-y-3">
                    {{-- Each referral renders as, e.g.: --}}
                    {{--
                    <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-100 p-3.5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600">J</div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">John M.</p>
                                <p class="text-xs text-gray-400">Joined 3 days ago</p>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-emerald-600">+500 pts</span>
                    </div>
                    --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
