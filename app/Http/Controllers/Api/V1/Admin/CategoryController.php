<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\CategoryRequest;
use App\Models\Content\Category;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

final class CategoryController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        return JsonResource::collection(Category::query()->orderBy('sort_order')->orderBy('name')->paginate(30));
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());
        $this->auditLogger->log($request, 'admin.category.created', $category);
        return response()->json(['data' => $category], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json(['data' => $category]);
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());
        $this->auditLogger->log($request, 'admin.category.updated', $category);
        return response()->json(['data' => $category->fresh()]);
    }

    public function destroy(\Illuminate\Http\Request $request, Category $category): JsonResponse
    {
        if ($category->cartoons()->exists()) {
            return response()->json(['message' => 'Cannot delete a category that contains cartoons.'], 409);
        }
        $category->delete();
        $this->auditLogger->log($request, 'admin.category.deleted', $category, ['category_id' => $category->getKey()]);
        return response()->json(['message' => 'Category deleted.']);
    }
}
