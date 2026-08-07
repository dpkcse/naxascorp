<?php
namespace App\Domain\Media;
use App\Models\MediaAsset; use Illuminate\Database\Eloquent\Model;
final class MediaResolver
{
 public function asset(Model $model,string $slot,int $order=1):?MediaAsset{$type=array_search($model::class,MediaRegistry::morphMap(),true);if($type===false||!isset(MediaRegistry::definition($type)['slots'][$slot])){return null;}return MediaAsset::query()->whereHas('usages',fn($query)=>$query->where('mediable_type',$type)->where('mediable_id',$model->getKey())->where('slot',$slot)->where('display_order',$order))->first();}
 /** @return array{url: ?string, alt: ?string, width: ?int, height: ?int, aspect_ratio: ?float, mime_type: ?string} */
 public function data(Model $model,string $slot,?string $legacyPath=null,bool $absolute=false,int $order=1):array{return app(MediaUrl::class)->data($this->asset($model,$slot,$order),$legacyPath,$absolute);}
}
