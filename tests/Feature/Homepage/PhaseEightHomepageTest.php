<?php

use App\Domain\Homepage\HomepageCache;
use App\Domain\Homepage\HomepageManager;
use App\Domain\Homepage\HomepageSectionRegistry;
use App\Models\HomepageItem;
use App\Models\HomepageSection;
use App\Models\HomepageSetting;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\mock;

function phaseEightAdministrator(array $attributes = []): User
{
    return User::factory()->create(array_merge(['is_active' => true, 'email_verified_at' => now()], $attributes));
}

function phaseEightInstalled(): void
{
    mock(\App\Domain\Installation\InstalledState::class)->shouldReceive('isInstalled')->andReturnTrue();
}

function phaseEightSections(): void
{
    app(HomepageManager::class)->ensureSections();
}

test('homepage administration and preview require authentication active administrator and installed state', function () {
    phaseEightInstalled();
    $this->get('/website/homepage')->assertRedirect('/login');
    $this->get('/website/homepage/preview')->assertRedirect('/login');
    $this->actingAs(phaseEightAdministrator(['is_active' => false]))->get('/website/homepage')->assertRedirect('/login');

    $administrator = phaseEightAdministrator();
    mock(\App\Domain\Installation\InstalledState::class)->shouldReceive('isInstalled')->andReturnFalse();
    $this->actingAs($administrator)->get('/website/homepage')->assertRedirect();
});

test('public homepage stays anonymous and unpublished drafts never leak', function () {
    HomepageSetting::create(['title' => 'Secret draft', 'description' => 'Private copy']);
    Http::fake();
    $this->get('/')->assertSuccessful()->assertSee('Website content is being prepared')->assertDontSee('Secret draft');
    Http::assertNothingSent();
});

test('published homepage renders enabled sections in order and hides disabled sections', function () {
    HomepageSetting::create(['title' => 'Published company', 'status' => 'published', 'published_at' => now()]);
    HomepageSection::create(['section_key' => 'capabilities', 'display_order' => 2, 'is_enabled' => true, 'heading' => 'Our capabilities']);
    HomepageSection::create(['section_key' => 'trust_strip', 'display_order' => 1, 'is_enabled' => true, 'heading' => 'Trusted statements']);
    HomepageSection::create(['section_key' => 'industries', 'display_order' => 3, 'is_enabled' => false, 'heading' => 'Hidden industries']);
    $response = $this->get('/')->assertSuccessful()->assertSee('Published company')->assertDontSee('Hidden industries');
    expect(strpos($response->getContent(), 'Trusted statements'))->toBeLessThan(strpos($response->getContent(), 'Our capabilities'));
});

test('section registry is fixed and database uniqueness rejects duplicate singleton sections', function () {
    expect(array_keys(HomepageSectionRegistry::all()))->toBe(['hero', 'trust_strip', 'about', 'featured_solutions', 'featured_products', 'capabilities', 'industries', 'process', 'clients', 'statistics', 'testimonials', 'insights', 'faq', 'bottom_cta']);
    HomepageSection::create(['section_key' => 'hero', 'display_order' => 1]);
    expect(fn () => HomepageSection::create(['section_key' => 'hero', 'display_order' => 2]))->toThrow(QueryException::class);
});

test('draft save keeps singleton unpublished and publish and unpublish preserve content', function () {
    phaseEightInstalled();
    $this->actingAs(phaseEightAdministrator());
    $payload = ['title' => 'Draft homepage', 'primary_cta_label' => 'Contact', 'primary_cta_url' => '/contact'];
    $this->put(route('admin.homepage.update'), $payload)->assertSessionHasNoErrors();
    $this->put(route('admin.homepage.update'), $payload + ['description' => 'Updated'])->assertSessionHasNoErrors();
    expect(HomepageSetting::count())->toBe(1)->and(HomepageSetting::first()->status)->toBe('draft')->and(HomepageSetting::first()->published_at)->toBeNull();
    phaseEightSections();
    HomepageSection::where('section_key', 'hero')->update(['is_enabled' => false]);
    $this->post(route('admin.homepage.publish'))->assertSessionHasNoErrors();
    expect(HomepageSetting::first()->status)->toBe('published')->and(HomepageSetting::first()->published_at)->not->toBeNull();
    $this->post(route('admin.homepage.unpublish'))->assertSessionHasNoErrors();
    expect(HomepageSetting::first()->status)->toBe('draft')->and(HomepageSetting::first()->title)->toBe('Draft homepage');
});

