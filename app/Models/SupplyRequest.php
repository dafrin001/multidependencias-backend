<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SupplyRequest extends Model
{
    protected $fillable = [
        'request_number', 'office_id', 'requested_by', 'request_date',
        'needed_by', 'status', 'dispatch_date', 'dispatched_by',
        'notes', 'rejection_reason',
    ];

    public function office() { return $this->belongsTo(Office::class); }
    public function items()  { return $this->hasMany(SupplyRequestItem::class); }
}
