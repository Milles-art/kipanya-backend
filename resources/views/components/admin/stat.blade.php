@props(['label', 'value', 'icon' => 'chart-bar', 'detail' => null, 'change' => null, 'positive' => true, 'tone' => 'emerald'])

@php
    $tones = [
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
    ];
    $toneClass = $tones[$tone] ?? $tones['emerald'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-5 transition-shadow hover:shadow-md']) }}>
    <div class="mb-4 flex items-start justify-between gap-3">
        <span class="grid h-10 w-10 place-items-center rounded-xl {{ $toneClass }}">
            <x-dynamic-component :component="'tabler-'.$icon" size="20" stroke-width="1.8" />
        </span>
        @if($change !== null && $change !== '')
            <span class="text-xs font-semibold {{ $positive ? 'text-emerald-600' : 'text-rose-600' }}">{{ $positive ? '+' : '' }}{{ $change }}</span>
        @endif
    </div>
    <p class="mb-1 text-sm text-slate-500">{{ $label }}</p>
    <p class="font-display text-2xl leading-none text-slate-950">{{ $value }}</p>
    @if($detail !== null && $detail !== '')
        <p class="mt-2 text-xs text-slate-400">{{ $detail }}</p>
    @endif
</div>
