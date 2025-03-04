<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUserScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StockRecord extends Model
{
    use HasUserScope;
    protected $fillable = [
        'item_id',
        'number_of_bags',
        'user_id',
        'average_quantity',
        'total_quantity',
        'notes',
        'recorded_at'
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'number_of_bags' => 'decimal:2',
        'average_quantity' => 'decimal:2',
        'total_quantity' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
