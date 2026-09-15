@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">

        @include('account._account-sidebar')

        <div data-size-profile-page>
            <div class="border-b border-gray-200 pb-6">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    <x-tabler-ruler-2 size="14" />
                    Fit
                </p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Size profile</h1>
                <p class="mt-2 text-sm text-gray-500">Save your usual sizes so we can pre-select them for you on product pages.</p>
            </div>

            <form data-size-profile-form class="mt-6 space-y-4">

                {{-- Tops --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100">
                            <x-tabler-shirt size="17" class="text-gray-600" />
                        </div>
                        <p class="text-sm font-medium text-gray-900">Tops</p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach(['XS','S','M','L','XL','XXL'] as $size)
                            <label class="flex h-10 w-14 cursor-pointer items-center justify-center rounded-lg border border-gray-200 text-sm font-medium text-gray-700 has-[:checked]:border-gray-900 has-[:checked]:bg-gray-900 has-[:checked]:text-white">
                                <input type="radio" name="size_top" value="{{ $size }}" class="hidden">
                                {{ $size }}
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- Bottoms --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100">
                            <x-tabler-trouser size="17" class="text-gray-600" />
                        </div>
                        <p class="text-sm font-medium text-gray-900">Bottoms</p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach(['28','30','32','34','36','38'] as $size)
                            <label class="flex h-10 w-14 cursor-pointer items-center justify-center rounded-lg border border-gray-200 text-sm font-medium text-gray-700 has-[:checked]:border-gray-900 has-[:checked]:bg-gray-900 has-[:checked]:text-white">
                                <input type="radio" name="size_bottom" value="{{ $size }}" class="hidden">
                                {{ $size }}
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- Shoes --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100">
                            <x-tabler-shoe size="17" class="text-gray-600" />
                        </div>
                        <p class="text-sm font-medium text-gray-900">Shoes</p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach(['39','40','41','42','43','44','45'] as $size)
                            <label class="flex h-10 w-14 cursor-pointer items-center justify-center rounded-lg border border-gray-200 text-sm font-medium text-gray-700 has-[:checked]:border-gray-900 has-[:checked]:bg-gray-900 has-[:checked]:text-white">
                                <input type="radio" name="size_shoe" value="{{ $size }}" class="hidden">
                                {{ $size }}
                            </label>
                        @endforeach
                    </div>
                </section>

                <button class="button-dark inline-flex items-center gap-2 rounded-full px-6">Save size profile</button>
            </form>

            <div class="mt-6 flex items-start gap-2.5 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-xs text-gray-500">
                <x-tabler-info-circle size="16" class="mt-0.5 flex-shrink-0 text-gray-400" />
                We'll pre-select these sizes automatically on product pages — you can still change your selection anytime before adding to bag.
            </div>
        </div>
    </div>
</div>
@endsection
