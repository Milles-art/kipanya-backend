<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administration\StoreSetting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeSettings($request);

        $settings = StoreSetting::query()->get()->keyBy('key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorizeSettings($request);

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:120'],
            'store_tagline' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'currency' => ['required', 'string', 'in:TZS,USD'],
            'timezone' => ['required', 'timezone'],
        ]);

        $changed = [];

        DB::transaction(function () use ($validated, &$changed): void {
            foreach ($validated as $key => $value) {
                $setting = StoreSetting::query()->where('key', $key)->firstOrFail();
                $value = $value === null ? null : trim((string) $value);

                if ($setting->value !== $value) {
                    $changed[] = $key;
                    $setting->update(['value' => $value]);
                }
            }
        });

        if ($changed !== []) {
            $auditLogger->log($request, 'admin.settings.updated', null, [
                'keys' => $changed,
            ]);
        }

        return back()->with('success', $changed === []
            ? 'Settings are already up to date.'
            : 'Store settings saved successfully.');
    }

    private function authorizeSettings(Request $request): void
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('settings.manage'),
            403,
        );
    }
}
