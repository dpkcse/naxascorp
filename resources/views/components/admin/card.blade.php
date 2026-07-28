@props(['title' => null, 'description' => null])
<section {{ $attributes->class(['min-w-0 rounded-xl border border-admin-border bg-admin-surface p-5 shadow-sm sm:p-6']) }}>@if($title)<div class="mb-5"><h2 class="text-base font-semibold text-navy-950">{{ $title }}</h2>@if($description)<p class="mt-1 text-sm text-slate-500">{{ $description }}</p>@endif</div>@endif{{ $slot }}</section>
