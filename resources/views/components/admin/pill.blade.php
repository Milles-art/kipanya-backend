@props(['href' => null, 'active' => false, 'dark' => false])

@php
    $classes = trim('kp-admin-pill'.($active ? ' is-active' : '').($dark ? ' is-dark' : ''));
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
