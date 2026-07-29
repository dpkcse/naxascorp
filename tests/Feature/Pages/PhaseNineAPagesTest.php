<?php

use App\Domain\Pages\PageHierarchy;
use App\Domain\Pages\PageManager;
use App\Domain\Pages\PageSectionRegistry;
use App\Domain\Pages\PageSlug;
use App\Domain\Pages\PageTemplateRegistry;
use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use App\Models\Page;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\mock;

function phaseNineAdministrator(array $attributes = []): User { return User::factory()->create(array_merge(['is_active' => true, 'email_verified_at' => now()], $attributes)); }
function phaseNineInstalled(): void { mock(\App\Domain\Installation\InstalledState::class)->shouldReceive('isInstalled')->andReturnTrue(); }
function pagePayload(array $overrides = []): array { return array_merge(['title' => 'About Naxora', 'slug' => 'about-naxora', 'template' => 'standard', 'show_breadcrumb' => 1, 'show_title' => 1, 'robots_index' => 1, 'robots_follow' => 1], $overrides); }

test('pages administration create and preview require authentication active administrator and installed state', function () {
    phaseNineInstalled(); $page = Page::factory()->create();
    foreach (['/website/pages', '/website/pages/create', "/website/pages/{$page->id}/preview"] as $url) { $this->get($url)->assertRedirect('/login'); }
    $this->actingAs(phaseNineAdministrator(['is_active' => false]))->get('/website/pages')->assertRedirect('/login');
    $administrator = phaseNineAdministrator(); mock(\App\Domain\Installation\InstalledState::class)->shouldReceive('isInstalled')->andReturnFalse(); $this->actingAs($administrator)->get('/website/pages')->assertRedirect();
});

test('public lifecycle never leaks draft future or archived pages and due schedules render anonymously', function () {
    $draft = Page::factory()->create(['slug' => 'draft-secret']); $future = Page::factory()->create(['slug' => 'future', 'status' => 'scheduled', 'scheduled_for' => now()->addHour()]); $archived = Page::factory()->archived()->create(['slug' => 'old']); $published = Page::factory()->published()->create(['slug' => 'public-company', 'title' => 'Public company']);
    $this->get('/draft-secret')->assertNotFound(); $this->get('/future')->assertNotFound(); $this->get('/old')->assertNotFound(); $this->get('/public-company')->assertSuccessful()->assertSee('Public company');
    $this->travel(2)->hours(); Cache::flush(); $this->get('/future')->assertSuccessful();
});

test('page validation normalizes unique safe slugs and restricts templates assets SEO and canonical host', function () {
    phaseNineInstalled(); $this->actingAs(phaseNineAdministrator()); WebsiteSetting::create(['site_name'=>'Naxora','legal_name'=>'Naxas','primary_email'=>'admin@example.com','country_code'=>'GB','timezone'=>'UTC','site_url'=>'https://example.com']);
    $this->post(route('admin.pages.store'), pagePayload(['slug' => '  About---Us  ']))->assertSessionHasNoErrors(); expect(Page::first()->slug)->toBe('about-us')->and(Page::first()->status)->toBe('draft')->and(Page::first()->published_at)->toBeNull();
    $this->post(route('admin.pages.store'), pagePayload(['slug' => 'about-us']))->assertSessionHasErrors('slug');
    foreach (['install', 'dashboard', 'website', 'settings'] as $slug) { $this->post(route('admin.pages.store'), pagePayload(['slug' => $slug]))->assertSessionHasErrors('slug'); }
    foreach (['../secret', 'nested/path', 'https://evil.test/x'] as $slug) { $this->post(route('admin.pages.store'), pagePayload(['slug' => $slug]))->assertSessionHasErrors('slug'); }
    $this->post(route('admin.pages.store'), pagePayload(['template' => '../../admin']))->assertSessionHasErrors('template');
    $this->post(route('admin.pages.store'), pagePayload(['featured_image_path' => '../secret.png', 'featured_image_alt' => 'Secret']))->assertSessionHasErrors('featured_image_path');
    $this->post(route('admin.pages.store'), pagePayload(['featured_image_path' => 'images/team.webp']))->assertSessionHasErrors('featured_image_alt');
    $this->post(route('admin.pages.store'), pagePayload(['meta_title' => str_repeat('x', 71), 'meta_description' => str_repeat('x', 171), 'canonical_url' => 'https://evil.test/page']))->assertSessionHasErrors(['meta_title', 'meta_description', 'canonical_url']);
    expect(PageSlug::normalize('A---Safe Slug'))->toBe('a-safe-slug')->and(array_keys(PageTemplateRegistry::all()))->toBe(['standard','full_width','sidebar','landing','contact_ready']);
});

test('hierarchy rejects self descendant cycles depth overflow and archive dependencies', function () {
    $manager = app(PageManager::class); $user = phaseNineAdministrator(); $root = Page::factory()->create(); $child = Page::factory()->create(['parent_id'=>$root->id]); $grand = Page::factory()->create(['parent_id'=>$child->id]);
    expect(fn () => app(PageHierarchy::class)->validate($root, $root))->toThrow(ValidationException::class)->and(fn () => app(PageHierarchy::class)->validate($root, $grand))->toThrow(ValidationException::class);
    $extra = Page::factory()->create(); expect(fn () => app(PageHierarchy::class)->validate($extra, $grand))->toThrow(ValidationException::class)->and(fn () => $manager->archive($root))->toThrow(ValidationException::class);
    $grand->update(['status'=>'archived','archived_at'=>now()]); expect(fn () => app(PageHierarchy::class)->validate($extra, $grand))->toThrow(ValidationException::class);
});

