<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Molderia extends Model
{
    protected $fillable = [
        'coleccion', 'nota_general', 'tallas', 'total_piezas',
    ];

    protected $casts = [
        'tallas' => 'array',
    ];

    public function piezas()
    {
        return $this->hasMany(MolderiaPieza::class);
    }
}
