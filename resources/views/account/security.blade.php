@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">

        @include('components.account-sidebar')

        <div data-security-page class="space-y-6">
            <div class="border-b border-gray-200 pb-6">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    <x-tabler-shield-lock size="14" />
                    Account
                </p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Security &amp; login</h1>
                <p class="mt-2 text-sm text-gray-500">Manage how you sign in and keep your account secure.</p>
            </div>

            {{-- Phone number --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100">
                            <x-tabler-device-mobile size="18" class="text-gray-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Phone number</p>
                            <p data-current-phone class="text-sm text-gray-500">07XX XXX XXX</p>
                        </div>
                    </div>
                    <button data-change-phone type="button" class="text-sm font-medium text-emerald-600 hover:underline">Change</button>
                </div>

                <form data-change-phone-form class="mt-5 hidden gap-3 border-t border-gray-100 pt-5 sm:grid-cols-2">
                    <input name="new_phone" class="field w-full" placeholder="New phone number" required>
                    <button class="button-dark inline-flex items-center justify-center gap-2 py-2.5">
                        Send verification code
                    </button>
                </form>
            </section>

            {{-- Active sessions --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100">
                        <x-tabler-devices size="18" class="text-gray-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Active sessions</p>
                        <p class="text-sm text-gray-500">Devices currently signed in to your account.</p>
                    </div>
                </div>

                <div data-sessions-list class="mt-5 space-y-3 border-t border-gray-100 pt-5">
                    {{-- Each session renders as, e.g.: --}}
                    {{--
                    <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-100 p-3.5">
                        <div class="flex items-center gap-3">
                            <x-tabler-device-mobile size="18" class="text-gray-400" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">Chrome · Dar es Salaam</p>
                                <p class="text-xs text-gray-400">Current session</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">This device</span>
                    </div>
                    --}}
                </div>

                <button data-signout-all type="button" class="mt-5 flex items-center gap-1.5 text-sm font-medium text-rose-500 hover:underline">
                    <x-tabler-logout size="15" />
                    Sign out of all other devices
                </button>
            </section>

            {{-- Delete account --}}
            <section class="rounded-2xl border border-red-200 bg-red-50 p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <x-tabler-alert-triangle size="18" class="mt-0.5 flex-shrink-0 text-red-500" />
                    <div>
                        <p class="text-sm font-semibold text-red-900">Delete account</p>
                        <p class="mt-1 text-sm text-red-700">Permanently delete your account and all associated data. This cannot be undone.</p>
                        <button data-delete-account type="button" class="mt-4 rounded-full border border-red-300 bg-white px-5 py-2 text-sm font-medium text-red-600 hover:bg-red-100">
                            Request account deletion
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
