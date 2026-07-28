@props(['settings' => null])
@if($settings?->primary_email || $settings?->primary_phone || $settings?->country_code)
<div class="bg-public-navy text-sm text-slate-200" data-public-top-bar><div class="public-container flex min-h-10 flex-wrap items-center justify-between gap-x-6 gap-y-1 py-2"><div class="flex min-w-0 flex-wrap items-center gap-x-5 gap-y-1">
@if($settings?->primary_phone)<a class="public-top-link" href="tel:{{ preg_replace('/[^+0-9]/', '', $settings->primary_phone) }}"><span aria-hidden="true">☎</span><span>{{ $settings->primary_phone }}</span></a>@endif
@if($settings?->primary_email)<a class="public-top-link min-w-0" href="mailto:{{ $settings->primary_email }}"><span aria-hidden="true">✉</span><span class="truncate">{{ $settings->primary_email }}</span></a>@endif
</div>@if($settings?->country_code)<span class="hidden uppercase tracking-wide sm:inline">{{ $settings->country_code }}</span>@endif</div></div>
@endif