test('enabled hero requires an active slide and hero validation is safe', function () {
    phaseEightInstalled();
    $this->actingAs(phaseEightAdministrator());
    HomepageSetting::create(['title' => 'Homepage']);
    phaseEightSections();
    $hero = HomepageSection::where('section_key', 'hero')->firstOrFail();
    $this->post(route('admin.homepage.publish'))->assertSessionHasErrors('publish');
    $this->post(route('admin.homepage.items.store', $hero), ['display_order' => 0, 'is_active' => 1])->assertSessionHasErrors('title');
    $this->post(route('admin.homepage.items.store', $hero), ['title' => 'Unsafe', 'image_path' => 'https://example.com/x.png', 'image_alt' => 'X', 'display_order' => 0, 'is_active' => 1])->assertSessionHasErrors('image_path');
    $this->post(route('admin.homepage.items.store', $hero), ['title' => 'Missing alt', 'image_path' => 'images/hero.webp', 'display_order' => 0, 'is_active' => 1])->assertSessionHasErrors('image_alt');
    $this->post(route('admin.homepage.items.store', $hero), ['title' => 'Bad CTA', 'primary_cta_label' => 'Go', 'display_order' => 0, 'is_active' => 1])->assertSessionHasErrors('primary_cta_url');
    $this->post(route('admin.homepage.items.store', $hero), ['title' => 'Valid hero', 'display_order' => 0, 'is_active' => 1])->assertSessionHasNoErrors();
    $this->post(route('admin.homepage.items.store', $hero), ['title' => 'Second hero', 'display_order' => 1, 'is_active' => 1])->assertSessionHasNoErrors();
    $this->post(route('admin.homepage.publish'))->assertSessionHasNoErrors();
    $this->get('/')->assertSee('Previous hero slide', false)->assertSee('Next hero slide', false)->assertDontSee('setInterval', false);
});

test('item limits icon rating URLs statistics and FAQ markup are enforced', function () {
    phaseEightInstalled();
    $this->actingAs(phaseEightAdministrator());
    phaseEightSections();
    $capabilities = HomepageSection::where('section_key', 'capabilities')->firstOrFail();
    $this->post(route('admin.homepage.items.store', $capabilities), ['title' => 'Bad icon', 'icon' => 'raw-svg', 'display_order' => 0])->assertSessionHasErrors('icon');
    $testimonial = HomepageSection::where('section_key', 'testimonials')->firstOrFail();
    $this->post(route('admin.homepage.items.store', $testimonial), ['title' => 'Person', 'description' => str_repeat('x', 1001), 'rating' => 6, 'display_order' => 0])->assertSessionHasErrors(['description', 'rating']);
    $client = HomepageSection::where('section_key', 'clients')->firstOrFail();
    $this->post(route('admin.homepage.items.store', $client), ['title' => 'Client', 'primary_cta_label' => 'Website', 'primary_cta_url' => 'javascript:alert(1)', 'display_order' => 0])->assertSessionHasErrors('primary_cta_url');
    $statistic = HomepageSection::where('section_key', 'statistics')->firstOrFail();
    $this->post(route('admin.homepage.items.store', $statistic), ['title' => 'Exact', 'value' => '12.50', 'display_order' => 0, 'is_active' => 1])->assertSessionHasNoErrors();
    expect(HomepageItem::where('item_type', 'statistic')->first()->value)->toBe('12.50');
    HomepageSetting::create(['title' => 'Published', 'status' => 'published']);
    $faq = HomepageSection::where('section_key', 'faq')->firstOrFail();
    $faq->update(['is_enabled' => true, 'heading' => 'Questions']);
    $faq->items()->create(['item_type' => 'faq', 'title' => 'Question?', 'description' => 'Answer.', 'is_active' => true]);
    HomepageCache::forget();
    $this->get('/')->assertSee('aria-expanded', false)->assertSee('aria-controls="faq-panel-', false);
});

test('preview bypasses public cache and identifies preview mode', function () {
    phaseEightInstalled();
    $this->actingAs(phaseEightAdministrator());
    HomepageSetting::create(['title' => 'Draft preview']);
    Cache::put(HomepageCache::PUBLISHED, ['settings' => ['title' => 'Cached public'], 'sections' => []], 60);
    $this->get(route('admin.homepage.preview'))->assertSuccessful()->assertSee('Preview Mode')->assertSee('Draft preview')->assertDontSee('Cached public');
});

test('reordering normalizes contiguous positions and leaf deletion requires the CSRF mutation route', function () {
    phaseEightInstalled();
    $this->actingAs(phaseEightAdministrator());
    phaseEightSections();
    $second = HomepageSection::orderBy('display_order')->skip(1)->firstOrFail();
    $this->post(route('admin.homepage.sections.move', $second), ['direction' => 'up'])->assertSessionHasNoErrors();
    expect(HomepageSection::orderBy('display_order')->pluck('display_order')->all())->toBe(range(1, 14));
    $item = $second->items()->create(['item_type' => HomepageSectionRegistry::all()[$second->section_key]['item_type'], 'title' => 'Leaf']);
    $this->from(route('admin.homepage.section', $second->section_key))->delete(route('admin.homepage.items.destroy', [$second, $item]))->assertRedirect();
    expect($item->fresh())->toBeNull();
});

test('cache payload contains only transformed public fields and no secrets', function () {
    HomepageSetting::create(['title' => 'Safe', 'status' => 'published']);
    $this->get('/')->assertSuccessful();
    $payload = json_encode(Cache::get(HomepageCache::PUBLISHED));
    expect($payload)->not->toContain('request_token', 'signed_entitlement', 'APP_KEY', 'administrator', 'password');
});
