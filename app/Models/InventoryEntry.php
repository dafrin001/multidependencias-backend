<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InventoryEntry extends Model
{
    protected $fillable = [
        'entry_number', 'supplier_id', 'invoice_number', 'entry_date',
        'received_by', 'total_amount', 'notes', 'status',
    ];

    public function supplier() { return $this->belongsTo(Provider::class, 'supplier_id'); }
    public function items()    { return $this->hasMany(InventoryEntryItem::class); }
}
