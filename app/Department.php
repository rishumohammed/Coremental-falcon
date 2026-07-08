<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::saved(fn($model) => \Cache::forget('departments'));
        static::deleted(fn($model) => \Cache::forget('departments'));
    }
}
