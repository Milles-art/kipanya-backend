<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Enums\Commerce\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('analytics.view'),
            403,
        );

        $period = (int) $request->query('period', 30);
        $period = in_array($period, [7, 30, 90], true) ? $period : 30;
        $start = today()->subDays($period - 1)->startOfDay();
        $end = now()->endOfDay();

        $paidOrders = WearOrder::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->whereBetween('placed_at', [$start, $end]);

        $revenue = (float) (clone $paidOrders)->sum('total');
        $orders = (clone $paidOrders)->count();
        $itemsSold = (int) WearOrderItem::query()
            ->whereHas('order', fn ($query) => $query
                ->where('payment_status', PaymentStatus::Paid->value)
                ->whereBetween('placed_at', [$start, $end]))
            ->sum('quantity');

        $dailySales = collect(range($period - 1, 0))->map(function (int $daysAgo) use ($start, $period): array {
            $date = $start->copy()->addDays($period - 1 - $daysAgo)->startOfDay();

            return [
                'date' => $date->toDateString(),
                'label' => $period <= 30 ? $date->format('d M') : $date->format('d M'),
                'revenue' => round((float) WearOrder::query()
                    ->where('payment_status', PaymentStatus::Paid->value)
                    ->whereDate('placed_at', $date)
                    ->sum('total'), 2),
            ];
        })->values();

        $topProducts = WearOrderItem::query()
            ->select([
                'wear_product_id',
                'product_name',
                DB::raw('SUM(quantity) as units_sold'),
                DB::raw('SUM(line_total) as revenue'),
            ])
            ->whereHas('order', fn ($query) => $query
                ->where('payment_status', PaymentStatus::Paid->value)
                ->whereBetween('placed_at', [$start, $end]))
            ->groupBy('wear_product_id', 'product_name')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        $categorySales = WearOrderItem::query()
            ->join('wear_products', 'wear_products.id', '=', 'wear_order_items.wear_product_id')
            ->join('wear_orders', 'wear_orders.id', '=', 'wear_order_items.wear_order_id')
            ->select([
                'wear_products.category',
                DB::raw('SUM(wear_order_items.quantity) as units_sold'),
                DB::raw('SUM(wear_order_items.line_total) as revenue'),
            ])
            ->where('wear_orders.payment_status', PaymentStatus::Paid->value)
            ->whereBetween('wear_orders.placed_at', [$start, $end])
            ->groupBy('wear_products.category')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        return view('admin.wear.analytics.index', [
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'metrics' => [
                'revenue' => $revenue,
                'orders' => $orders,
                'items_sold' => $itemsSold,
                'average_order_value' => $orders > 0 ? $revenue / $orders : 0,
            ],
            'dailySales' => $dailySales,
            'topProducts' => $topProducts,
            'categorySales' => $categorySales,
        ]);
    }
}
