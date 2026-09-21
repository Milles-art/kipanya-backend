<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Auth\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\ValidTanzanianPhoneNumber;
use App\Services\Auth\OtpService;
use App\Services\Auth\TotpService;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class AuthController extends Controller
{
    private const PENDING_2FA_SESSION_KEY = 'admin.two_factor.pending';

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function requestOtp(Request $request, OtpService $otpService): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30', new ValidTanzanianPhoneNumber],
        ]);

        $phone = PhoneNumber::normalize($data['phone'])->value();
        $key = 'admin-login-otp:'.sha1($phone);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['phone' => ['Too many requests. Please try again later.']]);
        }

        $adminExists = User::query()->where('phone', $phone)->where('status', 'active')->get()
            ->contains(fn (User $user) => $user->isAdmin());

        RateLimiter::hit($key, 3600);

        if ($adminExists) {
            $otpService->send($phone, OtpPurpose::AdminLogin);
        }

        return back()->with('otp_sent', true)->with('phone', $request->string('phone')->toString());
    }

    public function login(Request $request, OtpService $otpService): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30', new ValidTanzanianPhoneNumber],
            'code' => ['required', 'digits:6'],
        ]);

        $phone = PhoneNumber::normalize($data['phone'])->value();
        $otpService->verify($phone, OtpPurpose::AdminLogin, $data['code']);

        $user = User::query()->where('phone', $phone)->where('status', 'active')->first();

        if (! $user || ! $user->isAdmin()) {
            throw ValidationException::withMessages(['phone' => ['Administrator access required.']]);
        }

        if ($user->twoFactorEnabled()) {
            $request->session()->put(self::PENDING_2FA_SESSION_KEY, [
                'user_id' => $user->id,
            ]);

            return back()->with('two_factor_required', true);
        }

        $this->signIn($user, $request);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function confirmTwoFactor(Request $request, TotpService $totpService): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $pending = $request->session()->get(self::PENDING_2FA_SESSION_KEY);

        if (! is_array($pending) || empty($pending['user_id'])) {
            throw ValidationException::withMessages(['code' => ['Your sign-in session has expired. Please start again.']]);
        }

        $user = User::query()->find($pending['user_id']);

        if (! $user || ! $user->isAdmin() || ! $user->twoFactorEnabled()) {
            $request->session()->forget(self::PENDING_2FA_SESSION_KEY);

            throw ValidationException::withMessages(['code' => ['Your sign-in session has expired. Please start again.']]);
        }

        $key = 'admin-2fa-login:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['code' => ['Too many incorrect attempts. Please try again later.']]);
        }

        if (! $totpService->verifyAndConsume((string) $user->two_factor_secret, $data['code'])) {
            RateLimiter::hit($key, 600);

            throw ValidationException::withMessages(['code' => ['The two-factor code is incorrect.']]);
        }

        RateLimiter::clear($key);
        $request->session()->forget(self::PENDING_2FA_SESSION_KEY);

        $this->signIn($user, $request);

        return redirect()->intended(route('admin.dashboard'));
    }

    private function signIn(User $user, Request $request): void
    {
        Auth::login($user);
        $request->session()->regenerate();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
