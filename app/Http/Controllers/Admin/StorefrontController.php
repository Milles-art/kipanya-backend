<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administration\StorefrontSetting;
use App\Models\Wear\WearCollection;
use App\Models\Wear\WearProduct;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeStorefront($request);

        return view('admin.storefront.index', [
            'settings' => StorefrontSetting::query()->get()->keyBy('key'),
            'products' => WearProduct::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'collections' => WearCollection::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'featuredProductIds' => StorefrontSetting::json('homepage_featured_product_ids'),
            'featuredCollectionIds' => StorefrontSetting::json('homepage_featured_collection_ids'),
        ]);
    }

    public function update(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorizeStorefront($request);

        $validated = $request->validate([
            'hero_eyebrow' => ['required', 'string', 'max:80'],
            'hero_title' => ['required', 'string', 'max:160'],
            'hero_description' => ['nullable', 'string', 'max:500'],
            'hero_cta_label' => ['required', 'string', 'max:60'],
            'hero_cta_url' => ['required', 'string', 'max:255', function (string $attribute, mixed $value, $fail): void {
                $value = trim((string) $value);
                if ($value === '' || str_starts_with($value, '//')) { $fail('The CTA URL is invalid.'); return; }
                if (str_starts_with($value, '/')) return;
                $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
                if (! in_array($scheme, ['http', 'https'], true) || ! filter_var($value, FILTER_VALIDATE_URL)) {
                    $fail('The CTA URL must be an internal path or a valid HTTP(S) URL.');
                }
            }],
            'hero_image_desktop' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'hero_image_mobile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'hero_is_active' => ['nullable', 'boolean'],
            'homepage_featured_product_ids' => ['nullable', 'array', 'max:12'],
            'homepage_featured_product_ids.*' => ['integer', 'distinct', 'exists:wear_products,id'],
            'homepage_featured_collection_ids' => ['nullable', 'array', 'max:8'],
            'homepage_featured_collection_ids.*' => ['integer', 'distinct', 'exists:wear_collections,id'],
            'shop_banner_title' => ['nullable', 'string', 'max:160'],
            'shop_banner_description' => ['nullable', 'string', 'max:500'],
            'shop_banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'shop_banner_is_active' => ['nullable', 'boolean'],
            'shop_default_sort' => ['required', 'in:featured,newest,price-asc,price-desc'],
        ]);

        $changed = [];
        DB::transaction(function () use ($validated, $request, &$changed): void {
            $values = [
                'hero_eyebrow' => $validated['hero_eyebrow'],
                'hero_title' => $validated['hero_title'],
                'hero_description' => $validated['hero_description'] ?? null,
                'hero_cta_label' => $validated['hero_cta_label'],
                'hero_cta_url' => $validated['hero_cta_url'],
                'hero_is_active' => $request->boolean('hero_is_active') ? '1' : '0',
                'homepage_featured_product_ids' => json_encode(array_values($validated['homepage_featured_product_ids'] ?? []), JSON_THROW_ON_ERROR),
                'homepage_featured_collection_ids' => json_encode(array_values($validated['homepage_featured_collection_ids'] ?? []), JSON_THROW_ON_ERROR),
                'shop_banner_title' => $validated['shop_banner_title'] ?? null,
                'shop_banner_description' => $validated['shop_banner_description'] ?? null,
                'shop_banner_is_active' => $request->boolean('shop_banner_is_active') ? '1' : '0',
                'shop_default_sort' => $validated['shop_default_sort'],
            ];

            foreach ($values as $key => $value) {
                $setting = StorefrontSetting::query()->where('key', $key)->firstOrFail();
                $value = $value === null ? null : trim((string) $value);
                if ($setting->value !== $value) {
                    $changed[] = $key;
                    $setting->update(['value' => $value]);
                }
            }

            foreach (['hero_image_desktop', 'hero_image_mobile', 'shop_banner_image'] as $field) {
                if (! $request->hasFile($field)) {
                    continue;
                }

                $setting = StorefrontSetting::query()->where('key', $field)->firstOrFail();
                $old = $setting->value;
                $path = $request->file($field)->store('storefront', 'public');
                $value = '/storage/'.$path;
                $setting->update(['value' => $value]);
                $changed[] = $field;

                if ($old && str_starts_with($old, '/storage/')) {
                    Storage::disk('public')->delete(ltrim(substr($old, 9), '/'));
                }
            }
        });

        if ($changed !== []) {
            $auditLogger->log($request, 'admin.storefront.updated', null, ['keys' => array_values(array_unique($changed))]);
        }

        return back()->with('success', $changed === [] ? 'Storefront is already up to date.' : 'Storefront settings saved successfully.');
    }

    private function authorizeStorefront(Request $request): void
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('settings.manage'),
            403,
        );
    }
}
