<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InventoryEntryItem extends Model
{
    protected $fillable = [
        'inventory_entry_id', 'item_id', 'quantity', 'unit_price', 'notes',
    ];

    public function entry() { return $this->belongsTo(InventoryEntry::class, 'inventory_entry_id'); }
    public function item()  { return $this->belongsTo(Item::class); }
}
