<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $guarded = ['id'];

    public function employees()
    {
        return $this->hasMany(\App\Employee::class);
    }
}
