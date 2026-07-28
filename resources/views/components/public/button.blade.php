@props(['href' => null, 'variant' => 'primary', 'size' => 'md', 'disabled' => false, 'loading' => false, 'type' => 'button'])
@php
$classes = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-lg font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-public-focus focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-55 aria-disabled:cursor-not-allowed aria-disabled:opacity-55 '.match($variant) {
    'secondary' => 'bg-slate-200 text-public-navy hover:bg-slate-300',
    'outline' => 'border border-public-primary text-public-primary hover:bg-blue-50',
    'ghost' => 'text-public-primary hover:bg-blue-50',
    'light' => 'bg-white text-public-navy hover:bg-slate-100',
    'outline-dark' => 'border border-white/50 text-white hover:bg-white/10',
    'icon' => 'bg-public-primary text-white hover:bg-blue-700',
    default => 'bg-public-primary text-white shadow-sm hover:bg-blue-700',
}.' '.($size === 'sm' ? 'px-4 py-2 text-sm' : 'px-5 py-2.5 text-base');
@endphp
@if($href && ! $disabled)<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>@if($loading)<span class="public-spinner" aria-hidden="true"></span>@endif{{ $slot }}</a>
@else<button type="{{ $type }}" @disabled($disabled || $loading) @if($loading) aria-busy="true" @endif {{ $attributes->merge(['class' => $classes]) }}>@if($loading)<span class="public-spinner" aria-hidden="true"></span><span class="sr-only">Loading</span>@endif{{ $slot }}</button>@endif
