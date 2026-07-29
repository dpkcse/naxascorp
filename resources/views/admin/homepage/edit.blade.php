<x-layouts.app title="Homepage" :breadcrumbs="[['label' => 'Website'], ['label' => 'Homepage']]">
    <x-admin.page-header title="Homepage" description="Manage the fixed, structured homepage. Changes remain drafts until explicitly published.">
        <x-slot:actions><a href="{{ route('admin.homepage.preview') }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center rounded-md border border-admin-border px-4 font-semibold focus-visible:ring-2 focus-visible:ring-blue-600">Preview draft</a></x-slot:actions>
    </x-admin.page-header>
    @include('admin.public-chrome._messages')
    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.homepage.update') }}" class="grid gap-5">@csrf @method('PUT')
                <x-admin.validation-summary />
                <div class="grid gap-5 sm:grid-cols-2"><x-admin.form-input name="eyebrow" label="Eyebrow" :value="old('eyebrow', $setting->eyebrow)" /><x-admin.form-input name="title" label="Page title" :value="old('title', $setting->title)" required /></div>
                <x-admin.form-input name="highlighted_text" label="Highlighted text" :value="old('highlighted_text', $setting->highlighted_text)" />
                <x-admin.textarea name="description" label="Meta and page description">{{ old('description', $setting->description) }}</x-admin.textarea>
                <div class="grid gap-5 sm:grid-cols-2"><x-admin.form-input name="primary_cta_label" label="Primary CTA label" :value="old('primary_cta_label', $setting->primary_cta_label)" /><x-admin.form-input name="primary_cta_url" label="Primary CTA URL" :value="old('primary_cta_url', $setting->primary_cta_url)" /></div>
                <div class="grid gap-5 sm:grid-cols-2"><x-admin.form-input name="secondary_cta_label" label="Secondary CTA label" :value="old('secondary_cta_label', $setting->secondary_cta_label)" /><x-admin.form-input name="secondary_cta_url" label="Secondary CTA URL" :value="old('secondary_cta_url', $setting->secondary_cta_url)" /></div>
                <x-admin.form-input name="og_image_path" label="Local Open Graph image path" :value="old('og_image_path', $setting->og_image_path)" />
                <x-admin.form-footer><x-admin.primary-button>Save draft</x-admin.primary-button></x-admin.form-footer>
            </form>
        </x-admin.card>
        <div class="grid content-start gap-4">
            <x-admin.card><p class="text-sm font-semibold">Publication status</p><div class="mt-3"><x-admin.status-badge :status="$setting->status === 'published' ? 'healthy' : 'warning'">{{ ucfirst($setting->status ?? 'draft') }}</x-admin.status-badge></div><p class="mt-3 text-sm text-slate-600">Last updated: {{ $setting->updated_at?->format('Y-m-d H:i') ?? 'Not saved' }}</p></x-admin.card>
            @if($setting->exists)<x-admin.card><div class="flex flex-wrap gap-3">@if($setting->status === 'published')<form method="POST" action="{{ route('admin.homepage.unpublish') }}" onsubmit="return confirm('Unpublish the homepage? Content will be preserved.')">@csrf<x-admin.danger-button>Unpublish</x-admin.danger-button></form>@else<form method="POST" action="{{ route('admin.homepage.publish') }}" onsubmit="return confirm('Publish this homepage now?')">@csrf<x-admin.primary-button>Publish</x-admin.primary-button></form>@endif</div></x-admin.card>@endif
        </div>
    </div>
    <section class="mt-8" aria-labelledby="sections-heading"><h2 id="sections-heading" class="text-xl font-bold text-navy-950">Homepage sections</h2><div class="mt-4 grid gap-3">
        @foreach($sections as $section) @php($definition = $registry[$section->section_key])
            <x-admin.card><div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><div class="flex flex-wrap items-center gap-2"><h3 class="font-semibold text-navy-950">{{ $definition['label'] }}</h3><x-admin.status-badge :status="$section->is_enabled ? 'healthy' : 'neutral'">{{ $section->is_enabled ? 'Enabled' : 'Disabled' }}</x-admin.status-badge></div><p class="mt-1 text-sm text-slate-600">{{ $definition['description'] }}</p><p class="mt-2 text-xs text-slate-500">Order {{ $section->display_order }} · {{ $section->items_count }} active item(s) @if($section->is_enabled && $definition['maximum'] && $section->items_count === 0) · <strong class="text-amber-700">Enabled but empty</strong>@endif</p></div><div class="flex flex-wrap gap-2"><form method="POST" action="{{ route('admin.homepage.sections.move', $section) }}">@csrf<input type="hidden" name="direction" value="up"><x-admin.secondary-button aria-label="Move {{ $definition['label'] }} up">Move up</x-admin.secondary-button></form><form method="POST" action="{{ route('admin.homepage.sections.move', $section) }}">@csrf<input type="hidden" name="direction" value="down"><x-admin.secondary-button aria-label="Move {{ $definition['label'] }} down">Move down</x-admin.secondary-button></form><a class="inline-flex min-h-11 items-center rounded-md bg-blue-600 px-4 text-sm font-semibold text-white focus-visible:ring-2 focus-visible:ring-blue-600" href="{{ route('admin.homepage.section', $section->section_key) }}">Edit</a></div></div></x-admin.card>
        @endforeach
    </div></section>
</x-layouts.app>
