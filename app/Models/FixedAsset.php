<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_id',
        'provider_id',
        'inventory_code',
        'serial_number',
        'purchase_price',
        'status',
        'image_url',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(Assignment::class)->where('is_active', true);
    }

    public function offices()
    {
        return $this->belongsToMany(Office::class, 'assignments')
                    ->withPivot('custodian_name', 'assignment_date', 'is_active')
                    ->withTimestamps();
    }
}
