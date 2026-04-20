<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AssetMaintenance extends Model
{
    protected $fillable = [
        'fixed_asset_id', 'type', 'maintenance_date', 'next_maintenance_date',
        'technician', 'cost', 'description', 'status',
    ];

    protected $casts = ['cost' => 'decimal:2'];

    public function fixedAsset() { return $this->belongsTo(FixedAsset::class); }
}
