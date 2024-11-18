<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigAnuncio extends Model
{
    use HasFactory;

    // Definir la tabla que corresponde a este modelo
    protected $table = 'config_anuncios';

    // Definir los campos que son asignables
    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_publicacion',
        'tipo',
        'activo',
        'url_imagen'
    ];

}
