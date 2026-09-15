<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\CollectionRequest;
use App\Models\Content\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use App\Support\AuditLogger;

final class CollectionController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index() { return JsonResource::collection(Collection::query()->orderBy('sort_order')->orderBy('name')->paginate(30)); }

    public function store(CollectionRequest $request): JsonResponse
    {
        $collection = Collection::create($request->validated());
        $this->auditLogger->log($request, 'admin.collection.created', $collection);
        return response()->json(['data' => $collection], 201);
    }

    public function show(Collection $collection): JsonResponse { return response()->json(['data' => $collection->load('cartoons')]); }

    public function update(CollectionRequest $request, Collection $collection): JsonResponse
    {
        $collection->update($request->validated());
        $this->auditLogger->log($request, 'admin.collection.updated', $collection);
        return response()->json(['data' => $collection->fresh()]);
    }

    public function destroy(Request $request, Collection $collection): JsonResponse
    {
        $collection->cartoons()->detach();
        $collection->delete();
        $this->auditLogger->log($request, 'admin.collection.deleted', $collection, ['collection_id' => $collection->getKey()]);
        return response()->json(['message' => 'Collection deleted.']);
    }

    public function syncCartoons(Collection $collection, Request $request): JsonResponse
    {
        $data = $request->validate(['cartoon_ids' => ['required', 'array'], 'cartoon_ids.*' => ['integer', 'exists:cartoons,id']]);
        $sync = [];
        foreach ($data['cartoon_ids'] as $index => $id) $sync[$id] = ['sort_order' => $index];
        $collection->cartoons()->sync($sync);
        $this->auditLogger->log($request, 'admin.collection.cartoons_synced', $collection, ['cartoon_ids' => array_values($data['cartoon_ids'])]);
        return response()->json(['data' => $collection->fresh()->load('cartoons')]);
    }
}
