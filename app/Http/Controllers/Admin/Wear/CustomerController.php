<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
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

    /**
     * Anti-farm report: every browser (kp_device cookie) that created more
     * than one account. Two accounts sharing a device_hash were provably
     * created on the same browser; signup_ip shows whether they also came
     * from the same connection. Unlike index(), zero-order accounts appear
     * here too — that is exactly where farm accounts sit.
     */
    public function duplicates(Request $request): View
    {
        $this->authorizeCustomers($request);

        $orderStats = WearOrder::query()
            ->select([
                'user_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('COALESCE(SUM(total), 0) as total_spent'),
            ])
            ->whereNotNull('user_id')
            ->groupBy('user_id');

        // Device fingerprints that back more than one account.
        $sharedDevices = User::query()
            ->select('device_hash')
            ->whereNotNull('device_hash')
            ->groupBy('device_hash')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('device_hash');

        $groups = User::query()
            ->joinSub($orderStats, 'wear_duplicate_stats', 'wear_duplicate_stats.user_id', '=', 'users.id', 'left')
            ->select([
                'users.id',
                'users.name',
                'users.phone',
                'users.email',
                'users.status',
                'users.device_hash',
                'users.signup_ip',
                'users.created_at',
                'wear_duplicate_stats.orders_count',
                'wear_duplicate_stats.total_spent',
            ])
            ->whereIn('users.device_hash', $sharedDevices)
            ->orderBy('users.device_hash')
            ->orderByDesc('users.created_at')
            ->get()
            ->groupBy('device_hash');

        return view('admin.wear.customers.duplicates', [
            'groups' => $groups,
            'groupCount' => $groups->count(),
            'accountCount' => $groups->flatten()->count(),
        ]);
    }

    /**
     * Support path for accounts that cannot self-serve a password: rows
     * created in the SMS-code era have no password and (often) no email, so
     * "Forgot password" cannot reach them. Mirroring the administrator rule,
     * only a super administrator may set a sign-in password — it is a
     * credential, and this endpoint would otherwise allow impersonation of
     * any customer. Every existing session is revoked afterwards.
     */
    public function setPassword(Request $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorizeCustomers($request);

        abort_unless($request->user()?->hasRole('super_admin'), 403, 'Only a super administrator can set a customer password.');

        $data = $request->validate([
            'password' => ['required', 'string', 'min:10', 'max:200', 'confirmed'],
        ]);

        // The 'hashed' cast hashes the plaintext on save.
        $user->forceFill(['password' => $data['password']])->save();

        // Any live sessions (including a hijacker's) are signed out; the
        // customer then signs in with the new password.
        $user->tokens()->delete();

        $auditLogger->log($request, 'admin.customer_password_set', $user, [
            'user_id' => $user->id,
        ]);

        return back()->with('success', "Password set for {$user->name}. Their existing sessions were signed out.");
    }

    private function authorizeCustomers(Request $request): void
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('commerce.manage'),
            403,
        );
    }
}
