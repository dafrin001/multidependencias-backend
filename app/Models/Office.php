<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function fixedAssets()
    {
        return $this->belongsToMany(FixedAsset::class, 'assignments')
                    ->withPivot('custodian_name', 'assignment_date', 'is_active')
                    ->withTimestamps();
    }
}
