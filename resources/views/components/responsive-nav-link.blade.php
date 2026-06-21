@props(['active'])

@php
$classes = ($active ?? false)
            ? 'mono-responsive-nav-link is-active'
            : 'mono-responsive-nav-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
