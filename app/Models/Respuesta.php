<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Respuesta extends Model
{
    use HasFactory;
    
    protected $table = "respuestas";

    protected $fillable = [
        'estudiante_id',
        'pregunta_id',
        'examen_id',
        'respuesta_id', // Esta es la opción seleccionada
        'calificacion', // Calificación de la respuesta
    ];

    // Relación con el usuario (estudiante)
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    // Relación con la pregunta
    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class);
    }

    // Relación con el examen
    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    // Relación con la opción seleccionada
    public function opcion()
    {
        return $this->belongsTo(Opcion::class, 'respuesta_id');
    }
}
