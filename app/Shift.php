<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start_time', 'end_time'];

    protected static function booted()
    {
        static::saved(fn($model) => \Cache::forget('shifts'));
        static::deleted(fn($model) => \Cache::forget('shifts'));
    }
}
