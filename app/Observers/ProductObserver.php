<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */

    public function creating(Product $product): void
    {
        if (empty($product->sku)) {
            $prefix = Str::upper(Str::substr($product->name, 0, 3));

            $product->sku = $prefix . '-001';
        }
    }

    public function created(Product $product): void
    {
        $prefix = Str::upper(Str::substr($product->name, 0, 3));

        $paddedId = str_pad($product->id, 3, '0', STR_PAD_LEFT);

        $product->sku = $prefix . '-' . $paddedId;
        $product->save();
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
