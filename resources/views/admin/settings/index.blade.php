@extends('admin.layouts.app', ['title' => 'Settings', 'heading' => 'Store Settings'])

@section('content')
<div class="mx-auto max-w-5xl space-y-8">
    <section>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Administration</p>
        <h2 class="mt-1 text-2xl font-black tracking-tight sm:text-3xl">Store settings</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">Manage the core store identity and commerce defaults used by Kipanya Wear.</p>
    </section>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-bold">Please check the highlighted fields.</p>
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

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-5 sm:px-6">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Store</p>
                <h3 class="mt-1 text-lg font-bold">Identity & support</h3>
                <p class="mt-1 text-sm text-gray-500">The basic information your team uses to identify and support the store.</p>
            </div>

            <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                <div class="sm:col-span-2">
                    <label for="store_name" class="text-sm font-semibold text-gray-800">Store name</label>
                    <input id="store_name" name="store_name" type="text" required maxlength="120" value="{{ old('store_name', $settings->get('store_name')?->value) }}" class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                </div>

                <div class="sm:col-span-2">
                    <label for="store_tagline" class="text-sm font-semibold text-gray-800">Store tagline</label>
                    <input id="store_tagline" name="store_tagline" type="text" maxlength="255" value="{{ old('store_tagline', $settings->get('store_tagline')?->value) }}" class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                </div>

                <div>
                    <label for="support_email" class="text-sm font-semibold text-gray-800">Support email</label>
                    <input id="support_email" name="support_email" type="email" maxlength="255" value="{{ old('support_email', $settings->get('support_email')?->value) }}" class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                </div>

                <div>
                    <label for="support_phone" class="text-sm font-semibold text-gray-800">Support phone</label>
                    <input id="support_phone" name="support_phone" type="text" maxlength="30" value="{{ old('support_phone', $settings->get('support_phone')?->value) }}" class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-5 sm:px-6">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Commerce</p>
                <h3 class="mt-1 text-lg font-bold">Store defaults</h3>
                <p class="mt-1 text-sm text-gray-500">Defaults for money display and store operating time zone.</p>
            </div>

            <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                <div>
                    <label for="currency" class="text-sm font-semibold text-gray-800">Currency</label>
                    <select id="currency" name="currency" required class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                        @foreach(['TZS' => 'Tanzanian Shilling (TZS)', 'USD' => 'US Dollar (USD)'] as $code => $label)
                            <option value="{{ $code }}" @selected(old('currency', $settings->get('currency')?->value) === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="timezone" class="text-sm font-semibold text-gray-800">Timezone</label>
                    <input id="timezone" name="timezone" type="text" required maxlength="64" value="{{ old('timezone', $settings->get('timezone')?->value) }}" class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                    <p class="mt-2 text-xs text-gray-400">Use an IANA timezone such as Africa/Dar_es_Salaam.</p>
                </div>
            </div>
        </section>

        <div class="flex flex-col gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-400">Changes are recorded in the administration audit trail.</p>
            <button type="submit" class="rounded-xl bg-gray-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-gray-800">Save settings</button>
        </div>
    </form>
</div>
@endsection
