<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AssetDisposal extends Model
{
    protected $fillable = [
        'disposal_number', 'fixed_asset_id', 'reason', 'disposal_date',
        'authorized_by', 'processed_by', 'description', 'resolution_number',
    ];

    public function fixedAsset() { return $this->belongsTo(FixedAsset::class); }
}
