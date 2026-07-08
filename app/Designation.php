<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected static function booted()
    {
        static::saved(fn($model) => \Cache::forget('designations'));
        static::deleted(fn($model) => \Cache::forget('designations'));
    }
}
