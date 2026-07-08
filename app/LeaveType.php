<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_default'];

    protected static function booted()
    {
        static::saved(fn($model) => \Cache::forget('leave_types'));
        static::deleted(fn($model) => \Cache::forget('leave_types'));
    }
}
