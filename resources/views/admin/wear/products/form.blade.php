@if($errors->any())
<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
    <p class="font-bold">Please fix the following:</p>
    <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

@if(session('success'))
<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ $formAction }}" class="space-y-6">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <section class="grid gap-5 rounded-2xl border border-gray-200 bg-white p-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-semibold">Product name</label>
            <input class="field w-full" name="name" value="{{ old('name', $product->name) }}" required maxlength="160">
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold">Slug</label>
            <input class="field w-full" name="slug" value="{{ old('slug', $product->slug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*">
            <p class="mt-1 text-xs text-gray-400">Lowercase letters, numbers and hyphens only.</p>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold">Category</label>
            <select class="field w-full" name="category" required>
                @foreach($categories as $item)
                    <option value="{{ $item }}" @selected(old('category', $product->category) === $item)>{{ $item }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold">Price (TZS)</label>
            <input class="field w-full" type="number" name="price" min="0" step="0.01" value="{{ old('price', $product->price) }}" required>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold">Compare-at price (TZS)</label>
            <input class="field w-full" type="number" name="compare_at_price" min="0" step="0.01" value="{{ old('compare_at_price', $product->compare_at_price) }}">
        </div>

        <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-semibold">Image path / URL</label>
            <input class="field w-full" name="image_path" value="{{ old('image_path', $product->image_path) }}" maxlength="500" placeholder="assets/wear/catalog/generated/product-01.jpg">
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold">Badge</label>
            <input class="field w-full" name="badge" value="{{ old('badge', $product->badge) }}" maxlength="30" placeholder="NEW">
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold">Sort order</label>
            <input class="field w-full" type="number" name="sort_order" min="0" value="{{ old('sort_order', $product->sort_order ?? 0) }}" required>
        </div>

        <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-semibold">Description</label>
            <textarea class="field min-h-32 w-full" name="description" maxlength="10000">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="sm:col-span-2 flex flex-wrap gap-6">
            <label class="inline-flex items-center gap-2 text-sm font-medium">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                Active
            </label>
            <label class="inline-flex items-center gap-2 text-sm font-medium">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                Featured
            </label>
        </div>
    </section>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.wear.products.index') }}" class="rounded-xl border border-gray-200 px-5 py-3 text-sm font-semibold hover:bg-gray-50">Cancel</a>
        <button class="button-dark">{{ $method === 'POST' ? 'Create product' : 'Save changes' }}</button>
    </div>
</form>
