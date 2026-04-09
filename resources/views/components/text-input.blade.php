@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-white/20 bg-black text-white placeholder-white/40 focus:border-white focus:ring-white/30 rounded-md shadow-sm']) !!}>
