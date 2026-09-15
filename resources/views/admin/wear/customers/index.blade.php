@extends('admin.layouts.app', ['title' => 'Customers', 'heading' => 'Customers'])

@section('content')
<div class="space-y-6">
    <section>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear</p>
        <div class="mt-1 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-black tracking-tight sm:text-3xl">Customers</h2>
                <p class="mt-2 text-sm leading-6 text-gray-500">View registered customers who have placed KP Wear orders.</p>
            </div>
        </div>
    </section>

    <form method="GET" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search name, email or phone"
                   class="min-w-0 flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-100">
            <button class="rounded-xl bg-gray-950 px-5 py-3 text-sm font-bold text-white hover:bg-gray-800">Search</button>
            @if($search !== '')
                <a href="{{ route('admin.wear.customers.index') }}" class="rounded-xl border border-gray-200 px-5 py-3 text-center text-sm font-bold text-gray-700 hover:bg-gray-50">Clear</a>
            @endif
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Customer directory</p>
            <h3 class="mt-1 text-lg font-bold">{{ $customers->total() }} customer(s)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3">Orders</th>
                        <th class="px-5 py-3">Total spent</th>
                        <th class="px-5 py-3">Last order</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4">
                            <p class="font-bold">{{ $customer->name ?: 'Unnamed customer' }}</p>
                            <p class="mt-1 text-xs text-gray-400">#{{ $customer->id }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-medium">{{ $customer->phone ?: '—' }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ $customer->email ?: '—' }}</p>
                        </td>
                        <td class="px-5 py-4 font-bold">{{ $customer->orders_count }}</td>
                        <td class="px-5 py-4 font-bold">TZS {{ number_format((float) $customer->total_spent, 0) }}</td>
                        <td class="px-5 py-4 text-gray-600">
                            {{ $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at)->format('d M Y, H:i') : '—' }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-700">{{ ucfirst($customer->status ?? 'unknown') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">No customers found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
@endsection
