@extends('admin.layouts.app', ['title' => 'Add Admin User', 'heading' => 'Add Admin User'])

@section('content')
<div class="mx-auto max-w-3xl">
    <x-admin.page-header title="Add Admin User" subtitle="Add a staff account and assign an existing administration role." />

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <x-admin.card class="p-6">
            @include('admin.users.form')
            <div class="mt-7 flex flex-wrap justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="kp-admin-secondary-button">Cancel</a>
                <button type="submit" class="kp-admin-primary-button">Create admin</button>
            </div>
        </x-admin.card>
    </form>
</div>
@endsection
