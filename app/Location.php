<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected static function booted()
    {
        static::saved(fn($model) => \Cache::forget('locations'));
        static::deleted(fn($model) => \Cache::forget('locations'));
    }
}
