<?php

namespace App\Http\Requests\Pages;

use App\Domain\Pages\PageSectionRegistry;
use App\Domain\PublicChrome\PublicAssetPath;
use App\Domain\PublicChrome\PublicLink;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSectionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    protected function prepareForValidation(): void { $this->merge(['is_enabled' => $this->boolean('is_enabled')]); }
    /** @return array<string,mixed> */
    public function rules(): array
    {
        $safeUrl = fn (string $attribute, mixed $value, Closure $fail) => $value && ! PublicLink::isSafeUrl((string) $value) ? $fail('Use a safe relative or HTTP/HTTPS URL.') : null;
        return ['section_type' => ['required', Rule::in(PageSectionRegistry::TYPES)], 'heading' => ['nullable', 'string', 'max:160'], 'eyebrow' => ['nullable', 'string', 'max:120'], 'body' => ['nullable', 'string', 'max:20000'],
            'image_path' => ['nullable', 'string', 'max:255', fn (string $attribute, mixed $value, Closure $fail) => PublicAssetPath::isSafe(is_string($value) ? $value : null) ?: $fail('Use an approved local image path.')], 'image_alt' => ['nullable', 'required_with:image_path', 'string', 'max:160'],
            'primary_cta_label' => ['nullable', 'required_with:primary_cta_url', 'string', 'max:80'], 'primary_cta_url' => ['nullable', 'required_with:primary_cta_label', 'string', 'max:2048', $safeUrl], 'secondary_cta_label' => ['nullable', 'required_with:secondary_cta_url', 'string', 'max:80'], 'secondary_cta_url' => ['nullable', 'required_with:secondary_cta_label', 'string', 'max:2048', $safeUrl],
            'background_style' => ['required', Rule::in(PageSectionRegistry::BACKGROUNDS)], 'content_width' => ['required', Rule::in(PageSectionRegistry::WIDTHS)], 'is_enabled' => ['boolean']];
    }
}
