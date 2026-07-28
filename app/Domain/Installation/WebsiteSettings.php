<?php

namespace App\Domain\Installation;

use App\Models\WebsiteSetting;

class WebsiteSettings
{
    public function current(): ?WebsiteSetting
    {
        return WebsiteSetting::query()->where('singleton_key', 1)->first();
    }

    public function siteName(): string
    {
        return $this->current()?->site_name ?? (string) config('app.name');
    }
}
