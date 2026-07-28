@props(['rows' => 3])
<div role="status" aria-label="Loading content" class="animate-pulse space-y-3 motion-reduce:animate-none">@for($i=0;$i<$rows;$i++)<div @class(['h-4 rounded bg-slate-200', 'w-full' => $i % 3 === 0, 'w-11/12' => $i % 3 === 1, 'w-4/5' => $i % 3 === 2])></div>@endfor<span class="sr-only">Loading…</span></div>
