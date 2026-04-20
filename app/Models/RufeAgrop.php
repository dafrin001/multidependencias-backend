<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RufeAgrop extends Model
{
    use HasFactory;

    protected $table = 'rufe_agropecuarios';

    protected $fillable = [
        'rufe_record_id',
        'tipo_cultivo',
        'unidad_medida',
        'area_cantidad',
        'sector_pecuario_especie',
        'cantidad_unidades',
    ];

    public function record()
    {
        return $this->belongsTo(RufeRecord::class, 'rufe_record_id');
    }
}
