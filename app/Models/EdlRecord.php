<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EdlRecord extends Model
{
    use HasFactory;

    protected $table = 'hr_edl_records';

    protected $fillable = [
        'official_id',
        'year',
        'period',
        'score',
        'compromises',
        'feedback',
        'status',
        'file_url'
    ];

    public function official()
    {
        return $this->belongsTo(Official::class);
    }
}
