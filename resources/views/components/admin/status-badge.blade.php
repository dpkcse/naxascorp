@props(['status' => 'info'])
@php($classes = match($status) {'healthy','active','success' => 'bg-emerald-50 text-emerald-800 ring-emerald-600/20','warning','pending' => 'bg-amber-50 text-amber-900 ring-amber-600/20','danger','invalid','action-required' => 'bg-red-50 text-red-800 ring-red-600/20',default => 'bg-blue-50 text-blue-800 ring-blue-600/20'})
<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset', $classes]) }}><span aria-hidden="true">●</span>{{ $slot }}</span>
