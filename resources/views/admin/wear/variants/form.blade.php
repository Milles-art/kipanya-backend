@if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><ul class="space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="grid gap-5 md:grid-cols-2">
    <div><label class="field-label">Size</label><input class="field" name="size" value="{{ old('size', $variant->size) }}" maxlength="8" required></div>
    <div><label class="field-label">Color</label><input class="field" name="color" value="{{ old('color', $variant->color) }}" maxlength="30" required></div>
    <div><label class="field-label">SKU</label><input class="field font-mono" name="sku" value="{{ old('sku', $variant->sku) }}" maxlength="80" pattern="[A-Za-z0-9][A-Za-z0-9._-]*" required><p class="mt-1 text-xs text-gray-400">Letters, numbers, dots, underscores and hyphens.</p></div>
    <div><label class="field-label">Stock</label><input class="field" type="number" name="stock" value="{{ old('stock', $variant->stock ?? 0) }}" min="0" max="2147483647" required></div>
</div>

<div class="flex gap-3 pt-2">
    <button class="button-dark">{{ $submitLabel }}</button>
    <a href="{{ route('admin.wear.products.variants.index', $product) }}" class="rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold hover:bg-gray-50">Cancel</a>
</div>
