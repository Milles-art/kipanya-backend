<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Http\Controllers\Controller;
use App\Models\Wear\WearProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ProductController extends Controller
{
    private const CATEGORIES = [
        'Hoodies',
        'Long Sleeves',
        'T-Shirts',
        'Shirts',
        'Polos',
    ];

    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all');
        $category = $request->query('category', 'all');

        $products = WearProduct::query()
            ->withCount('variants')
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
            ->when(in_array($category, self::CATEGORIES, true), fn ($query) => $query->where('category', $category))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.wear.products.index', [
            'products' => $products,
            'categories' => self::CATEGORIES,
            'search' => $search,
            'status' => $status,
            'category' => $category,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('admin.wear.products.create', [
            'product' => new WearProduct([
                'is_active' => true,
                'is_featured' => false,
            ]),
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $this->validateProduct($request);

        $product = DB::transaction(function () use ($request, $data) {
            if ($request->hasFile('image')) {
                $data['image_path'] = '/storage/'.$request->file('image')->store('wear/products', 'public');
            }

            $product = WearProduct::create($data);

            app(\App\Support\AuditLogger::class)->log(
                request(),
                'admin.wear.product.created',
                $product,
                ['name' => $product->name, 'category' => $product->category],
            );

            return $product;
        });

        return redirect()
            ->route('admin.wear.products.edit', $product)
            ->with('success', 'Product created successfully.');
    }

    public function edit(Request $request, WearProduct $product): View
    {
        $this->authorizeAdmin($request);

        return view('admin.wear.products.edit', [
            'product' => $product,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function update(Request $request, WearProduct $product): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $this->validateProduct($request, $product);

        DB::transaction(function () use ($request, $product, $data) {
            $before = $product->only([
                'name', 'slug', 'category', 'price', 'compare_at_price',
                'image_path', 'badge', 'is_featured', 'is_active', 'sort_order',
            ]);

            if ($request->hasFile('image')) {
                $old = $product->image_path;
                $data['image_path'] = '/storage/'.$request->file('image')->store('wear/products', 'public');
                $this->deleteStoredImage($old);
            }

            $product->update($data);

            app(\App\Support\AuditLogger::class)->log(
                $request,
                'admin.wear.product.updated',
                $product,
                ['before' => $before, 'after' => $product->only(array_keys($before))],
            );
        });

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, WearProduct $product): RedirectResponse
    {
        $this->authorizeAdmin($request);

        if ($product->variants()->exists()) {
            return back()->withErrors([
                'product' => 'This product has variants. Remove its variants before deleting it, or deactivate the product instead.',
            ]);
        }

        DB::transaction(function () use ($request, $product) {
            $name = $product->name;
            $oldImage = $product->image_path;
            $product->delete();
            $this->deleteStoredImage($oldImage);

            app(\App\Support\AuditLogger::class)->log(
                $request,
                'admin.wear.product.deleted',
                null,
                ['product_id' => $product->id, 'name' => $name],
            );
        });

        return redirect()
            ->route('admin.wear.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    private function validateProduct(Request $request, ?WearProduct $product = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => [
                'required', 'string', 'max:180',
                Rule::unique('wear_products', 'slug')->ignore($product?->id),
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'description' => ['nullable', 'string', 'max:10000'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'gte:price'],
            'image_path' => ['nullable', 'string', 'max:500', function (string $attribute, mixed $value, $fail): void {
                $value = trim((string) $value);
                if ($value === '') return;
                if (str_contains($value, '..') || str_contains($value, '\\')) {
                    $fail('The image path is invalid.');
                    return;
                }
                if (preg_match('/^https?:\/\//i', $value)) return;
                if (str_starts_with($value, '/storage/')) return;
                if (str_starts_with($value, 'assets/')) return;
                $fail('The image path must be a local storage/assets path or an HTTP(S) URL.');
            }],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'badge' => ['nullable', 'string', 'max:30'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('commerce.manage'),
            403,
        );
    }

    /**
     * Delete a previously stored product image. Only genuine `/storage/` paths
     * are accepted and traversal segments are refused so a crafted value can
     * never delete files outside the public disk.
     */
    private function deleteStoredImage(?string $path): void
    {
        if (
            ! is_string($path)
            || ! str_starts_with($path, '/storage/')
            || str_contains($path, '..')
            || str_contains($path, '\\')
        ) {
            return;
        }

        Storage::disk('public')->delete(ltrim(substr($path, 9), '/'));
    }
}
