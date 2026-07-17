@props([
    'padding' => 'p-5',
])

<section {{ $attributes->merge(['class' => 'glass-card '.$padding]) }}>
    {{ $slot }}
</section>
