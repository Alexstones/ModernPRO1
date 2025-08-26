<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Talla extends Model
{
    protected $fillable = [
        'categoria',
        'talle',
        'ancho',
        'alto',
        'molderias',       // JSON: array de URLs
        'composiciones',   // JSON: array de URLs (renders guardados)
    ];

    protected $casts = [
        'ancho'         => 'float',
        'alto'          => 'float',
        'molderias'     => 'array',
        'composiciones' => 'array',
    ];
}

