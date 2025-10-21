<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $guarded = [];

    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

   
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
