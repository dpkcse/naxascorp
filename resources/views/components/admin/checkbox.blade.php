@props(['name', 'label', 'description' => null])
<flux:field variant="inline"><flux:checkbox :name="$name" {{ $attributes }} /><div><flux:label>{{ $label }}</flux:label>@if($description)<flux:description>{{ $description }}</flux:description>@endif<flux:error :name="$name" /></div></flux:field>
