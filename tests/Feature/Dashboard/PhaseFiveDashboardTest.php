<?php

use App\Domain\Installation\EntitlementRevalidator;
use App\Domain\Installation\InstalledState;
use App\Models\LicenseState;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

beforeEach(function () {
    mock(InstalledState::class)->shouldReceive('isInstalled')->andReturnTrue();
});

function phaseFiveLicenseState(): LicenseState
{
    return new LicenseState([
        'product_slug' => 'naxora-cms',
        'license_type' => 'single_site',
        'normalized_domain' => 'example.com',
        'license_status' => 'active',
        'acknowledged_at' => now(),
    ]);
}

test('dashboard requires authentication', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('dashboard requires an active administrator', function () {
    $this->actingAs(User::factory()->create(['is_active' => false]))
        ->get(route('dashboard'))->assertRedirect(route('login'));
});

test('dashboard requires installed state', function () {
    mock(InstalledState::class)->shouldReceive('isInstalled')->andReturnFalse();

    $this->actingAs(User::factory()->create())->get(route('dashboard'))
        ->assertRedirect(route('installer.welcome'));
});

test('active installed administrator sees premium safe dashboard data', function () {
    $administrator = User::factory()->create(['name' => 'Naxora Administrator', 'email' => 'admin@example.com']);
    WebsiteSetting::query()->create([
        'site_name' => 'Naxora Corporate', 'legal_name' => 'Naxas Innovations Limited',
        'primary_email' => 'hello@example.com', 'country_code' => 'BD', 'timezone' => 'Asia/Dhaka',
        'default_locale' => 'en', 'site_url' => 'https://example.com',
    ]);
    mock(EntitlementRevalidator::class)->shouldReceive('validate')->once()->andReturn(phaseFiveLicenseState());

    $this->actingAs($administrator)->get('http://example.com/dashboard')->assertSuccessful()
        ->assertSeeText('Naxora CMS')->assertSeeText('Naxas Innovations Limited')
        ->assertSeeText('Naxora Corporate')->assertSeeText('Asia/Dhaka')->assertSeeText('Active')
        ->assertSeeText('Naxora Administrator')->assertSeeText(config('app.version', '1.0.0'))
        ->assertSee('Skip to main content')->assertSee('id="main-content"', false)
        ->assertSee('aria-label="Breadcrumb"', false)->assertSee('aria-label="Open navigation"', false)
        ->assertSeeText('No recent activity available')->assertSeeText('CMS modules pending')
        ->assertDontSee('encrypted_request_token')->assertDontSee('encrypted_signed_entitlement')
        ->assertDontSee((string) config('database.connections.mysql.password'));
});

test('navigation links only existing destinations and disables future modules', function () {
    mock(EntitlementRevalidator::class)->shouldReceive('validate')->andReturn(phaseFiveLicenseState());
    $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

    $response->assertSee('href="'.route('settings.profile').'"', false)
        ->assertSee('href="'.route('license.status').'"', false)
        ->assertSee('aria-disabled="true"', false)
        ->assertSeeText('Media Library')->assertDontSee('href="/pages"', false);
});

test('authentication settings license routes remain protected and registration remains closed', function () {
    $this->get(route('login'))->assertSuccessful();
    $this->get(route('register'))->assertRedirect(route('login'));

    $administrator = User::factory()->create();
    $this->actingAs($administrator)->get(route('settings.profile'))->assertSuccessful();
    $this->actingAs($administrator)->get(route('license.status'))->assertSuccessful();
    $this->actingAs($administrator)->get(route('license.diagnostics'))->assertSuccessful();
});

test('reusable admin primitives render accessible foundations', function () {
    $card = Blade::render('<x-admin.card title="Card heading">Card body</x-admin.card>');
    $badge = Blade::render('<x-admin.status-badge status="warning">Needs review</x-admin.status-badge>');
    $alert = Blade::render('<x-admin.alert type="error">Error details</x-admin.alert>');
    $empty = Blade::render('<x-admin.empty-state title="Nothing here" />');
    $table = Blade::render('<x-admin.responsive-table label="Records"><tbody></tbody></x-admin.responsive-table>');
    $modal = Blade::render('<x-admin.modal name="confirm" title="Confirm action" description="Review this action">Body</x-admin.modal>');

    expect($card)->toContain('Card heading', 'Card body')
        ->and($badge)->toContain('Needs review', '●')
        ->and($alert)->toContain('role="alert"')
        ->and($empty)->toContain('Nothing here')
        ->and($table)->toContain('overflow-x-auto', '<caption class="sr-only">Records</caption>')
        ->and($modal)->toContain('confirm-title', 'confirm-description');
});
