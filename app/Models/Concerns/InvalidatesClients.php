<?php
namespace App\Models\Concerns; use App\Domain\Clients\ClientCache; trait InvalidatesClients { protected static function bootInvalidatesClients():void{static::saved(fn($model)=>ClientCache::forget($model->id));static::deleted(fn($model)=>ClientCache::forget($model->id));} }
