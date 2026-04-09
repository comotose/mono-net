<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-white text-black border border-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-white/90 focus:outline-none focus:ring-2 focus:ring-white/50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
