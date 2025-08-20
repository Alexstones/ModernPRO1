<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Molde extends Model
{
    protected $fillable = [
        'nombre',
        'camiseta_frente_path', 'camiseta_frente_orig',
        'camiseta_espalda_path','camiseta_espalda_orig',
        'short_izq_path','short_izq_orig',
        'short_der_path','short_der_orig',
        'manga_izq_path','manga_izq_orig',
        'manga_der_path','manga_der_orig',
    ];
}
