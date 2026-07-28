@props(['name', 'label', 'help' => null, 'required' => false])
<flux:field><flux:label>{{ $label }}@if($required)<span class="text-red-600" aria-hidden="true"> *</span>@endif</flux:label><flux:select :name="$name" :required="$required" {{ $attributes }}>{{ $slot }}</flux:select>@if($help)<flux:description>{{ $help }}</flux:description>@endif<flux:error :name="$name" /></flux:field>
