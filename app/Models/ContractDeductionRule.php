<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractDeductionRule extends Model
{
    use HasFactory;

    protected $fillable = ['contract_id', 'name', 'type', 'value', 'is_recurrent'];

    public function contract()
    {
        return $this->belongsTo(ContractorContract::class, 'contract_id');
    }
}
