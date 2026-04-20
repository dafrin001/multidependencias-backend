<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_record_id',
        'type',
        'fixed_asset_id',
        'item_id',
        'quantity',
        'description',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────

    public function deliveryRecord()
    {
        return $this->belongsTo(DeliveryRecord::class);
    }

    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class)->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────

    /**
     * Descripción legible del ítem para mostrar en el acta
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'asset') {
            $name = $this->fixedAsset?->item?->name ?? ($this->description ?? 'Activo Fijo');
            $code = $this->fixedAsset?->inventory_code ?? '';
            return $code ? "{$name} ({$code})" : $name;
        }

        $name = $this->item?->name ?? ($this->description ?? 'Artículo');
        return "{$name} × {$this->quantity} unid.";
    }

    protected $appends = ['display_name'];
}
