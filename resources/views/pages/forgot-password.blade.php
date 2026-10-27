@extends('layouts.app')
@section('content')
<div class="kp-auth-page" style="--kp-auth-bg: url('{{ asset('images/kp-wear-auth-bg.png') }}');">
    <div class="kp-auth-backdrop" aria-hidden="true"></div>
    <div class="kp-auth-content">
        <section class="kp-auth-card kp-auth-card-main kp-auth-animate" aria-labelledby="forgot-title">
        <div class="kp-auth-topbar">
            <a href="{{ route('login') }}" class="kp-auth-back">
                <x-tabler-arrow-left size="18" aria-hidden="true" />
                <span>Back to sign in</span>
            </a>
        </div>

        <div class="kp-auth-card-brand" aria-label="KP Wear">
            <img src="{{ asset('images/kp-wear-logo.png') }}" alt="KP Wear" style="height:72px;width:auto;max-width:100%">
        </div>

        <div class="kp-auth-heading">
            <h1 id="forgot-title">Forgot password</h1>
            <p>Enter the phone number on your account and we'll email you a link to set a new password. It's free — no SMS needed.</p>
        </div>

        <form novalidate data-forgot-form class="kp-auth-form">
            <div data-auth-error class="kp-form-error hidden" role="alert" aria-live="polite"></div>

            <div>
                <label class="kp-auth-label" for="forgot-phone">Phone number</label>
                <input id="forgot-phone" name="phone" type="tel" class="field w-full kp-auth-field" required placeholder="07XXXXXXXX" autocomplete="tel">
            </div>

            <button class="button-dark kp-auth-submit" type="submit">
                Send reset link <span aria-hidden="true">→</span>
            </button>

            <p class="kp-auth-help">
                Accounts without an email address on file need our support team to set a password —
                <a href="{{ route('contact') }}">contact us</a> and we'll help.
            </p>
        </form>

        {{-- Shown after a successful request. The response is identical whether
             or not the account exists, so this copy covers both outcomes. --}}
        <div data-forgot-success class="kp-auth-form" hidden>
            <div class="kp-auth-heading" style="text-align:left">
                <h2 style="font-size:1.1rem">Check your inbox</h2>
                <p>
                    If this account has an email address on file, a reset link is on its way.
                    It's valid for 60 minutes and can be used once. Don't see it? Look in your
                    spam folder, or <a href="{{ route('password.forgot') }}">try again</a>.
                </p>
            </div>
            <a href="{{ route('login') }}" class="button-dark kp-auth-submit" style="display:inline-flex;text-align:center">
                Back to sign in
            </a>
        </div>
        </section>
    </div>
</div>
@endsection
