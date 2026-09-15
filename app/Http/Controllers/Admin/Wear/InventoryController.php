<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Enums\Commerce\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Commerce\StockReservationItem;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('commerce.manage'), 403);

        $reserved = StockReservationItem::query()
            ->join('wear_stock_reservations', 'wear_stock_reservation_items.reservation_id', '=', 'wear_stock_reservations.id')
            ->where('wear_stock_reservations.status', ReservationStatus::Active->value)
            ->where('wear_stock_reservations.expires_at', '>', now())
            ->groupBy('wear_stock_reservation_items.wear_product_variant_id')
            ->select('wear_stock_reservation_items.wear_product_variant_id')
            ->selectRaw('SUM(wear_stock_reservation_items.quantity) as reserved_quantity');

        $availableExpression = 'wear_product_variants.stock - COALESCE(reserved_stock.reserved_quantity, 0)';

        $variants = WearProductVariant::query()
            ->join('wear_products', 'wear_product_variants.wear_product_id', '=', 'wear_products.id')
            ->leftJoinSub($reserved, 'reserved_stock', function ($join): void {
                $join->on('reserved_stock.wear_product_variant_id', '=', 'wear_product_variants.id');
            })
            ->select('wear_product_variants.*')
            ->selectRaw('COALESCE(reserved_stock.reserved_quantity, 0) as reserved_quantity')
            ->selectRaw("{$availableExpression} as available_quantity")
            ->with('product:id,name,category,image_path,is_active')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = trim((string) $request->string('q'));
                $query->where(function ($query) use ($term): void {
                    $query->where('wear_products.name', 'like', "%{$term}%")
                        ->orWhere('wear_product_variants.sku', 'like', "%{$term}%")
                        ->orWhere('wear_product_variants.size', 'like', "%{$term}%")
                        ->orWhere('wear_product_variants.color', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('wear_products.category', $request->string('category')))
            ->when($request->filled('status'), function ($query) use ($request, $availableExpression): void {
                match ((string) $request->string('status')) {
                    'out' => $query->whereRaw("{$availableExpression} <= 0"),
                    'low' => $query->whereRaw("{$availableExpression} > 0 AND {$availableExpression} <= 5"),
                    'in' => $query->whereRaw("{$availableExpression} > 5"),
                    default => null,
                };
            })
            ->orderBy('wear_products.name')
            ->orderBy('wear_product_variants.size')
            ->orderBy('wear_product_variants.color')
            ->paginate(25)
            ->withQueryString();

        return view('admin.wear.inventory.index', [
            'variants' => $variants,
            'categories' => WearProduct::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function updateStock(Request $request, WearProductVariant $variant): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('commerce.manage'), 403);

        $validated = $request->validate([
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $variant, $validated): void {
            $locked = WearProductVariant::query()->lockForUpdate()->findOrFail($variant->id);

            $reserved = (int) StockReservationItem::query()
                ->where('wear_product_variant_id', $locked->id)
                ->whereHas('reservation', function ($query): void {
                    $query->where('status', ReservationStatus::Active->value)
                        ->where('expires_at', '>', now());
                })
                ->sum('quantity');

            $newStock = (int) $validated['stock'];

            if ($newStock < $reserved) {
                abort(422, "Stock cannot be set below the {$reserved} unit(s) currently reserved for active orders.");
            }

            $before = (int) $locked->stock;
            $locked->update(['stock' => $newStock]);

            app(\App\Support\AuditLogger::class)->log(
                $request,
                'admin.wear.inventory.stock_updated',
                $locked,
                [
                    'product_id' => $locked->wear_product_id,
                    'sku' => $locked->sku,
                    'before_stock' => $before,
                    'after_stock' => $newStock,
                    'reserved_stock' => $reserved,
                    'reason' => $validated['reason'] ?? null,
                ],
            );
        });

        return back()->with('success', 'Stock updated successfully.');
    }
}
