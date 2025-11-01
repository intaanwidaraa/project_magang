<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Product;

class StockCorrection extends Model
{
    use HasFactory;

    // Izinkan semua kolom diisi
    protected $guarded = [];

    public function stockRequisition(): BelongsTo
    {
        return $this->belongsTo(StockRequisition::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function product(): BelongsTo
    {
        // Ini mengasumsikan ada kolom 'product_id' di tabel stock_corrections
        return $this->belongsTo(Product::class);
    }
}