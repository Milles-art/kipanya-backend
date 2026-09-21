<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wear\WearOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('commerce.manage'),
            403,
        );

        $search = trim((string) $request->string('search'));

        $orderStats = WearOrder::query()
            ->select([
                'user_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('COALESCE(SUM(total), 0) as total_spent'),
                DB::raw('MAX(placed_at) as last_order_at'),
            ])
            ->whereNotNull('user_id')
            ->groupBy('user_id');

        $customers = User::query()
            ->joinSub($orderStats, 'wear_customer_stats', 'wear_customer_stats.user_id', '=', 'users.id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                'users.status',
                'wear_customer_stats.orders_count',
                'wear_customer_stats.total_spent',
                'wear_customer_stats.last_order_at',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%")
                        ->orWhere('users.phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('wear_customer_stats.last_order_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.wear.customers.index', compact('customers', 'search'));
    }
}
