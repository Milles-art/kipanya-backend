@extends('layouts.app')
@section('content')
<div class="kp-auth-page" style="--kp-auth-bg: url('{{ asset('images/kp-wear-auth-bg.png') }}');">
    <div class="kp-auth-backdrop" aria-hidden="true"></div>
    <div class="kp-auth-content">
        <section class="kp-auth-card kp-auth-card-main kp-auth-animate" aria-labelledby="register-title">
        <div class="kp-auth-topbar">
            <a href="{{ route('home') }}" class="kp-auth-back">
                <x-tabler-arrow-left size="18" aria-hidden="true" />
                <span>Back to store</span>
            </a>
        </div>

        <div class="kp-auth-card-brand" aria-label="KP Wear">
            <span>KP WEAR</span>
            <small>STYLE MOVES YOU</small>
        </div>

        <div class="kp-auth-heading">
            <h1 id="register-title">Create your account</h1>
            <p>Use your phone number to register.</p>
        </div>

        <div class="kp-auth-switch" aria-label="Authentication">
            <a href="{{ route('login', ['redirect' => request('redirect')]) }}">Login</a>
            <span class="is-active">Register</span>
        </div>

        <form novalidate data-register-form class="kp-auth-form">
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            <div data-auth-error class="kp-form-error hidden" role="alert" aria-live="polite"></div>

            <div data-primary-step class="kp-auth-register-fields">
                <div>
                    <label class="kp-auth-label" for="register-name">Full name</label>
                    <input id="register-name" name="name" class="field w-full kp-auth-field" required placeholder="Your full name" autocomplete="name">
                </div>
                <div>
                    <label class="kp-auth-label" for="register-phone">Phone number</label>
                    <input id="register-phone" name="phone" type="tel" class="field w-full kp-auth-field" required placeholder="07XXXXXXXX" autocomplete="tel">
                </div>
                <div>
                    <label class="kp-auth-label" for="register-referral">Referral code <span>(optional)</span></label>
                    <input id="register-referral" name="referral_code" class="field w-full kp-auth-field" placeholder="Enter a code if you have one" autocomplete="off">
                </div>
            </div>

            <div data-otp-step class="hidden">
                <label class="kp-auth-label" for="register-code">Verification code</label>
                <input id="register-code" name="code" class="field w-full kp-auth-field kp-auth-otp" maxlength="6" inputmode="numeric" placeholder="000000" autocomplete="one-time-code">
                <p class="kp-auth-help">We sent a 6-digit code to your phone.</p>
            </div>

            <button class="button-dark kp-auth-submit" type="submit">
                Continue <span aria-hidden="true">→</span>
            </button>

            <p class="kp-auth-switch-text">
                Already have an account?
                <a href="{{ route('login', ['redirect' => request('redirect')]) }}">Sign in</a>
            </p>
        </form>
        </section>
    </div>
</div>
@endsection
