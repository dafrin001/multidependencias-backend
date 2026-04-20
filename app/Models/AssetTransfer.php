<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AssetTransfer extends Model
{
    protected $fillable = [
        'transfer_number', 'fixed_asset_id', 'from_office_id', 'to_office_id',
        'transferred_by', 'received_by', 'transfer_date', 'notes', 'status',
    ];

    public function fixedAsset()  { return $this->belongsTo(FixedAsset::class); }
    public function fromOffice()  { return $this->belongsTo(Office::class, 'from_office_id'); }
    public function toOffice()    { return $this->belongsTo(Office::class, 'to_office_id'); }
}
