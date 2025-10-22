<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}