<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Auth\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function requestOtp(Request $request, OtpService $otpService): RedirectResponse
    {
        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();
        $key = 'admin-login-otp:' . sha1($phone);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['phone' => ['Too many requests. Please try again later.']]);
        }

        $adminExists = User::query()->where('phone', $phone)->where('status', 'active')->get()
            ->contains(fn (User $user) => $user->isAdmin());

        RateLimiter::hit($key, 3600);

        if ($adminExists) {
            $otpService->send($phone, OtpPurpose::Login);
        }

        return back()->with('otp_sent', true)->with('phone', $request->string('phone')->toString());
    }

    public function login(Request $request, OtpService $otpService): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'digits:6'],
        ]);

        $phone = PhoneNumber::normalize($data['phone'])->value();
        $otpService->verify($phone, OtpPurpose::Login, $data['code']);

        $user = User::query()->where('phone', $phone)->where('status', 'active')->first();

        if (! $user || ! $user->isAdmin()) {
            throw ValidationException::withMessages(['phone' => ['Administrator access required.']]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
