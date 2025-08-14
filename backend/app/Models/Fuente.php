<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Fuente extends Model
{
    protected $table = 'fuentes';

    protected $fillable = [
        'nombre',
        'archivo',   // ruta relativa en el disco "public", p.ej: "fuentes/mi_font.ttf"
    ];

    // Para que el front reciba 'path' y 'url' sin mapear manualmente
    protected $appends = ['path', 'url'];

    public function getPathAttribute()
    {
        return $this->archivo;
    }

    public function getUrlAttribute()
    {
        return $this->archivo ? Storage::url($this->archivo) : null;
    }

    public function perfiles()
    {
        return $this->hasMany(PerfilFuente::class, 'fuente_id');
    }
}
