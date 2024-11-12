<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigImagenesPrincipal extends Model
{
    use HasFactory;

    // Definir la tabla que se utilizará
    protected $table = 'config_imagenes_principal';

    // Campos que se pueden asignar de manera masiva
    protected $fillable = [
        'nombre',
        'url',
        'estado',
    ];

}
