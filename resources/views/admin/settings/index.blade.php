@extends('admin.layouts.app', ['title' => 'Settings', 'heading' => 'Store Settings'])

@section('content')
<div class="mx-auto max-w-5xl">
    <x-admin.page-header title="Store settings" subtitle="Manage the core store identity and commerce defaults used by Kipanya Wear." />

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-semibold">Please check the highlighted fields.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-admin.card class="overflow-hidden">
            <div class="border-b border-slate-100 px-6 pb-5 pt-6">
                <p class="text-xs uppercase tracking-wider text-slate-400">Store</p>
                <h3 class="kp-admin-display mt-1.5 text-lg">Identity &amp; support</h3>
                <p class="mt-1.5 text-xs text-slate-400">The basic information your team uses to identify and support the store.</p>
            </div>

            <div class="grid gap-5 p-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="store_name" class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Store name</label>
                    <input id="store_name" name="store_name" type="text" required maxlength="120" value="{{ old('store_name', $settings->get('store_name')?->value) }}" class="kp-admin-input">
                </div>

                <div class="sm:col-span-2">
                    <label for="store_tagline" class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Store tagline</label>
                    <input id="store_tagline" name="store_tagline" type="text" maxlength="255" value="{{ old('store_tagline', $settings->get('store_tagline')?->value) }}" class="kp-admin-input">
                </div>

                <div>
                    <label for="support_email" class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Support email</label>
                    <input id="support_email" name="support_email" type="email" maxlength="255" value="{{ old('support_email', $settings->get('support_email')?->value) }}" class="kp-admin-input">
                </div>

                <div>
                    <label for="support_phone" class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Support phone</label>
                    <input id="support_phone" name="support_phone" type="text" maxlength="30" value="{{ old('support_phone', $settings->get('support_phone')?->value) }}" class="kp-admin-input">
                </div>
            </div>
        </x-admin.card>

        <x-admin.card class="overflow-hidden">
            <div class="border-b border-slate-100 px-6 pb-5 pt-6">
                <p class="text-xs uppercase tracking-wider text-slate-400">Commerce</p>
                <h3 class="kp-admin-display mt-1.5 text-lg">Store defaults</h3>
                <p class="mt-1.5 text-xs text-slate-400">Defaults for money display and store operating time zone.</p>
            </div>

            <div class="grid gap-5 p-6 sm:grid-cols-2">
                <div>
                    <label for="currency" class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Currency</label>
                    <select id="currency" name="currency" required class="kp-admin-input">
                        @foreach(['TZS' => 'Tanzanian Shilling (TZS)', 'USD' => 'US Dollar (USD)'] as $code => $label)
                            <option value="{{ $code }}" @selected(old('currency', $settings->get('currency')?->value) === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="timezone" class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Timezone</label>
                    <input id="timezone" name="timezone" type="text" required maxlength="64" value="{{ old('timezone', $settings->get('timezone')?->value) }}" class="kp-admin-input">
                    <p class="mt-2 text-xs text-slate-400">Use an IANA timezone such as Africa/Dar_es_Salaam.</p>
                </div>
            </div>
        </x-admin.card>

        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-400">Changes are recorded in the administration audit trail.</p>
            <button type="submit" class="kp-admin-primary-button">Save settings</button>
        </div>
    </form>
</div>
@endsection
