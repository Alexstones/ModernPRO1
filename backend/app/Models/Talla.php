<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Talla extends Model
{
    protected $fillable = ['categoria','talle','ancho','alto'];

    protected $casts = [
        'ancho' => 'float',
        'alto'  => 'float',
    ];
}
