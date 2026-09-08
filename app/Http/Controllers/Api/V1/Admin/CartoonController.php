<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\Content\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\CartoonRequest;
use App\Models\Content\Cartoon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

final class CartoonController extends Controller
{
    public function index(Request $request)
    {
        $query = Cartoon::query()->with('category')->latest('id');
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        return JsonResource::collection($query->paginate(30));
    }

    public function store(CartoonRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (($data['status'] ?? ContentStatus::Draft->value) === ContentStatus::Published->value) {
            $data['published_at'] ??= now();
        }
        $cartoon = Cartoon::create($data);
        return response()->json(['data' => $cartoon->load('category')], 201);
    }

    public function show(Cartoon $cartoon): JsonResponse
    {
        return response()->json(['data' => $cartoon->load(['category', 'episodes', 'collections'])]);
    }

    public function update(CartoonRequest $request, Cartoon $cartoon): JsonResponse
    {
        $data = $request->validated();
        if (($data['status'] ?? $cartoon->status->value) === ContentStatus::Published->value && ! $cartoon->published_at) {
            $data['published_at'] = now();
        }
        $cartoon->update($data);
        return response()->json(['data' => $cartoon->fresh()->load('category')]);
    }

    public function destroy(Cartoon $cartoon): JsonResponse
    {
        DB::transaction(fn () => $cartoon->delete());
        return response()->json(['message' => 'Cartoon deleted.']);
    }

    public function publish(Cartoon $cartoon): JsonResponse
    {
        $cartoon->update(['status' => ContentStatus::Published, 'published_at' => $cartoon->published_at ?? now()]);
        return response()->json(['data' => $cartoon->fresh()]);
    }

    public function archive(Cartoon $cartoon): JsonResponse
    {
        $cartoon->update(['status' => ContentStatus::Archived]);
        return response()->json(['data' => $cartoon->fresh()]);
    }

    public function feature(Cartoon $cartoon): JsonResponse
    {
        $cartoon->update(['is_featured' => true]);
        return response()->json(['data' => $cartoon->fresh()]);
    }

    public function unfeature(Cartoon $cartoon): JsonResponse
    {
        $cartoon->update(['is_featured' => false]);
        return response()->json(['data' => $cartoon->fresh()]);
    }
}
