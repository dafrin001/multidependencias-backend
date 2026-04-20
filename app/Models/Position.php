<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'grade',
        'base_salary',
        'functions',
        'level',
    ];

    public function officials()
    {
        return $this->hasMany(Official::class);
    }
}
