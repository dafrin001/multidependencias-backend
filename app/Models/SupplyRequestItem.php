<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SupplyRequestItem extends Model
{
    protected $fillable = [
        'supply_request_id', 'item_id', 'requested_quantity',
        'approved_quantity', 'dispatched_quantity', 'notes',
    ];

    public function request() { return $this->belongsTo(SupplyRequest::class, 'supply_request_id'); }
    public function item()    { return $this->belongsTo(Item::class); }
}
