@props(['message' => 'Nothing to show here yet.'])

<div {{ $attributes->merge(['class' => 'px-6 py-16 text-center']) }}>
    <p class="text-sm text-slate-400">{{ $message }}</p>
</div>
