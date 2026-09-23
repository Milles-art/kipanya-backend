@extends('admin.layouts.app', ['title' => 'Customers', 'heading' => 'Customers'])

@section('content')
<div class="space-y-6">
    <section>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear</p>
        <div class="mt-1 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-black tracking-tight sm:text-3xl">Customers</h2>
                <p class="mt-2 text-sm leading-6 text-black">View registered customers who have placed KP Wear orders.</p>
            </div>
        </div>
    </section>

    <form method="GET" class="rounded-2xl border border-black bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search name, email or phone"
                   class="min-w-0 flex-1 rounded-xl border border-black px-4 py-3 text-sm outline-none focus:border-black focus:ring-2 focus:ring-black">
            <button class="rounded-xl bg-black px-5 py-3 text-sm font-bold text-white hover:bg-black">Search</button>
            @if($search !== '')
                <a href="{{ route('admin.wear.customers.index') }}" class="rounded-xl border border-black px-5 py-3 text-center text-sm font-bold text-black hover:bg-white">Clear</a>
            @endif
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-black bg-white shadow-sm">
        <div class="border-b border-black px-5 py-4">
            <p class="text-xs font-bold uppercase tracking-widest text-black">Customer directory</p>
            <h3 class="mt-1 text-lg font-bold">{{ $customers->total() }} customer(s)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-white text-xs font-bold uppercase tracking-wider text-black">
                    <tr>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3">Orders</th>
                        <th class="px-5 py-3">Total spent</th>
                        <th class="px-5 py-3">Last order</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black">
                @forelse($customers as $customer)
                    <tr class="hover:bg-white/70">
                        <td class="px-5 py-4">
                            <p class="font-bold">{{ $customer->name ?: 'Unnamed customer' }}</p>
                            <p class="mt-1 text-xs text-black">#{{ $customer->id }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-medium">{{ $customer->phone ?: '—' }}</p>
                            <p class="mt-1 text-xs text-black">{{ $customer->email ?: '—' }}</p>
                        </td>
                        <td class="px-5 py-4 font-bold">{{ $customer->orders_count }}</td>
                        <td class="px-5 py-4 font-bold">TZS {{ number_format((float) $customer->total_spent, 0) }}</td>
                        <td class="px-5 py-4 text-black">
                            {{ $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at)->format('d M Y, H:i') : '—' }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-black">{{ ucfirst($customer->status ?? 'unknown') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-black">No customers found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="border-t border-black px-5 py-4">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
@endsection
