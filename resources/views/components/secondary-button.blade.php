<button {{ $attributes->merge(['type' => 'button', 'class' => 'mono-button-secondary inline-flex gap-2']) }}>
    {{ $slot }}
</button>
