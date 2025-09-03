<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function stockMovements(): HasMany // <-- Tambahkan method ini
    {
        return $this->hasMany(StockMovement::class);
    }
}
