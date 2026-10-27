@if($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <ul class="space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="variant-size">Size</label>
        <input id="variant-size" class="field" name="size" value="{{ old('size', $variant->size) }}" maxlength="8" required>
    </div>
    <div>
        <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="variant-color">Color</label>
        <input id="variant-color" class="field" name="color" value="{{ old('color', $variant->color) }}" maxlength="30" required>
    </div>
    <div>
        <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="variant-sku">SKU</label>
        <input id="variant-sku" class="field font-mono" name="sku" value="{{ old('sku', $variant->sku) }}" maxlength="80" pattern="[A-Za-z0-9][A-Za-z0-9._-]*" required>
        <p class="mt-1 text-xs text-slate-400">Letters, numbers, dots, underscores and hyphens.</p>
    </div>
    <div>
        <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="variant-stock">Stock</label>
        <input id="variant-stock" class="field" type="number" name="stock" value="{{ old('stock', $variant->stock ?? 0) }}" min="0" max="2147483647" required>
    </div>
</div>

<div class="flex gap-3 pt-2">
    <button type="submit" class="kp-admin-primary-button">{{ $submitLabel }}</button>
    <a href="{{ route('admin.wear.products.variants.index', $product) }}" class="kp-admin-secondary-button">Cancel</a>
</div>
