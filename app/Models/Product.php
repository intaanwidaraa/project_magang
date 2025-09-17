<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    // --- MULAI PENAMBAHAN ---
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            // Cek jika SKU belum diisi, maka buat otomatis
            if (empty($product->sku)) {
                // Ambil 3 huruf pertama dari nama produk
                $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $product->name), 0, 3));
                // Tambahkan ID unik berdasarkan waktu
                $product->sku = $prefix . '-' . time();
            }
        });
    }
    // --- SELESAI PENAMBAHAN ---

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