<?php

namespace App\Http\Controllers\Web;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Commerce\LoyaltyAccount;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearReturnRequest;
use Illuminate\Http\Request;

final class AccountController extends Controller
{
    private const RETURN_WINDOW_DAYS = 30;

    public function dashboard(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $this->user($request);
        $orders = WearOrder::query()
            ->where('user_id', $user->id)
            ->with(['items'])
            ->latest('id')
            ->limit(5)
            ->get();

        return view('account.dashboard', [
            'user' => $user,
            'totalOrders' => WearOrder::query()->where('user_id', $user->id)->count(),
            'transitOrders' => WearOrder::query()->where('user_id', $user->id)->whereIn('status', [
                OrderStatus::Shipped->value,
            ])->count(),
            'deliveredOrders' => WearOrder::query()->where('user_id', $user->id)->where('status', OrderStatus::Delivered->value)->count(),
            'wishlistCount' => $user->wearWishlist()->count(),
            'addressCount' => $user->addresses()->count(),
            'recentOrders' => $orders,
            'defaultAddress' => $user->addresses()->where('is_default', true)->first() ?? $user->addresses()->latest('id')->first(),
            'defaultPayment' => $user->paymentMethods()->orderByDesc('is_default')->latest('id')->first(),
            'loyalty' => LoyaltyAccount::query()->firstOrCreate(['user_id' => $user->id]),
        ]);
    }

    public function orders(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $this->user($request);
        $orders = WearOrder::query()
            ->where('user_id', $user->id)
            ->with(['items'])
            ->latest('id')
            ->get();

        return view('account.orders', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }

    public function orderDetail(Request $request, string $orderId): \Illuminate\Contracts\View\View
    {
        $user = $this->user($request);
        $order = WearOrder::query()
            ->where('user_id', $user->id)
            ->with(['items', 'payments', 'statusHistory'])
            ->where(fn ($q) => $q->where('order_number', $orderId)->orWhere('id', (int) $orderId))
            ->latest('id')
            ->firstOrFail();

        return view('account.order-detail', ['user' => $user, 'order' => $order]);
    }

    public function profile(Request $request): \Illuminate\Contracts\View\View
    {
        return view('account.profile', ['user' => $this->user($request)]);
    }

    public function security(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $this->user($request);
        $currentId = $user->currentAccessToken()?->id;

        $sessions = $user->tokens()
            ->orderByDesc('last_used_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'device' => $token->name,
                'is_current' => $currentId !== null && (int) $token->id === (int) $currentId,
                'ip_address' => null,
                'last_active' => $token->last_used_at ?? $token->created_at,
            ])
            ->values();

        return view('account.security', ['user' => $user, 'sessions' => $sessions]);
    }

    public function paymentMethods(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $this->user($request);

        return view('account.payment-methods', [
            'user' => $user,
            'paymentMethods' => $user->paymentMethods()->orderByDesc('is_default')->latest('id')->get(),
        ]);
    }

    public function notifications(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $this->user($request);
        $preferences = $user->notificationPreferences()->firstOrCreate([]);

        return view('account.notifications', ['user' => $user, 'preferences' => $preferences]);
    }

    public function returns(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $this->user($request);

        $eligibleOrders = WearOrder::query()
            ->where('user_id', $user->id)
            ->where('status', OrderStatus::Delivered->value)
            ->where('payment_status', '!=', PaymentStatus::Refunded->value)
            ->where('delivered_at', '>=', now()->subDays(self::RETURN_WINDOW_DAYS))
            ->with(['items'])
            ->latest('id')
            ->get();

        $returnRequests = WearReturnRequest::query()
            ->where('user_id', $user->id)
            ->with(['order.items'])
            ->latest()
            ->get();

        return view('account.returns', [
            'user' => $user,
            'eligibleOrders' => $eligibleOrders,
            'returnRequests' => $returnRequests,
        ]);
    }

    public function loyalty(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $this->user($request);
        $account = LoyaltyAccount::query()->firstOrCreate(['user_id' => $user->id]);
        $account->load(['transactions' => fn ($q) => $q->latest('id')->limit(50)]);

        return view('account.loyalty', ['user' => $user, 'loyalty' => $account]);
    }

    public function sizeProfile(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $this->user($request);
        $profile = $user->profile()->firstOrCreate([]);
        $sizeProfile = is_array($profile->size_profile) ? $profile->size_profile : [];

        return view('account.size-profile', ['user' => $user, 'sizeProfile' => $sizeProfile]);
    }

    public function addresses(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $this->user($request);

        return view('account.addresses', [
            'user' => $user,
            'addresses' => $user->addresses()->orderByDesc('is_default')->latest('id')->get(),
        ]);
    }

    private function user(Request $request): User
    {
        return $request->user() ?? auth('sanctum')->user();
    }
}