@props(['name', 'label', 'description' => null])
<flux:switch :name="$name" :label="$label" :description="$description" {{ $attributes }} />
