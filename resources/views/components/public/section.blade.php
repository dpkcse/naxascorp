@props(['variant' => 'standard'])
<section {{ $attributes->merge(['class' => 'public-section '.($variant === 'alternate' ? 'bg-public-alternate' : 'bg-public-background')]) }}><div class="public-container">@isset($decoration)<div aria-hidden="true">{{ $decoration }}</div>@endisset{{ $slot }}</div></section>
