<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Http\Controllers\Controller;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class VariantController extends Controller
{
    public function index(Request $request, WearProduct $product): View
    {
        $this->authorizeAdmin($request);

        $variants = $product->variants()
            ->orderBy('size')
            ->orderBy('color')
            ->paginate(20)
            ->withQueryString();

        return view('admin.wear.variants.index', compact('product', 'variants'));
    }

    public function create(Request $request, WearProduct $product): View
    {
        $this->authorizeAdmin($request);

        return view('admin.wear.variants.create', [
            'product' => $product,
            'variant' => new WearProductVariant(['stock' => 0]),
        ]);
    }

    public function store(Request $request, WearProduct $product): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $this->validateVariant($request, $product);

        $variant = DB::transaction(function () use ($request, $product, $data): WearProductVariant {
            $variant = $product->variants()->create($data);

            app(\App\Support\AuditLogger::class)->log(
                $request,
                'admin.wear.variant.created',
                $variant,
                [
                    'product_id' => $product->id,
                    'sku' => $variant->sku,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'stock' => $variant->stock,
                ],
            );

            return $variant;
        });

        return redirect()
            ->route('admin.wear.products.variants.index', $product)
            ->with('success', 'Variant created successfully.');
    }

    public function edit(Request $request, WearProduct $product, WearProductVariant $variant): View
    {
        $this->authorizeAdmin($request);
        $this->ensureBelongsToProduct($product, $variant);

        return view('admin.wear.variants.edit', compact('product', 'variant'));
    }

    public function update(Request $request, WearProduct $product, WearProductVariant $variant): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $this->ensureBelongsToProduct($product, $variant);
        $data = $this->validateVariant($request, $product, $variant);

        DB::transaction(function () use ($request, $product, $variant, $data): void {
            $before = $variant->only(['size', 'color', 'stock', 'sku']);
            $variant->update($data);

            app(\App\Support\AuditLogger::class)->log(
                $request,
                'admin.wear.variant.updated',
                $variant,
                ['product_id' => $product->id, 'before' => $before, 'after' => $variant->only(array_keys($before))],
            );
        });

        return back()->with('success', 'Variant updated successfully.');
    }

    public function destroy(Request $request, WearProduct $product, WearProductVariant $variant): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $this->ensureBelongsToProduct($product, $variant);

        $usedByCart = $variant->cartItems()->exists();
        $usedByOrder = $variant->orderItems()->exists();
        $usedByReservation = $variant->stockReservationItems()->exists();

        if ($usedByCart || $usedByOrder || $usedByReservation) {
            return back()->withErrors([
                'variant' => 'This variant is already referenced by commerce records and cannot be deleted. Deactivate the product or set stock to 0 instead.',
            ]);
        }

        DB::transaction(function () use ($request, $product, $variant): void {
            $snapshot = ['product_id' => $product->id, 'sku' => $variant->sku, 'size' => $variant->size, 'color' => $variant->color];
            $variant->delete();

            app(\App\Support\AuditLogger::class)->log(
                $request,
                'admin.wear.variant.deleted',
                null,
                ['variant_id' => $variant->id, ...$snapshot],
            );
        });

        return redirect()
            ->route('admin.wear.products.variants.index', $product)
            ->with('success', 'Variant deleted successfully.');
    }

    private function validateVariant(Request $request, WearProduct $product, ?WearProductVariant $variant = null): array
    {
        return $request->validate([
            'size' => ['required', 'string', 'max:8'],
            'color' => ['required', 'string', 'max:30'],
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'sku' => [
                'required', 'string', 'max:80',
                'regex:/^[A-Za-z0-9][A-Za-z0-9._-]*$/',
                Rule::unique('wear_product_variants', 'sku')->ignore($variant?->id),
            ],
            'product' => ['prohibited'],
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('commerce.manage'),
            403,
        );
    }

    private function ensureBelongsToProduct(WearProduct $product, WearProductVariant $variant): void
    {
        abort_unless((int) $variant->wear_product_id === (int) $product->id, 404);
    }
}
