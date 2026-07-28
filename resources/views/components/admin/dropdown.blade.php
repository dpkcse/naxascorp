@props(['label'])
<flux:dropdown position="bottom" align="end"><flux:button variant="ghost">{{ $label }}</flux:button><flux:menu>{{ $slot }}</flux:menu></flux:dropdown>
