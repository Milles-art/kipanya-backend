@extends('admin.layouts.app', ['title' => 'Add product', 'heading' => 'Add product'])

@section('content')
<div class="mx-auto max-w-4xl">
    <x-admin.page-header title="Add product" subtitle="Create a new product for the KP Wear catalog." />

    @include('admin.wear.products.form', ['formAction' => route('admin.wear.products.store'), 'method' => 'POST'])
</div>
@endsection
