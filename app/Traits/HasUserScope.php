<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasUserScope
{
    protected static function bootHasUserScope()
    {
        static::creating(function ($model) {
            if (!$model->user_id) {
                $model->user_id = Auth::id();
            }
        });
    }

    public function scopeForUser(Builder $query)
    {
        return $query->where('user_id', Auth::id());
    }
}
