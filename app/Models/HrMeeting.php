<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_committee_id', 'title', 'meeting_date', 'meeting_time', 
        'location', 'agenda', 'minutes_content', 'status', 'file_url'
    ];

    public function committee()
    {
        return $this->belongsTo(HrCommittee::class, 'hr_committee_id');
    }
}
