@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'mono-input rounded-2xl px-4 py-3 text-base']) !!}>