test('ordering publication archive restore and duplication are transactional and preserve safe content', function () {
    $manager = app(PageManager::class); $user = phaseNineAdministrator(); $first = Page::factory()->create(['display_order'=>1]); $second = Page::factory()->create(['display_order'=>2]); $first->sections()->create(['section_type'=>'rich_text','body'=>'Safe <script>alert(1)</script>','display_order'=>1]);
    $manager->move($second,'up'); expect($second->fresh()->display_order)->toBe(1)->and($first->fresh()->display_order)->toBe(2);
    $manager->publish($first); expect($first->fresh()->published_at)->not->toBeNull(); $manager->unpublish($first); expect($first->fresh()->status)->toBe('draft')->and($first->body)->toBe($first->fresh()->body);
    $manager->archive($first); expect($first->fresh()->status)->toBe('archived'); $manager->restore($first); expect($first->fresh()->status)->toBe('draft');
    $copy = $manager->duplicate($first,$user->id); expect($copy->status)->toBe('draft')->and($copy->slug)->not->toBe($first->slug)->and($copy->published_at)->toBeNull()->and($copy->sections)->toHaveCount(1);
});

test('preview is uncached noindex protected and renders escaped disabled draft content', function () {
    phaseNineInstalled(); $page = Page::factory()->create(['title'=>'Draft preview','robots_index'=>true]); $page->sections()->create(['section_type'=>'rich_text','body'=>'<script>unsafe()</script>','is_enabled'=>false]); Cache::put('public.pages.sentinel','public copy',60);
    $this->actingAs(phaseNineAdministrator())->get(route('admin.pages.preview',$page))->assertSuccessful()->assertHeader('Cache-Control','no-store, private, max-age=0')->assertHeader('X-Robots-Tag','noindex, nofollow')->assertSee('Preview Mode')->assertSee('&lt;script&gt;unsafe()&lt;/script&gt;',false)->assertDontSee('<script>unsafe()</script>',false);
});

test('sections enforce bounded registries CTA pairs paths alt maximum and public enabled ordering', function () {
    phaseNineInstalled(); $this->actingAs(phaseNineAdministrator()); $page = Page::factory()->published()->create();
    $base=['section_type'=>'rich_text','background_style'=>'default','content_width'=>'standard','is_enabled'=>1];
    $this->post(route('admin.pages.sections.store',$page),$base+['section_type'=>'blade-component'])->assertSessionHasErrors('section_type');
    $this->post(route('admin.pages.sections.store',$page),$base+['primary_cta_label'=>'Go'])->assertSessionHasErrors('primary_cta_url');
    $this->post(route('admin.pages.sections.store',$page),$base+['image_path'=>'https://evil.test/x.png','image_alt'=>'X'])->assertSessionHasErrors('image_path');
    foreach(range(1,20) as $order){$page->sections()->create($base+['heading'=>"Section {$order}",'display_order'=>$order]);}
    $this->post(route('admin.pages.sections.store',$page),$base)->assertSessionHasErrors('section_type'); expect(PageSectionRegistry::MAXIMUM)->toBe(20);
});

test('navigation page references follow slugs disable unpublished destinations and block archive', function () {
    $page=Page::factory()->published()->create(['slug'=>'current-slug']); $menu=NavigationMenu::create(['name'=>'Primary','location'=>'primary']); NavigationItem::create(['navigation_menu_id'=>$menu->id,'label'=>'Company','link_type'=>'page','page_id'=>$page->id,'target'=>'_self']);
    $this->get('/')->assertSee('/current-slug',false); $page->update(['slug'=>'new-slug']); $this->get('/')->assertSee('/new-slug',false)->assertDontSee('/current-slug',false);
    $page->update(['status'=>'draft','published_at'=>null]); $this->get('/')->assertDontSee('href="http://localhost/new-slug"',false); expect(fn()=>app(PageManager::class)->archive($page))->toThrow(ValidationException::class);
});

test('sitemap includes only published indexable pages and public pages make no portal requests or expose secrets', function () {
    Page::factory()->published()->create(['slug'=>'included','robots_index'=>true]); Page::factory()->create(['slug'=>'draft-excluded']); Page::factory()->archived()->create(['slug'=>'archived-excluded']); Http::fake();
    $this->get('/sitemap.xml')->assertSuccessful()->assertSee('/included')->assertDontSee('draft-excluded')->assertDontSee('archived-excluded');
    $html=$this->get('/included')->assertSuccessful()->getContent(); Http::assertNothingSent(); expect($html)->not->toContain('request_token','signed_entitlement','APP_KEY');
});

test('phase nine a adds no later modules roles permissions media or Gumroad', function () {
    expect(Schema::hasTable('roles'))->toBeFalse()->and(Schema::hasTable('permissions'))->toBeFalse()->and(Schema::hasTable('media'))->toBeFalse()->and(Schema::hasTable('products'))->toBeFalse();
});
