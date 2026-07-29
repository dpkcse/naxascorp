<?php

namespace App\Http\Requests\Pages;

use App\Domain\Pages\PageSlug;
use App\Domain\Pages\PageTemplateRegistry;
use App\Domain\PublicChrome\PublicAssetPath;
use App\Models\Page;
use App\Models\WebsiteSetting;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePageRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    protected function prepareForValidation(): void { $this->merge(['slug' => PageSlug::normalize((string) $this->input('slug')), 'show_breadcrumb' => $this->boolean('show_breadcrumb'), 'show_title' => $this->boolean('show_title'), 'robots_index' => $this->boolean('robots_index'), 'robots_follow' => $this->boolean('robots_follow')]); }
    /** @return array<string,mixed> */
    public function rules(): array
    {
        $page = $this->route('page');
        $asset = fn (string $attribute, mixed $value, Closure $fail) => PublicAssetPath::isSafe(is_string($value) ? $value : null) ?: $fail('Use an approved local image path.');
        return ['title' => ['required', 'string', 'max:160'], 'slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique(Page::class)->ignore($page), fn (string $attribute, mixed $value, Closure $fail) => PageSlug::isReserved((string) $value) ? $fail('This slug is reserved by the application.') : null],
            'parent_id' => ['nullable', 'integer', Rule::exists(Page::class, 'id')], 'eyebrow' => ['nullable', 'string', 'max:120'], 'summary' => ['nullable', 'string', 'max:500'], 'body' => ['nullable', 'string', 'max:50000'], 'template' => ['required', Rule::in(array_keys(PageTemplateRegistry::all()))],
            'featured_image_path' => ['nullable', 'string', 'max:255', $asset], 'featured_image_alt' => ['nullable', 'required_with:featured_image_path', 'string', 'max:160'], 'show_breadcrumb' => ['boolean'], 'show_title' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:70'], 'meta_description' => ['nullable', 'string', 'max:170'], 'canonical_url' => ['nullable', 'url:http,https', 'max:2048', function (string $attribute, mixed $value, Closure $fail): void { if ($value && ! $this->canonicalMatchesSite((string) $value)) { $fail('The canonical URL must use the configured website host.'); } }],
            'og_title' => ['nullable', 'string', 'max:70'], 'og_description' => ['nullable', 'string', 'max:200'], 'og_image_path' => ['nullable', 'string', 'max:255', $asset], 'robots_index' => ['boolean'], 'robots_follow' => ['boolean']];
    }
    private function canonicalMatchesSite(string $value): bool { $site = WebsiteSetting::query()->value('site_url'); return ! $site || strcasecmp((string) parse_url($value, PHP_URL_HOST), (string) parse_url($site, PHP_URL_HOST)) === 0; }
}
