<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandUseSector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'area_km2',
        'geometry_json',
        'description',
        'valuation',
    ];
}
