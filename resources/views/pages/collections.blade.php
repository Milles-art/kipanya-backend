@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl px-4 pb-20 pt-12 sm:px-6 lg:px-8">
  <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600">KP Wear</p>
  <h1 class="mt-2 text-3xl font-bold tracking-tight">Collections</h1>
  <div class="mt-8 grid gap-6 md:grid-cols-2">
    @foreach([
      ['New Drop','Fresh pieces from the latest KP Wear edit.','17_new_drop.jpg'],
      ['Everyday','Easy staples for your daily rotation.','18_lifestyle_rack.jpg'],
      ['Street','Relaxed fits built for movement.','19_lifestyle_street.jpg'],
      ['Editorial','The season’s visual story.','16_editorial_banner.jpg'],
    ] as [$name,$desc,$image])
      <a href="{{ route('shop') }}" class="group relative aspect-[4/3] overflow-hidden rounded-3xl bg-gray-100"><img src="{{ asset('assets/kp-wear/'.$image) }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" alt="{{ $name }}"><div class="absolute inset-0 bg-gradient-to-t from-black/75 to-transparent"></div><div class="absolute bottom-7 left-7 text-white"><p class="text-xs font-semibold uppercase tracking-wider text-emerald-300">Collection</p><h2 class="mt-1 text-2xl font-bold">{{ $name }}</h2><p class="mt-1 max-w-sm text-sm text-white/75">{{ $desc }}</p></div></a>
    @endforeach
  </div>
</div>
@endsection
