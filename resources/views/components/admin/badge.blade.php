@props(['status', 'label' => null])

@php
    $value = strtolower(trim((string) $status));

    $tones = [
        'green' => ['active', 'delivered', 'success', 'completed', 'resolved', 'approved', 'vip', 'paid', 'high', 'enabled'],
        'red' => ['cancelled', 'failed', 'rejected', 'suspended', 'archived', 'inactive', 'out', 'blocked'],
        'amber' => ['pending', 'requested', 'draft', 'new', 'low', 'scheduled'],
        'blue' => ['processing', 'shipped', 'open', 'medium', 'ready'],
        'purple' => ['refunded', 'closed', 'exchange'],
    ];

    $direct = ['green', 'red', 'amber', 'blue', 'gray', 'purple'];
    $tone = in_array($value, $direct, true) ? $value : 'gray';

    if ($tone === 'gray') {
        foreach ($tones as $candidate => $words) {
            if (in_array($value, $words, true)) {
                $tone = $candidate;
                break;
            }
        }
    }
@endphp

<span {{ $attributes->merge(['class' => 'kp-admin-badge is-'.$tone]) }}>{{ $label ?? $status }}</span>
