<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['type', 'category_id', 'amount', 'date', 'description', 'expense_type'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
