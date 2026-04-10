<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixed_asset_id',
        'office_id',
        'custodian_name',
        'assignment_date',
        'is_active',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
