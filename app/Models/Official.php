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
        'position', // Deprecated but kept for compatibility
        'position_id',
        'office_id',
        'email',
        'phone',
        'signature_url',
        'is_active',
        'employment_type',
        'employment_status',
        'entry_date',
        'exit_date',
        'retirement_reason',
        'sigep_updated',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sigep_updated' => 'boolean',
        'entry_date' => 'date',
        'exit_date' => 'date',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function position_rel()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function deliveries()
    {
        return $this->hasMany(DeliveryRecord::class);
    }

    public function activeDeliveries()
    {
        return $this->hasMany(DeliveryRecord::class)->where('is_returned', false);
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function trainingAttendance()
    {
        return $this->hasMany(TrainingAttendee::class);
    }

    public function sstRecords()
    {
        return $this->hasMany(SstRecord::class);
    }

    public function administrativeSituations()
    {
        return $this->hasMany(HrAdministrativeSituation::class);
    }

    public function committeeMemberships()
    {
        return $this->hasMany(HrCommitteeMember::class);
    }

    public function contracts()
    {
        return $this->hasMany(ContractorContract::class);
    }

    public function edlRecords()
    {
        return $this->hasMany(EdlRecord::class);
    }
}
