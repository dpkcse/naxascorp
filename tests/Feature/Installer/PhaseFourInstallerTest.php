<?php

use App\Domain\Installation\AdministratorLifecycle;
use App\Domain\Installation\EntitlementRevalidator;
use App\Models\LicenseState;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Volt;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

function phaseFourLicense(): LicenseState
{
    return new LicenseState(['installation_uuid' => '13a1a584-e065-4777-a52e-076a6e4a97ab', 'normalized_domain' => 'example.com']);
}

beforeEach(function () {
    mock(AdministratorLifecycle::class)->shouldReceive('hasAdministrator')->andReturnTrue();
});

it('blocks website setup before durable license acknowledgement', function () {
    $this->actingAs(User::factory()->create())->get('/install/website')->assertRedirect(route('installer.license'));
});

it('requires authentication and an active administrator for website setup', function () {
    DB::table('installation_progress')->insert(['key' => 'license_acknowledged', 'completed_at' => now()]);
    $this->get('/install/website')->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create(['is_active' => false]))->get('/install/website')->assertRedirect(route('login'));
});

it('shows all website setup fields', function () {
    DB::table('installation_progress')->insert(['key' => 'license_acknowledged', 'completed_at' => now()]);
    mock(EntitlementRevalidator::class)->shouldReceive('validate')->andReturn(phaseFourLicense());
    $this->actingAs(User::factory()->create())->get('http://example.com/install/website')->assertSuccessful()
        ->assertSeeText('Website name')->assertSeeText('Legal organization name')->assertSeeText('Primary email')
        ->assertSeeText('Primary phone')->assertSeeText('Country code')->assertSeeText('Timezone')
        ->assertSeeText('Default locale')->assertSeeText('Website URL')->assertSeeText('Short tagline');
});

it('validates website settings fields', function (array $changes, string $error) {
    mock(EntitlementRevalidator::class)->shouldReceive('validate')->andReturn(phaseFourLicense());
    Volt::actingAs(User::factory()->create())->test('installer.website')->set(array_key_first($changes), array_values($changes)[0])->call('save')->assertHasErrors($error);
})->with([
    'site name' => [['site_name' => ''], 'site_name'],
    'legal name' => [['legal_name' => ''], 'legal_name'],
    'email' => [['primary_email' => 'invalid'], 'primary_email'],
    'phone' => [['primary_phone' => str_repeat('1', 41)], 'primary_phone'],
    'country' => [['country_code' => 'Bangladesh'], 'country_code'],
    'timezone' => [['timezone' => 'Invalid/Zone'], 'timezone'],
    'locale' => [['default_locale' => 'bn'], 'default_locale'],
    'url' => [['site_url' => 'not-a-url'], 'site_url'],
]);

it('creates one settings record and updates it on repeat submission', function () {
    mock(EntitlementRevalidator::class)->shouldReceive('validate')->andReturn(phaseFourLicense());
    $user = User::factory()->create();
    $component = fn (string $name) => Volt::actingAs($user)->test('installer.website')
        ->set('site_name', $name)->set('legal_name', 'Naxas Innovations Limited')->set('primary_email', 'hello@example.com')
        ->set('primary_phone', '+880 1234')->set('country_code', 'BD')->set('timezone', 'Asia/Dhaka')
        ->set('default_locale', 'en')->set('site_url', 'https://example.com')->call('save');
    $component('First')->assertHasNoErrors();
    $component('Updated')->assertHasNoErrors();
    expect(WebsiteSetting::query()->count())->toBe(1)->and(WebsiteSetting::first()->site_name)->toBe('Updated')
        ->and(WebsiteSetting::first()->getAttributes())->not->toHaveKeys(['request_token', 'signed_entitlement']);
});

it('requires website setup and an explicit demo choice', function () {
    DB::table('installation_progress')->insert(['key' => 'website_settings_saved', 'completed_at' => now()]);
    mock(EntitlementRevalidator::class)->shouldReceive('validate')->andReturn(phaseFourLicense());
    $user = User::factory()->create();
    Volt::actingAs($user)->test('installer.demo-content')->assertRedirect(route('installer.website'));
    WebsiteSetting::query()->create(['site_name'=>'Site','legal_name'=>'Legal','primary_email'=>'a@example.com','country_code'=>'BD','timezone'=>'UTC','default_locale'=>'en','site_url'=>'https://example.com']);
    Volt::actingAs($user)->test('installer.demo-content')->call('continue')->assertHasErrors('choice');
});

it('keeps registration closed and login and dashboard available', function () {
    $user = User::factory()->create();
    $this->get('/register')->assertRedirect(route('login'));
    $this->get('/login')->assertSuccessful();
    $this->actingAs($user)->get('/dashboard')->assertSuccessful();
});
