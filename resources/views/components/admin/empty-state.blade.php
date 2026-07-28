@props(['title' => 'No data available', 'description' => null])
<div {{ $attributes->class(['rounded-lg border border-dashed border-slate-300 px-5 py-10 text-center']) }}><p class="font-semibold text-slate-800">{{ $title }}</p>@if($description)<p class="mx-auto mt-2 max-w-md text-sm text-slate-500">{{ $description }}</p>@endif{{ $slot }}</div>
