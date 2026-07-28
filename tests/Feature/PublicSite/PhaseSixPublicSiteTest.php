<?php

use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;

function publicWebsiteSettings(array $overrides = []): WebsiteSetting
{
    return WebsiteSetting::query()->create(array_merge([
        'site_name' => 'Configured Corporate Site',
        'legal_name' => 'Configured Legal Organization',
        'tagline' => 'A configured public description',
        'primary_email' => 'contact@example.com',
        'primary_phone' => '+880 123 456 789',
        'country_code' => 'BD',
        'timezone' => 'Asia/Dhaka',
        'default_locale' => 'en',
        'site_url' => 'https://example.com/path/',
    ], $overrides));
}

test('public root is accessible without authentication and uses safe fallbacks', function () {
    $response = $this->get('/');

    $response->assertSuccessful()->assertSeeText('Naxora CMS')->assertSeeText('Naxas Innovations Limited')
        ->assertSee('Skip to main content')->assertSee('id="main-content"', false)
        ->assertSee('<header', false)->assertSee('<nav', false)->assertSee('<main', false)->assertSee('<footer', false)
        ->assertDontSee('data-public-top-bar')->assertDontSee('noindex, nofollow');
});

test('installed settings safely brand the public shell and contact areas', function () {
    publicWebsiteSettings();

    $response = $this->get('http://example.com/');

    $response->assertSuccessful()->assertSeeText('Configured Corporate Site')->assertSeeText('Configured Legal Organization')
        ->assertSee('mailto:contact@example.com', false)->assertSee('tel:+880123456789', false)
        ->assertSee('data-public-top-bar', false)->assertSee('lang="en"', false);
});

test('missing optional contact values are hidden cleanly', function () {
    publicWebsiteSettings(['primary_phone' => null]);

    $this->get('/')->assertSuccessful()->assertDontSee('href="tel:', false)->assertSee('mailto:contact@example.com', false);
});

test('navigation exposes only home and honestly disables future destinations', function () {
    $response = $this->get('/');

    $response->assertSee('aria-label="Primary navigation"', false)->assertSee('aria-current="page"', false)
        ->assertSeeText('Company')->assertSeeText('Coming soon')->assertSee('aria-disabled="true"', false)
        ->assertDontSee('href="/solutions"', false)->assertSee('aria-label="Open navigation"', false)
        ->assertSee('aria-controls="mobile-navigation"', false)->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false)->assertSee('x-on:click.self="close()"', false);
});

test('public metadata is unique escaped canonical and indexable', function () {
    publicWebsiteSettings(['site_name' => 'Safe <Site>', 'tagline' => 'Safe "description"']);
    $html = $this->get('http://example.com/')->assertSuccessful()->getContent();

    expect(substr_count($html, '<title>'))->toBe(1)
        ->and($html)->toContain('Safe &lt;Site&gt;', 'Safe &quot;description&quot;', '<link rel="canonical" href="https://example.com/path/">', 'property="og:title"', 'name="twitter:card" content="summary"', 'content="index, follow"')
        ->not->toContain('fonts.googleapis.com', 'fonts.bunny.net', 'http://placehold');
});

test('public rendering performs no outbound license portal request', function () {
    Http::fake();

    $this->get('/')->assertSuccessful();

    Http::assertNothingSent();
});

test('public primitive components render accessible foundations', function () {
    $breadcrumb = Blade::render('<x-public.breadcrumb :items="[[\'label\' => \'Preview\']]" />');
    $heading = Blade::render('<x-public.section-heading title="Section title" description="Description" />');
    $link = Blade::render('<x-public.button href="/">Link action</x-public.button>');
    $button = Blade::render('<x-public.button type="submit">Button action</x-public.button>');
    $disabled = Blade::render('<x-public.button href="/unsafe" disabled>Disabled</x-public.button>');
    $card = Blade::render('<x-public.card title="Card title" description="Card description" />');
    $cta = Blade::render('<x-public.cta heading="CTA heading"><x-slot:primary>Action</x-slot:primary></x-public.cta>');
    $empty = Blade::render('<x-public.empty-state title="No content" description="Coming soon" />');
    $alert = Blade::render('<x-public.alert type="error">Important error</x-public.alert>');

    expect($breadcrumb)->toContain('aria-label="Breadcrumb"', 'aria-current="page"')
        ->and($heading)->toContain('Section title', '<h2')
        ->and($link)->toContain('<a href="/"', 'Link action')
        ->and($button)->toContain('<button type="submit"')
        ->and($disabled)->toContain('<button', 'disabled')->not->toContain('/unsafe')
        ->and($card)->toContain('Card title', 'Card description')
        ->and($cta)->toContain('CTA heading', 'Action')
        ->and($empty)->toContain('No content', 'Coming soon')
        ->and($alert)->toContain('role="alert"', 'Important error');
});

test('public form controls expose labels helpers errors and validation summary', function () {
    $this->withViewErrors(['email' => 'The email is invalid.']);
    $input = Blade::render('<x-public.form.input name="email" type="email" label="Email" required helper="Use work email" />');
    $summary = Blade::render('<x-public.validation-summary :errors="[\'The email is invalid.\']" />');

    expect($input)->toContain('for="email"', 'aria-invalid="true"', 'email-error', 'required')
        ->and($summary)->toContain('role="alert"', 'validation-summary-title', 'The email is invalid.');
});

test('responsive image enforces alt and layout shift safeguards', function () {
    expect(fn () => Blade::render('<x-public.image src="/image.webp" width="100" height="50" />'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => Blade::render('<x-public.image src="/image.webp" alt="Safe image" />'))->toThrow(InvalidArgumentException::class);

    $image = Blade::render('<x-public.image src="/image.webp" alt="Safe image" width="100" height="50" />');
    $decorative = Blade::render('<x-public.image src="/shape.svg" decorative aspect="aspect-square" />');

    expect($image)->toContain('alt="Safe image"', 'width="100"', 'height="50"', 'loading="lazy"', 'decoding="async"')
        ->and($decorative)->toContain('alt=""', 'role="presentation"');
});

test('phase six does not introduce content roles or permissions routes', function () {
    $routes = file_get_contents(base_path('routes/web.php'));
    $config = file_get_contents(config_path('public-site.php'));

    expect($routes.$config)->not->toContain('roles', 'permissions', 'Gumroad', 'solutions.store', 'navigation.store');
});
