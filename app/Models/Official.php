<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\DeliveryRecord;

class Official extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'document_number',
        'document_type',
        'position',
        'office_id',
        'email',
        'phone',
        'signature_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function deliveries()
    {
        return $this->hasMany(DeliveryRecord::class);
    }

    public function activeDeliveries()
    {
        return $this->hasMany(DeliveryRecord::class)->where('is_returned', false);
    }
}
