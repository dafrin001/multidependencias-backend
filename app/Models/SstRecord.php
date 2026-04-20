<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SstRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'official_id',
        'type',
        'record_date',
        'provider_name',
        'findings',
        'file_url',
    ];

    public function official()
    {
        return $this->belongsTo(Official::class);
    }
}
