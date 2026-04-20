<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'scheduled_date',
        'hours',
        'max_attendees',
        'status',
    ];

    public function attendees()
    {
        return $this->hasMany(TrainingAttendee::class);
    }
}
