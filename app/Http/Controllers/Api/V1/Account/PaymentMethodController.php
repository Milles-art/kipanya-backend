<?php

namespace App\Http\Controllers\Api\V1\Account;

use App\Http\Controllers\Api\V1\Concerns\AuthorizesUserOwnership;
use App\Http\Controllers\Controller;
use App\Models\Commerce\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymentMethodController extends Controller
{
    use AuthorizesUserOwnership;
    public function index(Request $request): JsonResponse
    {
        $methods = $request->user()
            ->paymentMethods()
            ->orderByDesc('is_default')
            ->latest('id')
            ->get();

        return response()->json([
            'data' => $methods->map(fn (PaymentMethod $m) => [
                'id' => $m->id,
                'brand' => $m->brand,
                'last4' => $m->last4,
                'exp_month' => $m->exp_month,
                'exp_year' => $m->exp_year,
                'is_default' => (bool) $m->is_default,
            ]),
        ]);
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $this->assertOwnedBy($paymentMethod);
        $paymentMethod->delete();

        return response()->json(['data' => ['deleted' => true]]);
    }

    public function setDefault(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $this->assertOwnedBy($paymentMethod);

        $request->user()->paymentMethods()->where('is_default', true)->update(['is_default' => false]);

        $paymentMethod->update(['is_default' => true]);

        return response()->json([
            'data' => [
                'id' => $paymentMethod->id,
                'is_default' => true,
            ],
        ]);
    }
}