<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

   
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->sku)) {
                $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $product->name), 0, 3));
                $product->sku = $prefix . '-' . time();
            }
        });
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function supplierItems(): HasMany
    {
        return $this->hasMany(SupplierItem::class);
    }

    public function getSisaLifetimeAttribute()
    {
        if (!$this->lifetime_penggunaan || !$this->updated_at) {
            return null;
        }

        return $this->lifetime_penggunaan - now()->diffInDays($this->updated_at);
    }
}