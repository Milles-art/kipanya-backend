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
            ->paginate(20);

        return response()->json([
            'data' => collect($items->items())->map(fn ($item) => $this->serialize($item))->values(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
            'links' => ['next' => $items->nextPageUrl(), 'prev' => $items->previousPageUrl()],
        ]);
    }


    public function eligibleOrders(Request $request)
    {
        $orders = WearOrder::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'delivered')
            ->latest('id')
            ->paginate(20);

        return response()->json([
            'data' => $orders->getCollection()->map(fn (WearOrder $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'created_at' => $order->created_at?->toISOString(),
            ])->values(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'links' => ['next' => $orders->nextPageUrl(), 'prev' => $orders->previousPageUrl()],
        ]);
    }

    public function store(Request $request)
    {
        $data = Validator::make($request->all(), [
            'order_id' => ['required', 'integer', 'exists:wear_orders,id'],
            'request_type' => ['required', 'in:return,exchange'],
            'reason' => ['required', 'in:wrong_size,damaged,not_as_described,changed_mind,other'],
            'item_ids' => ['required', 'array', 'min:1', 'max:50'],
            'item_ids.*' => ['integer', 'distinct', 'min:1'],
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
                ->whereIn('status', ['under_review', 'approved', 'received', 'processed', 'refunded', 'completed'])
                ->get(['order_item_ids']);

            $alreadyRequested = $existing->flatMap(fn ($r) => $r->order_item_ids ?? [])->map(fn ($id) => (int) $id)->unique();
            if ($itemIds->intersect($alreadyRequested)->isNotEmpty()) {
                throw ValidationException::withMessages(['item_ids' => 'One or more selected items already have a return or exchange request.']);
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
