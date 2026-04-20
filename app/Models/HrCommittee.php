<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrCommittee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'valid_from', 'valid_to'];

    public function members()
    {
        return $this->hasMany(HrCommitteeMember::class);
    }

    public function meetings()
    {
        return $this->hasMany(HrMeeting::class);
    }
}
