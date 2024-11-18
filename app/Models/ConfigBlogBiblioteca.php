<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigBlogBiblioteca extends Model
{
    use HasFactory;

    protected $table = 'config_blog_biblioteca';

    // Definir los campos que son asignables
    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_publicacion',
        'tipo',
        'activo',
        'url_imagen',
        'url_archivo'
    ];
}
