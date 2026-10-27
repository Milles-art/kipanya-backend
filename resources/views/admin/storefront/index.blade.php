@extends('admin.layouts.app', ['title' => 'Storefront', 'heading' => 'Storefront'])

@section('content')
<div class="mx-auto max-w-6xl">
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <x-admin.page-header title="Control what customers see" subtitle="Manage the homepage hero, merchandising and shop presentation without editing Blade templates.">
        <x-slot name="action">
            <x-admin.badge status="green" label="Live storefront controls" />
        </x-slot>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.storefront.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Hero --}}
        <x-admin.card class="p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="kp-admin-display text-lg">Hero</h3>
                    <p class="mt-1.5 text-xs text-slate-400">The first thing customers see on the storefront.</p>
                </div>
                <label class="inline-flex shrink-0 items-center gap-2 text-sm font-medium text-slate-600">
                    <input type="checkbox" name="hero_is_active" value="1" @checked(($settings['hero_is_active']->value ?? '1') === '1') class="h-4 w-4 rounded border-slate-300">
                    Active
                </label>
            </div>

            <div class="mt-6 grid gap-5 lg:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Eyebrow</label>
                    <input name="hero_eyebrow" value="{{ old('hero_eyebrow', $settings['hero_eyebrow']->value ?? '') }}" class="kp-admin-input">
                </div>
                <div>
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">CTA label</label>
                    <input name="hero_cta_label" value="{{ old('hero_cta_label', $settings['hero_cta_label']->value ?? '') }}" class="kp-admin-input">
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Headline</label>
                    <input name="hero_title" value="{{ old('hero_title', $settings['hero_title']->value ?? '') }}" class="kp-admin-input">
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Description</label>
                    <textarea name="hero_description" rows="3" class="kp-admin-input resize-none">{{ old('hero_description', $settings['hero_description']->value ?? '') }}</textarea>
                </div>
                <div>
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">CTA URL</label>
                    <input name="hero_cta_url" value="{{ old('hero_cta_url', $settings['hero_cta_url']->value ?? '') }}" class="kp-admin-input">
                </div>
                <div>
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Desktop image</label>
                    <input type="file" name="hero_image_desktop" accept="image/*" class="kp-admin-input w-full text-sm">
                </div>
                <div>
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Mobile image</label>
                    <input type="file" name="hero_image_mobile" accept="image/*" class="kp-admin-input w-full text-sm">
                </div>
            </div>
        </x-admin.card>

        {{-- Homepage merchandising --}}
        <x-admin.card class="p-6">
            <h3 class="kp-admin-display text-lg">Homepage merchandising</h3>
            <p class="mt-1.5 text-xs text-slate-400">Choose exactly which products and collections are featured, and in what order.</p>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Featured products</label>
                    <select name="homepage_featured_product_ids[]" multiple size="9" class="kp-admin-input w-full">
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(in_array($product->id, $featuredProductIds, true))>{{ $product->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-slate-400">Use Ctrl/Cmd-click to select multiple. Selection order controls display order.</p>
                </div>
                <div>
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Featured collections</label>
                    <select name="homepage_featured_collection_ids[]" multiple size="9" class="kp-admin-input w-full">
                        @foreach($collections as $collection)
                            <option value="{{ $collection->id }}" @selected(in_array($collection->id, $featuredCollectionIds, true))>{{ $collection->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-slate-400">Only active collections are available here.</p>
                </div>
            </div>
        </x-admin.card>

        {{-- Shop presentation --}}
        <x-admin.card class="p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="kp-admin-display text-lg">Shop presentation</h3>
                    <p class="mt-1.5 text-xs text-slate-400">Control the optional shop banner and default product ordering.</p>
                </div>
                <label class="inline-flex shrink-0 items-center gap-2 text-sm font-medium text-slate-600">
                    <input type="checkbox" name="shop_banner_is_active" value="1" @checked(($settings['shop_banner_is_active']->value ?? '0') === '1') class="h-4 w-4 rounded border-slate-300">
                    Banner active
                </label>
            </div>

            <div class="mt-6 grid gap-5 lg:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Banner title</label>
                    <input name="shop_banner_title" value="{{ old('shop_banner_title', $settings['shop_banner_title']->value ?? '') }}" class="kp-admin-input">
                </div>
                <div>
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Default sort</label>
                    <select name="shop_default_sort" class="kp-admin-input">
                        <option value="featured" @selected(($settings['shop_default_sort']->value ?? 'featured') === 'featured')>Featured</option>
                        <option value="newest" @selected(($settings['shop_default_sort']->value ?? '') === 'newest')>Newest</option>
                        <option value="price-asc" @selected(($settings['shop_default_sort']->value ?? '') === 'price-asc')>Price: low to high</option>
                        <option value="price-desc" @selected(($settings['shop_default_sort']->value ?? '') === 'price-desc')>Price: high to low</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Banner description</label>
                    <textarea name="shop_banner_description" rows="3" class="kp-admin-input resize-none">{{ old('shop_banner_description', $settings['shop_banner_description']->value ?? '') }}</textarea>
                </div>
                <div>
                    <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Banner image</label>
                    <input type="file" name="shop_banner_image" accept="image/*" class="kp-admin-input w-full text-sm">
                </div>
            </div>
        </x-admin.card>

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <p class="font-semibold">Please fix the following:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex justify-end">
            <button type="submit" class="kp-admin-primary-button">Save storefront changes</button>
        </div>
    </form>
</div>
@endsection
