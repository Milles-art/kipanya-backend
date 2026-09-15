<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login — Kipanya</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="min-h-screen bg-gray-950 p-4 text-gray-950"><main class="mx-auto flex min-h-[calc(100vh-2rem)] max-w-md items-center"><section class="w-full rounded-3xl bg-white p-7 shadow-2xl sm:p-9"><p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-600">Kipanya Control Panel</p><h1 class="mt-2 text-3xl font-black">Administrator sign in</h1><p class="mt-2 text-sm text-gray-500">Use your administrator phone number. A verification code will be sent if the account is authorized.</p>
@if($errors->any())<div class="mt-5 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
@if(session('otp_sent'))<div class="mt-5 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-700">Verification code sent. Enter it below.</div>@endif
<form method="POST" action="{{ session('otp_sent') ? route('admin.login.submit') : route('admin.login.request-otp') }}" class="mt-6 space-y-4">@csrf
<label class="block text-sm font-semibold">Phone<input name="phone" value="{{ old('phone',session('phone')) }}" required class="field mt-2 w-full" placeholder="+255..." autocomplete="tel"></label>
@if(session('otp_sent'))<label class="block text-sm font-semibold">Verification code<input name="code" required inputmode="numeric" maxlength="6" class="field mt-2 w-full tracking-[0.4em]" placeholder="000000" autocomplete="one-time-code"></label>@endif
<button class="button-dark w-full">{{ session('otp_sent') ? 'Sign in' : 'Send verification code' }}</button></form></section></main></body></html>
