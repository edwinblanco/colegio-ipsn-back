<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Grado;

class Examen extends Model
{
    use HasFactory;

    protected $table = "examenes";

    protected $fillable = [
        'materia_id',
        'profesor_id',
        'titulo',
        'descripcion',
        'fecha_limite',
        'estado',
    ];

    // Relación con la tabla materias
    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    // Relación con la tabla users (profesores)
    public function profesor()
    {
        return $this->belongsTo(User::class);
    }

    public function preguntas()
    {
        return $this->hasMany(Pregunta::class);
    }

    public function respuestas()
    {
        return $this->hasMany(Respuesta::class);
    }

    public function grados()
    {
        return $this->belongsToMany(Grado::class, 'examen_grado', 'examen_id', 'grado_id')
                    ->withPivot('fecha_asignacion', 'sede_id') // Agregamos sede_id al pivot
                    ->withTimestamps();
    }

    public function estudiantes()
    {
        return $this->belongsToMany(User::class, 'examen_estudiante', 'examen_id', 'estudiante_id')
                    ->withPivot('fecha_presentacion', 'puntaje', 'estado')
                    ->withTimestamps();
    }

}
