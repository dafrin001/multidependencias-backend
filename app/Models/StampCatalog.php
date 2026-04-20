<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StampCatalog extends Model {
    protected $table = 'stamp_catalog';
    protected $fillable = ['name', 'default_value', 'type'];
}
