<?php

use App\Domain\Media\{MediaRegistry, MediaResolver, MediaUpload, MediaUrl};
use App\Models\{BrandingSetting, MediaAsset, MediaUsage, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function mediaAdministrator(): User
{
    return User::factory()->create(['administrator_status' => 'active', 'email_verified_at' => now()]);
}

test('media library requires authentication', function () { $this->get('/content/media')->assertRedirect('/login'); });
test('no public upload endpoint exists', function () { $this->post('/media', [])->assertNotFound(); });

test('valid raster upload records safe metadata', function (string $extension, string $mime) {
    Storage::fake('public'); $user = mediaAdministrator();
    $file = UploadedFile::fake()->image('dangerous-name.'.$extension, 64, 32)->mimeType($mime);
    $this->actingAs($user)->post(route('admin.media.store'), ['file' => $file, 'media_type' => 'image'])->assertRedirect();
    $asset = MediaAsset::query()->firstOrFail();
    expect($asset->mime_type)->toBe($mime)->and($asset->extension)->toBe($extension)->and($asset->width)->toBe(64)->and($asset->height)->toBe(32)->and($asset->size_bytes)->toBeGreaterThan(0)->and($asset->checksum_sha256)->toHaveLength(64)->and($asset->uploaded_by)->toBe($user->id)->and($asset->stored_name)->not->toContain('dangerous-name');
    Storage::disk('public')->assertExists($asset->relativePath());
})->with([['jpg','image/jpeg'],['png','image/png']]);

test('unsafe and spoofed uploads are rejected', function (string $name, string $contents) {
    Storage::fake('public'); $file = UploadedFile::fake()->createWithContent($name, $contents);
    $this->actingAs(mediaAdministrator())->post(route('admin.media.store'), ['file'=>$file,'media_type'=>'image'])->assertSessionHasErrors('file');
    expect(MediaAsset::query()->count())->toBe(0);
})->with([['payload.svg','<svg onload="alert(1)"></svg>'],['payload.php','<?php echo 1;'],['payload.html','<script>alert(1)</script>'],['payload.js','alert(1)'],['fake.jpg','not an image'],['escape/../../fake.jpg','not an image']]);

test('archived media is not selectable but remains resolvable', function () {
    Storage::fake('public'); Storage::disk('public')->put('media/2026/08/a.png','x');
    $asset=MediaAsset::query()->create(['directory'=>'media/2026/08','filename'=>'media/2026/08/a.png','stored_name'=>'a.png','extension'=>'png','mime_type'=>'image/png','size_bytes'=>1,'width'=>16,'height'=>16,'aspect_ratio'=>1,'media_type'=>'image','status'=>'archived']);
    expect(MediaAsset::query()->selectable()->count())->toBe(0)->and(app(MediaUrl::class)->url($asset))->toBe('/storage/media/2026/08/a.png');
});

test('referenced deletion is blocked and dependency uses approved labels', function () {
    Storage::fake('public'); Storage::disk('public')->put('media/2026/08/a.png','x'); $user=mediaAdministrator(); $branding=BrandingSetting::query()->create(['singleton_key'=>1]);
    $asset=MediaAsset::query()->create(['directory'=>'media/2026/08','filename'=>'media/2026/08/a.png','stored_name'=>'a.png','extension'=>'png','mime_type'=>'image/png','size_bytes'=>1,'width'=>16,'height'=>16,'aspect_ratio'=>1,'media_type'=>'image','status'=>'active']);
    MediaUsage::query()->create(['media_asset_id'=>$asset->id,'mediable_type'=>'branding','mediable_id'=>$branding->id,'slot'=>'primary_logo','display_order'=>1]);
    $this->actingAs($user)->delete(route('admin.media.destroy',$asset),['confirm'=>1])->assertConflict();
    $this->actingAs($user)->get(route('admin.media.show',$asset))->assertSee('Branding')->assertSee('Primary logo')->assertDontSee('App\\Models');
});

test('metadata update never renames stored file', function () {
    $asset=MediaAsset::query()->create(['directory'=>'media/2026/08','filename'=>'media/2026/08/a.png','stored_name'=>'a.png','extension'=>'png','mime_type'=>'image/png','size_bytes'=>1,'width'=>16,'height'=>16,'aspect_ratio'=>1,'media_type'=>'image','status'=>'active']);
    $this->actingAs(mediaAdministrator())->patch(route('admin.media.update',$asset),['title'=>'Renamed','alt_text'=>'Useful context'])->assertRedirect();
    expect($asset->fresh()->stored_name)->toBe('a.png')->and($asset->fresh()->alt_text)->toBe('Useful context');
});

test('registry is bounded and picker has accessibility hooks', function () {
    expect(MediaRegistry::morphMap())->toHaveKeys(['branding','homepage_setting','page','solution','product','industry','capability','work_process','client','testimonial','article','case_study']);
    $view=view('components.admin.media-picker',['type'=>'branding','modelId'=>1,'slot'=>'primary_logo'])->render();
    expect($view)->toContain('role="dialog"')->toContain('aria-modal="true"')->toContain('@keydown.escape.window')->not->toContain('remote_url');
});
