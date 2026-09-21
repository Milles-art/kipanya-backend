@extends('admin.layouts.app')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif

    <div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-gray-400">Storefront</p>
        <div class="mt-1 flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
            <div>
                <h2 class="text-2xl font-black tracking-tight">Control what customers see</h2>
                <p class="mt-1 text-sm text-gray-500">Manage the homepage hero, merchandising and shop presentation without editing Blade templates.</p>
            </div>
            <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">Live storefront controls</span>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.storefront.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

        <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start justify-between gap-4">
                <div><h3 class="text-lg font-black">Hero</h3><p class="mt-1 text-sm text-gray-500">The first thing customers see on the storefront.</p></div>
                <label class="inline-flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="hero_is_active" value="1" @checked(($settings['hero_is_active']->value ?? '1') === '1') class="rounded border-gray-300"> Active</label>
            </div>
            <div class="mt-6 grid gap-5 lg:grid-cols-2">
                <div><label class="text-sm font-semibold">Eyebrow</label><input name="hero_eyebrow" value="{{ old('hero_eyebrow', $settings['hero_eyebrow']->value ?? '') }}" class="mt-2 w-full rounded-xl border-gray-200 px-4 py-3"></div>
                <div><label class="text-sm font-semibold">CTA label</label><input name="hero_cta_label" value="{{ old('hero_cta_label', $settings['hero_cta_label']->value ?? '') }}" class="mt-2 w-full rounded-xl border-gray-200 px-4 py-3"></div>
                <div class="lg:col-span-2"><label class="text-sm font-semibold">Headline</label><input name="hero_title" value="{{ old('hero_title', $settings['hero_title']->value ?? '') }}" class="mt-2 w-full rounded-xl border-gray-200 px-4 py-3"></div>
                <div class="lg:col-span-2"><label class="text-sm font-semibold">Description</label><textarea name="hero_description" rows="3" class="mt-2 w-full rounded-xl border-gray-200 px-4 py-3">{{ old('hero_description', $settings['hero_description']->value ?? '') }}</textarea></div>
                <div><label class="text-sm font-semibold">CTA URL</label><input name="hero_cta_url" value="{{ old('hero_cta_url', $settings['hero_cta_url']->value ?? '') }}" class="mt-2 w-full rounded-xl border-gray-200 px-4 py-3"></div>
                <div><label class="text-sm font-semibold">Desktop image</label><input type="file" name="hero_image_desktop" accept="image/*" class="mt-2 block w-full rounded-xl border border-gray-200 px-4 py-3 text-sm"></div>
                <div><label class="text-sm font-semibold">Mobile image</label><input type="file" name="hero_image_mobile" accept="image/*" class="mt-2 block w-full rounded-xl border border-gray-200 px-4 py-3 text-sm"></div>
            </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
            <div><h3 class="text-lg font-black">Homepage merchandising</h3><p class="mt-1 text-sm text-gray-500">Choose exactly which products and collections are featured, and in what order.</p></div>
            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div><label class="text-sm font-semibold">Featured products</label><select name="homepage_featured_product_ids[]" multiple size="9" class="mt-2 w-full rounded-xl border-gray-200 px-3 py-2">
                    @foreach($products as $product)<option value="{{ $product->id }}" @selected(in_array($product->id, $featuredProductIds, true))>{{ $product->name }}</option>@endforeach
                </select><p class="mt-2 text-xs text-gray-400">Use Ctrl/Cmd-click to select multiple. Selection order controls display order.</p></div>
                <div><label class="text-sm font-semibold">Featured collections</label><select name="homepage_featured_collection_ids[]" multiple size="9" class="mt-2 w-full rounded-xl border-gray-200 px-3 py-2">
                    @foreach($collections as $collection)<option value="{{ $collection->id }}" @selected(in_array($collection->id, $featuredCollectionIds, true))>{{ $collection->name }}</option>@endforeach
                </select><p class="mt-2 text-xs text-gray-400">Only active collections are available here.</p></div>
            </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start justify-between gap-4"><div><h3 class="text-lg font-black">Shop presentation</h3><p class="mt-1 text-sm text-gray-500">Control the optional shop banner and default product ordering.</p></div><label class="inline-flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="shop_banner_is_active" value="1" @checked(($settings['shop_banner_is_active']->value ?? '0') === '1') class="rounded border-gray-300"> Banner active</label></div>
            <div class="mt-6 grid gap-5 lg:grid-cols-2">
                <div><label class="text-sm font-semibold">Banner title</label><input name="shop_banner_title" value="{{ old('shop_banner_title', $settings['shop_banner_title']->value ?? '') }}" class="mt-2 w-full rounded-xl border-gray-200 px-4 py-3"></div>
                <div><label class="text-sm font-semibold">Default sort</label><select name="shop_default_sort" class="mt-2 w-full rounded-xl border-gray-200 px-4 py-3"><option value="featured" @selected(($settings['shop_default_sort']->value ?? 'featured') === 'featured')>Featured</option><option value="newest" @selected(($settings['shop_default_sort']->value ?? '') === 'newest')>Newest</option><option value="price-asc" @selected(($settings['shop_default_sort']->value ?? '') === 'price-asc')>Price: low to high</option><option value="price-desc" @selected(($settings['shop_default_sort']->value ?? '') === 'price-desc')>Price: high to low</option></select></div>
                <div class="lg:col-span-2"><label class="text-sm font-semibold">Banner description</label><textarea name="shop_banner_description" rows="3" class="mt-2 w-full rounded-xl border-gray-200 px-4 py-3">{{ old('shop_banner_description', $settings['shop_banner_description']->value ?? '') }}</textarea></div>
                <div><label class="text-sm font-semibold">Banner image</label><input type="file" name="shop_banner_image" accept="image/*" class="mt-2 block w-full rounded-xl border border-gray-200 px-4 py-3 text-sm"></div>
            </div>
        </section>

        @if($errors->any())<div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700"><p class="font-bold">Please fix the following:</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="flex justify-end"><button class="rounded-2xl bg-gray-950 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-gray-800">Save storefront changes</button></div>
    </form>
</div>
@endsection
