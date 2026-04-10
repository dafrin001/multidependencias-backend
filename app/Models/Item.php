<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'is_asset',
        'stock',
        'min_stock',
    ];

    protected $casts = [
        'is_asset'  => 'boolean',
        'stock'     => 'integer',
        'min_stock' => 'integer',
    ];

    public function getIsLowStockAttribute(): bool
    {
        return !$this->is_asset && $this->stock <= $this->min_stock;
    }

    protected $appends = ['is_low_stock'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function fixedAssets()
    {
        return $this->hasMany(FixedAsset::class);
    }

    public function deliveries()
    {
        return $this->hasMany(DeliveryRecord::class);
    }
}
