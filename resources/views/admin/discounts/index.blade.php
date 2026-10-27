@extends('admin.layouts.app', ['title' => 'Discounts', 'heading' => 'Discounts'])

@section('content')
<div>
    <x-admin.page-header title="Discounts" subtitle="Create and manage promotional codes" />

    <x-admin.card class="p-6">
        <div class="px-6 py-16 text-center">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                <x-tabler-percentage size="26" stroke-width="1.8" />
            </span>
            <h3 class="kp-admin-display mt-5 text-lg">Coupons are coming soon</h3>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-400">
                Promotional codes are on the way. Soon you will be able to create percentage and fixed
                discounts, set usage limits and expiry dates, and share promo codes with your customers.
            </p>
        </div>
    </x-admin.card>
</div>
@endsection
