<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RufeRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'departamento',
        'municipio',
        'evento',
        'fecha_evento',
        'fecha_rufe',
        'ubicacion_tipo',
        'corregimiento',
        'vereda_sector_barrio',
        'direccion',
        'forma_tenencia',
        'estado_bien',
        'alojamiento_actual_tipo',
        'tipo_bien',
        'observaciones',
        'vo_bo',
    ];

    protected $casts = [
        'fecha_evento' => 'date',
        'fecha_rufe' => 'date',
    ];

    public function demographics()
    {
        return $this->hasMany(RufeDemographic::class);
    }

    public function agros()
    {
        return $this->hasMany(RufeAgrop::class);
    }
}
