<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrCommitteeMember extends Model
{
    use HasFactory;

    protected $fillable = ['hr_committee_id', 'official_id', 'role', 'appointment_date'];

    public function committee()
    {
        return $this->belongsTo(HrCommittee::class, 'hr_committee_id');
    }

    public function official()
    {
        return $this->belongsTo(Official::class);
    }
}
