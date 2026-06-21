<button {{ $attributes->merge(['type' => 'submit', 'class' => 'mono-button-danger']) }}>
    {{ $slot }}
</button>
