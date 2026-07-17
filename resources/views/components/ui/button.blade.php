@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-950 disabled:cursor-not-allowed disabled:opacity-50';
    $variants = [
        'primary' => 'bg-emerald-400 text-slate-950 hover:bg-emerald-300 focus:ring-emerald-300 shadow-lg shadow-emerald-500/20',
        'secondary' => 'bg-white/10 text-white hover:bg-white/15 focus:ring-white/40 border border-white/10',
        'danger' => 'bg-rose-500 text-white hover:bg-rose-400 focus:ring-rose-300 shadow-lg shadow-rose-500/20',
        'warning' => 'bg-orange-400 text-slate-950 hover:bg-orange-300 focus:ring-orange-300 shadow-lg shadow-orange-500/20',
        'ghost' => 'bg-transparent text-slate-200 hover:bg-white/10 focus:ring-white/30',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.($variants[$variant] ?? $variants['primary'])]) }}>
    {{ $slot }}
</button>
