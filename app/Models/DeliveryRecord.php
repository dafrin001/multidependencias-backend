<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'acta_number',
        'type',
        'fixed_asset_id',
        'item_id',
        'quantity',
        'official_id',
        'delivered_by',
        'delivery_date',
        'notes',
        'signature_data',
        'is_returned',
        'returned_date',
        'return_notes',
    ];

    protected $casts = [
        'delivery_date'  => 'date',
        'returned_date'  => 'date',
        'is_returned'    => 'boolean',
    ];

    // signature_data se oculta en listados (es un texto Base64 pesado)
    protected $hidden = ['signature_data'];

    // accessor para saber si tiene firma
    public function getHasSignatureAttribute(): bool
    {
        return !empty($this->getRawOriginal('signature_data'));
    }

    protected $appends = ['has_signature'];

    // ── Relaciones ─────────────────────────────────────────────────────

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class)
            ->with(['fixedAsset.item.category', 'item.category']);
    }

    /** @deprecated usa deliveryItems() en su lugar */
    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class)->withTrashed();
    }

    /** @deprecated usa deliveryItems() en su lugar */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function official()
    {
        return $this->belongsTo(Official::class);
    }
}
