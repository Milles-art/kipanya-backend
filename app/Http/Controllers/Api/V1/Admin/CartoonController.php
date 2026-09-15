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
use App\Support\AuditLogger;

final class CartoonController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

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
        $this->auditLogger->log($request, 'admin.cartoon.created', $cartoon);
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
        $this->auditLogger->log($request, 'admin.cartoon.updated', $cartoon);
        return response()->json(['data' => $cartoon->fresh()->load('category')]);
    }

    public function destroy(Request $request, Cartoon $cartoon): JsonResponse
    {
        DB::transaction(fn () => $cartoon->delete());
        $this->auditLogger->log($request, 'admin.cartoon.deleted', $cartoon, ['cartoon_id' => $cartoon->getKey()]);
        return response()->json(['message' => 'Cartoon deleted.']);
    }

    public function publish(Request $request, Cartoon $cartoon): JsonResponse
    {
        $cartoon->update(['status' => ContentStatus::Published, 'published_at' => $cartoon->published_at ?? now()]);
        $this->auditLogger->log($request, 'admin.cartoon.published', $cartoon);
        return response()->json(['data' => $cartoon->fresh()]);
    }

    public function archive(Request $request, Cartoon $cartoon): JsonResponse
    {
        $cartoon->update(['status' => ContentStatus::Archived]);
        $this->auditLogger->log($request, 'admin.cartoon.archived', $cartoon);
        return response()->json(['data' => $cartoon->fresh()]);
    }

    public function feature(Request $request, Cartoon $cartoon): JsonResponse
    {
        $cartoon->update(['is_featured' => true]);
        $this->auditLogger->log($request, 'admin.cartoon.featured', $cartoon);
        return response()->json(['data' => $cartoon->fresh()]);
    }

    public function unfeature(Request $request, Cartoon $cartoon): JsonResponse
    {
        $cartoon->update(['is_featured' => false]);
        $this->auditLogger->log($request, 'admin.cartoon.unfeatured', $cartoon);
        return response()->json(['data' => $cartoon->fresh()]);
    }
}
