@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-[rgb(var(--mono-text-soft))]']) }}>
    {{ $value ?? $slot }}
</label>
