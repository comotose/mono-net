@props(['active'])

@php
$classes = ($active ?? false)
            ? 'mono-nav-link is-active'
            : 'mono-nav-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
