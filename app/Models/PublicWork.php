<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicWork extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'status',
        'image_url',
        'geometry_type',
        'geometry_data',
        'beneficiaries_count',
        'budget',
        'start_date',
        'end_date',
    ];
}
