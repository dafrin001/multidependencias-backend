<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrAdministrativeSituation extends Model
{
    use HasFactory;

    protected $fillable = [
        'official_id', 'type', 'start_date', 'end_date', 
        'reason', 'document_url', 'status'
    ];

    public function official()
    {
        return $this->belongsTo(Official::class);
    }
}
