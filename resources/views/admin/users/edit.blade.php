@extends('admin.layouts.app', ['title' => 'Edit Admin User', 'heading' => 'Edit Admin User'])
@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Administration</p><h2 class="mt-1 text-2xl font-black tracking-tight">Edit administrator</h2><p class="mt-2 text-sm text-black">Update {{ $user->name ?: 'this administrator' }} and their staff role.</p></div>
    @if($errors->any())<div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="rounded-2xl border border-black bg-white p-5 shadow-sm sm:p-6">@csrf @method('PUT') @include('admin.users.form')<div class="mt-7 flex flex-col gap-3 sm:flex-row sm:justify-between"><a href="{{ route('admin.users.index') }}" class="rounded-xl border border-black px-5 py-3 text-center text-sm font-bold text-black hover:bg-white">Back</a><button class="rounded-xl bg-black px-5 py-3 text-sm font-bold text-white hover:bg-black">Save changes</button></div></form>
    @if(!auth()->user()->is($user))
    <form method="POST" action="{{ route('admin.users.status', $user) }}" class="rounded-2xl border border-black bg-white p-5 shadow-sm sm:p-6">@csrf
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><h3 class="font-bold">Account access</h3><p class="mt-1 text-sm text-black">{{ $user->status === 'active' ? 'Deactivate this account to prevent admin login.' : 'Reactivate this account to restore admin login.' }}</p></div><button class="rounded-xl border border-black px-5 py-3 text-sm font-bold text-black hover:bg-white">{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}</button></div>
    </form>
    @endif
</div>
@endsection
