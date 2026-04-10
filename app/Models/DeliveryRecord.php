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

    // Oculta el Base64 en listados para no saturar la respuesta
    protected $hidden = ['signature_data'];

    // accessor para saber si tiene firma
    public function getHasSignatureAttribute(): bool
    {
        return !empty($this->getRawOriginal('signature_data'));
    }

    protected $appends = ['has_signature'];

    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class)->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function official()
    {
        return $this->belongsTo(Official::class);
    }
}
