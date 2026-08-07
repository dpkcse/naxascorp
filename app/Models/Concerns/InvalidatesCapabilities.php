<?php
namespace App\Models\Concerns; use App\Domain\Capabilities\CapabilityCache; trait InvalidatesCapabilities { protected static function bootInvalidatesCapabilities():void { static::saved(fn($model)=>CapabilityCache::forget((int)($model->capability_id??$model->id))); static::deleted(fn($model)=>CapabilityCache::forget((int)($model->capability_id??$model->id))); } }
