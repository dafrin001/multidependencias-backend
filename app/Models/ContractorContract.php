<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractorContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'official_id', 'contract_number', 'cdp', 'rp', 'rubro', 'contract_type', 'object', 
        'supervisor_name', 'supervisor_position', 'arl_risk_level',
        'start_date', 'end_date', 'total_contract_value', 'monthly_payment_value', 'is_active', 'observations'
    ];

    public function official()
    {
        return $this->belongsTo(Official::class);
    }

    public function deductionRules()
    {
        return $this->hasMany(ContractDeductionRule::class, 'contract_id');
    }

    public function payments()
    {
        return $this->hasMany(PayrollItem::class, 'contractor_contract_id');
    }
}
