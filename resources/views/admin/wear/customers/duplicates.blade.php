@extends('admin.layouts.app', ['title' => 'Duplicate devices', 'heading' => 'Duplicate devices'])

@section('content')
<div>
    <x-admin.page-header title="Duplicate devices" subtitle="Browsers that created more than one customer account. Two accounts sharing a device were signed up on the same browser; the same signup IP means the same connection too. Accounts with no orders show up here as well — that is where bulk sign-ups sit.">
        <x-slot name="action">
            <a href="{{ route('admin.wear.customers.index') }}" class="kp-admin-secondary-button"><x-tabler-arrow-left size="16" />Back to customers</a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="mb-6 p-5">
        <div class="flex flex-wrap items-baseline gap-x-3">
            <span class="font-display text-3xl text-slate-950">{{ $groupCount }}</span>
            <span class="text-sm font-medium text-slate-600">shared browser{{ $groupCount === 1 ? '' : 's' }}</span>
            <span class="text-sm font-medium text-emerald-600">·</span>
            <span class="font-display text-3xl text-slate-950">{{ $accountCount }}</span>
            <span class="text-sm font-medium text-slate-600">account{{ $accountCount === 1 ? '' : 's' }} involved</span>
        </div>
    </x-admin.card>

    @forelse($groups as $deviceHash => $accounts)
        @php
            $short = substr((string) $deviceHash, 0, 12);
            $ips = $accounts->pluck('signup_ip')->filter()->unique()->values();
        @endphp
        <x-admin.card class="mb-5 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h3 class="kp-admin-display text-lg">Device {{ $short }}…</h3>
                <p class="mt-1.5 text-xs text-slate-400">
                    {{ $accounts->count() }} accounts
                    @if($ips->isNotEmpty())
                        · signup IP{{ $ips->count() === 1 ? '' : 's' }} {{ $ips->implode(', ') }}
                    @else
                        · no signup IP recorded
                    @endif
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[860px] text-sm">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-slate-400">
                            <th class="px-6 py-3.5 text-left font-medium">Customer</th>
                            <th class="px-3 py-3.5 text-left font-medium">Contact</th>
                            <th class="hidden px-3 py-3.5 text-left font-medium sm:table-cell">Signed up</th>
                            <th class="px-3 py-3.5 text-right font-medium">Orders</th>
                            <th class="px-3 py-3.5 text-right font-medium">Spent</th>
                            <th class="px-3 py-3.5 text-left font-medium">Status</th>
                            <th class="px-6 py-3.5 text-left font-medium">Password</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account)
                            <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $account->name }}</td>
                                <td class="px-3 py-4 text-slate-600">
                                    {{ $account->phone }}
                                    @if($account->email)
                                        <span class="block text-xs text-slate-400">{{ $account->email }}</span>
                                    @endif
                                </td>
                                <td class="hidden px-3 py-4 text-slate-500 sm:table-cell">{{ \Illuminate\Support\Carbon::parse($account->created_at)->format('d M Y, H:i') }}</td>
                                <td class="px-3 py-4 text-right font-medium text-slate-800">{{ (int) $account->orders_count }}</td>
                                <td class="px-3 py-4 text-right font-medium text-slate-900">TZS {{ number_format((float) $account->total_spent, 0) }}</td>
                                <td class="px-3 py-4"><x-admin.badge :status="$account->status ?: 'unknown'" :label="ucfirst($account->status ?? 'unknown')" /></td>
                                <td class="px-6 py-4">
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
                                                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                            </label>
                                            <label class="flex-1">
                                                <span class="sr-only">Confirm new password</span>
                                                <input type="password" name="password_confirmation" required minlength="10" autocomplete="new-password"
                                                       placeholder="Repeat password"
                                                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                            </label>
                                            <button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">Set</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @empty
        <x-admin.card class="p-12 text-center">
            <h3 class="kp-admin-display text-lg">No Shared Devices</h3>
            <p class="mt-2 text-sm text-slate-400">No two accounts were created on the same browser. Nothing to review.</p>
        </x-admin.card>
    @endforelse
</div>
@endsection
