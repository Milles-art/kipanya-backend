@extends('layouts.app')
@section('content')
<div class="mx-auto kp-content px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">

        @include('components.account-sidebar')

        <div data-returns-page>
            <div class="flex flex-col gap-3 border-b border-emerald-950/12 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-black">
                        <x-tabler-rotate size="14" />
                        Support
                    </p>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-black sm:text-3xl">Returns &amp; support</h1>
                    <p class="mt-2 text-sm text-black">Request a return or exchange, or get help with an order.</p>
                </div>
                <button data-new-request type="button" class="button-dark px-5">
                    <x-tabler-plus size="16" />
                    New request
                </button>
            </div>

            {{-- Empty state --}}
            <div data-returns-empty class="hidden flex-col items-center py-20 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50/70 ring-8 ring-black">
                    <x-tabler-rotate size="26" class="text-black" />
                </div>
                <h2 class="mt-4 text-lg font-semibold text-black">No return requests yet</h2>
                <p class="mt-2 max-w-xs text-sm text-black">Select a delivered order to request a return or exchange. Our team will review the request and confirm eligibility.</p>
            </div>

            {{-- Requests list --}}
            <div data-returns-list class="mt-6 space-y-3">
                {{-- Each request renders as, e.g.: --}}
                {{--
                <div class="rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-black">Return · Order #KP-000123</p>
                            <p class="mt-0.5 text-xs text-black">Requested 2 days ago · 1 item</p>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-medium text-amber-700">Under review</span>
                    </div>
                    <div class="mt-4 flex items-center gap-3 border-t border-emerald-950/10 pt-4">
                        <img src="" class="h-12 w-12 rounded-lg bg-emerald-50/70 object-cover" alt="">
                        <div>
                            <p class="text-sm text-black">Product name · Size M</p>
                            <p class="text-xs text-black">Reason: Wrong size</p>
                        </div>
                    </div>
                </div>
                --}}
            </div>

            {{-- Contact support --}}
            <div class="mt-8 rounded-2xl border border-emerald-950/12 bg-emerald-50/50 p-5 sm:p-6">
                <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm">
                            <x-tabler-headset size="18" class="text-black" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-black">Need help with something else?</p>
                            <p class="text-sm text-black">Our team typically replies within a few hours.</p>
                        </div>
                    </div>
                    <button data-contact-support type="button" class="kp-button-secondary min-h-10 rounded-full px-5 py-2.5">
                        <x-tabler-message-2 size="16" />
                        Contact support
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- New return/exchange request modal --}}
    <div data-return-modal class="fixed inset-0 z-50 hidden items-end justify-center bg-black/40 sm:items-center">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-t-2xl bg-white p-6 shadow-xl sm:rounded-2xl">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-black">Request return or exchange</h2>
                <button data-close-return-modal type="button" class="text-black hover:text-black">
                    <x-tabler-x size="20" />
                </button>
            </div>

            <form data-return-form class="mt-5 space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-black">Select order</label>
                    <select name="order_id" data-return-order-select class="field w-full" required>
                        <option value="">Choose an order…</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-black">Item(s)</label>
                    <div data-return-items class="space-y-2 rounded-xl border border-emerald-950/10 p-3 text-sm text-black">
                        Select an order to see items
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-black">Request type</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-emerald-950/12 py-2.5 text-sm font-medium text-black has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white">
                            <input type="radio" name="request_type" value="return" class="hidden" checked>
                            Return
                        </label>
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-emerald-950/12 py-2.5 text-sm font-medium text-black has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white">
                            <input type="radio" name="request_type" value="exchange" class="hidden">
                            Exchange
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-black">Reason</label>
                    <select name="reason" class="field w-full" required>
                        <option value="">Select a reason…</option>
                        <option value="wrong_size">Wrong size</option>
                        <option value="damaged">Damaged or defective</option>
                        <option value="not_as_described">Not as described</option>
                        <option value="changed_mind">Changed my mind</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-black">Additional details <span class="font-normal text-black">(optional)</span></label>
                    <textarea name="notes" class="field min-h-24 w-full" placeholder="Tell us more…"></textarea>
                </div>

                <button class="button-dark w-full py-3">Submit request</button>
            </form>
        </div>
    </div>
</div>
@endsection
