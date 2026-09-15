@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">

        @include('account._account-sidebar')

        <div data-payment-methods-page>
            <div class="flex flex-col gap-3 border-b border-gray-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">
                        <x-tabler-credit-card size="14" />
                        Payments
                    </p>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Payment methods</h1>
                    <p class="mt-2 text-sm text-gray-500">Save a mobile money number or card so checkout is faster next time.</p>
                </div>
                <button data-add-payment-method type="button" class="button-dark inline-flex items-center justify-center gap-2 rounded-full px-5">
                    <x-tabler-plus size="16" />
                    Add method
                </button>
            </div>

            {{-- Empty state --}}
            <div data-payment-methods-empty class="hidden flex-col items-center py-20 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 ring-8 ring-gray-50">
                    <x-tabler-credit-card size="26" class="text-gray-400" />
                </div>
                <h2 class="mt-4 text-lg font-semibold text-gray-900">No saved payment methods</h2>
                <p class="mt-2 max-w-xs text-sm text-gray-500">Add a mobile money number or card to check out faster.</p>
            </div>

            {{-- Saved methods list --}}
            <div data-payment-methods-list class="mt-6 space-y-3">
                {{-- Each saved method renders as a card, e.g.: --}}
                {{--
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-100">
                            <x-tabler-brand-mastercard size="22" class="text-gray-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Visa ending in 4417</p>
                            <p class="text-xs text-gray-500">Expires 08/28</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">Default</span>
                        <button class="text-gray-400 hover:text-red-600"><x-tabler-trash size="17" /></button>
                    </div>
                </div>
                --}}
                {{--
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50">
                            <x-tabler-device-mobile size="20" class="text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">M-Pesa · 07XX XXX XXX</p>
                            <p class="text-xs text-gray-500">Mobile money</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="text-xs font-medium text-gray-500 underline underline-offset-2 hover:text-gray-900">Set default</button>
                        <button class="text-gray-400 hover:text-red-600"><x-tabler-trash size="17" /></button>
                    </div>
                </div>
                --}}
            </div>

            <div class="mt-6 flex items-start gap-2.5 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-xs text-gray-500">
                <x-tabler-shield-check size="16" class="mt-0.5 flex-shrink-0 text-gray-400" />
                Payment details are stored and processed securely through Selcom. We never store your full card number.
            </div>
        </div>
    </div>

    {{-- Add payment method modal --}}
    <div data-add-payment-modal class="fixed inset-0 z-50 hidden items-end justify-center bg-black/40 sm:items-center">
        <div class="w-full max-w-md rounded-t-2xl bg-white p-6 shadow-xl sm:rounded-2xl">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Add payment method</h2>
                <button data-close-payment-modal type="button" class="text-gray-400 hover:text-gray-700">
                    <x-tabler-x size="20" />
                </button>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <button data-payment-type="mobile" type="button" class="flex flex-col items-center gap-2 rounded-xl border border-gray-200 p-4 text-sm font-medium text-gray-700 transition hover:border-gray-900">
                    <x-tabler-device-mobile size="22" />
                    Mobile Money
                </button>
                <button data-payment-type="card" type="button" class="flex flex-col items-center gap-2 rounded-xl border border-gray-200 p-4 text-sm font-medium text-gray-700 transition hover:border-gray-900">
                    <x-tabler-credit-card size="22" />
                    Card
                </button>
            </div>

            <form data-payment-method-form class="mt-5 space-y-3">
                <input name="account_number" class="field w-full" placeholder="Phone number or card number" required>
                <button class="button-dark w-full py-3">Save method</button>
            </form>
        </div>
    </div>
</div>
@endsection
