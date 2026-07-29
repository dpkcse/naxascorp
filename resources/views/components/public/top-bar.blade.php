@props(['settings' => null, 'header' => []])
@php($items = array_filter([$settings?->primary_email, $settings?->primary_phone, $settings?->country_code, data_get($header,'message'), data_get($header,'support'), data_get($header,'careers')]))
@if(data_get($header,'show_top_bar',true) && $items)
<div class="bg-public-navy text-sm text-slate-200" data-public-top-bar><div class="public-container flex min-h-10 flex-wrap items-center justify-between gap-x-6 gap-y-2 py-2"><div class="flex min-w-0 flex-wrap items-center gap-x-5 gap-y-2">
@if($settings?->primary_phone)<a class="public-top-link" href="tel:{{ preg_replace('/[^+0-9]/', '', $settings->primary_phone) }}"><span aria-hidden="true">☎</span><span>{{ $settings->primary_phone }}</span></a>@endif
@if($settings?->primary_email)<a class="public-top-link min-w-0" href="mailto:{{ filter_var($settings->primary_email,FILTER_SANITIZE_EMAIL) }}"><span aria-hidden="true">✉</span><span class="truncate">{{ $settings->primary_email }}</span></a>@endif
@if(data_get($header,'message'))<span>{{ data_get($header,'message') }}</span>@endif
</div><div class="flex flex-wrap items-center gap-4">@foreach(['support','careers'] as $key)@if($link=data_get($header,$key))<a class="public-top-link" href="{{ $link['href'] }}" target="{{ $link['target'] }}" @if($link['external']&&$link['target']==='_blank') rel="noopener noreferrer" @endif>{{ $link['label'] }}</a>@endif @endforeach @if($settings?->country_code)<span class="uppercase tracking-wide">{{ $settings->country_code }}</span>@endif</div></div></div>
@endif
