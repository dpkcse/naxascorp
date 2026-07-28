@props(['title', 'description' => null, 'href' => null, 'variant' => 'basic', 'disabled' => false])
@php $dark = $variant === 'dark'; $classes = $dark ? 'border-white/15 bg-white/8 text-white' : 'border-public-border bg-white text-public-text'; @endphp
<article {{ $attributes->merge(['class' => "group flex h-full flex-col rounded-public border p-6 shadow-public transition motion-safe:hover:-translate-y-1 motion-safe:hover:shadow-lg $classes"]) }}>
@if(isset($icon))<div class="mb-5">{{ $icon }}</div>@endif<h3 class="text-xl font-bold tracking-tight {{ $dark ? 'text-white' : 'text-public-navy' }}">{{ $title }}</h3>@if($description)<p class="mt-3 grow leading-7 {{ $dark ? 'text-slate-200' : 'text-public-secondary' }}">{{ $description }}</p>@endif
@if(isset($action))<div class="mt-5">{{ $action }}</div>@elseif($href && ! $disabled)<a href="{{ $href }}" class="mt-5 rounded-sm font-semibold text-public-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-public-focus">Learn more<span class="sr-only"> about {{ $title }}</span></a>@elseif($disabled)<span class="mt-5 text-sm font-semibold text-public-muted" aria-disabled="true">Coming soon</span>@endif
</article>
