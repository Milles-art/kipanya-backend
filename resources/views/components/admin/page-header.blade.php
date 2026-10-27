@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-wrap items-end justify-between gap-4']) }}>
    <div class="min-w-0">
        <h1 class="kp-admin-display text-2xl sm:text-3xl">{{ $title }}</h1>
        @if($subtitle !== null && $subtitle !== '')
            <p class="mt-2 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($action)
        <div class="flex flex-wrap items-center gap-3">{{ $action }}</div>
    @endisset
</div>
