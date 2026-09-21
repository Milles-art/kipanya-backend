<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Http\Controllers\Controller;
use App\Models\Wear\WearProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CategoryController extends Controller
{
    private const CATEGORIES = [
        'Hoodies',
        'Long Sleeves',
        'T-Shirts',
        'Shirts',
        'Polos',
    ];

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('commerce.manage'),
            403,
        );

        $counts = WearProduct::query()
            ->selectRaw('category, COUNT(*) as total')
            ->whereIn('category', self::CATEGORIES)
            ->groupBy('category')
            ->pluck('total', 'category');

        $activeCounts = WearProduct::query()
            ->selectRaw('category, COUNT(*) as total')
            ->whereIn('category', self::CATEGORIES)
            ->where('is_active', true)
            ->groupBy('category')
            ->pluck('total', 'category');

        $categories = collect(self::CATEGORIES)->map(fn (string $name): array => [
            'name' => $name,
            'total' => (int) ($counts[$name] ?? 0),
            'active' => (int) ($activeCounts[$name] ?? 0),
        ]);

        return view('admin.wear.categories.index', [
            'categories' => $categories,
        ]);
    }
}
