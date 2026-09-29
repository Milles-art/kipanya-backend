<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login — KP Wear</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
<div class="kp-auth-page" style="--kp-auth-bg: url('{{ asset('images/kp-wear-auth-bg.png') }}');">
    <div class="kp-auth-backdrop" aria-hidden="true"></div>
    <div class="kp-auth-content">
        <section class="kp-auth-card kp-auth-card-main kp-auth-animate" aria-labelledby="admin-login-title">
        <div class="kp-auth-topbar">
            <a href="{{ route('home') }}" class="kp-auth-back">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                <span>Back to store</span>
            </a>
        </div>

        <div class="kp-auth-card-brand" aria-label="KP Wear">
            <img src="{{ asset('images/kp-wear-logo.png') }}" alt="KP Wear" style="height:64px;width:auto;max-width:100%">
        </div>

        <div class="kp-auth-heading">
            <h1 id="admin-login-title">Administrator sign in</h1>
            @if(session('two_factor_required'))
            <p>Your account is protected by a second factor. Enter the six-digit code from your authenticator app.</p>
            @else
            <p>Sign in with your work email and password.</p>
            @endif
        </div>

        @if($errors->any())<div class="kp-form-error" role="alert">{{ $errors->first() }}</div>@endif

        @if(session('two_factor_required'))
        <form method="POST" action="{{ route('admin.login.two-factor') }}" class="kp-auth-form">@csrf
            <div>
                <label class="kp-auth-label" for="admin-totp">Authenticator code</label>
                <input id="admin-totp" name="code" required inputmode="numeric" maxlength="6" class="field w-full kp-auth-field kp-auth-otp" placeholder="000000" autocomplete="one-time-code" autofocus>
            </div>
            <button class="button-dark kp-auth-submit" type="submit">Verify &amp; sign in</button>
        </form>
        @else
        <form method="POST" action="{{ route('admin.login.submit') }}" class="kp-auth-form">@csrf
            <div>
                <label class="kp-auth-label" for="admin-email">Work email</label>
                <input id="admin-email" name="email" value="{{ old('email') }}" required type="email" class="field w-full kp-auth-field" placeholder="you@example.com" autocomplete="username">
            </div>
            <div>
                <label class="kp-auth-label" for="admin-password">Password</label>
                <input id="admin-password" name="password" required type="password" class="field w-full kp-auth-field" placeholder="••••••••••" autocomplete="current-password">
            </div>
            <button class="button-dark kp-auth-submit" type="submit">Sign in</button>
        </form>
        @endif
        </section>
    </div>
</div>
</body></html>
