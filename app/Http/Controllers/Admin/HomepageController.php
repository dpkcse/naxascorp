<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Homepage\HomepageCache;
use App\Domain\Homepage\HomepageManager;
use App\Domain\Homepage\HomepageSectionRegistry;
use App\Domain\Homepage\HomepageViewData;
use App\Domain\PublicChrome\PublicAssetPath;
use App\Domain\PublicChrome\PublicLink;
use App\Http\Controllers\Controller;
use App\Models\HomepageItem;
use App\Models\Client;
use App\Models\Testimonial;
use App\Models\Statistic;
use App\Models\Capability;
use App\Models\WorkProcess;
use App\Models\Industry;
use App\Models\HomepageSection;
use App\Models\HomepageSetting;
use App\Models\Product;
use App\Models\Solution;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HomepageController extends Controller
{
    public function edit(HomepageManager $manager): View
    {
        $manager->ensureSections();

        return view('admin.homepage.edit', $this->adminData());
    }

    public function section(string $section, HomepageManager $manager): View
    {
        $manager->ensureSections();
        $definition = HomepageSectionRegistry::all()[$section] ?? abort(404);
        $record = HomepageSection::query()->where('section_key', $section)->with(['items' => fn ($query) => $query->orderBy('display_order')->orderBy('id')])->firstOrFail();

        $products = Product::query()->whereNull('archived_at')->orderBy('title')->get(['id', 'title', 'status']);

        $industries = Industry::query()->whereNull('archived_at')->orderBy('title')->get(['id', 'title', 'status']);

        $capabilities = Capability::query()->whereNull('archived_at')->orderBy('title')->get(['id','title','status']);
        $workProcesses = WorkProcess::query()->whereNull('archived_at')->orderBy('title')->get(['id','title','status']);

        $clients = Client::query()->whereNull('archived_at')->orderBy('name')->get(['id', 'name', 'status']);
        $testimonials = Testimonial::query()->whereNull('archived_at')->orderBy('person_name')->get(['id', 'person_name', 'status']);
        $statistics = Statistic::query()->whereNull('archived_at')->orderBy('label')->get(['id', 'label', 'value', 'status']);

        $solutions = Solution::query()->whereNull('archived_at')->orderBy('title')->get(['id', 'title', 'status']);

        return view('admin.homepage.section', compact('definition', 'record', 'products', 'solutions', 'industries', 'capabilities', 'workProcesses', 'clients', 'testimonials', 'statistics'));
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $data = $request->validate($this->settingsRules());
        $this->validatePairs($data);
        $this->validateAsset($data, 'og_image_path', null);
        $data['status'] = 'draft';
        $data['published_at'] = null;
        HomepageSetting::query()->updateOrCreate(['singleton_key' => 1], $data);
        HomepageCache::forget();

        return back()->with('status', 'Homepage draft saved.');
    }

    public function publish(HomepageManager $manager): RedirectResponse
    {
        $settings = HomepageSetting::query()->where('singleton_key', 1)->firstOrFail();
        $manager->publish($settings);

        return back()->with('status', 'Homepage published.');
    }

    public function unpublish(): RedirectResponse
    {
        HomepageSetting::query()->where('singleton_key', 1)->update(['status' => 'draft', 'published_at' => null]);
        HomepageCache::forget();

        return back()->with('status', 'Homepage unpublished. Content was preserved.');
    }

    public function saveSection(Request $request, HomepageSection $section): RedirectResponse
    {
        $this->guardSection($section);
        $data = $request->validate([
            'work_process_id' => [$section->section_key === 'process' ? 'nullable' : 'prohibited', Rule::exists(WorkProcess::class, 'id')->whereNull('archived_at')], 'is_enabled' => ['sometimes', 'boolean'], 'eyebrow' => ['nullable', 'string', 'max:120'], 'heading' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'], 'secondary_description' => ['nullable', 'string', 'max:2000'],
            'image_path' => ['nullable', 'string', 'max:255'], 'image_alt' => ['nullable', 'string', 'max:180'],
            'primary_cta_label' => ['nullable', 'string', 'max:80'], 'primary_cta_url' => ['nullable', 'string', 'max:2048'],
            'secondary_cta_label' => ['nullable', 'string', 'max:80'], 'secondary_cta_url' => ['nullable', 'string', 'max:2048'],
            'background_style' => ['required', Rule::in(HomepageSectionRegistry::BACKGROUNDS)], 'content_width' => ['required', Rule::in(HomepageSectionRegistry::WIDTHS)],
        ]);
        $data['is_enabled'] = $request->boolean('is_enabled');
        $this->validatePairs($data);
        $this->validateAsset($data, 'image_path', 'image_alt');
        $section->update($data);

        return back()->with('status', 'Section saved as draft content.');
    }

    public function move(Request $request, HomepageSection $section, HomepageManager $manager): RedirectResponse
    {
        $this->guardSection($section);
        $manager->move($section, $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction']);

        return back()->with('status', 'Section order updated.');
    }

    public function storeItem(Request $request, HomepageSection $section, HomepageManager $manager): RedirectResponse
    {
        $this->guardSection($section);
        $data = $request->validate($this->itemRules($section));
        $data['is_active'] = $request->boolean('is_active');
        $this->validatePairs($data);
        $this->validateAsset($data, 'image_path', 'image_alt');
        $this->validateAsset($data, 'mobile_image_path', 'image_alt');
        $manager->addItem($section, $data);

        return back()->with('status', 'Homepage item created.');
    }

    public function destroyItem(HomepageSection $section, HomepageItem $item): RedirectResponse
    {
        $this->guardSection($section);
        abort_unless($item->homepage_section_id === $section->id, 404);
        $item->delete();

        return back()->with('status', 'Homepage item deleted. Referenced files were not removed.');
    }

    public function preview(HomepageViewData $homepage): Response
    {
        return response()->view('homepage', ['homepage' => $homepage->preview(), 'preview' => true] + app(\App\Http\Controllers\PublicSiteController::class)->sharedViewData(request()))
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /** @return array<string, mixed> */
    private function adminData(): array
    {
        return ['setting' => HomepageSetting::query()->first() ?? new HomepageSetting, 'sections' => HomepageSection::query()->withCount(['items' => fn ($query) => $query->where('is_active', true)])->orderBy('display_order')->get(), 'registry' => HomepageSectionRegistry::all()];
    }

    /** @return array<string, array<int, mixed>> */
    private function settingsRules(): array
    {
        return ['eyebrow' => ['nullable', 'string', 'max:120'], 'title' => ['required', 'string', 'max:180'], 'highlighted_text' => ['nullable', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:500'], 'primary_cta_label' => ['nullable', 'string', 'max:80'], 'primary_cta_url' => ['nullable', 'string', 'max:2048'], 'secondary_cta_label' => ['nullable', 'string', 'max:80'], 'secondary_cta_url' => ['nullable', 'string', 'max:2048'], 'og_image_path' => ['nullable', 'string', 'max:255']];
    }

    /** @return array<string, array<int, mixed>> */
    private function itemRules(HomepageSection $section): array
    {
        $definition = HomepageSectionRegistry::all()[$section->section_key];
        $type = $definition['item_type'];
        return [
            'client_id' => [$section->section_key === 'clients' ? 'nullable' : 'prohibited', 'integer', Rule::exists(Client::class, 'id')->whereNull('archived_at')],
            'testimonial_id' => [$section->section_key === 'testimonials' ? 'nullable' : 'prohibited', 'integer', Rule::exists(Testimonial::class, 'id')->whereNull('archived_at')],
            'statistic_id' => [$section->section_key === 'statistics' ? 'nullable' : 'prohibited', 'integer', Rule::exists(Statistic::class, 'id')->whereNull('archived_at')],
            'capability_id' => [$section->section_key === 'capabilities' ? 'nullable' : 'prohibited', 'integer', Rule::exists(Capability::class, 'id')->whereNull('archived_at')],
            'industry_id' => [$section->section_key === 'industries' ? 'nullable' : 'prohibited', 'integer', Rule::exists(Industry::class, 'id')->whereNull('archived_at')],
            'product_id' => [$section->section_key === 'featured_products' ? 'nullable' : 'prohibited', 'integer', Rule::exists(Product::class, 'id')->whereNull('archived_at')],
            'solution_id' => [$section->section_key === 'featured_solutions' ? 'nullable' : 'prohibited', 'integer', Rule::exists(Solution::class, 'id')->whereNull('archived_at')],
            'item_type' => ['nullable', Rule::in(array_filter([$type, $definition['secondary_item_type']]))], 'title' => ['required', 'string', 'max:180'], 'eyebrow' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:'.($type === 'testimonial' ? 1000 : 1200)], 'secondary_text' => ['nullable', 'string', 'max:500'],
            'highlighted_text' => ['nullable', 'string', 'max:120'], 'icon' => ['nullable', Rule::in(HomepageSectionRegistry::ICONS)], 'badge' => ['nullable', 'string', 'max:60'],
            'image_path' => ['nullable', 'string', 'max:255'], 'mobile_image_path' => ['nullable', 'string', 'max:255'], 'image_alt' => ['nullable', 'string', 'max:180'],
            'primary_cta_label' => ['nullable', 'string', 'max:80'], 'primary_cta_url' => ['nullable', 'string', 'max:2048'], 'secondary_cta_label' => ['nullable', 'string', 'max:80'], 'secondary_cta_url' => ['nullable', 'string', 'max:2048'],
            'organization' => ['nullable', 'string', 'max:160'], 'value' => [Rule::requiredIf($type === 'statistic'), 'nullable', 'string', 'max:80', 'not_regex:/[<>]/'],
            'prefix' => ['nullable', 'string', 'max:20', 'not_regex:/[<>]/'], 'suffix' => ['nullable', 'string', 'max:20', 'not_regex:/[<>]/'], 'rating' => ['nullable', 'integer', 'between:1,5'],
            'published_on' => ['nullable', 'date'], 'display_order' => ['required', 'integer', 'between:0,999'], 'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /** @param array<string, mixed> $data */
    private function validatePairs(array $data): void
    {
        foreach (['primary', 'secondary'] as $name) {
            $label = filled($data[$name.'_cta_label'] ?? null);
            $url = filled($data[$name.'_cta_url'] ?? null);
            if ($label !== $url || ($url && ! PublicLink::isSafeUrl($data[$name.'_cta_url']))) {
                throw ValidationException::withMessages([$name.'_cta_url' => 'CTA label and a safe relative or HTTP/HTTPS URL are both required.']);
            }
        }
    }

    /** @param array<string, mixed> $data */
    private function validateAsset(array $data, string $pathKey, ?string $altKey): void
    {
        if (filled($data[$pathKey] ?? null) && ! PublicAssetPath::isSafe($data[$pathKey])) {
            throw ValidationException::withMessages([$pathKey => 'Use an approved local image path.']);
        }
        if (filled($data[$pathKey] ?? null) && $altKey && blank($data[$altKey] ?? null)) {
            throw ValidationException::withMessages([$altKey => 'Alt text is required when an image is provided.']);
        }
    }

    private function guardSection(HomepageSection $section): void
    {
        abort_unless(isset(HomepageSectionRegistry::all()[$section->section_key]), 404);
    }
}
