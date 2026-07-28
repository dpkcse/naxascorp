@props(['settings' => null, 'siteName' => 'Naxora CMS', 'legalName' => 'Naxas Innovations Limited', 'navigation' => [], 'pageTitle' => 'Naxora — Premium Corporate Website CMS', 'description' => 'A premium corporate website foundation.', 'canonical' => null, 'robots' => 'index, follow', 'image' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $settings?->default_locale ?: app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $description }}"><meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonical ?: url()->current() }}">
    <meta property="og:type" content="website"><meta property="og:site_name" content="{{ $siteName }}"><meta property="og:title" content="{{ $pageTitle }}"><meta property="og:description" content="{{ $description }}"><meta property="og:url" content="{{ $canonical ?: url()->current() }}">
    @if($image)<meta property="og:image" content="{{ $image }}">@endif
    <meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}"><meta name="theme-color" content="#081a33">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-public-background font-sans text-public-text antialiased">
<a href="#main-content" class="fixed start-4 top-4 z-[100] -translate-y-24 rounded-md bg-white px-4 py-3 font-semibold text-public-navy shadow-lg focus:translate-y-0 focus:outline-none focus:ring-2 focus:ring-public-focus">Skip to main content</a>
<div id="public-announcements" aria-live="polite" aria-atomic="true"></div>
<x-public.top-bar :settings="$settings" /><x-public.header :site-name="$siteName" :navigation="$navigation" />
<main id="main-content" tabindex="-1">{{ $slot }}</main>
<x-public.footer :settings="$settings" :site-name="$siteName" :legal-name="$legalName" :navigation="$navigation" />
<div id="public-alerts" class="pointer-events-none fixed inset-x-4 bottom-4 z-50 mx-auto max-w-md" role="region" aria-label="Notifications" aria-live="polite"></div>
@fluxScripts
</body></html>
