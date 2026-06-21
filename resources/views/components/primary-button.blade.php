<button {{ $attributes->merge(['type' => 'submit', 'class' => 'mono-button-primary inline-flex gap-2']) }}>
    {{ $slot }}
</button>
