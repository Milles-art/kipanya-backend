@extends('admin.layouts.app', ['title' => 'Duplicate devices', 'heading' => 'Duplicate devices'])

@section('content')
<div class="space-y-6">
    <section>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear</p>
        <div class="mt-1 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-black tracking-tight sm:text-3xl">Duplicate devices</h2>
                <p class="mt-2 text-sm leading-6 text-black">
                    Browsers that created more than one customer account. Two accounts sharing a device
                    were signed up on the same browser; the same signup IP means the same connection too.
                    Accounts with no orders show up here as well — that is where bulk sign-ups sit.
                </p>
            </div>
            <a href="{{ route('admin.wear.customers.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-black bg-white px-5 py-3 text-sm font-bold hover:bg-black hover:text-white">
                Back to customers
            </a>
        </div>
    </section>

    <section class="rounded-2xl border border-black bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-baseline gap-x-3">
            <span class="text-3xl font-black tracking-tight">{{ $groupCount }}</span>
            <span class="text-sm font-bold text-black">shared browser{{ $groupCount === 1 ? '' : 's' }}</span>
            <span class="text-sm font-black text-emerald-600">·</span>
            <span class="text-3xl font-black tracking-tight">{{ $accountCount }}</span>
            <span class="text-sm font-bold text-black">account{{ $accountCount === 1 ? '' : 's' }} involved</span>
        </div>
    </section>

    @forelse($groups as $deviceHash => $accounts)
        @php
            $short = substr((string) $deviceHash, 0, 12);
            $ips = $accounts->pluck('signup_ip')->filter()->unique()->values();
        @endphp
        <section class="overflow-hidden rounded-2xl border border-black bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-black px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-black tracking-tight">Device {{ $short }}…</p>
                    <p class="mt-1 text-xs font-bold text-black">
                        {{ $accounts->count() }} accounts
                        @if($ips->isNotEmpty())
                            · signup IP{{ $ips->count() === 1 ? '' : 's' }} {{ $ips->implode(', ') }}
                        @else
                            · no signup IP recorded
                        @endif
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-black/10 text-left text-sm">
                    <thead class="bg-[#f8f8f6] text-[11px] uppercase tracking-[0.14em] text-black">
                        <tr>
                            <th class="px-5 py-3 font-bold">Customer</th>
                            <th class="px-5 py-3 font-bold">Contact</th>
                            <th class="px-5 py-3 font-bold">Signed up</th>
                            <th class="px-5 py-3 font-bold">Orders</th>
                            <th class="px-5 py-3 font-bold">Spent</th>
                            <th class="px-5 py-3 font-bold">Status</th>
                            <th class="px-5 py-3 font-bold">Password</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/10">
                        @foreach($accounts as $account)
                            <tr>
                                <td class="px-5 py-4 font-bold">{{ $account->name }}</td>
                                <td class="px-5 py-4 text-black">
                                    {{ $account->phone }}
                                    @if($account->email)
                                        <span class="block text-xs text-black/60">{{ $account->email }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-black">
                                    {{ \Illuminate\Support\Carbon::parse($account->created_at)->format('d M Y, H:i') }}
                                </td>
                                <td class="px-5 py-4 font-bold">{{ (int) $account->orders_count }}</td>
                                <td class="px-5 py-4 font-bold">TZS {{ number_format((float) $account->total_spent, 0) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-black ring-1 ring-black/20">{{ ucfirst($account->status ?? 'unknown') }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    {{-- Support path: accounts created in the SMS era have no password
                                         and often no email, so they cannot use "Forgot password".
                                         Super administrators can set one here. --}}
                                    @if(auth()->user()?->hasRole('super_admin'))
                                        <form method="POST" action="{{ route('admin.wear.customers.password', $account) }}"
                                              class="flex min-w-[240px] items-end gap-2">
                                            @csrf
                                            <label class="flex-1">
                                                <span class="sr-only">New password for {{ $account->name }}</span>
                                                <input type="password" name="password" required minlength="10" autocomplete="new-password"
                                                       placeholder="New password (min 10)"
                                                       class="w-full rounded-lg border border-black px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-black">
                                            </label>
                                            <label class="flex-1">
                                                <span class="sr-only">Confirm new password</span>
                                                <input type="password" name="password_confirmation" required minlength="10" autocomplete="new-password"
                                                       placeholder="Repeat password"
                                                       class="w-full rounded-lg border border-black px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-black">
                                            </label>
                                            <button class="rounded-lg bg-black px-3 py-2 text-xs font-bold text-white hover:bg-emerald-600">
                                                Set
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs font-bold text-black/50">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <section class="rounded-2xl border border-black bg-white p-12 text-center shadow-sm">
            <p class="text-lg font-black tracking-tight">No shared devices</p>
            <p class="mt-2 text-sm text-black">No two accounts were created on the same browser. Nothing to review.</p>
        </section>
    @endforelse
</div>
@endsection
