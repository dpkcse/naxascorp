<?php

namespace App\Domain\Homepage;

use App\Models\HomepageItem;
use App\Models\HomepageSection;
use App\Models\HomepageSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class HomepageManager
{
    public function ensureSections(): void
    {
        foreach (array_keys(HomepageSectionRegistry::all()) as $order => $key) {
            HomepageSection::query()->firstOrCreate(['section_key' => $key], ['display_order' => $order + 1, 'is_enabled' => HomepageSectionRegistry::all()[$key]['default_enabled']]);
        }
    }

    public function move(HomepageSection $section, string $direction): void
    {
        DB::transaction(function () use ($section, $direction): void {
            $sections = HomepageSection::query()->orderBy('display_order')->orderBy('id')->lockForUpdate()->get();
            $index = $sections->search(fn (HomepageSection $candidate): bool => $candidate->is($section));
            $target = $direction === 'up' ? $index - 1 : $index + 1;
            if ($index !== false && isset($sections[$target])) {
                [$sections[$index], $sections[$target]] = [$sections[$target], $sections[$index]];
            }
            foreach ($sections->values() as $order => $orderedSection) {
                $orderedSection->updateQuietly(['display_order' => $order + 1]);
            }
        });
        HomepageCache::forget();
    }

    public function publish(HomepageSetting $settings): void
    {
        $hero = HomepageSection::query()->where('section_key', 'hero')->first();
        if ($hero?->is_enabled && ! $hero->items()->where('item_type', 'hero_slide')->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['publish' => 'Enable at least one valid hero slide before publishing.']);
        }

        $settings->update(['status' => 'published', 'published_at' => now()]);
        HomepageCache::forget();
    }

    public function addItem(HomepageSection $section, array $data): HomepageItem
    {
        $definition = HomepageSectionRegistry::all()[$section->section_key] ?? null;
        if (! $definition || ! $definition['item_type']) {
            throw ValidationException::withMessages(['section' => 'This section does not accept list items.']);
        }
        $requestedType = $data['item_type'] ?? $definition['item_type'];
        $maximum = $requestedType === $definition['secondary_item_type'] ? $definition['secondary_maximum'] : $definition['maximum'];
        if (! in_array($requestedType, array_filter([$definition['item_type'], $definition['secondary_item_type']]), true)) {
            throw ValidationException::withMessages(['item_type' => 'Unsupported homepage item type.']);
        }
        if ($section->items()->where('item_type', $requestedType)->count() >= $maximum) {
            throw ValidationException::withMessages(['items' => "This section supports at most {$maximum} items of this type."]);
        }
        $data['item_type'] = $requestedType;

        return $section->items()->create($data);
    }
}
