<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUserScope;

class Item extends Model
{
    use HasUserScope;
    protected $fillable = [
        'name',
        'user_id',
        'code',
        'type',
        'category',
        'unit',
        'unit_price',
        'minimum_stock',
        'maximum_stock',
        'is_active',
        'description',
        'location',
        'supplier'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Constants for item types
    const TYPE_RAW_MATERIAL = 'raw_material';
    const TYPE_FINISHED_GOOD = 'finished_good';
    const TYPE_PACKAGING = 'packaging';

    // Constants for units
    const UNIT_KG = 'kg';
    const UNIT_GRAM = 'gram';
    const UNIT_PIECE = 'piece';
    const UNIT_BAG = 'bag';

    public static function getTypes(): array
    {
        return [
            self::TYPE_RAW_MATERIAL => 'Raw Material',
            self::TYPE_FINISHED_GOOD => 'Finished Good',
            self::TYPE_PACKAGING => 'Packaging Material'
        ];
    }

    public static function getUnits(): array
    {
        return [
            self::UNIT_KG => 'Kilogram',
            self::UNIT_GRAM => 'Gram',
            self::UNIT_PIECE => 'Piece',
            self::UNIT_BAG => 'Bag'
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

