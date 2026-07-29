<?php

namespace App\Domain\Pages;

final class PageSectionRegistry
{
    public const TYPES = ['rich_text', 'text_image', 'feature_grid', 'statistic_strip', 'quote', 'cta', 'contact_prompt'];
    public const BACKGROUNDS = ['default', 'alternate', 'navy', 'gradient'];
    public const WIDTHS = ['narrow', 'standard', 'wide'];
    public const MAXIMUM = 20;
}
