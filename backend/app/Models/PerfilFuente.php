<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilFuente extends Model
{
    protected $table = 'perfiles_fuente';

    protected $fillable = [
        'perfil',
        'fuente_id',     // FK a fuentes.id
        'cont',
        'letra_dir',     // ruta o carpeta relativa en storage/app/public
        'contorno_dir',  // ruta o carpeta relativa en storage/app/public
    ];

    public function fuenteRef()
    {
        return $this->belongsTo(Fuente::class, 'fuente_id');
    }
}
