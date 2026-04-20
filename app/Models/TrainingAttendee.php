<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingAttendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_program_id',
        'official_id',
        'attended',
        'score',
        'certificate_url',
    ];

    public function program()
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    public function official()
    {
        return $this->belongsTo(Official::class);
    }
}
