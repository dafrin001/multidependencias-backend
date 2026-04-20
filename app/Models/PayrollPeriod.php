<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'year',
        'type',
        'start_date',
        'end_date',
        'status',
        'resolution_number',
        'resolution_date',
        'total_amount',
    ];

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }
}
