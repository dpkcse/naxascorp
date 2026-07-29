<?php

namespace App\Domain\Pages;

final class PageTemplateRegistry
{
    /** @return array<string, array{label:string,description:string,view:string,sections:array<int,string>,breadcrumb:bool,sidebar:bool}> */
    public static function all(): array
    {
        $sections = PageSectionRegistry::TYPES;
        return [
            'standard' => ['label' => 'Standard', 'description' => 'Balanced corporate content layout.', 'view' => 'pages.templates.standard', 'sections' => $sections, 'breadcrumb' => true, 'sidebar' => false],
            'full_width' => ['label' => 'Full width', 'description' => 'Wide editorial layout.', 'view' => 'pages.templates.full-width', 'sections' => $sections, 'breadcrumb' => true, 'sidebar' => false],
            'sidebar' => ['label' => 'Sidebar', 'description' => 'Content with bounded sibling navigation.', 'view' => 'pages.templates.sidebar', 'sections' => $sections, 'breadcrumb' => true, 'sidebar' => true],
            'landing' => ['label' => 'Landing', 'description' => 'Focused page without breadcrumbs.', 'view' => 'pages.templates.landing', 'sections' => $sections, 'breadcrumb' => false, 'sidebar' => false],
            'contact_ready' => ['label' => 'Contact ready', 'description' => 'Standard layout supporting contact prompts.', 'view' => 'pages.templates.contact-ready', 'sections' => $sections, 'breadcrumb' => true, 'sidebar' => false],
        ];
    }

    public static function view(string $key): string { return self::all()[$key]['view'] ?? self::all()['standard']['view']; }
}
