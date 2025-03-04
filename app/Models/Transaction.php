<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUserScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model

{
    use HasUserScope;
    protected $fillable = ['type', 'category_id', 'amount', 'date', 'description',
    'user_id', 'expense_type'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
