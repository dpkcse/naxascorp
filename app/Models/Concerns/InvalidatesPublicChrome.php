<?php
namespace App\Models\Concerns;
use App\Domain\PublicChrome\PublicChromeCache;
trait InvalidatesPublicChrome { protected static function bootInvalidatesPublicChrome(): void { static::saved(fn () => PublicChromeCache::forgetAll()); static::deleted(fn () => PublicChromeCache::forgetAll()); } }
