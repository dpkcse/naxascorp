@props(['label', 'value', 'hint' => null])
<x-admin.card><p class="text-sm font-medium text-slate-500">{{ $label }}</p><p class="mt-2 break-words text-2xl font-bold text-navy-950">{{ $value }}</p>@if($hint)<p class="mt-2 text-xs text-slate-500">{{ $hint }}</p>@endif</x-admin.card>
