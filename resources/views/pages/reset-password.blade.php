@extends('layouts.app')
@section('content')
<div class="kp-auth-page" style="--kp-auth-bg: url('{{ asset('images/kp-wear-auth-bg.png') }}');">
    <div class="kp-auth-backdrop" aria-hidden="true"></div>
    <div class="kp-auth-content">
        <section class="kp-auth-card kp-auth-card-main kp-auth-animate" aria-labelledby="reset-title">
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
            <h1 id="reset-title">Set a new password</h1>
            <p>Choose a password you haven't used before. You'll be signed in right away.</p>
        </div>

        {{-- The token arrives in the query string (also as a hidden field the
             JS reads); nothing about the account is echoed on this page. --}}
        <form novalidate data-reset-form data-reset-token="{{ request('token') }}" class="kp-auth-form">
            <input type="hidden" name="token" value="{{ request('token') }}">
            <div data-auth-error class="kp-form-error hidden" role="alert" aria-live="polite"></div>

            <div class="kp-auth-register-fields">
                <div>
                    <label class="kp-auth-label" for="reset-password">New password</label>
                    <input id="reset-password" name="password" type="password" class="field w-full kp-auth-field" required minlength="8" placeholder="At least 8 characters" autocomplete="new-password">
                </div>
                <div>
                    <label class="kp-auth-label" for="reset-password-confirmation">Confirm new password</label>
                    <input id="reset-password-confirmation" name="password_confirmation" type="password" class="field w-full kp-auth-field" required minlength="8" placeholder="Type it again" autocomplete="new-password">
                </div>
            </div>

            <button class="button-dark kp-auth-submit" type="submit">
                Update password <span aria-hidden="true">→</span>
            </button>

            <p class="kp-auth-help">
                This link works once. Need another one?
                <a href="{{ route('password.forgot') }}">Request a new link</a>.
            </p>
        </form>
        </section>
    </div>
</div>
@endsection
