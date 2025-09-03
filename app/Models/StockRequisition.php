<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class StockRequisition extends Model
{
    use HasFactory;

    protected $guarded = [];

    // TAMBAHKAN PROPERTI INI
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'items' => 'array',
    ];

    public function stockMovements(): MorphMany // <-- Tambahkan method ini
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }
}
