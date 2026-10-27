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
            <img src="{{ asset('images/kp-wear-logo.png') }}" alt="KP Wear" style="height:72px;width:auto;max-width:100%">
        </div>

        <div class="kp-auth-heading">
            <h1 id="register-title">Create your account</h1>
            <p>Your phone number and a password — that's all it takes.</p>
        </div>

        <div class="kp-auth-switch" aria-label="Authentication">
            <a href="{{ route('login', ['redirect' => request('redirect')]) }}">Login</a>
            <span class="is-active">Register</span>
        </div>

        <form novalidate data-register-form class="kp-auth-form">
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            <div data-auth-error class="kp-form-error hidden" role="alert" aria-live="polite"></div>

            <div class="kp-auth-register-fields">
                <div>
                    <label class="kp-auth-label" for="register-name">Full name</label>
                    <input id="register-name" name="name" class="field w-full kp-auth-field" required placeholder="Your full name" autocomplete="name">
                </div>
                <div>
                    <label class="kp-auth-label" for="register-phone">Phone number</label>
                    <input id="register-phone" name="phone" type="tel" class="field w-full kp-auth-field" required placeholder="07XXXXXXXX" autocomplete="tel">
                </div>
                <div>
                    <label class="kp-auth-label" for="register-email">Email <span>(optional — lets you reset your password yourself)</span></label>
                    <input id="register-email" name="email" type="email" class="field w-full kp-auth-field" placeholder="you@example.com" autocomplete="email">
                </div>
                <div>
                    <label class="kp-auth-label" for="register-password">Password</label>
                    <input id="register-password" name="password" type="password" class="field w-full kp-auth-field" required minlength="8" placeholder="At least 8 characters" autocomplete="new-password">
                </div>
                <div>
                    <label class="kp-auth-label" for="register-password-confirmation">Confirm password</label>
                    <input id="register-password-confirmation" name="password_confirmation" type="password" class="field w-full kp-auth-field" required minlength="8" placeholder="Type your password again" autocomplete="new-password">
                </div>
                <div>
                    <label class="kp-auth-label" for="register-referral">Referral code <span>(optional)</span></label>
                    <input id="register-referral" name="referral_code" class="field w-full kp-auth-field" placeholder="Enter a code if you have one" autocomplete="off">
                </div>
            </div>

            <button class="button-dark kp-auth-submit" type="submit">
                Create account <span aria-hidden="true">→</span>
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
