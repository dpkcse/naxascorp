@props(['columns' => '3'])
@php $classes = match((string) $columns) {'2' => 'md:grid-cols-2', '4' => 'sm:grid-cols-2 xl:grid-cols-4', default => 'md:grid-cols-2 lg:grid-cols-3'}; @endphp
<div {{ $attributes->merge(['class' => "grid grid-cols-1 gap-6 $classes"]) }}>{{ $slot }}</div>
