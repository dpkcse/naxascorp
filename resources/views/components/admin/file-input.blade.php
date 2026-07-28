@props(['name', 'label', 'help' => 'Choose a file that meets the documented size and format requirements.'])
<x-admin.form-input :name="$name" :label="$label" type="file" :help="$help" {{ $attributes }} />
