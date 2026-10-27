@extends('admin.layouts.app', ['title' => 'Customers', 'heading' => 'Customers'])

@section('content')
@php
    $totalSpent = $customers->sum('total_spent');
    $averageSpent = $customers->count() > 0 ? $totalSpent / $customers->count() : 0;
    $repeatCustomers = $customers->filter(fn ($customer) => (int) $customer->orders_count > 1)->count();
@endphp
<div>
    <x-admin.page-header title="Customers" subtitle="Registered customers who have placed KP Wear orders." />

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 xl:grid-cols-4">
        <x-admin.stat label="Customers" :value="number_format($customers->total())" icon="users" detail="With order history" />
        <x-admin.stat label="Total spend" value="TZS {{ number_format($totalSpent, 0) }}" icon="wallet" tone="sky" detail="Shown on this page" />
        <x-admin.stat label="Avg. spend" value="TZS {{ number_format($averageSpent, 0) }}" icon="trending-up" tone="violet" detail="Per customer on this page" />
        <x-admin.stat label="Repeat customers" :value="number_format($repeatCustomers)" icon="refresh" tone="amber" detail="More than one order" />
    </div>

    <form method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row">
        <input type="search" name="search" value="{{ $search }}" placeholder="Search customers" class="kp-admin-input min-w-0 flex-1" />
        <button type="submit" class="kp-admin-primary-button">Search</button>
        @if($search !== '')
            <a href="{{ route('admin.wear.customers.index') }}" class="kp-admin-secondary-button">Clear</a>
        @endif
    </form>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($customers as $customer)
            @php
                $initials = collect(explode(' ', trim($customer->name ?: 'Customer')))->filter()->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
            @endphp
            <x-admin.card class="p-5 transition-shadow hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-emerald-100 font-display text-lg text-emerald-700">{{ $initials ?: 'C' }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $customer->name ?: 'Unnamed customer' }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-400">{{ $customer->email ?: $customer->phone ?: 'No contact details' }}</p>
                        </div>
                    </div>
                    <x-admin.badge :status="$customer->status ?: 'unknown'" :label="ucfirst($customer->status ?? 'unknown')" />
                </div>
                <div class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-400">Orders</p>
                        <p class="mt-1 font-display text-lg text-slate-950">{{ number_format($customer->orders_count) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-wider text-slate-400">Total spent</p>
                        <p class="mt-1 font-display text-lg text-slate-950">TZS {{ number_format((float) $customer->total_spent, 0) }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs uppercase tracking-wider text-slate-400">Last order</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at)->format('d M Y, H:i') : '—' }}</p>
                    </div>
                </div>
            </x-admin.card>
        @empty
            <div class="col-span-full"><x-admin.empty message="No customers found — customers with orders will appear here." /></div>
        @endforelse
    </div>

    @if($customers->hasPages())
        <div class="mt-6">{{ $customers->links() }}</div>
    @endif
</div>
@endsection
