@props(['label' => 'Data table'])
<div class="max-w-full overflow-x-auto rounded-lg border border-admin-border"><table class="w-full min-w-[40rem] border-collapse text-left text-sm"><caption class="sr-only">{{ $label }}</caption>{{ $slot }}</table></div>
