<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HrSetting extends Model {
    protected $fillable = ['key', 'label', 'value', 'type'];
}
