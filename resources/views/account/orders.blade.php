@extends('layouts.app')
@section('content')<div data-orders-page class="mx-auto max-w-7xl px-4 pb-20 pt-12 sm:px-6 lg:px-8"><div class="grid gap-8 lg:grid-cols-[256px_1fr]">@include('components.account-sidebar')<div><h1 class="text-3xl font-bold">My Orders</h1><div data-orders-list class="mt-8 space-y-4"><p class="text-sm text-gray-500">Loading orders…</p></div></div></div></div>@endsection
