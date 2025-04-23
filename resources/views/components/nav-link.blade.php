@props(['active'])

@php
$classes = ($active ?? false)
            ? 'nav-link active border border-2 border-primary text-black bg-[rgb(255,174,0)] rounded-3' // Active styles
            : 'nav-link text-white'; // Default styles
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>