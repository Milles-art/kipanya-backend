@extends('layouts.app')
@section('content')
<div class="kp-auth-page" style="--kp-auth-bg: url('{{ asset('images/kp-wear-auth-bg.png') }}');">
    <div class="kp-auth-backdrop" aria-hidden="true"></div>
    <div class="kp-auth-content">
        <section class="kp-auth-card kp-auth-card-main kp-auth-animate" aria-labelledby="login-title">
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
            <h1 id="login-title">Welcome back</h1>
            <p>Sign in with your phone number and password.</p>
        </div>

        <div class="kp-auth-switch" aria-label="Authentication">
            <span class="is-active">Login</span>
            <a href="{{ route('register', ['redirect' => request('redirect')]) }}">Register</a>
        </div>

        <form novalidate data-login-form class="kp-auth-form">
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            <div data-auth-error class="kp-form-error hidden" role="alert" aria-live="polite"></div>

            <div class="kp-auth-register-fields">
                <div>
                    <label class="kp-auth-label" for="login-phone">Phone number</label>
                    <input id="login-phone" name="phone" type="tel" class="field w-full kp-auth-field" required placeholder="07XXXXXXXX" autocomplete="tel">
                </div>
                <div>
                    <label class="kp-auth-label" for="login-password">Password</label>
                    <input id="login-password" name="password" type="password" class="field w-full kp-auth-field" required placeholder="Your password" autocomplete="current-password">
                </div>
            </div>

            <p class="kp-auth-help" style="margin-top:-4px">
                <a href="{{ route('password.forgot', ['redirect' => request('redirect')]) }}">Forgot your password?</a>
            </p>

            <button class="button-dark kp-auth-submit" type="submit">
                Sign in <span aria-hidden="true">→</span>
            </button>

            <p class="kp-auth-switch-text">
                New here?
                <a href="{{ route('register', ['redirect' => request('redirect')]) }}">Create an account</a>
            </p>
        </form>
        </section>
    </div>
</div>
@endsection
