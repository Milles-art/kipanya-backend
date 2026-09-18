<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auth\TotpService;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class TwoFactorController extends Controller
{
    public function index(Request $request, TotpService $totpService): View
    {
        $user = $request->user();

        if (! $user->twoFactorEnabled() && empty($user->two_factor_secret)) {
            $user->two_factor_secret = $totpService->generateSecret();
            $user->save();
        }

        $uri = $user->two_factor_secret
            ? $totpService->provisioningUri($user->two_factor_secret, config('app.name', 'Kipanya'), (string) $user->phone)
            : null;

        return view('admin.security.two-factor', [
            'enabled' => $user->twoFactorEnabled(),
            'secret' => $user->two_factor_secret,
            'uri' => $uri,
        ]);
    }

    public function enable(Request $request, TotpService $totpService, AuditLogger $auditLogger): RedirectResponse
    {
        $user = $request->user();

        if ($user->twoFactorEnabled()) {
            return back()->with('info', 'Two-factor authentication is already enabled.');
        }

        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if (empty($user->two_factor_secret)) {
            $user->two_factor_secret = $totpService->generateSecret();
            $user->save();
        }

        $key = 'admin-2fa-enable:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages(['code' => ['Too many attempts. Please try again later.']]);
        }

        if (! $totpService->verifyAndConsume($user->two_factor_secret, $data['code'])) {
            RateLimiter::hit($key, 900);

            throw ValidationException::withMessages(['code' => ['The code is incorrect. Try again.']]);
        }

        $user->enableTwoFactor();
        RateLimiter::clear($key);

        $auditLogger->log($request, 'admin.security.two_factor.enabled', $user);

        return back()->with('success', 'Two-factor authentication is now enabled.');
    }

    public function disable(Request $request, TotpService $totpService, AuditLogger $auditLogger): RedirectResponse
    {
        $user = $request->user();

        if (! $user->twoFactorEnabled()) {
            return back()->with('info', 'Two-factor authentication is already disabled.');
        }

        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $key = 'admin-2fa-disable:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages(['code' => ['Too many attempts. Please try again later.']]);
        }

        if (! $totpService->verifyAndConsume((string) $user->two_factor_secret, $data['code'])) {
            RateLimiter::hit($key, 900);

            throw ValidationException::withMessages(['code' => ['The code is incorrect.']]);
        }

        $user->disableTwoFactor();
        RateLimiter::clear($key);

        $auditLogger->log($request, 'admin.security.two_factor.disabled', $user);

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }
}
