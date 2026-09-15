<?php

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class ReturnRequestController extends Controller
{
    public function index(Request $request)
    {
        $items = WearReturnRequest::query()
            ->where('user_id', $request->user()->id)
            ->with(['order.items'])
            ->latest()
            ->get();

        return response()->json(['data' => $items->map(fn ($item) => $this->serialize($item))->values()]);
    }

    public function store(Request $request)
    {
        $data = Validator::make($request->all(), [
            'order_id' => ['required', 'integer', 'exists:wear_orders,id'],
            'request_type' => ['required', 'in:return,exchange'],
            'reason' => ['required', 'in:wrong_size,damaged,not_as_described,changed_mind,other'],
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['integer'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        $returnRequest = DB::transaction(function () use ($request, $data) {
            $order = WearOrder::query()->with('items')->lockForUpdate()->findOrFail($data['order_id']);
            abort_unless($order->user_id === $request->user()->id, 403);
            abort_unless($order->status->value === 'delivered', 422, 'Only delivered orders can be returned or exchanged.');

            $validItemIds = $order->items->pluck('id')->map(fn ($id) => (int) $id);
            $itemIds = collect($data['item_ids'])->map(fn ($id) => (int) $id)->unique()->values();
            abort_if($itemIds->diff($validItemIds)->isNotEmpty(), 422, 'One or more selected items do not belong to this order.');

            $existing = WearReturnRequest::query()
                ->where('user_id', $request->user()->id)
                ->where('wear_order_id', $order->id)
                ->whereIn('status', ['under_review', 'approved'])
                ->exists();

            if ($existing) {
                throw ValidationException::withMessages(['order_id' => 'A return or exchange request already exists for this order.']);
            }

            return WearReturnRequest::create([
                'wear_order_id' => $order->id,
                'user_id' => $request->user()->id,
                'request_type' => $data['request_type'],
                'reason' => $data['reason'],
                'order_item_ids' => $itemIds->all(),
                'notes' => $data['notes'] ?? null,
                'status' => 'under_review',
            ]);
        });

        return response()->json(['data' => $this->serialize($returnRequest->load(['order.items']))], 201);
    }

    private function serialize(WearReturnRequest $request): array
    {
        $items = collect($request->order?->items ?? [])->whereIn('id', $request->order_item_ids ?? [])->values();
        return [
            'id' => $request->id,
            'order_id' => $request->wear_order_id,
            'order_number' => $request->order?->order_number,
            'request_type' => $request->request_type,
            'reason' => $request->reason,
            'notes' => $request->notes,
            'status' => $request->status,
            'items' => $items->map(fn ($item) => [
                'id' => $item->id, 'product_name' => $item->product_name, 'size' => $item->size, 'color' => $item->color, 'quantity' => $item->quantity,
            ])->values(),
            'created_at' => $request->created_at?->toISOString(),
        ];
    }
}
