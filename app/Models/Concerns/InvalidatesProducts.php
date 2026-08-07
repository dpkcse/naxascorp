<?php
namespace App\Models\Concerns;
use App\Domain\Products\ProductCache; use App\Models\{Product,ProductFeature};
trait InvalidatesProducts
{
    protected static function bootInvalidatesProducts(): void
    {
        $invalidate = function ($model): void {
            $productId = match (true) {
                $model instanceof Product => (int) $model->id,
                $model instanceof ProductFeature => (int) $model->group()->value('product_id'),
                isset($model->product_id) => (int) $model->product_id,
                default => 0,
            };
            ProductCache::forget($productId);
        };
        static::saved($invalidate);
        static::deleted($invalidate);
    }
}
