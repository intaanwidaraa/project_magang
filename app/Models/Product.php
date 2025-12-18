<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (! empty($product->sku)) {
                return;
            }
            $product_name = $product->name ?? 'ITM'; 
            $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $product_name), 0, 3));
        
            $product->sku = $prefix . '-' . time();
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
}