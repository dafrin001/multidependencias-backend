<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RufeDemographic extends Model
{
    use HasFactory;

    protected $fillable = [
        'rufe_record_id',
        'nombres',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'parentesco',
        'genero',
        'fecha_nacimiento',
        'pertenencia_etnica',
        'telefono',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function record()
    {
        return $this->belongsTo(RufeRecord::class, 'rufe_record_id');
    }
}
