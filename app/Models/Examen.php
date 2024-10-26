<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}
