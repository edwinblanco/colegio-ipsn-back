<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagenPregunta extends Model
{
    use HasFactory;

    protected $table = 'imagenes_preguntas';

    // Si deseas asignar los atributos de forma masiva, define los campos que pueden ser llenados
    protected $fillable = [
        'id_pregunta',
        'url',
    ];

    // Define la relación con el modelo de Pregunta
    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'id_pregunta');
    }
}
