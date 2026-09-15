@extends('admin.layouts.app', ['title' => ucfirst($module), 'heading' => ucfirst($module)])
@section('content')
<div class="rounded-3xl border border-gray-200 bg-white p-8"><p class="text-xs font-bold uppercase tracking-widest text-emerald-600">Control Panel module</p><h2 class="mt-2 text-2xl font-black">{{ ucfirst($module) }}</h2><p class="mt-2 max-w-xl text-sm leading-6 text-gray-500">The module is registered in the central Control Panel. Its operational screens will be implemented domain-by-domain, with Kipanya Wear receiving the first full build.</p></div>
@endsection
