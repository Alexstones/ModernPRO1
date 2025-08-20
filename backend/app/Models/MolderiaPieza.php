<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MolderiaPieza extends Model
{
    protected $fillable = [
        'molderia_id','nombre_original','nombre_pieza','prenda','genero',
        'lado','talla','cantidad','notas','orden','mime','size','path',
    ];

    public function molderia()
    {
        return $this->belongsTo(Molderia::class);
    }
}
