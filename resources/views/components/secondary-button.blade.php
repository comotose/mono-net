<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-transparent border border-white/30 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
