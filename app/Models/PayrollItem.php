<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_period_id',
        'official_id',
        'contractor_contract_id',
        'salary_base',
        'allowances',
        'overtime',
        'deductions_health',
        'deductions_pension',
        'net_pay',
        'details',
    ];

    public function contract()
    {
        return $this->belongsTo(ContractorContract::class, 'contractor_contract_id');
    }

    protected $casts = [
        'details' => 'json',
    ];

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function official()
    {
        return $this->belongsTo(Official::class);
    }
}
