<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ControlPanelController extends Controller
{
    public function dashboard(Request $request): View
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('admin.dashboard.view'),
            403,
        );

        $paidOrders = WearOrder::query()->where('payment_status', PaymentStatus::Paid->value);

        $revenue = fn ($query) => (float) $query->sum('total');

        $todayRevenue = $revenue((clone $paidOrders)->whereDate('placed_at', today()));
        $weekRevenue = $revenue((clone $paidOrders)->whereBetween('placed_at', [now()->startOfWeek(), now()->endOfWeek()]));
        $monthRevenue = $revenue((clone $paidOrders)->whereBetween('placed_at', [now()->startOfMonth(), now()->endOfMonth()]));
        $totalRevenue = $revenue($paidOrders);

        $recentOrders = WearOrder::query()
            ->with(['user:id,name,email', 'items:id,wear_order_id,product_name,quantity'])
            ->latest('placed_at')
            ->latest('id')
            ->limit(8)
            ->get();

        $lowStockVariants = WearProductVariant::query()
            ->with('product:id,name')
            ->whereBetween('stock', [1, 5])
            ->orderBy('stock')
            ->limit(8)
            ->get();

        $salesChart = collect(range(29, 0))->map(function (int $daysAgo) use ($paidOrders): array {
            $date = today()->subDays($daysAgo);

            return [
                'date' => $date->toDateString(),
                'label' => $date->format('d M'),
                'revenue' => round((float) (clone $paidOrders)->whereDate('placed_at', $date)->sum('total'), 2),
            ];
        })->values()->all();

        return view('admin.dashboard', [
            'metrics' => [
                'today_revenue' => $todayRevenue,
                'week_revenue' => $weekRevenue,
                'month_revenue' => $monthRevenue,
                'total_revenue' => $totalRevenue,
                'orders_total' => WearOrder::count(),
                'orders_paid' => WearOrder::where('payment_status', PaymentStatus::Paid->value)->count(),
                'orders_pending_payment' => WearOrder::where('status', OrderStatus::PendingPayment->value)->count(),
                'orders_processing' => WearOrder::where('status', OrderStatus::Processing->value)->count(),
                'orders_shipped' => WearOrder::where('status', OrderStatus::Shipped->value)->count(),
                'orders_delivered' => WearOrder::where('status', OrderStatus::Delivered->value)->count(),
                'products_total' => WearProduct::count(),
                'products_active' => WearProduct::where('is_active', true)->count(),
                'products_featured' => WearProduct::where('is_featured', true)->where('is_active', true)->count(),
                'variants_total' => WearProductVariant::count(),
                'stock_units_total' => (int) WearProductVariant::sum('stock'),
                'low_stock' => WearProductVariant::whereBetween('stock', [1, 5])->count(),
                'out_of_stock' => WearProductVariant::where('stock', '<=', 0)->count(),
                'customers' => WearOrder::whereNotNull('user_id')->distinct('user_id')->count('user_id'),
                'customers_30d' => WearOrder::whereNotNull('user_id')
                    ->where('placed_at', '>=', now()->subDays(30))
                    ->distinct('user_id')
                    ->count('user_id'),
            ],
            'recentOrders' => $recentOrders,
            'lowStockVariants' => $lowStockVariants,
            'salesChart' => $salesChart,
        ]);
    }

    public function module(string $module): View
    {
        abort_unless(in_array($module, ['cartoon', 'wear', 'book', 'motors', 'tv'], true), 404);

        return view('admin.modules.placeholder', [
            'module' => $module,
        ]);
    }
}
