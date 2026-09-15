@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">

        @include('account._account-sidebar')

        <div data-notifications-page>
            <div class="border-b border-gray-200 pb-6">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    <x-tabler-bell size="14" />
                    Preferences
                </p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Notifications</h1>
                <p class="mt-2 text-sm text-gray-500">Choose what you hear from us and how.</p>
            </div>

            <div class="mt-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="divide-y divide-gray-100">

                    {{-- Order updates - mandatory --}}
                    <div class="flex items-start justify-between gap-4 p-5 sm:p-6">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100">
                                <x-tabler-package size="17" class="text-gray-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Order updates</p>
                                <p class="mt-0.5 text-sm text-gray-500">Confirmation, shipping, and delivery status for your orders.</p>
                            </div>
                        </div>
                        <span class="mt-1 flex-shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-500">Always on</span>
                    </div>

                    {{-- Promotions --}}
                    <div class="flex items-start justify-between gap-4 p-5 sm:p-6">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100">
                                <x-tabler-sparkles size="17" class="text-gray-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Promotions &amp; new drops</p>
                                <p class="mt-0.5 text-sm text-gray-500">Sales, discount codes, and new collection launches.</p>
                            </div>
                        </div>
                        <label class="relative mt-1 inline-flex flex-shrink-0 cursor-pointer items-center">
                            <input type="checkbox" name="notify_promotions" class="peer sr-only" checked>
                            <div class="h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-gray-900 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition after:content-[''] peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>

                    {{-- Restock alerts --}}
                    <div class="flex items-start justify-between gap-4 p-5 sm:p-6">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100">
                                <x-tabler-heart size="17" class="text-gray-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Restock alerts</p>
                                <p class="mt-0.5 text-sm text-gray-500">When a wishlist item you saved comes back in stock.</p>
                            </div>
                        </div>
                        <label class="relative mt-1 inline-flex flex-shrink-0 cursor-pointer items-center">
                            <input type="checkbox" name="notify_restock" class="peer sr-only" checked>
                            <div class="h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-gray-900 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition after:content-[''] peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>

                    {{-- Price drop alerts --}}
                    <div class="flex items-start justify-between gap-4 p-5 sm:p-6">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100">
                                <x-tabler-tag size="17" class="text-gray-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Price drop alerts</p>
                                <p class="mt-0.5 text-sm text-gray-500">When a saved item's price is reduced.</p>
                            </div>
                        </div>
                        <label class="relative mt-1 inline-flex flex-shrink-0 cursor-pointer items-center">
                            <input type="checkbox" name="notify_price_drop" class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-gray-900 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition after:content-[''] peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Delivery channel --}}
            <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-sm font-medium text-gray-900">Send notifications via</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <label class="flex cursor-pointer items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm text-gray-700 has-[:checked]:border-gray-900 has-[:checked]:bg-gray-900 has-[:checked]:text-white">
                        <input type="checkbox" name="channel_sms" class="hidden" checked>
                        <x-tabler-message size="15" />
                        SMS
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm text-gray-700 has-[:checked]:border-gray-900 has-[:checked]:bg-gray-900 has-[:checked]:text-white">
                        <input type="checkbox" name="channel_email" class="hidden" checked>
                        <x-tabler-mail size="15" />
                        Email
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm text-gray-700 has-[:checked]:border-gray-900 has-[:checked]:bg-gray-900 has-[:checked]:text-white">
                        <input type="checkbox" name="channel_push" class="hidden">
                        <x-tabler-bell-ringing size="15" />
                        Push
                    </label>
                </div>
            </div>

            <button data-save-notifications type="button" class="button-dark mt-6 inline-flex items-center gap-2 rounded-full px-6">
                Save preferences
            </button>
        </div>
    </div>
</div>
@endsection
