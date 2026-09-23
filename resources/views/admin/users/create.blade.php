@extends('admin.layouts.app', ['title' => 'Add Admin User', 'heading' => 'Add Admin User'])
@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Administration</p><h2 class="mt-1 text-2xl font-black tracking-tight">Create administrator</h2><p class="mt-2 text-sm text-black">Add a staff account and assign an existing administration role.</p></div>
    @if($errors->any())<div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.users.store') }}" class="rounded-2xl border border-black bg-white p-5 shadow-sm sm:p-6">@csrf @include('admin.users.form')<div class="mt-7 flex justify-end gap-3"><a href="{{ route('admin.users.index') }}" class="rounded-xl border border-black px-5 py-3 text-sm font-bold text-black hover:bg-white">Cancel</a><button class="rounded-xl bg-black px-5 py-3 text-sm font-bold text-white hover:bg-black">Create admin</button></div></form>
</div>
@endsection
